<?php

namespace App\Http\Controllers\Apoteker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang login
use Illuminate\Support\Facades\DB;   // Untuk transaksi database
use Illuminate\Support\Str;          // Untuk no_transaksi unik

use App\Models\Pasien;
use App\Models\Obat;
use App\Models\PasienDenganResep;
use App\Models\TransaksiPenjualan;
use App\Models\DetailTransaksi;

class TransaksiApotekerController extends Controller
{
    /**
     * Menampilkan daftar transaksi yang dibuat oleh Apoteker ini.
     */
    public function index()
    {
        $apotekerId = Auth::id(); // Ambil ID apoteker yang sedang login
        $transaksis = TransaksiPenjualan::where('id_admin', $apotekerId)
                                        ->with(['pasien', 'detailTransaksi.obat'])
                                        ->orderBy('created_at', 'desc')
                                        ->get();
        return view('apoteker.transaksi.index', compact('transaksis'));
    }

    /**
     * Menampilkan form untuk transaksi obat DENGAN resep.
     */
    public function createResep()
    {
        $pasien = Pasien::all(); // Untuk memilih pasien yang sudah ada
        $obats = Obat::where('stok', '>', 0)->get(); // Hanya tampilkan obat yang tersedia
        return view('apoteker.transaksi.create_resep', compact('pasien', 'obats'));
    }

    /**
     * Menyimpan transaksi obat DENGAN resep ke database.
     */
    public function storeResep(Request $request)
    {
        $request->validate([
            'id_pasien' => 'required|exists:pasien,id_pasien',
            'nama_dokter' => 'required|string|max:255',
            'detail_obat' => 'required|array|min:1', // Harus ada minimal satu item obat
            'detail_obat.*.id_obat' => 'required|exists:obat,id_obat',
            'detail_obat.*.jumlah' => 'required|integer|min:1',
            'nama_pasien_resep' => 'required|string|max:255', // Dari diagram
            'umur_pasien_resep' => 'nullable|integer|min:0', // Dari diagram
            'alamat_pasien_resep' => 'nullable|string|max:255', // Dari diagram
            'no_telp_pasien_resep' => 'nullable|string|max:20', // Dari diagram
        ]);

        DB::beginTransaction(); // Memulai transaksi database

        try {
            // 1. Simpan data resep
            $pasienResep = PasienDenganResep::create([
                'id_pasien' => $request->id_pasien,
                'nama_pasien' => $request->nama_pasien_resep, // Nama pasien dari form resep
                'nama_dokter' => $request->nama_dokter,
                'umur' => $request->umur_pasien_resep,
                'alamat' => $request->alamat_pasien_resep,
                'no_telp' => $request->no_telp_pasien_resep,
            ]);

            // 2. Buat Transaksi Penjualan
            $noTransaksi = 'TRX-' . Str::upper(Str::random(8));
            $totalHargaBayar = 0;
            $totalJumlahObat = 0;

            // Loop untuk menghitung total dan memeriksa stok
            foreach ($request->detail_obat as $item) {
                $obat = Obat::find($item['id_obat']);
                if (!$obat || $obat->stok < $item['jumlah']) {
                    throw new \Exception('Stok obat ' . ($obat->nama_obat ?? 'ID:' . $item['id_obat']) . ' tidak mencukupi atau tidak ditemukan.');
                }
                $totalHargaBayar += $obat->harga * $item['jumlah'];
                $totalJumlahObat += $item['jumlah'];
            }

            $transaksi = TransaksiPenjualan::create([
                'no_transaksi' => $noTransaksi,
                'tgl_transaksi' => now()->toDateString(), // Tanggal transaksi hari ini
                'id_pasien' => $request->id_pasien,
                'id_admin' => Auth::id(), // ID Apoteker yang login
                'jumlah' => $totalJumlahObat, // Total jumlah item dalam transaksi
                'id_obat' => $request->detail_obat[0]['id_obat'], // Ambil ID obat pertama sebagai obat utama (sesuai diagram lama)
                'harga_bayar' => $totalHargaBayar,
            ]);

            // 3. Simpan Detail Transaksi & Kurangi Stok
            foreach ($request->detail_obat as $item) {
                $obat = Obat::find($item['id_obat']);
                DetailTransaksi::create([
                    'no_transaksi' => $noTransaksi,
                    'tgl_transaksi' => now()->toDateString(),
                    'harga_bayar' => $obat->harga * $item['jumlah'], // Harga untuk item ini
                    'jumlah' => $item['jumlah'],
                    'id_obat' => $item['id_obat'],
                ]);

                // Kurangi stok obat
                $obat->decrement('stok', $item['jumlah']);
            }

            DB::commit(); // Komit transaksi jika semua berhasil

            return redirect()->route('apoteker.transaksi.index')
                             ->with('success', 'Transaksi dengan resep berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi jika terjadi error
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan form untuk transaksi obat TANPA resep.
     */
    public function createTanpaResep()
    {
        $pasien = Pasien::all();
        $obats = Obat::where('stok', '>', 0)->get();
        return view('apoteker.transaksi.create_tanpa_resep', compact('pasien', 'obats'));
    }

    /**
     * Menyimpan transaksi obat TANPA resep ke database.
     */
    public function storeTanpaResep(Request $request)
    {
        $request->validate([
            'id_pasien' => 'required|exists:pasien,id_pasien',
            'detail_obat' => 'required|array|min:1',
            'detail_obat.*.id_obat' => 'required|exists:obat,id_obat',
            'detail_obat.*.jumlah' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $noTransaksi = 'TRX-' . Str::upper(Str::random(8));
            $totalHargaBayar = 0;
            $totalJumlahObat = 0;

            foreach ($request->detail_obat as $item) {
                $obat = Obat::find($item['id_obat']);
                if (!$obat || $obat->stok < $item['jumlah']) {
                    throw new \Exception('Stok obat ' . ($obat->nama_obat ?? 'ID:' . $item['id_obat']) . ' tidak mencukupi atau tidak ditemukan.');
                }
                $totalHargaBayar += $obat->harga * $item['jumlah'];
                $totalJumlahObat += $item['jumlah'];
            }

            $transaksi = TransaksiPenjualan::create([
                'no_transaksi' => $noTransaksi,
                'tgl_transaksi' => now()->toDateString(),
                'id_pasien' => $request->id_pasien,
                'id_admin' => Auth::id(), // ID Apoteker yang login
                'jumlah' => $totalJumlahObat,
                'id_obat' => $request->detail_obat[0]['id_obat'],
                'harga_bayar' => $totalHargaBayar,
            ]);

            foreach ($request->detail_obat as $item) {
                $obat = Obat::find($item['id_obat']);
                DetailTransaksi::create([
                    'no_transaksi' => $noTransaksi,
                    'tgl_transaksi' => now()->toDateString(),
                    'harga_bayar' => $obat->harga * $item['jumlah'],
                    'jumlah' => $item['jumlah'],
                    'id_obat' => $item['id_obat'],
                ]);
                $obat->decrement('stok', $item['jumlah']);
            }

            DB::commit();

            return redirect()->route('apoteker.transaksi.index')
                             ->with('success', 'Transaksi tanpa resep berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail satu transaksi penjualan oleh Apoteker.
     */
    public function show($no_transaksi)
    {
        $transaksi = TransaksiPenjualan::where('no_transaksi', $no_transaksi)
                                        ->where('id_admin', Auth::id()) // Pastikan hanya bisa melihat transaksinya sendiri
                                        ->with(['pasien', 'adminUser', 'detailTransaksi.obat'])
                                        ->firstOrFail();
        return view('apoteker.transaksi.show', compact('transaksi'));
    }
}