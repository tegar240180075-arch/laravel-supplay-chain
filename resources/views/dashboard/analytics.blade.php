@extends('layouts.app')

@section('title', 'Visualisasi Data Analitik')
@section('page_title', 'Visualisasi Data Analitik')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 glass-card text-center py-5">
        <h3 class="text-gradient mb-3">Mesin Analitik Lanjutan</h3>
        <p class="text-muted w-75 mx-auto">
            Pilih negara untuk menganalisis tren ekonomi (GDP &amp; Inflasi) berdasarkan data
            <strong>nyata multi-tahun dari World Bank API</strong> serta memantau riwayat skor risiko rantai pasok.
        </p>

        <div class="d-flex justify-content-center mt-4">
            <div class="input-group w-50">
                <select class="form-select bg-dark text-white border-secondary" id="analyticsCountry">
                    <option value="">Memuat negara...</option>
                </select>
                <button class="btn btn-primary" onclick="loadAnalytics()">Analisis Data</button>
            </div>
        </div>
    </div>

    <!-- GDP Trend -->
    <div class="col-12 col-md-6">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-3">
                <h5 class="mb-0">Tren GDP (Miliar USD)</h5>
                <span class="badge bg-info text-dark small" id="gdpSource" style="display:none">Sumber: World Bank</span>
            </div>
            <div style="height: 300px; position: relative;">
                <canvas id="gdpChart"></canvas>
                <div id="gdpNoData" class="text-center text-muted py-5 d-none">
                    <i class="fa-solid fa-database me-2"></i>Belum ada data GDP. Jalankan
                    <code>php artisan economic:fill</code>
                </div>
            </div>
        </div>
    </div>

    <!-- Inflation Trend -->
    <div class="col-12 col-md-6">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-3">
                <h5 class="mb-0">Tingkat Inflasi (%) — World Bank</h5>
                <span class="badge bg-danger small" id="infSource" style="display:none">Sumber: World Bank</span>
            </div>
            <div style="height: 300px; position: relative;">
                <canvas id="inflationChart"></canvas>
                <div id="infNoData" class="text-center text-muted py-5 d-none">
                    <i class="fa-solid fa-database me-2"></i>Belum ada data inflasi. Jalankan
                    <code>php artisan economic:fill</code>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk History -->
    <div class="col-12">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-3">
                <h5 class="mb-0">Riwayat Skor Risiko</h5>
                <button class="btn btn-sm btn-outline-warning" onclick="recalcRisk()">
                    <i class="fa-solid fa-calculator me-1"></i>Hitung Ulang Risiko
                </button>
            </div>
            <div style="height: 350px; position: relative;">
                <canvas id="riskHistoryChart"></canvas>
                <div id="riskNoData" class="text-center text-muted py-5 d-none">
                    <i class="fa-solid fa-chart-line me-2"></i>Belum ada riwayat risiko.
                    Klik "Hitung Ulang Risiko" untuk menghasilkan data pertama.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let gdpChart, inflationChart, riskChart;
    let currentCode = null;

    document.addEventListener('DOMContentLoaded', async function () {
        await populateCountries();
        loadAnalytics();
    });

    async function populateCountries() {
        const select = document.getElementById('analyticsCountry');
        try {
            const countries = await apiGet('countries');
            if (countries && countries.length > 0) {
                select.innerHTML = '';
                countries.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.code;
                    opt.textContent = c.name;
                    select.appendChild(opt);
                });
                // Default ke Indonesia jika ada
                if (countries.find(c => c.code === 'ID')) select.value = 'ID';
            }
        } catch (e) {
            console.error('Gagal memuat negara:', e);
        }
    }

    async function loadAnalytics() {
        const code = document.getElementById('analyticsCountry').value;
        if (!code) return;
        currentCode = code;

        showLoader();
        try {
            await Promise.all([
                loadEconomicCharts(code),
                loadRiskHistory(code),
            ]);
        } catch (e) {
            console.error(e);
        } finally {
            hideLoader();
        }
    }

    // ── Economic Charts (GDP & Inflation) — data NYATA dari World Bank ─────────
    async function loadEconomicCharts(code) {
        const econ = await apiGet(`countries/${code}/economic`);

        // Sort ascending by year
        const sorted = (econ || [])
            .filter(e => e.year)
            .sort((a, b) => parseInt(a.year) - parseInt(b.year));

        const hasGdp       = sorted.some(e => e.gdp_billions && parseFloat(e.gdp_billions) > 0);
        const hasInflation = sorted.some(e => e.inflation_rate !== null && e.inflation_rate !== undefined);

        // GDP Chart
        const gdpNoData = document.getElementById('gdpNoData');
        const gdpCanvas = document.getElementById('gdpChart');
        if (hasGdp) {
            gdpNoData.classList.add('d-none');
            gdpCanvas.style.display = '';
            document.getElementById('gdpSource').style.display = '';
            const years = sorted.map(e => e.year);
            const gdp   = sorted.map(e => parseFloat(e.gdp_billions || 0).toFixed(2));
            renderLineChart('gdpChart', years, gdp, 'GDP (Miliar USD)', '#00d4ff', 'gdpChart');
        } else {
            gdpNoData.classList.remove('d-none');
            gdpCanvas.style.display = 'none';
        }

        // Inflation Chart
        const infNoData = document.getElementById('infNoData');
        const infCanvas = document.getElementById('inflationChart');
        if (hasInflation) {
            infNoData.classList.add('d-none');
            infCanvas.style.display = '';
            document.getElementById('infSource').style.display = '';
            const years     = sorted.map(e => e.year);
            const inflation = sorted.map(e => parseFloat(e.inflation_rate || 0).toFixed(2));
            renderLineChart('inflationChart', years, inflation, 'Inflasi (%)', '#ef4444', 'inflationChart');
        } else {
            infNoData.classList.remove('d-none');
            infCanvas.style.display = 'none';
        }
    }

    // ── Risk History Chart — data dari tabel risk_score_histories ──────────────
    async function loadRiskHistory(code) {
        const hist = await apiGet(`risk/${code}/history`);
        const riskNoData  = document.getElementById('riskNoData');
        const riskCanvas  = document.getElementById('riskHistoryChart');

        if (hist && hist.length > 0) {
            riskNoData.classList.add('d-none');
            riskCanvas.style.display = '';

            hist.sort((a, b) => new Date(a.record_date) - new Date(b.record_date));

            const dates  = hist.map(h => {
                const d = new Date(h.record_date);
                return `${d.getDate()}/${d.getMonth() + 1}/${d.getFullYear()}`;
            });
            const scores = hist.map(h => parseFloat(h.total_score).toFixed(2));

            renderLineChart('riskHistoryChart', dates, scores, 'Skor Risiko Total', '#f59e0b', 'riskHistoryChart');
        } else {
            // Jika belum ada riwayat, tampilkan skor hari ini saja sebagai titik tunggal
            const currentRisk = await apiGet(`risk/${code}`);
            if (currentRisk && currentRisk.total_score !== undefined) {
                riskNoData.classList.add('d-none');
                riskCanvas.style.display = '';
                const today  = new Date();
                const label  = `${today.getDate()}/${today.getMonth()+1}/${today.getFullYear()}`;
                renderLineChart('riskHistoryChart', [label], [parseFloat(currentRisk.total_score).toFixed(2)],
                    'Skor Risiko Total', '#f59e0b', 'riskHistoryChart');
            } else {
                riskNoData.classList.remove('d-none');
                riskCanvas.style.display = 'none';
            }
        }
    }

    async function recalcRisk() {
        if (!currentCode) return;
        showLoader();
        await apiPost(`risk/${currentCode}/calculate`, {});
        await loadRiskHistory(currentCode);
        hideLoader();
    }

    // ── Shared Chart Renderer ──────────────────────────────────────────────────
    function renderLineChart(canvasId, labels, data, title, color, chartRef) {
        const ctx = document.getElementById(canvasId).getContext('2d');

        if (chartRef === 'gdpChart'         && gdpChart)        gdpChart.destroy();
        if (chartRef === 'inflationChart'   && inflationChart)  inflationChart.destroy();
        if (chartRef === 'riskHistoryChart' && riskChart)       riskChart.destroy();

        const newChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: title,
                    data: data,
                    borderColor: color,
                    backgroundColor: color + '22',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#0a0e27',
                    pointBorderColor: color,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    fill: true,
                    tension: 0.35,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                },
                plugins: {
                    legend: { labels: { color: '#e2e8f0' } },
                    tooltip: {
                        backgroundColor: '#111',
                        borderColor: color,
                        borderWidth: 1,
                        titleColor: '#fff',
                        bodyColor: '#ccc',
                    },
                },
            },
        });

        if (chartRef === 'gdpChart')         gdpChart       = newChart;
        if (chartRef === 'inflationChart')   inflationChart = newChart;
        if (chartRef === 'riskHistoryChart') riskChart      = newChart;
    }
</script>
@endpush
