# Apotek Celine

Sistem Informasi Manajemen Apotek & Pemesanan Obat Online berbasis web, dibangun menggunakan **Laravel 10**, **Bootstrap 5**, dan **MySQL**.

Aplikasi ini mengintegrasikan alur bisnis apotek fisik dengan kemudahan e-commerce untuk pasien, mencakup transaksi resep dokter, pembelian non-resep di kasir, hingga pemesanan online mandiri dengan metode penjemputan fisik di gerai apotek (Buy Online, Pick-up in Store / BOPIS).

---

## 🌟 Fitur Utama

### 1. Modul Administrator (`/admin`)
- **Master Data Obat:** Manajemen katalog obat lengkap dengan foto produk, kategori, harga, dan kuantitas stok.
- **Manajemen Pengguna & Staf:** Pengelolaan akun pengguna, pembagian peran akses (Admin, Apoteker, Pasien), dan pengaturan keamanan.
- **Pelaporan & Analitik:** Rekapitulasi transaksi penjualan dan monitoring ketersediaan stok obat tersedikit, lengkap dengan ekspor laporan ke format PDF.

### 2. Modul Apoteker (`/apoteker`)
- **Pelayanan Resep Dokter:** Pencatatan data resep (nama dokter, identitas pasien, usia) dan penerbitan transaksi obat resep.
- **Transaksi Langsung (Non-Resep):** Pelayanan penjualan obat bebas di kasir apotek secara cepat.
- **Manajemen Pesanan Masuk:** Pemrosesan pesanan online dari pasien dengan pembaruan status bertahap (`menunggu_pembayaran` ➔ `terbayar` ➔ `diproses` ➔ `siap_diambil` ➔ `selesai` / `dibatalkan`).
- **Restorasi Stok Otomatis:** Pengembalian stok obat secara otomatis ke inventaris jika pesanan dibatalkan.
- **Laporan Penjualan Apoteker:** Rekapitulasi transaksi per staf apoteker dengan filter periode tanggal dan ekspor PDF.

### 3. Modul Pasien (`/pasien`)
- **Katalog Obat Interaktif:** Menampilkan produk obat aktif yang tersedia di rak apotek.
- **Keranjang Belanja Real-Time:** Penambahan obat, penyesuaian kuantitas, dan validasi ketersediaan stok.
- **Checkout Aman:** Perhitungan harga resmi terkini dari database yang terlindungi dari race condition (database row locking).
- **Instruksi Pembayaran & Riwayat Pesanan:** Informasi nomor rekening transfer bank dan pelacakan status pesanan secara transparan.

### 4. Sistem Keamanan & Autentikasi
- **Multi-Role RBAC:** Pembatasan akses berbasis peran (`admin`, `apoteker`, `pasien`) via middleware `CekPeran`.
- **Google OAuth:** Opsi masuk cepat menggunakan akun Google (Laravel Socialite).
- **Proteksi Standar Industri:** Dilengkapi proteksi CSRF di seluruh form, pencegahan XSS melalui Blade escaping, proteksi SQL Injection via Eloquent ORM, serta hashing kata sandi dengan Bcrypt.

---

## 🛠️ Tech Stack

- **Backend:** PHP 8.2+, Laravel 10.48.x
- **Frontend:** Blade Templating, Bootstrap 5, Sass, Vite 5
- **Database:** MySQL 8.x / MariaDB (lingkungan produksi & pengembangan), SQLite in-memory (lingkungan pengujian otomatis)
- **PDF Engine:** `barryvdh/laravel-dompdf` (Dompdf)
- **Autentikasi:** Laravel UI, Laravel Sanctum, Laravel Socialite

---

## 🚀 Panduan Memulai Cepat (Quick Start)

### 1. Prasyarat
- PHP >= 8.2
- Composer >= 2.2
- MySQL Server (XAMPP / Laragon / Docker)
- Node.js >= 18.x & npm

### 2. Instalasi
```bash
# 1. Kloning repositori
git clone https://github.com/<owner>/Apotek_Celine.git
cd Apotek_Celine

# 2. Instal dependensi PHP
composer install

# 3. Salin environment dan generate key
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi database di file .env
# DB_DATABASE=db_apotek_celine

# 5. Jalankan migrasi dan seeder
php artisan migrate --seed

# 6. Buat storage symlink untuk foto obat
php artisan storage:link

# 7. Instal dependensi JS & build aset
npm install
npm run build

# 8. Jalankan local development server
php artisan serve
```

Akses aplikasi di browser: `http://127.0.0.1:8000`

---

## 🔑 Akun Demo Bawaan (Seeder)

Setelah menjalankan `php artisan db:seed`, akun demo berikut siap digunakan:

| Peran | Email | Kata Sandi | Halaman Utama |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@example.com` | `password` | `/admin/dashboard` |
| **Apoteker** | `budi.apoteker@example.com` | `password` | `/apoteker/dashboard` |
| **Pasien** | `siti.pasien@example.com` | `password` | `/pasien/dashboard` |

---

## 🧪 Pengujian Otomatis (Testing)

Proyek dilengkapi automated feature & unit testing menggunakan basis data in-memory SQLite:

```bash
php artisan test
```

Pemeriksaan gaya penulisan kode (Laravel Pint):
```bash
php vendor/bin/pint --test
```

---

## 📚 Dokumentasi Lengkap

Dokumentasi teknis mendalam tersedia di direktori `docs/`:
- **[Product Requirements Document (PRD)](docs/PRD.md)** — Dokumen spesifikasi kebutuhan produk dan user stories.
- **[Entity Relationship Diagram (ERD)](docs/ERD.md)** — Diagram relasi Mermaid dan kamus data tabel.
- **[System Architecture](docs/ARCHITECTURE.md)** — Arsitektur sistem, alur RBAC, dan state machine pesanan.
- **[API & Route Specification](docs/SPECIFICATION.md)** — Spesifikasi seluruh endpoint rute HTTP dan aturan validasi.
- **[Setup & Installation Guide](docs/SETUP.md)** — Panduan instalasi dan deployment mendalam.
- **[Audit Reports](docs/audit/)** — Laporan baseline, security audit, bug audit, risk register, dan rencana remediasi.

---

## 📄 Lisensi

Kepemilikan hak cipta dan lisensi perangkat lunak memerlukan konfirmasi dari pemilik proyek (`OWNER DECISION REQUIRED`).
