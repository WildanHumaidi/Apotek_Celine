<?php

namespace App\Http\Controllers\Apoteker;

use App\Http\Controllers\Controller;
use App\Models\TransaksiPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanApotekerController extends Controller
{
    /**
     * Menampilkan halaman utama untuk opsi laporan Apoteker.
     */
    public function index()
    {
        return view('apoteker.laporan.index');
    }

    /**
     * Menghasilkan dan menampilkan laporan penjualan Apoteker berdasarkan periode waktu.
     */
    public function penjualanApoteker(Request $request) // NAMA METHOD BERUBAH
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $apotekerId = Auth::id(); // Ambil ID Apoteker yang sedang login

        $query = TransaksiPenjualan::where('id_admin', $apotekerId)
                                    ->with(['pasien', 'detailTransaksi.obat']);

        // Filter berdasarkan periode tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tgl_transaksi', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tgl_transaksi', '<=', $request->tanggal_akhir);
        }

        $laporanPenjualan = $query->get(); // Ambil transaksi dalam periode

        $totalPenjualan = $laporanPenjualan->sum('harga_bayar');

        // Kirim data laporan dan request (untuk mempertahankan filter di form) ke view
        return view('apoteker.laporan.penjualan_apoteker', compact('laporanPenjualan', 'totalPenjualan', 'request')); // NAMA VIEW BERUBAH
    }

    /**
     * Menghasilkan dan men-download PDF laporan penjualan Apoteker berdasarkan periode waktu.
     */
    public function cetakPenjualanApotekerPdf(Request $request) // NAMA METHOD BERUBAH
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $apotekerId = Auth::id();

        $query = TransaksiPenjualan::where('id_admin', $apotekerId)
                                    ->with(['pasien', 'detailTransaksi.obat']);

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tgl_transaksi', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tgl_transaksi', '<=', $request->tanggal_akhir);
        }

        $laporanPenjualan = $query->get();

        $totalPenjualan = $laporanPenjualan->sum('harga_bayar');

        // Load view khusus PDF, kirim data
        $pdf = Pdf::loadView('apoteker.laporan.pdf.penjualan_apoteker', compact('laporanPenjualan', 'totalPenjualan', 'request')); // NAMA VIEW BERUBAH

        $fileName = 'laporan_penjualan_apoteker_' . Carbon::parse($request->tanggal_mulai)->format('Ymd') . '-' . Carbon::parse($request->tanggal_akhir)->format('Ymd') . '.pdf';

        return $pdf->download($fileName);
    }
}