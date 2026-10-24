# DEEP BUG AUDIT & BUSINESS LOGIC REVIEW

Dokumen ini mencatat hasil audit mendalam terhadap logika bisnis, integritas data, alur transaksi, model relasional, serta potensi kegagalan tersembunyi pada aplikasi Apotek Celine sesuai panduan `PLAYBOOK.md` Fase 2.

---

## 1. Daftar Temuan Bug

### BUG-01: File Naming Case Mismatch on `pasien.php` & Missing Fillable `id_pasien`
- **Severity:** P0 Critical
- **Confidence:** Confirmed
- **Lokasi:** `app/Models/pasien.php` (seluruh file)
- **Expected:**
  1. Nama file model Eloquent harus sesuai standar PSR-4 (`app/Models/Pasien.php` dengan huruf 'P' kapital) agar dapat di-autoload secara andal di sistem Linux, container Docker, dan server produksi.
  2. Atribut `id_pasien` harus diizinkan untuk mass-assignment jika diisi secara eksplisit dari `User::id` saat registrasi, atau relasi `users` dan `pasien` harus dikaitkan menggunakan foreign key yang tepat.
- **Actual:**
  1. File diberi nama `app/Models/pasien.php` (huruf kecil). Di Windows (NTFS) file ini termuat karena case-insensitive, namun pada environment Linux/Unix CI/CD atau server produksi akan memicu fatal error: `Class "App\Models\Pasien" not found`.
  2. Model `Pasien` memiliki `$fillable = ['nama_pasien', 'alamat', 'no_telp'];` tanpa `id_pasien`. Pada `RegisterController@create`, pemanggilan `Pasien::create(['id_pasien' => $user->id, ...])` menyebabkan `id_pasien` diabaikan oleh Eloquent, dan MySQL menghasilkan ID auto-increment baru.
- **First Divergence:** Eksekusi `Pasien::create()` pada `RegisterController` baris 63 atau pemanggilan class di sistem case-sensitive.
- **Root Cause:** Nama file fisik tidak sinkron dengan deklarasi namespace PSR-4, serta `$fillable` tidak menyertakan primary key custom non-incrementing dari ID user.
- **Dampak Teknis & Bisnis:**
  - Crash total di lingkungan Linux/server hosting.
  - Desinkronisasi data: `User::id` tidak cocok dengan `Pasien::id_pasien`. Pasien yang login tidak dapat melihat pesanan mereka sendiri atau tertukar dengan akun pasien lain.
- **Rekomendasi Perbaikan:**
  1. Ubah nama file fisik menjadi `app/Models/Pasien.php`.
  2. Tambahkan `id_pasien` ke dalam array `$fillable` pada `Pasien` dan pastikan `$incrementing` dideklarasikan sesuai strategi integrasi.
- **Test Regresi:** Registrasi user baru, verifikasi pembuatan record `pasien` dengan `id_pasien === user.id`, dan akses checkout.

---

### BUG-02: Permanent Inventory Stock Loss upon Order Cancellation
- **Severity:** P1 High
- **Confidence:** Confirmed
- **Lokasi:**
  - `app/Http/Controllers/Admin/TransaksiPenjualanController.php` (method `updateStatus`, baris 47-70)
  - `app/Http/Controllers/Apoteker/PesananController.php` (method `updateStatus`, baris 52-75)
- **Expected:**
  Ketika status pesanan diubah menjadi `dibatalkan` (cancelled), stok obat yang sebelumnya telah dipotong saat checkout atau pemesanan harus dikembalikan (restored/incremented) ke tabel `obat`.
- **Actual:**
  Method `updateStatus` hanya mengubah kolom `status_pesanan = $request->status_pesanan` dan menyimpannya ke database tanpa melakukan restorasi stok pada item-item di `detail_transaksi`.
- **First Divergence:** Saat admin/apoteker memilih status `dibatalkan` pada form update status.
- **Root Cause:** Tidak adanya lifecycle handler atau event/state transition logic untuk menangani pengembalian stok saat pesanan gagal atau dibatalkan.
- **Dampak Teknis & Bisnis:**
  Stok fisik obat di gudang masih ada, tetapi stok sistem di apotek terus berkurang secara permanen setiap kali ada pesanan batal, mengakibatkan kehilangan potensi penjualan (false out-of-stock).
- **Rekomendasi Perbaikan:**
  Tambahkan pengecekan transisi status: jika status lama bukan `dibatalkan` dan status baru adalah `dibatalkan`, lakukan loop pada `detailTransaksi` dan kembalikan stok obat (`$obat->increment('stok', $detail->jumlah)`). Sebaliknya, jika status `dibatalkan` diaktifkan kembali, lakukan validasi ketersediaan stok sebelum memotong ulang.
- **Test Regresi:** Buat pesanan, catat stok awal, batalkan pesanan via admin/apoteker, verifikasi stok kembali ke nilai semula.

---

### BUG-03: Stale / Manipulable Medicine Price during Checkout
- **Severity:** P1 High
- **Confidence:** Confirmed
- **Lokasi:** `app/Http/Controllers/Pasien/PasienController.php` (baris 146-170)
- **Expected:**
  Perhitungan total harga pembayaran transaksi (`harga_bayar` dan `DetailTransaksi::harga_bayar`) harus selalu mengacu pada harga resmi terkini dari database (`$obat->harga`), bukan dari data session `cart` yang tersimpan di browser/sesi user.
- **Actual:**
  Kode menghitung total menggunakan harga dari session:
  `$totalHargaBayar += $item['harga'] * $item['jumlah'];`
- **First Divergence:** Saat perulangan `$cart as $item` di `PasienController@processCheckout` baris 152.
- **Root Cause:** Mengandalkan nilai `harga` dari array session tanpa re-fetching/re-validating harga resmi dari record Eloquent `Obat`.
- **Dampak Teknis & Bisnis:**
  Jika admin mengubah harga obat (misalnya karena kenaikan harga bahan baku atau koreksi harga), user yang telah menyimpan obat di keranjang belanja beberapa hari sebelumnya akan tetap membayar dengan harga lama, menyebabkan kerugian finansial bagi apotek.
- **Rekomendasi Perbaikan:**
  Gunakan `$obat->harga` yang diambil langsung dari database saat iterasi checkout untuk menghitung subtotal dan grand total.
- **Test Regresi:** Masukkan obat ke keranjang, ubah harga obat di database, lakukan checkout, verifikasi total transaksi menggunakan harga baru di database.

---

### BUG-04: Architectural Flaw & Foreign Key Hazard in `transaksi_penjualan` Table
- **Severity:** P1 High
- **Confidence:** Confirmed
- **Lokasi:**
  - `database/migrations/2025_06_24_183759_create_transaksi_penjualan_table.php` (baris 19-20, 26)
  - `app/Http/Controllers/Pasien/PasienController.php` (baris 160-170)
  - `app/Http/Controllers/Apoteker/TransaksiApotekerController.php` (baris 87-95, 163-171)
- **Expected:**
  Tabel header transaksi (`transaksi_penjualan`) hanya mencatat metadata pesanan level transaksi (nomor transaksi, tanggal, pemesan, kasir/apoteker, grand total, status, metode pembayaran). Rincian item obat harus dikelola sepenuhnya di tabel detail (`detail_transaksi`).
- **Actual:**
  Tabel `transaksi_penjualan` memiliki kolom wajib `id_obat` dan `jumlah` dengan foreign key ber-cascade (`onDelete('cascade')`). Akibatnya:
  1. Controller dipaksa memilih item obat pertama secara sembarangan (`$obatUtamaId = $item['id_obat']`) untuk mengisi kolom header.
  2. Kolom `jumlah` di header berisi total kuantitas dari seluruh jenis obat yang berbeda (misalnya 2 botol sirup + 1 strip tablet = jumlah 3), yang secara semantik tidak masuk akal jika disandingkan dengan `id_obat`.
  3. Jika obat pertama tersebut dihapus dari katalog, seluruh transaksi penjualan beserta semua detail item obat lainnya otomatis terhapus karena foreign key cascade!
- **First Divergence:** Perancangan skema database migrasi `create_transaksi_penjualan_table`.
- **Root Cause:** Denormalisasi parsial warisan desain database lama yang bertentangan dengan relasi one-to-many `detail_transaksi`.
- **Dampak Teknis & Bisnis:**
  Risiko tinggi kehilangan riwayat transaksi finansial apotek jika ada produk obat dihapus dari katalog.
- **Rekomendasi Perbaikan:**
  Jadikan kolom `id_obat` pada `transaksi_penjualan` nullable dengan `onDelete('set null')`, atau hapus ketergantungan cascade agar penghapusan katalog obat tidak melenyapkan riwayat transaksi apotek.
- **Test Regresi:** Hapus obat dari katalog admin dan pastikan transaksi historis yang pernah memuat obat tersebut tetap utuh.

---

### BUG-05: Sales History Deletion Cascade on User / Admin Deletion
- **Severity:** P1 High
- **Confidence:** Confirmed
- **Lokasi:** `database/migrations/2025_06_24_183759_create_transaksi_penjualan_table.php` (baris 25)
- **Expected:**
  Penghapusan akun staf (Apoteker/Admin) tidak boleh menghapus transaksi penjualan historis yang pernah dilayani oleh staf tersebut. Hubungan staf pencatat harus diset `NULL` (`onDelete('set null')`).
- **Actual:**
  Definisi foreign key adalah:
  `$table->foreign('id_admin')->references('id')->on('users')->onDelete('cascade');`
- **First Divergence:** Saat admin menghapus akun pengguna (staf apoteker) di `Admin\UserController@destroy`.
- **Root Cause:** Penggunaan opsi `cascade` pada relasi audit user di tabel transaksi.
- **Dampak Teknis & Bisnis:**
  Jika seorang apoteker resign dan akunnya dihapus oleh admin, semua transaksi penjualan yang pernah dicatat oleh apoteker tersebut hilang dari database, merusak laporan keuangan dan pembukuan tahunan.
- **Rekomendasi Perbaikan:**
  Ubah constraint foreign key `id_admin` menjadi `onDelete('set null')` (kolom `id_admin` sudah bertipe nullable).
- **Test Regresi:** Hapus user staf apoteker dan verifikasi transaksi yang bersangkutan tetap tersimpan dengan `id_admin = null`.

---

### BUG-06: Potential Division by Zero in Transaction Detail Views
- **Severity:** P2 Medium
- **Confidence:** Confirmed
- **Lokasi:**
  - `resources/views/admin/transaksi/show.blade.php` (baris 50)
  - `resources/views/apoteker/pesanan/show.blade.php` (baris 54)
  - `resources/views/apoteker/transaksi/show.blade.php` (baris 31)
  - `resources/views/pasien/riwayat_pesanan.blade.php` (baris 112)
- **Expected:**
  Harga satuan obat harus ditampilkan secara aman tanpa risiko pembagian dengan nol.
- **Actual:**
  Template mengeksekusi ekspresi:
  `{{ number_format($detail->harga_bayar / $detail->jumlah, 2, ',', '.') }}`
  Jika `jumlah` bernilai 0 atau tidak valid karena inkonsistensi data seeder atau manipulasi input, halaman melempar fatal error `DivisionByZeroError` (HTTP 500).
- **First Divergence:** Evaluasi ekspresi Blade saat render tampilan detail transaksi.
- **Root Cause:** Ketiadaan kolom eksplisit `harga_satuan` pada tabel `detail_transaksi` sehingga tampilan menghitung mundur dari subtotal tanpa pengaman guard `jumlah > 0`.
- **Dampak Teknis & Bisnis:**
  Halaman transaksi crash seketika bagi admin, apoteker, atau pasien jika data kuantitas bernilai nol.
- **Rekomendasi Perbaikan:**
  Gunakan pengecekan aman: `{{ number_format($detail->jumlah > 0 ? $detail->harga_bayar / $detail->jumlah : 0, 2, ',', '.') }}` atau ambil dari `$detail->obat->harga`.
- **Test Regresi:** Render view transaksi dengan detail berkuantitas 0, verifikasi tidak terjadi crash HTTP 500.

---

### BUG-07: Data Discrepancy between Header Total and Detail Total in Seeders
- **Severity:** P2 Medium
- **Confidence:** Confirmed
- **Lokasi:**
  - `database/seeders/TransaksiPenjualanSeeder.php` (baris 23-46)
  - `database/seeders/DetailTransaksiSeeder.php` (baris 23-51)
- **Expected:**
  Nilai `harga_bayar` pada tabel header `transaksi_penjualan` harus sama persis dengan jumlah (sum) dari `harga_bayar` pada tabel `detail_transaksi` untuk transaksi yang sama.
- **Actual:**
  Pada seeder transaksi pertama (tanggal 2025-06-20):
  - `transaksi_penjualan.harga_bayar` di-set sebesar Rp 10.000,00.
  - `detail_transaksi` memasukkan 2 item: Paracetamol (Rp 5.000,00) + Amoxicillin (Rp 15.000,00) = Total Rp 20.000,00!
- **First Divergence:** Eksekusi seeding database awal `php artisan db:seed`.
- **Root Cause:** Hardcoded data sintetis pada seeder yang tidak dikalkulasi secara konsisten antara header dan detail.
- **Dampak Teknis & Bisnis:**
  Laporan keuangan menunjukkan selisih nominal (discrepancy) antara rekap total penjualan dan rincian item transaksi.
- **Rekomendasi Perbaikan:**
  Koreksi nominal pada seeder agar total header sama dengan penjumlahan detail transaksi.
- **Test Regresi:** Jalankan seeder, query `transaksi_penjualan.harga_bayar` dan bandingkan dengan sum dari `detail_transaksi.harga_bayar`.

---

### BUG-08: Default Example Test Failure (`tests/Feature/ExampleTest.php`)
- **Severity:** P3 Low
- **Confidence:** Confirmed
- **Lokasi:** `tests/Feature/ExampleTest.php` (baris 15-18)
- **Expected:**
  Rangkaian automated test bawaan harus lulus (`PASS`) sebagai verifikasi kesehatan dasar aplikasi.
- **Actual:**
  Test melakukan `get('/')` dan mengharapkan status `200`, sedangkan route `/` pada `routes/web.php` secara sengaja melakukan redirect ke `/login` (HTTP 302).
- **First Divergence:** Eksekusi `php artisan test`.
- **Root Cause:** Template bawaan Laravel belum disesuaikan dengan arsitektur rute aplikasi yang mengalihkan root ke login.
- **Dampak Teknis & Bisnis:**
  Pipeline CI/CD dan quality gate otomatis gagal (`FAIL`), menghambat deployment dan audit otomatis.
- **Rekomendasi Perbaikan:**
  Sesuaikan test agar menguji redirect ke login: `$response->assertRedirect('/login');` atau `$response->assertStatus(302);`.
- **Test Regresi:** Jalankan `php artisan test` dan pastikan seluruh test berstatus hijau (`PASS`).

---

### BUG-09: Orphaned 0-byte File `stok` at Project Root
- **Severity:** P3 Low
- **Confidence:** Confirmed
- **Lokasi:** File `stok` di root direktori
- **Expected:**
  Root direktori bersih dari artefak kosong tanpa ekstensi atau tujuan fungsional.
- **Actual:**
  Terdapat file kosong 0 byte bernama `stok` yang tidak digunakan oleh sistem apa pun.
- **First Divergence:** File creation manual di masa lalu.
- **Root Cause:** Kemungkinan kesalahan ketik perintah shell pengguna di masa lalu.
- **Dampak Teknis & Bisnis:**
  Kekacauan repositori dan kebingungan bagi pengembang lain.
- **Rekomendasi Perbaikan:**
  Hapus file kosong `stok` pada fase pembersihan terkontrol.
- **Test Regresi:** Verifikasi proyek tetap berfungsi normal tanpa file tersebut.
