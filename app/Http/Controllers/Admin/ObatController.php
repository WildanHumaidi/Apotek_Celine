<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // <<< PASTIKAN INI DI-IMPORT

class ObatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $obats = Obat::all();
        return view('admin.obat.index', compact('obats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.obat.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_obat' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'kategori' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi file foto
        ]);

        $data = $request->only(['nama_obat', 'deskripsi', 'harga', 'kategori', 'stok']);

        if ($request->hasFile('foto')) {
            // Simpan foto ke storage/app/public/obat_photos
            $imagePath = $request->file('foto')->store('obat_photos', 'public');
            $data['foto'] = $imagePath; // Simpan path relatif di database
        } else {
            $data['foto'] = null; // Pastikan kolom foto diset null jika tidak ada file
        }

        Obat::create($data);

        return redirect()->route('admin.obat.index')
                         ->with('success', 'Obat berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Obat $obat)
    {
        return view('admin.obat.show', compact('obat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Obat $obat)
    {
        return view('admin.obat.edit', compact('obat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Obat $obat)
    {
        $request->validate([
            'nama_obat' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'kategori' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi file foto
        ]);

        $data = $request->only(['nama_obat', 'deskripsi', 'harga', 'kategori', 'stok']);

        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($obat->foto && Storage::disk('public')->exists($obat->foto)) {
                Storage::disk('public')->delete($obat->foto);
            }
            // Simpan foto baru
            $imagePath = $request->file('foto')->store('obat_photos', 'public');
            $data['foto'] = $imagePath;
        }

        $obat->update($data);

        return redirect()->route('admin.obat.index')
                         ->with('success', 'Obat berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Obat $obat)
    {
        // Hapus foto dari storage saat obat dihapus
        if ($obat->foto && Storage::disk('public')->exists($obat->foto)) {
            Storage::disk('public')->delete($obat->foto);
        }
        $obat->delete();

        return redirect()->route('admin.obat.index')
                         ->with('success', 'Obat berhasil dihapus.');
    }
}