# REMEDIATION PLAN & CHANGE CONTROL

Dokumen ini memetakan rencana kerja, urutan dependensi, kontrol ruang lingkup, strategi pengujian, dan mitigasi risiko sebelum dilakukannya modifikasi kode pada Fase 4 sesuai panduan `PLAYBOOK.md` Fase 3.

---

## 1. Strategi Prioritas & Urutan Implementasi

Perbaikan dilakukan secara bertahap dan terisolasi dari tingkat keparahan tertinggi (P0/P1) hingga pembersihan kecil (P3):

```
[Tahap 1: P0 - Autoload & Data Integrity]
  ├── Ubah nama file app/Models/pasien.php -> app/Models/Pasien.php
  └── Tambahkan 'id_pasien' ke $fillable pada model Pasien

[Tahap 2: P1 - Logika Bisnis & Transaksi]
  ├── PasienController: Ambil harga obat dari database saat checkout (BUG-03)
  ├── GoogleAuthController: Buat entri record Pasien otomatis saat pendaftaran Google OAuth (SEC-01)
  ├── TransaksiPenjualanController (Admin) & PesananController (Apoteker):
  │     Restorasi stok obat saat pesanan beralih ke status 'dibatalkan' (BUG-02)
  └── Migrasi transaksi_penjualan: Ubah foreign key id_admin menjadi onDelete('set null') (BUG-05)

[Tahap 3: P2 - Sanitasi Kode & Pencegahan Crash]
  ├── Hapus file stray middleware app/Models/CekPeran.php (SEC-02)
  ├── Amankan pembagian harga_satuan di 4 file view transaksi dari division by zero (BUG-06)
  ├── ObatController: Gunakan $request->validated() atau atribut eksplisit (SEC-03)
  └── Koreksi konsistensi nominal transaksi pada database seeders (BUG-07)

[Tahap 4: P3 - Konfigurasi, Quality Gate & Cleanup]
  ├── tests/Feature/ExampleTest.php: Sesuaikan ekspektasi test dengan route redirect /login (BUG-08)
  ├── .env.example: Lengkapi konfigurasi placeholder Google OAuth (SEC-05)
  └── Hapus file sampah 0 byte 'stok' di root direktori (BUG-09)
```

---

## 2. Rincian Modul & File yang Akan Diubah

| No | File Sasaran | Jenis Perubahan | Justifikasi |
| :--- | :--- | :--- | :--- |
| 1 | `app/Models/pasien.php` -> `Pasien.php` | Rename & Update `$fillable` | Standarisasi PSR-4 Linux & Integritas Relasi Pasien |
| 2 | `app/Http/Controllers/Pasien/PasienController.php` | Patch logic checkout | Menggunakan harga resmi database, bukan session data |
| 3 | `app/Http/Controllers/Auth/GoogleAuthController.php` | Patch logic OAuth registration | Mencegah lockout profil pasien yang belum lengkap |
| 4 | `app/Http/Controllers/Admin/TransaksiPenjualanController.php` | Patch `updateStatus` | Restorasi stok otomatis saat status `dibatalkan` |
| 5 | `app/Http/Controllers/Apoteker/PesananController.php` | Patch `updateStatus` | Restorasi stok otomatis saat status `dibatalkan` |
| 6 | `app/Http/Controllers/Admin/ObatController.php` | Patch `store` & `update` | Mitigasi mass assignment payload |
| 7 | `database/migrations/2025_06_24_183759_create_transaksi_penjualan_table.php` | Patch foreign key constraint | `id_admin` -> `onDelete('set null')` |
| 8 | `database/migrations/2014_06_11_183741_create_peran_table.php` | Tambah seed peran `pasien` | Memastikan default foreign key `id_peran = 3` valid |
| 9 | `resources/views/admin/transaksi/show.blade.php` | Patch tampilan kalkulasi | Perlindungan division by zero |
| 10 | `resources/views/apoteker/pesanan/show.blade.php` | Patch tampilan kalkulasi | Perlindungan division by zero |
| 11 | `resources/views/apoteker/transaksi/show.blade.php` | Patch tampilan kalkulasi | Perlindungan division by zero |
| 12 | `resources/views/pasien/riwayat_pesanan.blade.php` | Patch tampilan kalkulasi | Perlindungan division by zero |
| 13 | `database/seeders/TransaksiPenjualanSeeder.php` | Penyesuaian total harga seeder | Rekonsiliasi nominal header dengan detail seeder |
| 14 | `app/Models/CekPeran.php` | Penghapusan file (Delete) | Pembersihan file stray debug middleware di folder model |
| 15 | `tests/Feature/ExampleTest.php` | Update test assertions | Menguji status 302 / redirect to login |
| 16 | `.env.example` | Tambah template Google OAuth | Standardisasi konfigurasi environment |
| 17 | `stok` | Penghapusan file (Delete) | Pembersihan artefak kosong di root |

---

## 3. Item yang Ditunda / Memerlukan Persetujuan Pemilik

1. **Major Upgrade Dependensi Composer (SEC-04):**
   - Pembaruan paket `dompdf`, `guzzlehttp`, dan `symfony` yang dilaporkan dalam 61 advisories berpotensi menimbulkan incompatibility dengan PHP 8.2 atau Laravel 10 jika dilakukan otomatis secara agresif. Ditunda untuk rencana rilis terpisah.
2. **Penghapusan Kolom `id_obat` pada Tabel `transaksi_penjualan`:**
   - Kolom ini merupakan peninggalan desain awal. Menghapus kolom fisik ini membutuhkan migrasi skema destruktif. Diputuskan untuk mempertahankan kolom tersebut sebagai nullable/legacy agar tidak merusak kompatibilitas historis.
3. **Inisialisasi Git Repository:**
   - Memerlukan otorisasi pengguna pada Fase 8 sebelum inisialisasi git dan staging dilakukan.

---

## 4. Kriteria Penerimaan (Acceptance Criteria)

- [ ] Model `Pasien` dapat di-autoload oleh Composer tanpa error case mismatch.
- [ ] Registrasi melalui Google OAuth langsung menghasilkan record pada tabel `pasien`.
- [ ] Checkout transaksi menggunakan harga obat aktual dari database dan menghitung grand total secara akurat.
- [ ] Pembatalan pesanan (`dibatalkan`) mengembalikan stok ke tabel `obat`.
- [ ] Tampilan detail transaksi tidak crash saat kuantitas item nol.
- [ ] `php artisan test` menghasilkan seluruh test berstatus `PASS`.
- [ ] `php -l` pada seluruh file yang dimodifikasi menghasilkan exit code `0` (syntax valid).
- [ ] File stray `app/Models/CekPeran.php` dan `stok` telah dihapus secara aman.
