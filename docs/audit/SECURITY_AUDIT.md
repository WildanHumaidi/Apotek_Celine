# SECURITY AUDIT REPORT

Laporan audit keamanan ini disusun berdasarkan inspeksi menyeluruh terhadap arsitektur autentikasi, otorisasi (RBAC), penanganan input, file upload, proteksi CSRF/XSS/injection, manajemen sesi, serta audit dependensi pada proyek Apotek Celine.

---

## 1. Ringkasan Eksekutif & Cakupan Audit

- **Tanggal Audit:** 25 Oktober 2026
- **Auditor:** Hermes Agent (Security & DevSecOps Reviewer)
- **Cakupan Pemeriksaan:**
  - Autentikasi multi-role (Admin, Apoteker, Pasien) dan Google OAuth (Socialite).
  - Otorisasi berbasis role (Middleware `CekPeran`, route prefix & name grouping).
  - Validasi input, mass assignment, proteksi SQL Injection, CSRF, dan XSS.
  - Penanganan file upload obat (`ObatController`).
  - Eksposur data sensitif, logging kredensial/PII, dan konfigurasi environment.
  - Audit dependensi pihak ketiga (`composer audit`).
- **Batasan Audit:**
  - Audit dilakukan secara lokal dan statis.
  - Tidak melakukan automated penetration testing / live fuzzing terhadap endpoint eksternal.
  - Database fisik `db_apotek_celine` belum diinisialisasi sehingga pengujian runtime dinamis bergantung pada unit/feature testing dan inspeksi kode.

---

## 2. Matriks Temuan Keamanan

| ID Temuan | Kategori | Tingkat Keparahan | Status Verifikasi | Lokasi |
| :--- | :--- | :--- | :--- | :--- |
| **SEC-01** | Auth / Data Integrity | **High** | Confirmed | `app/Http/Controllers/Auth/GoogleAuthController.php` |
| **SEC-02** | Information Disclosure / Code Smell | **Medium** | Confirmed | `app/Models/CekPeran.php` |
| **SEC-03** | Mass Assignment | **Medium** | Confirmed | `app/Http/Controllers/Admin/ObatController.php` |
| **SEC-04** | Vulnerable Dependencies | **High** | Confirmed | `composer.lock` (`composer audit`) |
| **SEC-05** | Config & Secret Template | **Low** | Confirmed | `.env.example` vs `config/services.php` |
| **SEC-06** | Concurrency / Race Condition | **Medium** | Confirmed | `app/Http/Controllers/Pasien/PasienController.php` |
| **SEC-07** | Storage Architecture | **Low** | Confirmed | `public/storage` vs `storage/app/public` |

---

## 3. Rincian Temuan Keamanan

### SEC-01: Incomplete Patient Profile Creation via Google OAuth
- **Severity:** High
- **Confidence:** Confirmed
- **Lokasi:** `app/Http/Controllers/Auth/GoogleAuthController.php` (baris 48-60)
- **Deskripsi:**
  Saat user mendaftar baru melalui Google OAuth, controller membuat record `User` dengan `id_peran = pasien`, namun tidak membuat relasi data pada tabel `pasien`.
- **Dampak:**
  Ketika pasien Google mengakses alur checkout di `PasienController@showCheckout`, sistem melakukan pengecekan `$pasien = Pasien::where('id_pasien', Auth::user()->id)->first()`. Karena data pasien tidak ada, user langsung dialihkan ke dashboard dengan pesan "Profil pasien belum lengkap". Tidak ada halaman/form bagi pasien untuk melengkapi profil tersebut, mengakibatkan user Google terblokir permanen dari transaksi belanja.
- **Rekomendasi Mitigasi:**
  1. Buat record `Pasien` secara otomatis saat pendaftaran Google OAuth dengan fallback data alamat/telepon default atau placeholder.
  2. Sediakan fitur edit/lengkapi profil pasien mandiri jika data belum lengkap.

---

### SEC-02: Misplaced Duplicate Middleware with PII Logging in Models
- **Severity:** Medium
- **Confidence:** Confirmed
- **Lokasi:** `app/Models/CekPeran.php`
- **Deskripsi:**
  File `CekPeran.php` berada di direktori `app/Models/`, padahal memiliki namespace `App\Http\Middleware` dan mendeklarasikan class `CekPeran`. File ini merupakan duplikat dari `app/Http/Middleware/CekPeran.php`, tetapi berisi kode debugging yang mencatat User ID, Nama User, dan URL ke `Log::info`.
- **Dampak:**
  Pelanggaran PSR-4 autoloading dan potensi pencatatan PII (Personally Identifiable Information) ke log publik/aplikasi jika terpanggil.
- **Rekomendasi Mitigasi:**
  Hapus file stray `app/Models/CekPeran.php` dan pastikan rute hanya menggunakan middleware kanonis di `app/Http/Middleware/CekPeran.php` tanpa logging PII berlebihan.

---

### SEC-03: Mass Assignment Risk in Medicine Store/Update
- **Severity:** Medium
- **Confidence:** Confirmed
- **Lokasi:** `app/Http/Controllers/Admin/ObatController.php` (baris 43, 53, 89, 103)
- **Deskripsi:**
  Method `store` dan `update` mengekstrak seluruh payload input menggunakan `$data = $request->all();` lalu menyalurkannya langsung ke `Obat::create($data)` dan `$obat->update($data)`.
- **Dampak:**
  Meskipun `$fillable` pada model `Obat` membatasi atribut saat ini, penggunaan `$request->all()` berisiko menimbulkan mass assignment vulnerability jika model diperluas di masa depan.
- **Rekomendasi Mitigasi:**
  Gunakan `$request->validated()` atau `$request->only(['nama_obat', 'deskripsi', 'harga', 'kategori', 'stok'])` saat mengisi model.

---

### SEC-04: Known Vulnerabilities in Composer Dependencies
- **Severity:** High
- **Confidence:** Confirmed
- **Lokasi:** `composer.lock`
- **Deskripsi:**
  Hasil eksekusi `composer audit` menemukan 61 advisory kerentanan keamanan yang memengaruhi 17 paket. Beberapa paket penting terdampak meliputi:
  - `dompdf/dompdf` (<3.1.6): CVE-2026-59943 (SVG image leak), CVE-2026-59942 (DoS Resource Exhaustion), CVE-2026-56722 (Local File Read via SVG data-URI).
  - `guzzlehttp/guzzle` (<7.15.2): CVE-2026-69246 (Host header bypass), CVE-2026-67354 (Referer URI leak).
  - `symfony/mime` & `symfony/mailer`: CVE-2026-45067 (Email header injection / SMTP command injection), CVE-2026-45068.
- **Dampak:**
  Potensi penyerangan DoS, manipulasi header, dan eksfiltrasi file lokal jika fitur pembuatan PDF (`LaporanController`) memproses konten yang tidak tepercaya.
- **Rekomendasi Mitigasi:**
  Jadwalkan pembaruan dependensi minor/patch yang kompatibel melalui `composer update <package>` setelah verifikasi kompatibilitas PHP 8.2 dan Laravel 10.

---

### SEC-05: Missing OAuth Variables in `.env.example`
- **Severity:** Low
- **Confidence:** Confirmed
- **Lokasi:** `.env.example`
- **Deskripsi:**
  File konfigurasi `config/services.php` mendefinisikan array `google` yang membaca `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, dan `GOOGLE_REDIRECT_URL`. Variabel ini ada di `.env` lokal tetapi belum ada pada template `.env.example`.
- **Dampak:**
  Developer atau tim deployment baru tidak mendapatkan informasi kebutuhan environment variable untuk fungsionalitas Google Login.
- **Rekomendasi Mitigasi:**
  Tambahkan entri placeholder `GOOGLE_CLIENT_ID=`, `GOOGLE_CLIENT_SECRET=`, dan `GOOGLE_REDIRECT_URL=` ke dalam `.env.example`.

---

### SEC-06: Potential Concurrency Race Condition during Checkout Stock Deduction
- **Severity:** Medium
- **Confidence:** Confirmed
- **Lokasi:**
  - `app/Http/Controllers/Pasien/PasienController.php` (baris 146-184)
  - `app/Http/Controllers/Apoteker/TransaksiApotekerController.php` (baris 78-110)
- **Deskripsi:**
  Pengecekan stok obat (`$obat = Obat::find($item['id_obat'])`) dan pemotongan stok (`$obat->decrement('stok', $item['jumlah'])`) berada di dalam blok `DB::transaction`, namun pembacaan data obat awal tidak menggunakan row-level locking (`lockForUpdate()`).
- **Dampak:**
  Dua transaksi paralel yang memesan item obat terakhir secara bersamaan dapat lolos validasi stok dan menyebabkan overselling (stok fisik habis atau bernilai negatif).
- **Rekomendasi Mitigasi:**
  Gunakan `Obat::lockForUpdate()->find($item['id_obat'])` di dalam transaksi checkout dan penjualan langsung.

---

### SEC-07: Public Storage Physical Directory vs Symbolic Link
- **Severity:** Low
- **Confidence:** Confirmed
- **Lokasi:** `public/storage` dan `storage/app/public`
- **Deskripsi:**
  Direktori `public/storage` saat ini merupakan direktori fisik nyata, bukan symbolic link ke `storage/app/public`.
- **Dampak:**
  Penyimpanan foto baru oleh Laravel (`store('obat_photos', 'public')`) akan menyimpan file ke `storage/app/public/obat_photos/`. Karena `public/storage` bukan symlink, file baru yang diunggah tidak akan dapat diakses oleh browser melalui URL `/storage/obat_photos/...`.
- **Rekomendasi Mitigasi:**
  Pastikan konfigurasi link storage sesuai standar Laravel (`php artisan storage:link`) atau sinkronkan struktur direktori publik.

---

## 4. Evaluasi Proteksi Standar (Checklist)

- **SQL Injection:** `PASS` — Aplikasi sepenuhnya menggunakan Eloquent ORM dan parameter binding bawaan Laravel. Tidak ditemukan `DB::raw()` tanpa binding atau string concatenation pada query SQL.
- **Cross-Site Scripting (XSS):** `PASS` — Seluruh Blade templates menggunakan kurung kurawal ganda `{{ }}` yang meng-escape output secara otomatis. Tidak ditemukan penggunaan unescaped `{!! !!}` pada data pengguna.
- **Cross-Site Request Forgery (CSRF):** `PASS` — Seluruh form POST, PUT, PATCH, dan DELETE pada direktori views menyertakan direktif `@csrf`. Middleware `VerifyCsrfToken` aktif pada grup rute `web`.
- **Role-Based Access Control (RBAC):** `PASS` — Rute terbagi rapi menggunakan prefix dan middleware `cek.peran:admin`, `cek.peran:apoteker`, dan `cek.peran:pasien`. Pengguna tanpa peran yang cocok ditolak dengan HTTP 403.
- **Session Security:** `PASS` — `http_only` aktif (true), `same_site` diatur ke `lax`, dan cookie sesi terenkripsi secara default.
- **Secret Leaks in Codebase:** `PASS` — Tidak ditemukan hardcoded API keys, private keys, atau passwords di dalam kode sumber aplikasi (seluruh kredensial dibaca melalui environment/config).
