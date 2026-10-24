# RISK REGISTER

Dokumen ini memetakan risiko operasional, teknis, bisnis, serta kepatuhan pada sistem Apotek Celine sesuai dengan panduan `PLAYBOOK.md` Fase 2.

---

## 1. Skala Penilaian Risiko

- **Likelihood (Kemungkinan):** High, Medium, Low
- **Impact (Dampak):** Critical, High, Medium, Low
- **Severity (Tingkat Risiko Keseluruhan):** Critical, High, Medium, Low

---

## 2. Matriks Register Risiko

| ID Risiko | Kategori Risiko | Skenario Risiko | Pihak Terdampak | Kemungkinan | Dampak | Tingkat Risiko | Owner Disarankan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **RSK-01** | Data Loss / Financial | Penghapusan akun Staf/Apoteker memicu cascade delete pada seluruh transaksi penjualan historis yang ditanganinya. | Keuangan, Manajemen Apotek, Auditor | Medium | Critical | **High** | Lead Backend Engineer |
| **RSK-02** | Inventory Integrity | Pembatalan pesanan tidak mengembalikan stok obat ke sistem, menyebabkan stok tercatat habis palsu (ghost stockout). | Apoteker, Pasien, Bagian Gudang | High | High | **High** | Backend Developer |
| **RSK-03** | Operational / Outage | Penggunaan huruf kecil `pasien.php` menyebabkan kegagalan class loader (Fatal Crash) saat dideploy ke server Linux/Docker. | Seluruh Pengguna Sistem | High | Critical | **Critical** | DevOps / Lead Engineer |
| **RSK-04** | Revenue Loss | Perhitungan total checkout menggunakan harga obat dari session pengguna yang sudah basi/lama daripada harga database. | Pemilik Apotek, Manajemen | Medium | High | **High** | Backend Developer |
| **RSK-05** | Customer Frustration | Pasien yang mendaftar melalui Google OAuth terblokir dari transaksi checkout karena record tabel `pasien` tidak terbentuk otomatis. | Pasien Baru | High | High | **High** | Fullstack Developer |
| **RSK-06** | System Availability | Crash HTTP 500 saat menampilkan riwayat transaksi jika item transaksi memiliki kuantitas nol (Division by Zero). | Pasien, Staf Apoteker, Admin | Low | Medium | **Medium** | Frontend / View Developer |
| **RSK-07** | Security Vulnerability | Dependensi pihak ketiga yang rentan (Dompdf, Guzzle, Symfony) dieksploitasi untuk DoS atau manipulasi header. | Seluruh Sistem | Medium | High | **High** | DevSecOps / Lead Engineer |
| **RSK-08** | Deployment / DX | Pipeline automated test gagal out-of-the-box karena assertion status 200 pada route `/` yang dialihkan ke `/login`. | Tim Pengembang & CI/CD | High | Low | **Medium** | QA Engineer |

---

## 3. Rincian & Rencana Mitigasi Risiko

### RSK-01: Historical Transaction Loss via Foreign Key Cascade
- **Skenario:** Seorang apoteker berhenti bekerja dan akun stafnya dihapus melalui modul Admin User. Karena foreign key `id_admin` pada `transaksi_penjualan` menggunakan `onDelete('cascade')`, seluruh rekaman transaksi yang pernah dibuat oleh apoteker tersebut terhapus permanen dari basis data.
- **Pihak Terdampak:** Pemilik Apotek, Bagian Akuntansi, Auditor Pajak.
- **Mitigasi:**
  1. Ubah constraint migrasi `transaksi_penjualan` dari `cascade` menjadi `set null` pada kolom `id_admin`.
  2. Terapkan soft deletes pada model `User`.
- **Residual Risk:** Nilai `id_admin` menjadi `NULL`, namun rekam jejak nama staf dapat dipertahankan melalui audit log terpisah jika diperlukan audit forensik.

---

### RSK-02: Inventory Stock Desynchronization on Cancelled Orders
- **Skenario:** Pasien memesan obat lalu pesanan dibatalkan (karena tidak membayar atau salah pesan). Stok yang berkurang tidak dikembalikan, menyebabkan sistem menganggap obat habis padahal stok fisik masih tersedia di rak.
- **Pihak Terdampak:** Apoteker, Pasien (kehilangan kesempatan membeli).
- **Mitigasi:**
  Terapkan penyesuaian stok otomatis pada `updateStatus`: kembalikan kuantitas obat (`$obat->increment('stok', $detail->jumlah)`) saat status bergeser ke `dibatalkan`.
- **Residual Risk:** Rendah, selama seluruh pembaruan status melewati controller/service resmi.

---

### RSK-03: Production Outage due to File Name Case-Sensitivity
- **Skenario:** Proyek di-deploy dari sistem Windows (case-insensitive) ke server produksi Linux (Ubuntu/Debian) atau container Docker. Kode memanggil `App\Models\Pasien`, tetapi file fisik bernama `pasien.php`. PHP runtime langsung melempar fatal error: `Class "App\Models\Pasien" not found`.
- **Pihak Terdampak:** Seluruh pengguna aplikasi (pasien, apoteker, admin).
- **Mitigasi:**
  Ganti nama file model secara resmi menjadi `app/Models/Pasien.php` dan standarisasi nama model Eloquent sesuai konvensi PascalCase PSR-4.
- **Residual Risk:** Nol setelah nama file di repositori disinkronkan.

---

### RSK-04: Revenue Loss from Stale Cart Pricing
- **Skenario:** Terjadi kenaikan harga obat di apotek. Pasien yang telah memasukkan obat ke keranjang sebelum kenaikan harga melakukan checkout beberapa jam atau hari kemudian, dan ditagih dengan harga lama yang tersimpan di session.
- **Pihak Terdampak:** Manajemen Keuangan Apotek Celine.
- **Mitigasi:**
  Modifikasi `PasienController@processCheckout` agar selalu membaca dan menggunakan `$obat->harga` aktual dari database saat memproses pesanan dan menghitung total tagihan.
- **Residual Risk:** Nol, transaksi selalu mengikat harga riil saat eksekusi pembayaran/checkout.

---

### RSK-05: Google OAuth Patient Checkout Blocker
- **Skenario:** Pasien memilih login menggunakan akun Google. User berhasil dibuat, tetapi saat mencoba checkout, sistem menampilkan error "Profil pasien belum lengkap" tanpa menyediakan form untuk mengisi profil.
- **Pihak Terdampak:** Pasien pengguna Google OAuth.
- **Mitigasi:**
  Otomatisasi pembuatan data pada tabel `pasien` saat pendaftaran Google OAuth baru berlangsung, dengan nomor telepon dan alamat placeholder yang dapat diperbarui nanti.
- **Residual Risk:** Rendah, pasien mungkin perlu memperbarui alamat pengiriman saat fitur pengiriman ke rumah diaktifkan di masa depan.
