@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Transaksi Tanpa Resep</div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('apoteker.transaksi.tanpa_resep.store') }}" method="POST">
                        @csrf

                        <h5 class="mb-3">Data Pasien</h5>
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

                        <h5 class="mb-3 mt-4">Obat-obatan</h5>
                        <div id="obat-items">
                            @if(old('detail_obat'))
                                @foreach(old('detail_obat') as $index => $oldItem)
                                    <div class="row mb-3 obat-item">
                                        <div class="col-md-7">
                                            <label class="form-label">Obat</label>
                                            <select class="form-control obat-select @error('detail_obat.'.$index.'.id_obat') is-invalid @enderror" name="detail_obat[{{ $index }}][id_obat]" required>
                                                <option value="">-- Pilih Obat --</option>
                                                @foreach ($obats as $obat)
                                                    <option value="{{ $obat->id_obat }}" data-harga="{{ $obat->harga }}" data-stok="{{ $obat->stok }}" 
                                                        {{ $oldItem['id_obat'] == $obat->id_obat ? 'selected' : '' }}>
                                                        {{ $obat->nama_obat }} (Stok: {{ $obat->stok }}, Harga: Rp {{ number_format($obat->harga, 2, ',', '.') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('detail_obat.'.$index.'.id_obat')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Jumlah</label>
                                            <input type="number" class="form-control jumlah-input @error('detail_obat.'.$index.'.jumlah') is-invalid @enderror" 
                                                name="detail_obat[{{ $index }}][jumlah]" value="{{ $oldItem['jumlah'] }}" min="1" required>
                                            @error('detail_obat.'.$index.'.jumlah')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger remove-obat-item w-100">Hapus</button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <button type="button" class="btn btn-secondary mb-3" id="add-obat-item">Tambah Obat</button>

                        <h5 class="mt-4">Total Pembayaran: <span id="total-pembayaran">Rp 0,00</span></h5>

                        <button type="submit" class="btn btn-success">Simpan Transaksi</button>
                        <a href="{{ route('apoteker.transaksi.index') }}" class="btn btn-secondary ms-2">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const addObatItemButton = document.getElementById('add-obat-item');
    const obatItemsContainer = document.getElementById('obat-items');
    const obatsData = @json($obats->keyBy('id_obat'));
    let itemIndex = {{ old('detail_obat') ? count(old('detail_obat')) : 0 }};

    function addObatItem(initialObatId = '', initialJumlah = '') {
        const newRow = document.createElement('div');
        newRow.classList.add('row', 'mb-3', 'obat-item');
        let options = `<option value="">-- Pilih Obat --</option>`;
        @foreach ($obats as $obat)
            options += `<option value="{{ $obat->id_obat }}" data-harga="{{ $obat->harga }}" data-stok="{{ $obat->stok }}">
                            {{ $obat->nama_obat }} (Stok: {{ $obat->stok }}, Harga: Rp {{ number_format($obat->harga, 2, ',', '.') }})
                        </option>`;
        @endforeach

        newRow.innerHTML = `
            <div class="col-md-7">
                <label class="form-label">Obat</label>
                <select class="form-control obat-select" name="detail_obat[${itemIndex}][id_obat]" required>
                    ${options}
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

    document.querySelectorAll('.obat-item').forEach(attachEventListeners);
    addObatItemButton.addEventListener('click', () => addObatItem());
    calculateTotal();
});
</script>
@endsection
