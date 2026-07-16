@extends('layouts.app')

@section('title', 'Dasbor Admin')
@section('page_title', 'Administrasi Sistem')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 col-md-4">
        <div class="glass-card text-center pb-4">
            <div class="display-4 text-primary mb-2"><i class="fa-solid fa-users-gear"></i></div>
            <h4>Manajemen Pengguna</h4>
            <p class="text-muted small">Kelola tingkat akses dan peran</p>
            <button class="btn btn-outline-primary w-100 mt-2" onclick="alert('Fitur Manajemen Pengguna belum diimplementasikan di prototipe ini.')">Kelola Pengguna</button>
        </div>
    </div>
    
    <div class="col-12 col-md-4">
        <div class="glass-card text-center pb-4">
            <div class="display-4 text-success mb-2"><i class="fa-solid fa-ship"></i></div>
            <h4>Manajemen Data Pelabuhan</h4>
            <p class="text-muted small">Tambah atau perbarui lokasi pelabuhan global</p>
            <button class="btn btn-outline-success w-100 mt-2" onclick="alert('Fitur Manajemen Pelabuhan belum diimplementasikan.')">Kelola Pelabuhan</button>
        </div>
    </div>
    
    <div class="col-12 col-md-4">
        <div class="glass-card text-center pb-4">
            <div class="display-4 text-warning mb-2"><i class="fa-solid fa-file-pen"></i></div>
            <h4>Artikel Intelijen</h4>
            <p class="text-muted small">Terbitkan laporan analisis kustom</p>
            <button class="btn btn-outline-warning w-100 mt-2" onclick="alert('Fitur Manajemen Artikel belum diimplementasikan.')">Kelola Artikel</button>
        </div>
    </div>
    
    <div class="col-12">
        <div class="glass-card">
            <h5 class="border-bottom border-secondary pb-3 mb-3">Tindakan Sistem</h5>
            
            <div class="row g-3">
                <div class="col-12 col-md-6 border-end border-secondary">
                    <h6 class="text-info"><i class="fa-solid fa-rotate me-2"></i>Picu Pembaruan Mesin Manual</h6>
                    <p class="text-muted small mb-3">Memaksa sistem untuk menghitung ulang skor risiko dan mengambil berita terbaru untuk semua negara segera tanpa menunggu jadwal cron.</p>
                    <button class="btn btn-primary" onclick="triggerRiskUpdate()" id="btnRiskUpdate">
                        <i class="fa-solid fa-play me-2"></i> Jalankan Mesin UpdateRiskData
                    </button>
                    <div class="mt-2 small d-none" id="updateStatus">
                        <span class="text-warning"><i class="fa-solid fa-spinner fa-spin me-2"></i> Menjalankan tugas latar belakang... Ini mungkin memakan waktu beberapa menit.</span>
                    </div>
                </div>
                
                <div class="col-12 col-md-6 ps-md-4">
                    <h6 class="text-danger"><i class="fa-solid fa-trash-can me-2"></i>Bersihkan Cache API</h6>
                    <p class="text-muted small mb-3">Menghapus data cuaca yang disimpan di-cache, berita lama, dan nilai tukar mata uang untuk membebaskan ruang database.</p>
                    <button class="btn btn-outline-danger" onclick="alert('Cache berhasil dibersihkan! (Simulasi)')">
                        <i class="fa-solid fa-broom me-2"></i> Bersihkan Cache
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function triggerRiskUpdate() {
        const btn = document.getElementById('btnRiskUpdate');
        const status = document.getElementById('updateStatus');
        
        btn.disabled = true;
        status.classList.remove('d-none');
        
        setTimeout(() => {
            status.innerHTML = '<span class="text-success"><i class="fa-solid fa-check-circle me-2"></i> Pembaruan mesin berhasil dipicu. Periksa log untuk detailnya.</span>';
            setTimeout(() => {
                btn.disabled = false;
                status.classList.add('d-none');
                status.innerHTML = '<span class="text-warning"><i class="fa-solid fa-spinner fa-spin me-2"></i> Menjalankan tugas latar belakang... Ini mungkin memakan waktu beberapa menit.</span>';
            }, 5000);
        }, 1500);
    }
</script>
@endpush
