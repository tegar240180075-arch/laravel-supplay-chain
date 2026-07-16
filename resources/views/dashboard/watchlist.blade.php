@extends('layouts.app')

@section('title', 'Daftar Pantauan Saya')
@section('page_title', 'Daftar Pantauan Saya')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-3 mb-3">
                <h5 class="mb-0"><i class="fa-solid fa-star text-warning me-2"></i> Negara Dipantau</h5>
                <div class="d-flex">
                    <select class="form-select form-select-sm bg-dark text-white border-secondary me-2" id="addWatchlistCode">
                        <!-- Populated by JS -->
                    </select>
                    <button class="btn btn-sm btn-primary text-nowrap" onclick="addToWatchlist()"><i class="fa-solid fa-plus me-1"></i> Tambah</button>
                </div>
            </div>
            
            <div class="row g-4" id="watchlistContainer">
                <div class="col-12 text-center text-muted py-5">Memuat daftar pantauan...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadWatchlist();
        populateCountriesDropdown();
    });

    async function populateCountriesDropdown() {
        const countries = await apiGet('countries');
        if (countries) {
            const select = document.getElementById('addWatchlistCode');
            select.innerHTML = '';
            countries.forEach(c => {
                select.innerHTML += `<option value="${c.code}">${c.name}</option>`;
            });
        }
    }

    async function loadWatchlist() {
        showLoader();
        const container = document.getElementById('watchlistContainer');
        
        try {
            const list = await apiGet('watchlist');
            container.innerHTML = '';
            
            if (list && list.length > 0) {
                for (const item of list) {
                    const risk = await apiGet(`risk/${item.country.code}`);
                    
                    let levelLabel = risk ? risk.risk_level : 'Tidak diketahui';
                    if (levelLabel === 'Critical') levelLabel = 'Kritis';
                    if (levelLabel === 'High') levelLabel = 'Tinggi';
                    if (levelLabel === 'Medium') levelLabel = 'Sedang';
                    if (levelLabel === 'Low') levelLabel = 'Rendah';

                    container.innerHTML += `
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="p-3 border border-secondary rounded" style="background: rgba(255,255,255,0.02)">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center">
                                        <img src="https://flagcdn.com/32x24/${item.country.code.toLowerCase()}.png" class="me-2 rounded">
                                        <h5 class="mb-0">${item.country.name}</h5>
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger" onclick="removeWatchlist(${item.id})"><i class="fa-solid fa-trash"></i></button>
                                </div>
                                
                                <div class="mb-3 text-center p-2 rounded" style="background: rgba(0,0,0,0.2)">
                                    <div class="small text-muted mb-1">Skor Risiko Saat Ini</div>
                                    <div class="fs-4 fw-bold ${risk ? 'text-' + getRiskColorName(risk.risk_level) : 'text-muted'}">${risk ? risk.total_score : 'N/A'}</div>
                                    <span class="badge ${risk ? getRiskBadgeClass(risk.risk_level) : 'bg-secondary'}">${levelLabel}</span>
                                </div>
                                
                                <a href="/country/${item.country.code}" class="btn btn-sm btn-outline-info w-100">Lihat Dasbor Penuh</a>
                            </div>
                        </div>
                    `;
                }
            } else {
                container.innerHTML = '<div class="col-12 text-center text-muted py-5"><i class="fa-regular fa-folder-open fa-3x mb-3"></i><br>Daftar pantauan Anda kosong.</div>';
            }
        } catch (e) {
            console.error(e);
        } finally {
            hideLoader();
        }
    }
    
    async function addToWatchlist() {
        const code = document.getElementById('addWatchlistCode').value;
        if (!code) return;
        
        showLoader();
        await apiPost('watchlist', { country_code: code });
        hideLoader();
        await loadWatchlist();
    }
    
    async function removeWatchlist(id) {
        if (!confirm('Hapus dari daftar pantauan?')) return;
        
        showLoader();
        try {
            await fetch(`/api/watchlist/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
            });
            await loadWatchlist();
        } catch (e) {
            console.error(e);
            hideLoader();
        }
    }
    
    function getRiskColorName(level) {
        if (level === 'Critical') return 'danger';
        if (level === 'High') return 'warning';
        if (level === 'Medium') return 'info';
        return 'success';
    }
</script>
@endpush
