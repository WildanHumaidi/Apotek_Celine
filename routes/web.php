<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Admin\TransaksiPenjualanController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Apoteker\TransaksiApotekerController;
use App\Http\Controllers\Apoteker\LaporanApotekerController;
use App\Http\Controllers\Pasien\PasienController;
use App\Http\Controllers\Apoteker\PesananController;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

// Redirect setelah login berdasarkan peran
Route::get('/redirect', function () {
    $user = Auth::user();

    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->isApoteker()) {
        return redirect()->route('apoteker.dashboard');
    } elseif ($user->isPasien()) {
        return redirect()->route('pasien.dashboard');
    }

    abort(403, 'Peran tidak dikenali.');
})->middleware('auth')->name('redirect.peran');

// =================== ADMIN ===================
Route::middleware(['auth', 'cek.peran:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');

    // Obat
    Route::resource('obat', ObatController::class)->except(['show']);

    // User
    Route::resource('users', UserController::class)->except(['show']);

    // Transaksi Penjualan
    Route::get('transaksi-penjualan', [TransaksiPenjualanController::class, 'index'])->name('transaksi_penjualan.index');
    Route::get('transaksi-penjualan/{no_transaksi}', [TransaksiPenjualanController::class, 'show'])->name('transaksi_penjualan.show');
    Route::patch('transaksi-penjualan/{no_transaksi}/status', [TransaksiPenjualanController::class, 'updateStatus'])->name('transaksi_penjualan.update_status');

    // Laporan
    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/transaksi', [LaporanController::class, 'transaksi'])->name('laporan.transaksi');
    Route::get('laporan/stok-obat', [LaporanController::class, 'stokObat'])->name('laporan.stok_obat');
    Route::get('laporan/transaksi/pdf', [LaporanController::class, 'cetakTransaksiPdf'])->name('laporan.cetak_transaksi_pdf');
    Route::get('laporan/stok-obat/pdf', [LaporanController::class, 'cetakStokObatPdf'])->name('laporan.cetak_stok_obat_pdf');
});

// =================== APOTEKER ===================
Route::middleware(['auth', 'cek.peran:apoteker'])->prefix('apoteker')->name('apoteker.')->group(function () {
    Route::get('/dashboard', fn() => view('apoteker.dashboard'))->name('dashboard');

    // Transaksi
    Route::get('transaksi', [TransaksiApotekerController::class, 'index'])->name('transaksi.index');
    Route::get('transaksi/resep/buat', [TransaksiApotekerController::class, 'createResep'])->name('transaksi.resep.create');
    Route::post('transaksi/resep', [TransaksiApotekerController::class, 'storeResep'])->name('transaksi.resep.store');
    Route::get('transaksi/tanpa-resep/buat', [TransaksiApotekerController::class, 'createTanpaResep'])->name('transaksi.tanpa_resep.create');
    Route::post('transaksi/tanpa-resep', [TransaksiApotekerController::class, 'storeTanpaResep'])->name('transaksi.tanpa_resep.store');
    Route::get('transaksi/{no_transaksi}', [TransaksiApotekerController::class, 'show'])->name('transaksi.show');

    // Laporan
    Route::get('laporan', [LaporanApotekerController::class, 'index'])->name('laporan.index');
    Route::get('laporan/penjualan', [LaporanApotekerController::class, 'penjualanApoteker'])->name('laporan.penjualan_apoteker');
    Route::get('laporan/penjualan/pdf', [LaporanApotekerController::class, 'cetakPenjualanApotekerPdf'])->name('laporan.cetak_penjualan_apoteker_pdf');

    // PESANAN
    Route::get('pesanan', [PesananController::class, 'index'])->name('pesanan.index');
    Route::get('pesanan/{no_transaksi}', [PesananController::class, 'show'])->name('pesanan.show');
    Route::patch('pesanan/{no_transaksi}/status', [PesananController::class, 'updateStatus'])->name('pesanan.update_status');
});

// =================== PASIEN ===================
Route::middleware(['auth', 'cek.peran:pasien'])->prefix('pasien')->name('pasien.')->group(function () {
    Route::get('/dashboard', [PasienController::class, 'index'])->name('dashboard');

    Route::get('/katalog-obat', [PasienController::class, 'katalogObat'])->name('katalog_obat');
    Route::post('/keranjang/tambah', [PasienController::class, 'addToCart'])->name('keranjang.add');
    Route::get('/keranjang', [PasienController::class, 'viewCart'])->name('keranjang.view');
    Route::put('/keranjang/update', [PasienController::class, 'updateCart'])->name('keranjang.update');
    Route::delete('/keranjang/hapus', [PasienController::class, 'removeFromCart'])->name('keranjang.remove');

    Route::get('/checkout', [PasienController::class, 'showCheckout'])->name('checkout.show');
    Route::post('/checkout/proses', [PasienController::class, 'processCheckout'])->name('checkout.process');

    Route::get('/riwayat-pesanan', [PasienController::class, 'riwayatPesanan'])->name('riwayat_pesanan');
});

// Google Login
Route::get('auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
