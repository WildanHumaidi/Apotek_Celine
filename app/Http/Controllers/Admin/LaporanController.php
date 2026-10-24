<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransaksiPenjualan;
use App\Models\Obat;
use Illuminate\Http\Request;
use Carbon\Carbon; // PASTIKAN BARIS INI ADA

class LaporanController extends Controller
{
    public function index()
    {
        return view('admin.laporan.index');
    }

    public function transaksi(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $query = TransaksiPenjualan::with(['pasien', 'adminUser', 'detailTransaksi.obat']);

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tgl_transaksi', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tgl_transaksi', '<=', $request->tanggal_akhir);
        }

        $laporanTransaksis = $query->get();

        return view('admin.laporan.transaksi', compact('laporanTransaksis', 'request'));
    }

    public function cetakTransaksiPdf(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $query = TransaksiPenjualan::with(['pasien', 'adminUser', 'detailTransaksi.obat']);

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tgl_transaksi', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tgl_transaksi', '<=', $request->tanggal_akhir);
        }

        $laporanTransaksis = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.laporan.pdf.transaksi', compact('laporanTransaksis', 'request'));

        return $pdf->download('laporan_transaksi_penjualan_' . Carbon::now()->format('YmdHis') . '.pdf');
    }

    public function stokObat()
    {
        $laporanStok = Obat::orderBy('stok', 'asc')->get();
        return view('admin.laporan.stok_obat', compact('laporanStok'));
    }

    public function cetakStokObatPdf()
    {
        $laporanStok = Obat::orderBy('stok', 'asc')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.laporan.pdf.stok_obat', compact('laporanStok'));

        return $pdf->download('laporan_stok_obat_' . Carbon::now()->format('YmdHis') . '.pdf');
    }
}