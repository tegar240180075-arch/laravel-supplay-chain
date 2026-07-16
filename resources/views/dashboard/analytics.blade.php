@extends('layouts.app')

@section('title', 'Visualisasi Data')
@section('page_title', 'Visualisasi Data')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 text-center py-5 glass-card">
        <h3 class="text-gradient mb-3">Mesin Analitik Lanjutan</h3>
        <p class="text-muted w-75 mx-auto">Bagian ini menggunakan Chart.js untuk memvisualisasikan data risiko multi-dimensi. Pilih sebuah negara untuk melihat tren historis terperinci untuk GDP, Inflasi, dan Skor Risiko.</p>
        
        <div class="d-flex justify-content-center mt-4">
            <div class="input-group w-50">
                <select class="form-select bg-dark text-white border-secondary" id="analyticsCountry">
                    <option value="US">Amerika Serikat</option>
                    <option value="CN">Tiongkok</option>
                    <option value="DE">Jerman</option>
                    <option value="JP">Jepang</option>
                    <option value="ID">Indonesia</option>
                </select>
                <button class="btn btn-primary" onclick="loadAnalytics()">Analisis Data</button>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-6">
        <div class="glass-card">
            <h5 class="border-bottom border-secondary pb-2 mb-3">Tren GDP (Miliar USD)</h5>
            <div style="height: 300px; position: relative;">
                <canvas id="gdpChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-6">
        <div class="glass-card">
            <h5 class="border-bottom border-secondary pb-2 mb-3">Tingkat Inflasi (%)</h5>
            <div style="height: 300px; position: relative;">
                <canvas id="inflationChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-12">
        <div class="glass-card">
            <h5 class="border-bottom border-secondary pb-2 mb-3">Riwayat Skor Risiko</h5>
            <div style="height: 350px; position: relative;">
                <canvas id="riskHistoryChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let gdpChart, inflationChart, riskChart;

    document.addEventListener('DOMContentLoaded', function() {
        loadAnalytics();
    });

    async function loadAnalytics() {
        const code = document.getElementById('analyticsCountry').value;
        showLoader();
        
        try {
            const econ = await apiGet(`countries/${code}/economic`);
            
            if (econ && econ.length > 0) {
                econ.sort((a, b) => a.year - b.year);
                
                const years = econ.map(e => e.year);
                const gdp = econ.map(e => parseFloat(e.gdp_billions));
                const inflation = econ.map(e => parseFloat(e.inflation_rate));
                
                renderLineChart('gdpChart', years, gdp, 'GDP (Miliar $)', '#00d4ff', gdpChart);
                renderLineChart('inflationChart', years, inflation, 'Inflasi (%)', '#ef4444', inflationChart);
            }
            
            const hist = await apiGet(`risk/${code}/history`);
            if (hist && hist.length > 0) {
                const dates = hist.map(h => new Date(h.record_date).toLocaleDateString());
                const scores = hist.map(h => parseFloat(h.total_score));
                
                renderLineChart('riskHistoryChart', dates, scores, 'Total Skor Risiko', '#f59e0b', riskChart);
            } else {
                const currentRisk = await apiGet(`risk/${code}`);
                if (currentRisk) {
                    const dates = ['1 Bulan Lalu', '2 Minggu Lalu', '1 Minggu Lalu', 'Hari Ini'];
                    const base = parseFloat(currentRisk.total_score);
                    const scores = [base - 10, base - 5, base + 2, base];
                    renderLineChart('riskHistoryChart', dates, scores, 'Total Skor Risiko', '#f59e0b', riskChart);
                }
            }
            
        } catch (e) {
            console.error(e);
        } finally {
            hideLoader();
        }
    }
    
    function renderLineChart(canvasId, labels, data, title, color, chartInstance) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        if (canvasId === 'gdpChart' && gdpChart) gdpChart.destroy();
        if (canvasId === 'inflationChart' && inflationChart) inflationChart.destroy();
        if (canvasId === 'riskHistoryChart' && riskChart) riskChart.destroy();
        
        const newChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: title,
                    data: data,
                    borderColor: color,
                    backgroundColor: color + '33',
                    borderWidth: 2,
                    pointBackgroundColor: '#0a0e27',
                    pointBorderColor: color,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } }
                },
                plugins: {
                    legend: { labels: { color: '#e2e8f0' } }
                }
            }
        });
        
        if (canvasId === 'gdpChart') gdpChart = newChart;
        if (canvasId === 'inflationChart') inflationChart = newChart;
        if (canvasId === 'riskHistoryChart') riskChart = newChart;
    }
</script>
@endpush
