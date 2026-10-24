@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Transaksi Dengan Resep</div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('apoteker.transaksi.resep.store') }}" method="POST">
                        @csrf

                        {{-- Data Pasien --}}
                        <h5 class="mb-3">Data Pasien (dari data pasien terdaftar)</h5>
                        <div class="mb-3">
                            <label for="id_pasien" class="form-label">Pilih Pasien Terdaftar</label>
                            <select class="form-control @error('id_pasien') is-invalid @enderror" id="id_pasien" name="id_pasien" required>
                                <option value="">-- Pilih Pasien --</option>
                                @foreach ($pasien as $p)
                                    <option value="{{ $p->id_pasien }}" {{ old('id_pasien') == $p->id_pasien ? 'selected' : '' }}>
                                        {{ $p->nama_pasien }} ({{ $p->no_telp }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_pasien')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Detail Resep --}}
                        <h5 class="mb-3 mt-4">Detail Resep</h5>
                        <div class="mb-3">
                            <label for="nama_dokter" class="form-label">Nama Dokter</label>
                            <input type="text" class="form-control @error('nama_dokter') is-invalid @enderror" id="nama_dokter" name="nama_dokter" value="{{ old('nama_dokter') }}" required>
                            @error('nama_dokter')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nama_pasien_resep" class="form-label">Nama Pasien Resep (sesuai resep)</label>
                            <input type="text" class="form-control @error('nama_pasien_resep') is-invalid @enderror" id="nama_pasien_resep" name="nama_pasien_resep" value="{{ old('nama_pasien_resep') }}" required>
                            @error('nama_pasien_resep')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="umur_pasien_resep" class="form-label">Umur Pasien Resep</label>
                                <input type="number" class="form-control @error('umur_pasien_resep') is-invalid @enderror" id="umur_pasien_resep" name="umur_pasien_resep" value="{{ old('umur_pasien_resep') }}">
                                @error('umur_pasien_resep')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="no_telp_pasien_resep" class="form-label">No. Telepon Pasien Resep</label>
                                <input type="text" class="form-control @error('no_telp_pasien_resep') is-invalid @enderror" id="no_telp_pasien_resep" name="no_telp_pasien_resep" value="{{ old('no_telp_pasien_resep') }}">
                                @error('no_telp_pasien_resep')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="alamat_pasien_resep" class="form-label">Alamat Pasien Resep</label>
                            <textarea class="form-control @error('alamat_pasien_resep') is-invalid @enderror" id="alamat_pasien_resep" name="alamat_pasien_resep">{{ old('alamat_pasien_resep') }}</textarea>
                            @error('alamat_pasien_resep')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Obat-obatan --}}
                        <h5 class="mb-3 mt-4">Obat-obatan</h5>
                        <div id="obat-items">
                            @if(old('detail_obat'))
                                @foreach(old('detail_obat') as $index => $oldItem)
                                    @include('partials._obat_item', ['index' => $index, 'oldItem' => $oldItem, 'obats' => $obats])
                                @endforeach
                            @endif
                        </div>
                        <button type="button" class="btn btn-secondary mb-3" id="add-obat-item">Tambah Obat</button>

                        {{-- Total & Actions --}}
                        <h5 class="mt-4">Total Pembayaran: <span id="total-pembayaran">Rp 0,00</span></h5>
                        <button type="submit" class="btn btn-success">Simpan Transaksi</button>
                        <a href="{{ route('apoteker.transaksi.index') }}" class="btn btn-secondary ms-2">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addObatItemButton = document.getElementById('add-obat-item');
    const obatItemsContainer = document.getElementById('obat-items');
    const obatsData = @json($obats->keyBy('id_obat'));
    let itemIndex = {{ old('detail_obat') ? count(old('detail_obat')) : 0 }};

    function addObatItem(initialObatId = '', initialJumlah = '') {
        const newRow = document.createElement('div');
        newRow.classList.add('row', 'mb-3', 'obat-item');
        newRow.innerHTML = `
            <div class="col-md-7">
                <label class="form-label">Obat</label>
                <select class="form-control obat-select" name="detail_obat[${itemIndex}][id_obat]" required>
                    <option value="">-- Pilih Obat --</option>
                    @foreach ($obats as $obat)
                        <option value="{{ $obat->id_obat }}" data-harga="{{ $obat->harga }}" data-stok="{{ $obat->stok }}">
                            {{ $obat->nama_obat }} (Stok: {{ $obat->stok }}, Harga: Rp {{ number_format($obat->harga, 2, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Jumlah</label>
                <input type="number" class="form-control jumlah-input" name="detail_obat[${itemIndex}][jumlah]" value="${initialJumlah}" min="1" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger remove-obat-item w-100">Hapus</button>
            </div>
        `;
        obatItemsContainer.appendChild(newRow);
        itemIndex++;
        attachEventListeners(newRow);
        calculateTotal();
    }

    function attachEventListeners(element) {
        element.querySelector('.remove-obat-item').addEventListener('click', function () {
            element.remove();
            calculateTotal();
        });
        element.querySelector('.obat-select').addEventListener('change', calculateTotal);
        element.querySelector('.jumlah-input').addEventListener('input', calculateTotal);
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.obat-item').forEach(item => {
            const selectElement = item.querySelector('.obat-select');
            const jumlahInput = item.querySelector('.jumlah-input');

            const selectedObatId = selectElement.value;
            const jumlah = parseInt(jumlahInput.value);

            if (selectedObatId && !isNaN(jumlah) && jumlah > 0) {
                const harga = obatsData[selectedObatId]?.harga;
                if (harga) {
                    total += harga * jumlah;
                }
            }
        });

        document.getElementById('total-pembayaran').textContent = 'Rp ' + total.toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Init listeners
    document.querySelectorAll('.obat-item').forEach(attachEventListeners);
    addObatItemButton.addEventListener('click', () => addObatItem());
    calculateTotal();
});
</script>
@endsection
