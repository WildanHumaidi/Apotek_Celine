<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\TransaksiPenjualan;
use App\Models\DetailTransaksi;
use Carbon\Carbon;

class PasienController extends Controller
{
    public function katalogObat()
    {
        $obats = Obat::where('stok', '>', 0)->orderBy('nama_obat')->get();
        return view('pasien.katalog_obat', compact('obats'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'id_obat' => 'required|exists:obat,id_obat',
            'jumlah' => 'required|integer|min:1',
        ]);

        $obat = Obat::find($request->id_obat);
        $jumlah = $request->jumlah;

        if (!$obat || $obat->stok < $jumlah) {
            return redirect()->back()->with('error', 'Stok obat ' . $obat->nama_obat . ' tidak mencukupi.');
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$obat->id_obat])) {
            $cart[$obat->id_obat]['jumlah'] += $jumlah;

            if ($obat->stok < $cart[$obat->id_obat]['jumlah']) {
                return redirect()->back()->with('error', 'Stok obat ' . $obat->nama_obat . ' di keranjang melebihi yang tersedia.');
            }
        } else {
            $cart[$obat->id_obat] = [
                "id_obat" => $obat->id_obat,
                "nama_obat" => $obat->nama_obat,
                "harga" => $obat->harga,
                "jumlah" => $jumlah,
            ];
        }

        session()->put('cart', $cart);
        return redirect()->back()->with('success', $obat->nama_obat . ' berhasil ditambahkan ke keranjang!');
    }

    public function viewCart()
    {
        $cart = session()->get('cart', []);
        return view('pasien.keranjang', compact('cart'));
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'id_obat' => 'required|exists:obat,id_obat',
            'jumlah' => 'required|integer|min:0',
        ]);

        $cart = session()->get('cart', []);
        $obat = Obat::find($request->id_obat);

        if (isset($cart[$request->id_obat])) {
            if ($request->jumlah == 0) {
                unset($cart[$request->id_obat]);
                session()->put('cart', $cart);
                return redirect()->back()->with('success', $obat->nama_obat . ' dihapus dari keranjang.');
            }

            if ($obat->stok < $request->jumlah) {
                return redirect()->back()->with('error', 'Stok obat ' . $obat->nama_obat . ' tidak mencukupi untuk jumlah yang diminta.');
            }

            $cart[$request->id_obat]['jumlah'] = $request->jumlah;
            session()->put('cart', $cart);
            return redirect()->back()->with('success', 'Jumlah ' . $obat->nama_obat . ' berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Obat tidak ditemukan di keranjang.');
    }

    public function removeFromCart(Request $request)
    {
        $request->validate([
            'id_obat' => 'required|exists:obat,id_obat',
        ]);

        $cart = session()->get('cart', []);
        $obat = Obat::find($request->id_obat);

        if (isset($cart[$request->id_obat])) {
            unset($cart[$request->id_obat]);
            session()->put('cart', $cart);
            return redirect()->back()->with('success', $obat->nama_obat . ' berhasil dihapus dari keranjang.');
        }

        return redirect()->back()->with('error', 'Obat tidak ditemukan di keranjang.');
    }

    public function showCheckout()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('pasien.katalog_obat')->with('error', 'Keranjang belanja Anda kosong.');
        }

        $pasien = Pasien::where('id_pasien', Auth::user()->id)->first();
        if (!$pasien) {
            return redirect()->route('pasien.dashboard')->with('error', 'Profil pasien belum lengkap.');
        }

        return view('pasien.checkout', compact('cart', 'pasien'));
    }

    public function processCheckout(Request $request)
    {
        $request->validate([
            'metode_pembayaran' => 'required|string|in:transfer_bank_ambil_apotek',
            'confirmPayment' => 'required|accepted',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('pasien.katalog_obat')->with('error', 'Keranjang kosong.');
        }

        DB::beginTransaction();
        try {
            $totalHargaBayar = 0;
            $totalJumlahObat = 0;
            $noTransaksi = 'TRX-' . Str::upper(Str::random(8));
            $obatUtamaId = null;

            foreach ($cart as $item) {
                $obat = Obat::lockForUpdate()->find($item['id_obat']);
                if (!$obat || $obat->stok < $item['jumlah']) {
                    throw new \Exception('Stok obat ' . ($obat->nama_obat ?? 'tidak ditemukan') . ' tidak mencukupi.');
                }

                $totalHargaBayar += $obat->harga * $item['jumlah'];
                $totalJumlahObat += $item['jumlah'];

                if (is_null($obatUtamaId)) {
                    $obatUtamaId = $item['id_obat'];
                }
            }

            $transaksi = TransaksiPenjualan::create([
                'no_transaksi' => $noTransaksi,
                'tgl_transaksi' => now()->toDateString(),
                'id_pasien' => Auth::user()->id,
                'id_admin' => null,
                'jumlah' => $totalJumlahObat,
                'id_obat' => $obatUtamaId,
                'harga_bayar' => $totalHargaBayar,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pesanan' => 'menunggu_pembayaran',
            ]);

            foreach ($cart as $item) {
                $obat = Obat::lockForUpdate()->find($item['id_obat']);

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
            session()->forget('cart');

            return redirect()->route('pasien.riwayat_pesanan')
                ->with('success', 'Pesanan berhasil! No. Transaksi: ' . $noTransaksi)
                ->with('last_order_no', $noTransaksi);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }

    public function dashboard()
    {
        $featuredObats = Obat::where('stok', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('pasien.dashboard', compact('featuredObats'));
    }

    public function index()
    {
        $featuredObats = Obat::all();
        return view('pasien.dashboard', compact('featuredObats'));
    }

    public function riwayatPesanan()
    {
        $userId = Auth::user()->id;
        $riwayat = TransaksiPenjualan::where('id_pasien', $userId)
            ->with(['detailTransaksi.obat'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pasien.riwayat_pesanan', compact('riwayat'));
    }
}
