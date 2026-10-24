# PROJECT BASELINE

Dokumen ini mencatat kondisi baseline awal proyek sebelum dilakukannya perbaikan, refactoring, atau standarisasi repository sesuai panduan `PLAYBOOK.md` Fase 0.

---

## 1. Identifikasi Proyek & Lingkungan

- **Root Proyek:** `E:\Projects\Apotek_Celine`
- **Tipe Proyek:** Web Application (Sistem Informasi & Manajemen Apotek Celine)
- **Framework & Runtime:**
  - PHP: `PHP 8.2.12 (cli) (built: Oct 24 2023 21:15:15) (ZTS Visual C++ 2019 x64)` (lokasi: `C:\xampp\php\php.exe`)
  - Laravel Framework: `10.48.29`
  - Composer: `2.10.2` (lokasi: `C:\Program Files\composer.phar`)
  - Node.js: `v26.7.0` (lokasi: `C:\Users\wilda\AppData\Local\hermes\tools\node-26.7.0-win32-x64\node.exe`)
  - npm: `11.19.0` (lokasi: `C:\Users\wilda\AppData\Local\hermes\tools\node-26.7.0-win32-x64\npm`)
  - Database: MySQL (XAMPP 3306), status server: Running. Database `db_apotek_celine` belum dibuat.

---

## 2. Baseline Git & Repository

- **Git Status:** Direktori saat ini **bukan repository Git** (`fatal: not a git repository (or any of the parent directories): .git`).
- **File Konfigurasi Git Awal:** Terdapat file bawaan `.gitignore` dan `.gitattributes`.
- **Perlindungan Pekerjaan Pengguna:** Tidak ada branch atau commit yang dapat dimodifikasi saat ini. Sesuai Section 0.2 dan 8.3 `PLAYBOOK.md`, inisialisasi Git ditangguhkan hingga fase kesiapan Git / persetujuan eksplisit.

---

## 3. Struktur Direktori Utama

```
.
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/ (LaporanController, ObatController, TransaksiPenjualanController, UserController)
│   │   │   ├── Apoteker/ (LaporanApotekerController, PesananController, TransaksiApotekerController)
│   │   │   ├── Auth/ (GoogleAuthController, LoginController, RegisterController, dll.)
│   │   │   └── Pasien/ (PasienController)
│   │   └── Middleware/ (CekPeran)
│   ├── Models/ (CekPeran, DetailTransaksi, Obat, pasien, PasienDenganResep, Peran, TransaksiPenjualan, User)
│   └── Providers/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/ (13 migration files)
│   └── seeders/ (8 seeder files)
├── public/
├── resources/ (views: admin, apoteker, auth, layouts, partials, pasien; js, sass, css)
├── routes/ (web.php, api.php, console.php, channels.php)
├── storage/
├── tests/ (Unit/ExampleTest.php, Feature/ExampleTest.php)
├── vendor/
├── .env & .env.example
├── composer.json & composer.lock
├── package.json & vite.config.js
├── phpunit.xml
└── PLAYBOOK.md
```

File khusus teramati di root:
- `stok` (file 0-byte kosong di root).

---

## 4. Baseline Kualitas Aplikasi (Pre-remediation)

### 4.1 Syntax Check (`php -l`)
- **Command:** `find app routes database config tests -name "*.php" -exec php -l {} +`
- **Exit Code:** `0`
- **Status:** `PASS` (Semua file PHP memiliki sintaks valid).

### 4.2 Linter & Style Check (`laravel/pint`)
- **Command:** `php vendor/bin/pint --test`
- **Exit Code:** `1`
- **Status:** `FAIL` (90 files dipindai, ditemukan 38 files dengan style issues).

### 4.3 Automated Tests (`php artisan test`)
- **Command:** `php artisan test`
- **Exit Code:** `1`
- **Status:** `FAIL` (1 passed, 1 failed).
  - `Tests\Unit\ExampleTest > test_that_true_is_true`: `PASS`
  - `Tests\Feature\ExampleTest > test_the_application_returns_a_successful_response`: `FAIL`
    - Error: `Expected response status code [200] but received 302.` (Route `/` mengalihkan ke `/login`).

### 4.4 Frontend Build (`npm run build`)
- **Command:** `npm run build`
- **Exit Code:** `1`
- **Status:** `FAIL` (`node_modules` belum diinstal, command `vite` tidak ditemukan).

### 4.5 Database Connectivity & Migration Status
- **Command:** `php artisan migrate:status`
- **Exit Code:** `1`
- **Status:** `FAIL` (MySQL connection error: `SQLSTATE[HY000] [1049] Unknown database 'db_apotek_celine'`).

### 4.6 Dependency Security Audit (`composer audit`)
- **Command:** `php "C:/Program Files/composer.phar" audit`
- **Exit Code:** `1`
- **Status:** `FAIL` (Ditemukan 61 advisory kerentanan keamanan yang memengaruhi 17 paket pada `composer.lock`, termasuk dompdf, guzzlehttp, phpseclib, phpunit, symfony).

---

## 5. Sumber Dokumen & Temuan Awal

- **Dokumentasi yang ditemukan:** `PLAYBOOK.md` dan default Laravel `README.md`. Belum ada PRD, ERD, atau manual arsitektur resmi yang disiapkan sebelumnya di proyek.
- **Daftar Risiko Awal:**
  1. Direktori bukan Git repository; perubahan belum ter-track oleh version control lokal.
  2. Frontend build terblokir karena dependensi NPM belum terinstal.
  3. Database belum diinisialisasi sehingga runtime dinamis berbasis MySQL belum dapat dieksekusi secara lokal tanpa setup database.
  4. Adanya kerentanan keamanan pada pustaka pihak ketiga (termasuk Dompdf dan Symfony komponen).
  5. Konfigurasi Google OAuth (`GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`) tercantum pada `.env` lokal tetapi belum ada pada `.env.example`.
