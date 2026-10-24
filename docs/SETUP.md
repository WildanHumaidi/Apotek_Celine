# PANDUAN INSTALASI & PENGEMBANGAN (SETUP GUIDE)

Panduan ini mendokumentasikan langkah-langkah menyiapkan, menjalankan, menguji, dan mengembangkan aplikasi Apotek Celine di lingkungan lokal.

---

## 1. Prasyarat Sistem (Prerequisites)

Pastikan lingkungan kerja Anda telah terpasang:
- **PHP:** Versi `>= 8.2` dengan ekstensi: `pdo`, `pdo_mysql`, `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl`.
- **Database Server:** MySQL / MariaDB (misal: XAMPP / Laragon / Docker) pada port 3306.
- **Composer:** Versi `>= 2.2`.
- **Node.js & npm:** Node.js `>= 18.x` dan npm `>= 9.x`.
- **Web Browser Modern:** Chrome / Firefox / Edge.

---

## 2. Langkah-Langkah Instalasi (Installation Steps)

### Langkah 1: Kloning Repositori
```bash
git clone https://github.com/<owner>/Apotek_Celine.git
cd Apotek_Celine
```

### Langkah 2: Instalasi Dependensi Backend (Composer)
```bash
composer install
```
*(Di lingkungan Windows dengan XAMPP, dapat menggunakan `php "C:/Program Files/composer.phar" install`)*

### Langkah 3: Konfigurasi Environment File
Salin template konfigurasi dan generate application key:
```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan parameter basis data pada file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_apotek_celine
DB_USERNAME=root
DB_PASSWORD=
```

*(Opsional) Jika menggunakan Google Login, lengkapi kredensial Google Console:*
```env
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URL="${APP_URL}/auth/google/callback"
```

### Langkah 4: Pembuatan Basis Data & Migrasi
Buat database bernama `db_apotek_celine` pada server MySQL Anda:
```sql
CREATE DATABASE db_apotek_celine;
```

Jalankan seluruh migrasi skema tabel dan seed data awal:
```bash
php artisan migrate --seed
```

### Langkah 5: Tautan Storage Simbolik (Storage Link)
Buat symbolic link agar foto obat yang diunggah dapat diakses dari browser:
```bash
php artisan storage:link
```

### Langkah 6: Instalasi Dependensi Frontend & Kompilasi Aset
```bash
npm install
npm run build
```
Untuk mode pengembangan dengan hot-reload:
```bash
npm run dev
```

---

## 3. Menjalankan Aplikasi

Jalankan development server Laravel:
```bash
php artisan serve
```
Akses aplikasi melalui peramban web di: `http://127.0.0.1:8000`

---

## 4. Akun Bawaan Seeder (Default Demo Accounts)

Setelah menjalankan `php artisan db:seed`, akun demo berikut tersedia untuk pengujian:

| Peran | Email | Kata Sandi | Halaman Utama |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@example.com` | `password` | `/admin/dashboard` |
| **Apoteker** | `budi.apoteker@example.com` | `password` | `/apoteker/dashboard` |
| **Pasien** | `siti.pasien@example.com` | `password` | `/pasien/dashboard` |

---

## 5. Menjalankan Pengujian Otomatis (Automated Testing)

Pengujian otomatis dikonfigurasi menggunakan basis data in-memory SQLite sehingga dapat dijalankan tanpa memengaruhi data fisik MySQL:

```bash
php artisan test
```

Untuk menjalankan static analysis / style check menggunakan Laravel Pint:
```bash
php vendor/bin/pint --test
```
