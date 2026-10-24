# API & ROUTE SPECIFICATION

Dokumen ini memetakan seluruh rute HTTP, endpoint, middleware otorisasi, parameter input, aturan validasi, serta perilaku respons pada aplikasi Apotek Celine berdasarkan file rute aktual (`routes/web.php`).

---

## 1. Rute Publik & Autentikasi

### 1.1 Root Redirect
- **Method:** `GET`
- **URI:** `/`
- **Middleware:** None
- **Respons:** Redirect HTTP 302 ke rute `login` (`/login`).

### 1.2 Autentikasi Kredensial Bawaan (Laravel UI)
- `GET /login` — Menampilkan form login (`auth.login`).
- `POST /login` — Memproses autentikasi email & password. Memiliki throttling otomatis (5 percobaan/menit).
- `POST /logout` — Menghancurkan sesi pengguna dan redirect ke `/`.
- `GET /register` — Menampilkan form registrasi pasien (`auth.register`).
- `POST /register` — Mendaftarkan akun pasien baru.
  - **Payload:** `name` (required, string), `email` (required, email, unique:users), `password` (required, min:8, confirmed), `alamat` (required, string), `no_telp` (required, string).
  - **Respons:** Redirect ke rute dashboard setelah membuat entri `users` dan `pasien`.

### 1.3 Google OAuth
- `GET /auth/google` — Mengalihkan pengguna ke consent screen Google OAuth (`GoogleAuthController@redirectToGoogle`).
- `GET /auth/google/callback` — Menerima authorization code dari Google, memverifikasi token, membuat/mengupdate user, memastikan data `pasien` tersedia, dan mengalihkan ke dashboard.

### 1.4 Role Redirector
- **Method:** `GET`
- **URI:** `/redirect`
- **Nama Rute:** `redirect.peran`
- **Middleware:** `auth`
- **Perilaku:**
  - `isAdmin()` -> Redirect ke `/admin/dashboard`
  - `isApoteker()` -> Redirect ke `/apoteker/dashboard`
  - `isPasien()` -> Redirect ke `/pasien/dashboard`
  - Peran tidak dikenal -> Abort HTTP 403

---

## 2. Modul Administrator (`/admin/*`)
**Middleware:** `auth`, `cek.peran:admin`  
**Prefix:** `/admin` | **Name Prefix:** `admin.`

| Method | URI | Action / Handler | Deskripsi & Validasi |
| :--- | :--- | :--- | :--- |
| `GET` | `/dashboard` | Closure -> `view('admin.dashboard')` | Dashboard analitik ringkas admin |
| `GET` | `/obat` | `ObatController@index` | Daftar seluruh obat di inventaris |
| `GET` | `/obat/create` | `ObatController@create` | Form tambah obat baru |
| `POST` | `/obat` | `ObatController@store` | Menyimpan obat baru.<br>**Validasi:** `nama_obat` (req), `deskripsi` (opt), `harga` (req, numeric, min:0), `kategori` (req), `stok` (req, int, min:0), `foto` (opt, image, max:2048) |
| `GET` | `/obat/{id}/edit` | `ObatController@edit` | Form edit master data obat |
| `PUT/PATCH` | `/obat/{id}` | `ObatController@update` | Memperbarui data obat dan opsional foto baru |
| `DELETE` | `/obat/{id}` | `ObatController@destroy` | Menghapus obat dan file fotonya dari disk storage |
| `GET` | `/users` | `UserController@index` | Daftar pengguna dan staf terdaftar |
| `GET` | `/users/create` | `UserController@create` | Form tambah pengguna/staf baru |
| `POST` | `/users` | `UserController@store` | Menambah user baru.<br>**Validasi:** `name` (req), `email` (req, unique:users), `password` (req, min:8, confirmed), `id_peran` (req, exists:peran,id) |
| `GET` | `/users/{id}/edit` | `UserController@edit` | Form edit data pengguna |
| `PUT/PATCH` | `/users/{id}` | `UserController@update` | Memperbarui user (email unique ignore self, password opt) |
| `DELETE` | `/users/{id}` | `UserController@destroy` | Menghapus user (mencegah penghapusan akun diri sendiri) |
| `GET` | `/transaksi-penjualan` | `TransaksiPenjualanController@index` | Daftar seluruh transaksi penjualan |
| `GET` | `/transaksi-penjualan/{no}` | `TransaksiPenjualanController@show` | Detail rincian transaksi obat |
| `PATCH` | `/transaksi-penjualan/{no}/status` | `TransaksiPenjualanController@updateStatus` | Memperbarui status pesanan.<br>**Validasi:** `status_pesanan` (in: menunggu_pembayaran, terbayar, diproses, siap_diambil, selesai, dibatalkan). Mengembalikan stok jika dibatalkan. |
| `GET` | `/laporan` | `LaporanController@index` | Menu pusat pelaporan |
| `GET` | `/laporan/transaksi` | `LaporanController@transaksi` | Rekap transaksi per tanggal |
| `GET` | `/laporan/transaksi/pdf` | `LaporanController@cetakTransaksiPdf` | Unduh dokumen PDF transaksi |
| `GET` | `/laporan/stok-obat` | `LaporanController@stokObat` | Rekap monitoring stok obat |
| `GET` | `/laporan/stok-obat/pdf` | `LaporanController@cetakStokObatPdf` | Unduh dokumen PDF stok obat |

---

## 3. Modul Apoteker (`/apoteker/*`)
**Middleware:** `auth`, `cek.peran:apoteker`  
**Prefix:** `/apoteker` | **Name Prefix:** `apoteker.`

| Method | URI | Action / Handler | Deskripsi & Validasi |
| :--- | :--- | :--- | :--- |
| `GET` | `/dashboard` | Closure -> `view('apoteker.dashboard')` | Dashboard staf apoteker |
| `GET` | `/transaksi` | `TransaksiApotekerController@index` | Riwayat transaksi yang dilayani apoteker login |
| `GET` | `/transaksi/resep/buat` | `TransaksiApotekerController@createResep` | Form transaksi obat dengan resep dokter |
| `POST` | `/transaksi/resep` | `TransaksiApotekerController@storeResep` | Simpan resep & transaksi walk-in.<br>**Validasi:** `id_pasien` (req), `nama_dokter` (req), `nama_pasien_resep` (req), `detail_obat` (array, min:1) |
| `GET` | `/transaksi/tanpa-resep/buat` | `TransaksiApotekerController@createTanpaResep` | Form transaksi obat bebas tanpa resep |
| `POST` | `/transaksi/tanpa-resep` | `TransaksiApotekerController@storeTanpaResep` | Simpan transaksi bebas langsung ke kasir |
| `GET` | `/transaksi/{no}` | `TransaksiApotekerController@show` | Detail transaksi yang dibuat oleh apoteker ini |
| `GET` | `/pesanan` | `PesananController@index` | Daftar pesanan online masuk dari pasien |
| `GET` | `/pesanan/{no}` | `PesananController@show` | Detail item pesanan online pasien |
| `PATCH` | `/pesanan/{no}/status` | `PesananController@updateStatus` | Update status pesanan online & penyesuaian stok |
| `GET` | `/laporan` | `LaporanApotekerController@index` | Menu opsi laporan apoteker |
| `GET` | `/laporan/penjualan` | `LaporanApotekerController@penjualanApoteker` | Laporan penjualan apoteker terfilter tanggal |
| `GET` | `/laporan/penjualan/pdf` | `LaporanApotekerController@cetakPenjualanApotekerPdf` | Cetak PDF rekap penjualan apoteker |

---

## 4. Modul Pasien (`/pasien/*`)
**Middleware:** `auth`, `cek.peran:pasien`  
**Prefix:** `/pasien` | **Name Prefix:** `pasien.`

| Method | URI | Action / Handler | Deskripsi & Validasi |
| :--- | :--- | :--- | :--- |
| `GET` | `/dashboard` | `PasienController@index` | Halaman beranda pasien & etalase obat unggulan |
| `GET` | `/katalog-obat` | `PasienController@katalogObat` | Katalog belanja seluruh obat yang memiliki stok |
| `POST` | `/keranjang/tambah` | `PasienController@addToCart` | Tambah obat ke keranjang belanja session.<br>**Validasi:** `id_obat` (req, exists), `jumlah` (req, int, min:1) |
| `GET` | `/keranjang` | `PasienController@viewCart` | Halaman keranjang belanja |
| `PUT` | `/keranjang/update` | `PasienController@updateCart` | Ubah kuantitas obat di keranjang |
| `DELETE` | `/keranjang/hapus` | `PasienController@removeFromCart` | Hapus satu jenis obat dari keranjang |
| `GET` | `/checkout` | `PasienController@showCheckout` | Halaman ringkasan belanja & form metode bayar |
| `POST` | `/checkout/proses` | `PasienController@processCheckout` | Eksekusi pesanan dengan database locking, kalkulasi harga resmi DB, dan pemotongan stok |
| `GET` | `/riwayat-pesanan` | `PasienController@riwayatPesanan` | Riwayat transaksi pasien dengan rincian item |

---

## 5. Rute API
- `GET /api/user` (Middleware: `auth:sanctum`) — Mengembalikan objek JSON user yang sedang terotentikasi token Sanctum.
