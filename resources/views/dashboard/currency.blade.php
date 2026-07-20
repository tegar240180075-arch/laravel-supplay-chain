@extends('layouts.app')

@section('title', 'Dampak Mata Uang')
@section('page_title', 'Dasbor Dampak Mata Uang')

@section('content')
<div class="row g-4 mb-4">
    {{-- Converter --}}
    <div class="col-12 col-md-4">
        <div class="glass-card h-100">
            <h5 class="border-bottom border-secondary pb-2 mb-3">💱 Kalkulator Konversi</h5>

            <div class="mb-3">
                <label class="form-label text-muted small">Jumlah</label>
                <input type="number" id="convAmount" class="form-control bg-dark text-white border-secondary" value="1000" min="0">
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label text-muted small">Dari</label>
                    <select id="convFrom" class="form-select bg-dark text-white border-secondary">
                        <option value="USD">Memuat...</option>
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label text-muted small">Ke</label>
                    <select id="convTo" class="form-select bg-dark text-white border-secondary">
                        <option value="IDR">Memuat...</option>
                    </select>
                </div>
            </div>

            <button class="btn btn-primary w-100 mb-4" onclick="convertCurrency()">
                <i class="fa-solid fa-calculator me-1"></i>Hitung
            </button>

            <div class="text-center p-3 border border-secondary rounded" style="background:rgba(255,255,255,0.03)">
                <div class="text-muted small mb-1" id="convResultLabel">Masukkan jumlah dan klik Hitung</div>
                <h3 class="text-gradient mb-0" id="convResultValue">—</h3>
            </div>

            <hr class="border-secondary my-3">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small">Grafik Riwayat:</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="loadHistoryChart()">
                    <i class="fa-solid fa-chart-line me-1"></i>Tampilkan
                </button>
            </div>
            <div style="height: 150px; position:relative; margin-top:12px">
                <canvas id="historyMiniChart"></canvas>
                <div id="historyEmpty" class="text-center text-muted small py-3">Klik tombol di atas untuk melihat riwayat kurs</div>
            </div>
        </div>
    </div>

    {{-- Live Rates Table --}}
    <div class="col-12 col-md-8">
        <div class="glass-card h-100">
            <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-3">
                <h5 class="mb-0">Nilai Tukar Langsung (Basis: USD)</h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success small" id="updateTime"></span>
                    <button class="btn btn-sm btn-outline-primary" onclick="loadRates()">
                        <i class="fa-solid fa-refresh"></i>
                    </button>
                </div>
            </div>

            <input type="text" id="rateSearch" class="form-control form-control-sm bg-dark text-white border-secondary mb-3"
                placeholder="Filter mata uang..." oninput="filterRates(this.value)">

            <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                <table class="table table-dark table-hover">
                    <thead class="sticky-top" style="background:#111">
                        <tr>
                            <th>Mata Uang</th>
                            <th>Nilai (per 1 USD)</th>
                            <th>Tren (vs Kemarin)</th>
                        </tr>
                    </thead>
                    <tbody id="ratesTableBody">
                        <tr><td colspan="3" class="text-center text-muted">Memuat nilai tukar...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let allRatesData = [];
    let historyChart = null;

    document.addEventListener('DOMContentLoaded', function () {
        loadRates();
    });

    async function loadRates() {
        showLoader();
        try {
            const [ratesData, countries] = await Promise.all([
                apiGet('currency/rates?base=USD'),
                apiGet('countries'),
            ]);

            const tbody = document.getElementById('ratesTableBody');

            if (!ratesData || !countries) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Gagal memuat data.</td></tr>';
                return;
            }

            // Build a map: currencyCode → rate object
            let ratesMap = {};
            ratesData.forEach(r => { ratesMap[r.target_currency] = r; });

            let addedCurrencies = new Set();
            let html       = '';
            let optionsHtml = '<option value="USD">USD - United States</option>';

            // Ensure USD as base if not returned
            if (!ratesMap['USD']) {
                ratesMap['USD'] = { target_currency: 'USD', rate: 1, trend: 'stable', trend_pct: 0 };
            }

            allRatesData = [];

            countries.forEach(country => {
                const code = country.currency_code;
                if (!code || !ratesMap[code] || addedCurrencies.has(code)) return;
                addedCurrencies.add(code);

                const r        = ratesMap[code];
                const trendHtml = getTrendHtml(r.trend, r.trend_pct);

                allRatesData.push({
                    code,
                    name:      country.name,
                    flagCode:  country.code.toLowerCase(),
                    rate:      r.rate,
                    trend:     r.trend,
                    trendPct:  r.trend_pct,
                    html: `
                    <tr class="rate-row" data-currency="${code}" data-country="${country.name.toLowerCase()}">
                        <td>
                            <img src="https://flagcdn.com/20x15/${country.code.toLowerCase()}.png" class="me-2 rounded">
                            <span class="fw-bold">${code}</span>
                            <div class="text-muted" style="font-size:0.75rem">${country.name}</div>
                        </td>
                        <td class="align-middle fw-bold">${parseFloat(r.rate).toLocaleString('id-ID', {maximumFractionDigits: 6})}</td>
                        <td class="align-middle">${trendHtml}</td>
                    </tr>
                    `
                });

                optionsHtml += `<option value="${code}">${code} — ${country.name}</option>`;
            });

            // Sort: unstable first, then alphabetical
            allRatesData.sort((a, b) => {
                const order = { down: 0, up: 1, stable: 2 };
                return (order[a.trend] - order[b.trend]) || a.code.localeCompare(b.code);
            });

            tbody.innerHTML = allRatesData.map(r => r.html).join('');

            document.getElementById('convFrom').innerHTML = optionsHtml;
            document.getElementById('convTo').innerHTML   = optionsHtml;
            if (document.getElementById('convFrom').querySelector('option[value="USD"]'))
                document.getElementById('convFrom').value = 'USD';
            if (document.getElementById('convTo').querySelector('option[value="IDR"]'))
                document.getElementById('convTo').value = 'IDR';

            // Update timestamp
            document.getElementById('updateTime').innerText = 'Diperbarui: ' + new Date().toLocaleTimeString('id-ID');

        } catch (e) {
            console.error(e);
        } finally {
            hideLoader();
        }
    }

    function getTrendHtml(trend, pct) {
        if (trend === 'up') {
            return `<span class="text-success fw-bold"><i class="fa-solid fa-arrow-trend-up me-1"></i>Naik ${pct > 0 ? '+' : ''}${pct}%</span>`;
        } else if (trend === 'down') {
            return `<span class="text-danger fw-bold"><i class="fa-solid fa-arrow-trend-down me-1"></i>Turun ${pct}%</span>`;
        }
        return `<span class="text-muted"><i class="fa-solid fa-minus me-1"></i>Stabil</span>`;
    }

    function filterRates(query) {
        const q = query.toLowerCase();
        document.querySelectorAll('.rate-row').forEach(row => {
            const currency = row.getAttribute('data-currency').toLowerCase();
            const country  = row.getAttribute('data-country').toLowerCase();
            row.style.display = (currency.includes(q) || country.includes(q)) ? '' : 'none';
        });
    }

    async function convertCurrency() {
        const from   = document.getElementById('convFrom').value;
        const to     = document.getElementById('convTo').value;
        const amount = parseFloat(document.getElementById('convAmount').value);

        if (!amount || amount <= 0) {
            alert('Masukkan jumlah yang valid.');
            return;
        }

        showLoader();
        try {
            const result = await apiGet(`currency/convert?from=${from}&to=${to}&amount=${amount}`);
            if (result && result.converted_amount !== undefined) {
                document.getElementById('convResultLabel').innerText = `${amount.toLocaleString()} ${from} =`;
                document.getElementById('convResultValue').innerText =
                    parseFloat(result.converted_amount).toLocaleString('id-ID', { maximumFractionDigits: 4 }) + ' ' + to;
            }
        } catch (e) {
            console.error(e);
        } finally {
            hideLoader();
        }
    }

    async function loadHistoryChart() {
        const from   = document.getElementById('convFrom').value;
        const to     = document.getElementById('convTo').value;

        document.getElementById('historyEmpty').style.display = 'none';

        const history = await apiGet(`currency/history?base=${from}&target=${to}&days=30`);

        if (!history || history.length === 0) {
            document.getElementById('historyEmpty').style.display = 'block';
            document.getElementById('historyEmpty').innerText = `Belum ada riwayat untuk ${from}→${to}. Jalankan: php artisan dashboard:init`;
            return;
        }

        const labels = history.map(h => h.record_date);
        const rates  = history.map(h => parseFloat(h.rate));

        const ctx = document.getElementById('historyMiniChart').getContext('2d');
        if (historyChart) historyChart.destroy();

        historyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: `${from} → ${to}`,
                    data: rates,
                    borderColor: '#00d4ff',
                    backgroundColor: 'rgba(0,212,255,0.1)',
                    borderWidth: 2,
                    pointRadius: 2,
                    fill: true,
                    tension: 0.3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#666', maxTicksLimit: 6 }, grid: { color: 'rgba(255,255,255,0.03)' } },
                    y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                },
            },
        });
    }
</script>
@endpush
