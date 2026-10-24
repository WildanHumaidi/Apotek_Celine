<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransaksiPenjualan; // Pastikan model ini di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Pastikan Carbon di-import jika digunakan untuk format tanggal

class TransaksiPenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Mengambil semua transaksi penjualan dengan relasi pasien dan admin/user
        $transaksis = TransaksiPenjualan::with(['pasien', 'adminUser'])->get();
        return view('admin.transaksi.index', compact('transaksis'));
    }

    /**
     * Display the specified resource (detail transaksi).
     */
    public function show($no_transaksi)
    {
        $transaksi = TransaksiPenjualan::where('no_transaksi', $no_transaksi)
                                        ->with(['pasien', 'adminUser', 'detailTransaksi.obat'])
                                        ->firstOrFail();

        // Daftar status yang tersedia untuk dropdown
        $availableStatuses = [
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'terbayar' => 'Terbayar',
            'diproses' => 'Diproses',
            'siap_diambil' => 'Siap Diambil', // Status baru: pesanan siap diambil di apotek
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ];

        return view('admin.transaksi.show', compact('transaksi', 'availableStatuses'));
    }

    /**
     * Update the status of the specified transaction.
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

            // Redirect kembali ke halaman detail transaksi dengan pesan sukses
            return redirect()->route('admin.transaksi_penjualan.show', $no_transaksi)
                             ->with('success', 'Status pesanan berhasil diperbarui menjadi ' . ucfirst(str_replace('_', ' ', $request->status_pesanan)) . '.');
        } catch (\Exception $e) {
            // Jika ada error, redirect kembali dengan pesan error
            return redirect()->back()
                             ->with('error', 'Gagal memperbarui status pesanan: ' . $e->getMessage());
        }
    }

    // Metode CRUD obat Admin lainnya (index, create, store, show, edit, update, destroy)
    // Jika Anda punya metode lain di controller ini, pastikan mereka ada di sini.
}