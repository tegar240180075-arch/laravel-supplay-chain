@extends('layouts.app')

@section('title', 'Peringkat Ekonomi Global')
@section('page_title', 'Peringkat Ekonomi Global')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="glass-card">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom border-secondary pb-3 mb-4 gap-3">
                <div>
                    <h5 class="mb-1"><i class="fa-solid fa-ranking-star text-primary me-2"></i> Peringkat Ekonomi Global</h5>
                    <p class="text-muted small mb-0">Daftar peringkat negara berdasarkan GDP (Miliar USD) dan Tingkat Inflasi (%) dari data terbaru.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" id="rankingSearch" placeholder="Cari negara..." style="width: 200px;">
                    @if(auth()->user()->isAdmin())
                        <button class="btn btn-sm btn-outline-warning text-nowrap" id="btnSync" onclick="syncEconomicData()">
                            <i class="fa-solid fa-sync me-1"></i> Sinkronisasi Data
                        </button>
                    @endif
                </div>
            </div>

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs border-secondary mb-4" id="rankingTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active text-white border-0 bg-transparent pb-3" id="gdp-tab" data-bs-toggle="tab" data-bs-target="#gdp-pane" type="button" role="tab" aria-selected="true" onclick="changeTab('gdp')">
                        <i class="fa-solid fa-coins me-2 text-info"></i>Peringkat GDP
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-white border-0 bg-transparent pb-3" id="inflation-tab" data-bs-toggle="tab" data-bs-target="#inflation-pane" type="button" role="tab" aria-selected="false" onclick="changeTab('inflation')">
                        <i class="fa-solid fa-circle-nodes me-2 text-danger"></i>Peringkat Inflasi
                    </button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="rankingTabsContent">
                <!-- GDP Pane -->
                <div class="tab-pane fade show active" id="gdp-pane" role="tabpanel" aria-labelledby="gdp-tab">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Peringkat</th>
                                    <th>Negara</th>
                                    <th class="text-end">GDP (Miliar USD)</th>
                                    <th class="text-center">Tahun</th>
                                    <th class="text-center" style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="gdpRankingBody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">Memuat data peringkat GDP...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Inflation Pane -->
                <div class="tab-pane fade" id="inflation-pane" role="tabpanel" aria-labelledby="inflation-tab">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Peringkat</th>
                                    <th>Negara</th>
                                    <th class="text-end">Tingkat Inflasi (%)</th>
                                    <th class="text-center">Kategori</th>
                                    <th class="text-center">Tahun</th>
                                    <th class="text-center" style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="inflationRankingBody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">Memuat data peringkat inflasi...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .nav-tabs .nav-link {
        position: relative;
        font-weight: 500;
        opacity: 0.7;
        transition: all 0.3s ease;
    }
    .nav-tabs .nav-link.active {
        opacity: 1;
        border-bottom: 2px solid var(--bs-primary) !important;
    }
    .nav-tabs .nav-link:hover {
        opacity: 1;
        border-bottom: 2px solid rgba(255,255,255,0.2) !important;
    }
    .trophy-1st { color: #ffd700; text-shadow: 0 0 10px rgba(255, 215, 0, 0.5); }
    .trophy-2nd { color: #c0c0c0; text-shadow: 0 0 10px rgba(192, 192, 192, 0.5); }
    .trophy-3nd { color: #cd7f32; text-shadow: 0 0 10px rgba(205, 127, 50, 0.5); }
    
    .ranking-row {
        transition: background-color 0.2s ease;
    }
    .ranking-row:hover {
        background-color: rgba(255, 255, 255, 0.04) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    let rawEconomicData = [];
    let currentActiveTab = 'gdp';

    document.addEventListener('DOMContentLoaded', function() {
        fetchRankingData();

        // Setup live search filter
        document.getElementById('rankingSearch').addEventListener('input', function() {
            renderTables();
        });
    });

    async function fetchRankingData() {
        showLoader();
        try {
            const data = await apiGet('countries/ranking');
            if (data) {
                rawEconomicData = data;
                renderTables();
            } else {
                showEmptyStates('Gagal mengambil data dari server.');
            }
        } catch (error) {
            console.error('Error fetching rankings:', error);
            showEmptyStates('Terjadi kesalahan saat memuat data peringkat.');
        } finally {
            hideLoader();
        }
    }

    function renderTables() {
        const searchQuery = document.getElementById('rankingSearch').value.toLowerCase();
        
        // Filter based on search input
        const filteredData = rawEconomicData.filter(item => {
            return item.country.name.toLowerCase().includes(searchQuery) ||
                   item.country.code.toLowerCase().includes(searchQuery);
        });

        // 1. Render GDP Ranking Table
        const gdpSorted = [...filteredData]
            .filter(item => item.gdp_billions !== null && item.gdp_billions > 0)
            .sort((a, b) => b.gdp_billions - a.gdp_billions);

        const gdpTbody = document.getElementById('gdpRankingBody');
        gdpTbody.innerHTML = '';

        if (gdpSorted.length === 0) {
            gdpTbody.innerHTML = `<tr><td colspan="5" class="text-center text-warning py-5"><i class="fa-solid fa-circle-info me-2"></i>Tidak ada data peringkat GDP yang sesuai.</td></tr>`;
        } else {
            gdpSorted.forEach((item, index) => {
                const rank = index + 1;
                let rankDisplay = rank;
                if (rank === 1) rankDisplay = `<i class="fa-solid fa-trophy trophy-1st fs-5"></i>`;
                else if (rank === 2) rankDisplay = `<i class="fa-solid fa-trophy trophy-2nd fs-5"></i>`;
                else if (rank === 3) rankDisplay = `<i class="fa-solid fa-trophy trophy-3nd fs-5"></i>`;

                gdpTbody.innerHTML += `
                    <tr class="ranking-row">
                        <td class="text-center fw-bold">${rankDisplay}</td>
                        <td>
                            <img src="https://flagcdn.com/20x15/${item.country.code.toLowerCase()}.png" class="me-2 rounded shadow-sm">
                            <span class="fw-semibold">${item.country.name}</span>
                        </td>
                        <td class="text-end fw-bold text-info">$ ${formatCurrency(item.gdp_billions)} Miliar</td>
                        <td class="text-center text-muted">${item.year}</td>
                        <td class="text-center">
                            <a href="/country/${item.country.code}" class="btn btn-sm btn-outline-light"><i class="fa-solid fa-arrow-right"></i></a>
                        </td>
                    </tr>
                `;
            });
        }

        // 2. Render Inflation Ranking Table
        const inflationSorted = [...filteredData]
            .filter(item => item.inflation_rate !== null)
            .sort((a, b) => b.inflation_rate - a.inflation_rate);

        const infTbody = document.getElementById('inflationRankingBody');
        infTbody.innerHTML = '';

        if (inflationSorted.length === 0) {
            infTbody.innerHTML = `<tr><td colspan="6" class="text-center text-warning py-5"><i class="fa-solid fa-circle-info me-2"></i>Tidak ada data peringkat inflasi yang sesuai.</td></tr>`;
        } else {
            inflationSorted.forEach((item, index) => {
                const rank = index + 1;
                let rankDisplay = rank;
                if (rank === 1) rankDisplay = `<i class="fa-solid fa-fire text-danger fs-5"></i>`;

                const badge = getInflationBadge(item.inflation_rate);

                infTbody.innerHTML += `
                    <tr class="ranking-row">
                        <td class="text-center fw-bold">${rankDisplay}</td>
                        <td>
                            <img src="https://flagcdn.com/20x15/${item.country.code.toLowerCase()}.png" class="me-2 rounded shadow-sm">
                            <span class="fw-semibold">${item.country.name}</span>
                        </td>
                        <td class="text-end fw-bold">${item.inflation_rate.toFixed(2)} %</td>
                        <td class="text-center">${badge}</td>
                        <td class="text-center text-muted">${item.year}</td>
                        <td class="text-center">
                            <a href="/country/${item.country.code}" class="btn btn-sm btn-outline-light"><i class="fa-solid fa-arrow-right"></i></a>
                        </td>
                    </tr>
                `;
            });
        }
    }

    function changeTab(tabName) {
        currentActiveTab = tabName;
    }

    function formatCurrency(val) {
        return parseFloat(val).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function getInflationBadge(rate) {
        if (rate > 10) return `<span class="badge bg-danger text-white px-3 py-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Hiperinflasi / Kritis</span>`;
        if (rate > 5) return `<span class="badge bg-warning text-dark px-3 py-2"><i class="fa-solid fa-circle-exclamation me-1"></i> Tinggi</span>`;
        if (rate >= 0 && rate <= 3) return `<span class="badge bg-success text-white px-3 py-2"><i class="fa-solid fa-check-circle me-1"></i> Stabil / Sehat</span>`;
        if (rate > 3 && rate <= 5) return `<span class="badge bg-info text-dark px-3 py-2"><i class="fa-solid fa-circle-info me-1"></i> Moderat</span>`;
        return `<span class="badge bg-secondary text-white px-3 py-2"><i class="fa-solid fa-snowflake me-1"></i> Deflasi / Risiko</span>`;
    }

    function showEmptyStates(msg) {
        document.getElementById('gdpRankingBody').innerHTML = `<tr><td colspan="5" class="text-center text-danger py-5">${msg}</td></tr>`;
        document.getElementById('inflationRankingBody').innerHTML = `<tr><td colspan="6" class="text-center text-danger py-5">${msg}</td></tr>`;
    }

    async function syncEconomicData() {
        if (!confirm('Apakah Anda ingin memicu pengisian ulang data ekonomi untuk semua negara? Proses ini dapat memakan waktu beberapa saat.')) return;
        
        showLoader();
        const btn = document.getElementById('btnSync');
        if (btn) btn.disabled = true;

        try {
            const res = await apiPost('admin/engine/run', {});
            alert('Proses pembaruan berhasil dipicu. Silakan segarkan halaman dalam beberapa detik.');
            await fetchRankingData();
        } catch (error) {
            console.error(error);
            alert('Gagal memicu sinkronisasi data.');
        } finally {
            if (btn) btn.disabled = false;
            hideLoader();
        }
    }
</script>
@endpush
