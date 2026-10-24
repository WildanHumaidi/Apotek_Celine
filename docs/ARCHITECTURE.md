# SYSTEM ARCHITECTURE

Dokumen ini menjelaskan arsitektur teknis, pola desain, batas komponen, alur otentikasi, diagram status pesanan, serta manajemen concurrency pada aplikasi Apotek Celine.

---

## 1. Arsitektur Berlapis (Layered Architecture)

Aplikasi dibangun di atas framework Laravel 10 mengadopsi pola MVC (Model-View-Controller) dengan pemisahan tanggung jawab yang jelas:

```
[ Browser Client ]
       │
       ▼ (HTTP Request / HTTPS)
[ HTTP Kernel & Global Middleware ] (CORS, TrimStrings, EncryptCookies, StartSession)
       │
       ▼
[ Routing Layer: routes/web.php ]
       │
       ▼
[ Route Middleware ]
  ├── auth (Verifikasi session login pengguna)
  └── cek.peran:admin|apoteker|pasien (Role-Based Access Control)
       │
       ▼
[ Controllers Layer ]
  ├── Auth\ (Login, Register, Google OAuth)
  ├── Admin\ (Obat, User, Transaksi, Laporan PDF)
  ├── Apoteker\ (Transaksi Resep & Non-Resep, Pesanan Online, Laporan)
  └── Pasien\ (Katalog, Keranjang Session, Checkout Transaksi, Riwayat)
       │
       ▼
[ Domain & Model Layer (Eloquent ORM) ]
  ├── User, Peran, Pasien, Obat
  └── TransaksiPenjualan, DetailTransaksi, PasienDenganResep
       │
       ▼
[ Database Layer: MySQL / SQLite (Testing) ]
```

---

## 2. Alur Autentikasi & Otorisasi Berbasis Peran (RBAC)

Aplikasi menggunakan role-based access control melalui relasi `users.id_peran -> peran.id`. Setiap peran diarahkan ke dashboard khusus:

```mermaid
sequenceDiagram
    autonumber
    actor User as Pengguna
    participant Web as Browser
    participant Router as routes/web.php
    participant Middleware as Middleware (auth & cek.peran)
    participant Controller as Controller Sesuai Peran
    participant View as Blade View

    User->>Web: Mengakses URL (misal: /admin/dashboard)
    Web->>Router: GET /admin/dashboard
    Router->>Middleware: Cek auth & peran 'admin'
    alt Pengguna Belum Login
        Middleware-->>Web: Redirect 302 -> /login
    else Pengguna Bukan Admin
        Middleware-->>Web: Abort 403 Forbidden (Akses Ditolak)
    else Pengguna Valid (Admin)
        Middleware->>Controller: Forward Request ke Admin\DashboardController
        Controller->>View: Render admin.dashboard
        View-->>Web: HTTP 200 OK
    end
```

### Mekanisme Pengalihan (`/redirect`)
Saat pengguna berhasil login (baik via kredensial form maupun Google OAuth), pengguna diarahkan ke rute sentral `/redirect`:
- Jika `isAdmin()` -> dialihkan ke `admin.dashboard` (`/admin/dashboard`).
- Jika `isApoteker()` -> dialihkan ke `apoteker.dashboard` (`/apoteker/dashboard`).
- Jika `isPasien()` -> dialihkan ke `pasien.dashboard` (`/pasien/dashboard`).
- Jika peran tidak dikenali -> memicu HTTP 403.

---

## 3. Siklus Hidup Pesanan & Konservasi Stok (Order State Machine)

Setiap pesanan online yang dibuat oleh pasien melalui checkout melewati serangkaian status operasional yang dikelola oleh staf apotek:

```mermaid
stateDiagram-v2
    [*] --> menunggu_pembayaran: Pasien Checkout (Stok Berkurang)
    
    menunggu_pembayaran --> terbayar: Apoteker Konfirmasi Pembayaran
    terbayar --> diproses: Apoteker Menyiapkan Obat
    diproses --> siap_diambil: Obat Siap di Kasir Apotek
    siap_diambil --> selesai: Pasien Mengambil Obat di Apotek
    
    menunggu_pembayaran --> dibatalkan: Pembatalan (Stok Dikembalikan Otomatis)
    terbayar --> dibatalkan: Pembatalan (Stok Dikembalikan Otomatis)
    diproses --> dibatalkan: Pembatalan (Stok Dikembalikan Otomatis)
    
    dibatalkan --> diproses: Re-aktivasi (Stok Dikurangi Ulang dengan Validasi)
    
    selesai --> [*]
```

### Aturan Konservasi Stok:
1. **Saat Pembuatan Pesanan:** Stok obat langsung dipotong (`decrement`) di dalam database transaction dengan `Obat::lockForUpdate()` untuk mencegah race condition.
2. **Saat Pembatalan Pesanan (`dibatalkan`):** Seluruh item pada `detail_transaksi` otomatis dikembalikan (`increment`) ke stok master obat.
3. **Saat Re-aktivasi:** Jika pesanan dibatalkan hendak diproses kembali, sistem memvalidasi kecukupan stok fisik terlebih dahulu sebelum memotong kembali.

---

## 4. Alur Transaksi Checkout Pasien (Concurrency Safe Flow)

```mermaid
sequenceDiagram
    autonumber
    actor Pasien
    participant Web as Frontend / Browser
    participant Controller as PasienController@processCheckout
    participant DB as Database (MySQL Transaction)

    Pasien->>Web: Klik 'Konfirmasi Pembayaran & Buat Pesanan'
    Web->>Controller: POST /pasien/checkout/proses
    Controller->>DB: DB::beginTransaction()
    loop Setiap Item di Keranjang Belanja
        Controller->>DB: SELECT * FROM obat WHERE id_obat = ? FOR UPDATE
        Note over Controller,DB: Mengunci baris obat untuk isolasi concurrency
        alt Stok Kurang dari Permintaan
            Controller-->>DB: DB::rollBack()
            Controller-->>Web: Redirect back dengan pesan error stok habis
        else Stok Mencukupi
            Note over Controller: Hitung subtotal menggunakan harga resmi DB ($obat->harga)
        end
    end
    Controller->>DB: INSERT INTO transaksi_penjualan
    loop Setiap Item di Keranjang Belanja
        Controller->>DB: INSERT INTO detail_transaksi
        Controller->>DB: UPDATE obat SET stok = stok - ?
    end
    Controller->>DB: DB::commit()
    Controller->>Web: session()->forget('cart')
    Controller-->>Pasien: Redirect ke Riwayat Pesanan (No. Transaksi Baru)
```

---

## 5. Manajemen Berkas & Aset (File Storage)

- Berkas foto obat diunggah oleh Admin melalui modul `Admin\ObatController`.
- File disimpan menggunakan driver filesystem `public` Laravel (`storage/app/public/obat_photos/`).
- Akses berkas melalui web dilayani melalui symlink `public/storage` yang terhubung ke `storage/app/public`.
- Generator PDF menggunakan pustaka `barryvdh/laravel-dompdf` yang merender template Blade khusus cetak secara headless di sisi server.
