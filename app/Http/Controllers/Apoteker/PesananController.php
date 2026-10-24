<?php

namespace App\Http\Controllers\Apoteker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang login (opsional di sini, tapi bagus)
use Illuminate\Support\Facades\DB;
use App\Models\TransaksiPenjualan; // Model TransaksiPenjualan
use Carbon\Carbon; // Untuk format tanggal

class PesananController extends Controller
{
    /**
     * Menampilkan daftar semua pesanan (transaksi penjualan) untuk dikelola oleh Apoteker.
     */
    public function index()
    {
        // Mengambil semua transaksi penjualan, diurutkan berdasarkan tanggal terbaru
        // Menggunakan eager loading untuk pasien, user (admin/apoteker yang membuat), dan detail transaksi
        $transaksis = TransaksiPenjualan::with(['pasien', 'adminUser'])
                                        ->orderBy('created_at', 'desc')
                                        ->get();
        return view('apoteker.pesanan.index', compact('transaksis'));
    }

    /**
     * Menampilkan detail spesifik dari sebuah pesanan.
     */
    public function show($no_transaksi)
    {
        // Mencari transaksi berdasarkan nomor transaksi
        $transaksi = TransaksiPenjualan::where('no_transaksi', $no_transaksi)
                                        ->with(['pasien', 'adminUser', 'detailTransaksi.obat'])
                                        ->firstOrFail();

        // Daftar status yang tersedia untuk dropdown (sesuai yang di Admin)
        $availableStatuses = [
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'terbayar' => 'Terbayar',
            'diproses' => 'Diproses',
            'siap_diambil' => 'Siap Diambil', // Status baru: pesanan siap diambil di apotek
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ];

        return view('apoteker.pesanan.show', compact('transaksi', 'availableStatuses'));
    }

    /**
     * Memperbarui status pesanan dari transaksi tertentu.
     */
    public function updateStatus(Request $request, $no_transaksi)
    {
        // Validasi status baru yang dikirim dari form
        $request->validate([
            'status_pesanan' => 'required|string|in:menunggu_pembayaran,terbayar,diproses,siap_diambil,selesai,dibatalkan',
        ]);

        // Cari transaksi berdasarkan nomor transaksi dengan relasi detailTransaksi dan obat
        $transaksi = TransaksiPenjualan::where('no_transaksi', $no_transaksi)
                                        ->with('detailTransaksi.obat')
                                        ->firstOrFail();

        try {
            $oldStatus = $transaksi->status_pesanan;
            $newStatus = $request->status_pesanan;

            if ($oldStatus !== $newStatus) {
                DB::transaction(function () use ($transaksi, $oldStatus, $newStatus) {
                    // Jika dibatalkan, kembalikan stok obat
                    if ($newStatus === 'dibatalkan' && $oldStatus !== 'dibatalkan') {
                        foreach ($transaksi->detailTransaksi as $detail) {
                            if ($detail->obat) {
                                $detail->obat->increment('stok', $detail->jumlah);
                            }
                        }
                    }
                    // Jika pesanan yang dibatalkan diaktifkan kembali, kurangi stok kembali
                    elseif ($oldStatus === 'dibatalkan' && $newStatus !== 'dibatalkan') {
                        foreach ($transaksi->detailTransaksi as $detail) {
                            if ($detail->obat) {
                                if ($detail->obat->stok < $detail->jumlah) {
                                    throw new \Exception('Stok obat ' . $detail->obat->nama_obat . ' tidak mencukupi untuk mengaktifkan kembali pesanan.');
                                }
                                $detail->obat->decrement('stok', $detail->jumlah);
                            }
                        }
                    }

                    $transaksi->status_pesanan = $newStatus;
                    $transaksi->save();
                });
            }

            // Redirect kembali ke halaman detail pesanan dengan pesan sukses
            return redirect()->route('apoteker.pesanan.show', $no_transaksi)
                             ->with('success', 'Status pesanan berhasil diperbarui menjadi ' . ucfirst(str_replace('_', ' ', $request->status_pesanan)) . '.');
        } catch (\Exception $e) {
            // Jika ada error, redirect kembali dengan pesan error
            return redirect()->back()
                             ->with('error', 'Gagal memperbarui status pesanan: ' . $e->getMessage());
        }
    }
}