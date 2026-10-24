# PRODUCT REQUIREMENTS DOCUMENT (PRD)

> **Status Dokumen:** `DRAFT — REQUIRES OWNER VALIDATION`  
> **Versi:** 1.0 (Rekonstruksi Berdasarkan Kode Sumber & Skema Aktual)  
> **Terakhir Diperbarui:** 25 Oktober 2026

---

## 1. Ikhtisar Produk (Product Overview)

**Apotek Celine** adalah aplikasi web manajemen apotek dan penjualan obat terintegrasi. Sistem ini dirancang untuk melayani tiga peran utama: Administrator, Apoteker, dan Pasien (Pembeli). Aplikasi memfasilitasi transaksi resep dokter, transaksi langsung tanpa resep, serta pemesanan obat online mandiri oleh pasien dengan opsi pembayaran transfer bank dan pengambilan langsung di gerai apotek (BOPIS / Buy Online, Pick-up in Store).

---

## 2. Target Pengguna & Peran (User Personas & RBAC)

| Peran (Role) | Deskripsi Tanggung Jawab | Akses Utama |
| :--- | :--- | :--- |
| **Admin (`admin`)** | Manajemen master data obat, pengelolaan akun pengguna/staf, pemantauan seluruh transaksi, dan pelaporan eksekutif. | `/admin/*` |
| **Apoteker (`apoteker`)** | Pelayanan transaksi obat resep dokter, transaksi non-resep walk-in, pemrosesan pesanan online masuk, dan laporan penjualan apoteker. | `/apoteker/*` |
| **Pasien (`pasien`)** | Penjelajahan katalog obat, pengelolaan keranjang belanja, checkout pemesanan online, dan pemantauan riwayat pesanan. | `/pasien/*` |

---

## 3. Batasan & Ruang Lingkup (Scope & Non-Goals)

### 3.1 Dalam Lingkup (In-Scope)
- Autentikasi multi-role berbasis email/password dan opsi Google OAuth (Socialite).
- Manajemen data master obat (nama, deskripsi, kategori, harga, stok, dan foto).
- Manajemen akun staf dan pengguna oleh Administrator.
- Alur transaksi apoteker walk-in (resep dokter dan non-resep).
- E-commerce katalog obat mandiri untuk pasien.
- Alur checkout dengan metode pembayaran transfer bank dan penjemputan fisik di apotek (`transfer_bank_ambil_apotek`).
- Pelaporan rekapitulasi transaksi dan stok obat (tampilan web & ekspor PDF).

### 3.2 Di Luar Lingkup (Non-Goals)
- Pengiriman kurir ekspedisi pihak ketiga (JNE/J&T/GoSend) — pesanan saat ini berfokus pada pengambilan langsung di apotek.
- Payment gateway otomatis (Midtrans/Xendit) — pembayaran diverifikasi secara semi-manual oleh apoteker setelah transfer bank.
- Telekonsultasi dokter langsung via video/chat.

---

## 4. Kebutuhan Fungsional (Functional Requirements)

### Modul Autentikasi & Akun
- **FR-001 (Pendaftaran Mandiri):** Pengguna publik dapat mendaftar sebagai Pasien dengan menyertakan nama, email, password, nomor telepon, dan alamat domisili.
- **FR-002 (Autentikasi Google OAuth):** Pengguna dapat masuk/mendaftar menggunakan akun Google, yang otomatis didaftarkan sebagai Pasien.
- **FR-003 (Role-Based Redirect):** Sistem wajib mengalihkan pengguna setelah login ke dashboard sesuai peran (`/admin/dashboard`, `/apoteker/dashboard`, atau `/pasien/dashboard`).

### Modul Admin
- **FR-004 (Master Data Obat):** Admin dapat membuat, melihat, memperbarui, dan menghapus data obat beserta foto produk.
- **FR-005 (Manajemen Pengguna):** Admin dapat mengelola akun pengguna, mengatur peran (Admin, Apoteker, Pasien), dan mereset password.
- **FR-006 (Pelaporan Transaksi):** Admin dapat memfilter laporan transaksi berdasarkan rentang tanggal dan mengekspornya ke format PDF.
- **FR-007 (Pelaporan Stok):** Admin dapat memantau stok obat tersedikit dan mengunduh laporan PDF stok.

### Modul Apoteker
- **FR-008 (Transaksi Resep Walk-in):** Apoteker dapat menginput resep dokter (nama dokter, data pasien, umur, obat yang diresepkan) dan menerbitkan transaksi penjualan.
- **FR-009 (Transaksi Non-Resep Walk-in):** Apoteker dapat melayani pembelian obat bebas langsung.
- **FR-010 (Manajemen Pesanan Masuk):** Apoteker dapat meninjau pesanan online dari pasien dan memperbarui status pesanan (`menunggu_pembayaran` -> `terbayar` -> `diproses` -> `siap_diambil` -> `selesai` / `dibatalkan`).
- **FR-011 (Laporan Penjualan Apoteker):** Apoteker dapat melihat dan mencetak laporan transaksi yang ditanganinya per periode tanggal.

### Modul Pasien (E-Commerce)
- **FR-012 (Katalog Obat):** Pasien dapat melihat daftar obat yang tersedia (`stok > 0`), harga, kategori, dan deskripsi.
- **FR-013 (Keranjang Belanja):** Pasien dapat menambah obat ke keranjang, memperbarui kuantitas (dengan validasi batas stok), atau menghapus item.
- **FR-014 (Checkout & Pemesanan):** Pasien dapat memproses checkout dengan harga obat database terkini, memotong stok otomatis, dan menerbitkan nomor transaksi unik.
- **FR-015 (Riwayat Pesanan):** Pasien dapat memantau status pesanan dan melihat rincian item pesanan dalam modal detail.

---

## 5. Kebutuhan Non-Fungsional (Non-Functional Requirements)

- **NFR-01 (Integritas Data Transaksi):** Penghapusan akun staf tidak boleh melenyapkan data transaksi historis (`onDelete('set null')`).
- **NFR-02 (Konsistensi Inventaris):** Pembatalan pesanan wajib merestorasi stok obat secara otomatis. Pengurangan stok saat checkout harus tahan terhadap race condition.
- **NFR-03 (Keamanan & Privasi):** Kata sandi di-hash menggunakan Bcrypt. Seluruh form web dilindungi token CSRF. Seluruh output view di-escape untuk mencegah XSS.
- **NFR-04 (Kompatibilitas Lingkungan):** Seluruh penamaan class dan model harus mematuhi konvensi PSR-4 agar berjalan mulus di server berbasis Linux maupun Windows.

---

## 6. PRD-to-Implementation Alignment Table

| ID Kebutuhan | Fitur / Implementasi | Status Keselarasan | Catatan Temuan / Rekonsiliasi |
| :--- | :--- | :--- | :--- |
| **FR-001** | `RegisterController` | `CONFIRMED` | Mendaftarkan User dan Pasien secara sinkron. |
| **FR-002** | `GoogleAuthController` | `RECONCILED` | Telah diperbaiki agar membuat record `Pasien` otomatis. |
| **FR-003** | `routes/web.php` (`/redirect`) | `CONFIRMED` | Mengarahkan user berdasarkan helper `isAdmin()`, `isApoteker()`, `isPasien()`. |
| **FR-004** | `Admin\ObatController` | `CONFIRMED` | CRUD Obat + Upload gambar ke storage publik. |
| **FR-005** | `Admin\UserController` | `CONFIRMED` | CRUD User dengan pencegahan self-delete akun admin. |
| **FR-006** | `Admin\LaporanController` | `CONFIRMED` | Filter tanggal + DOMPDF export. |
| **FR-007** | `Admin\LaporanController` | `CONFIRMED` | Laporan stok obat tersedikit + DOMPDF export. |
| **FR-008** | `Apoteker\TransaksiApotekerController` | `CONFIRMED` | Transaksi dengan resep dokter. |
| **FR-009** | `Apoteker\TransaksiApotekerController` | `CONFIRMED` | Transaksi non-resep langsung. |
| **FR-010** | `Apoteker\PesananController` | `RECONCILED` | Telah diperbaiki dengan restorasi stok pada status `dibatalkan`. |
| **FR-011** | `Apoteker\LaporanApotekerController` | `CONFIRMED` | Rekapitulasi penjualan per apoteker + PDF. |
| **FR-012** | `Pasien\PasienController@katalogObat` | `CONFIRMED` | Menampilkan obat yang `stok > 0`. |
| **FR-013** | `Pasien\PasienController@addToCart` | `CONFIRMED` | Keranjang berbasis session. |
| **FR-014** | `Pasien\PasienController@processCheckout` | `RECONCILED` | Telah diperbaiki menggunakan harga database terkini & row locking. |
| **FR-015** | `Pasien\PasienController@riwayatPesanan` | `CONFIRMED` | Tampilan riwayat pesanan berbasis ID pasien login. |

---

## 7. Pertanyaan Terbuka untuk Pemilik Produk (Open Questions)

1. Apakah di masa depan apotek akan mengaktifkan opsi pengiriman kurir lokal/ekspedisi selain penjemputan mandiri?
2. Apakah bukti transfer bank pasien perlu diunggah langsung melalui sistem (fitur upload bukti transfer)?
3. Apakah diperlukan notifikasi email otomatis saat status pesanan diperbarui menjadi `siap_diambil`?
