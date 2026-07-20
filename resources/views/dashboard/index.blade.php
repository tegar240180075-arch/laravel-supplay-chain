@extends('layouts.app')

@section('title', 'Ringkasan Global')
@section('page_title', 'Ringkasan Global')

@section('content')
<div class="row g-4 mb-4">
    <!-- Summary Cards -->
    <div class="col-12 col-md-6 col-xl-3">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-muted mb-0">Negara Dipantau</h6>
                <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary">
                    <i class="fa-solid fa-earth-americas"></i>
                </div>
            </div>
            <h2 class="mb-0" id="totalCountries">-</h2>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-muted mb-0">Wilayah Risiko Kritis</h6>
                <div class="rounded-circle bg-danger bg-opacity-10 p-2 text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <h2 class="mb-0 text-danger" id="criticalRisks">-</h2>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-muted mb-0">Total Pelabuhan</h6>
                <div class="rounded-circle bg-info bg-opacity-10 p-2 text-info">
                    <i class="fa-solid fa-anchor"></i>
                </div>
            </div>
            <h2 class="mb-0" id="totalPorts">-</h2>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-muted mb-0">Berita Dianalisis</h6>
                <div class="rounded-circle bg-success bg-opacity-10 p-2 text-success">
                    <i class="fa-regular fa-newspaper"></i>
                </div>
            </div>
            <h2 class="mb-0" id="totalNews">-</h2>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-8">
        <div class="glass-card h-100">
            <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-3 mb-3">
                <h5 class="mb-0">Matriks Risiko Global</h5>
                <button class="btn btn-sm btn-outline-primary" onclick="loadRiskMatrix()"><i class="fa-solid fa-rotate-right"></i> Segarkan</button>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Negara</th>
                            <th>Total Risiko</th>
                            <th>Cuaca</th>
                            <th>Inflasi</th>
                            <th>Sentimen Berita</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="riskMatrixBody">
                        <tr><td colspan="6" class="text-center text-muted">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-lg-4">
        <div class="glass-card h-100">
            <h5 class="border-bottom border-secondary pb-3 mb-3">Distribusi Risiko</h5>
            <div style="height: 300px; position: relative;">
                <canvas id="riskPieChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let riskChart = null;

    document.addEventListener('DOMContentLoaded', function() {
        loadDashboardData();
    });

    async function loadDashboardData() {
        showLoader();
        
        try {
            const risks = await apiGet('risk');
            document.getElementById('totalCountries').innerText = (risks && risks.length) ? risks.length : 0;
            
            const ports = await apiGet('ports');
            document.getElementById('totalPorts').innerText = (ports && ports.length) ? ports.length : 0;
            
            const news = await apiGet('news');
            document.getElementById('totalNews').innerText = (news && news.length) ? news.length : 0;

            await loadRiskMatrix(risks);
            
        } catch (error) {
            console.error('Dashboard load error:', error);
        } finally {
            hideLoader();
        }
    }

    async function loadRiskMatrix(risksData = null) {
        let risks = risksData;
        if (!risks) {
            risks = await apiGet('risk');
            // Jika dipanggil dari tombol refresh, update juga angkanya
            document.getElementById('totalCountries').innerText = (risks && risks.length) ? risks.length : 0;
        }
        const tbody = document.getElementById('riskMatrixBody');
        tbody.innerHTML = '';
        
        if (!risks || risks.length === 0) {
            document.getElementById('criticalRisks').innerText = 0;
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-warning py-4">
                        <i class="fa-solid fa-database me-2"></i>
                        Belum ada data risiko. Jalankan: <code>php artisan dashboard:init</code>
                    </td>
                </tr>`;
            renderRiskChart({ Critical: 0, High: 0, Medium: 0, Low: 0 });
            return;
        }

        let criticalCount = 0;
        let dist = { Critical: 0, High: 0, Medium: 0, Low: 0 };

        risks.sort((a, b) => b.total_score - a.total_score);
        risks = risks.slice(0, 100); // Batasi hanya menampilkan maksimal 100 negara

        risks.forEach(risk => {
            if (risk.risk_level === 'Critical') criticalCount++;
            if (dist[risk.risk_level] !== undefined) dist[risk.risk_level]++;

            let levelLabel = risk.risk_level;
            if (levelLabel === 'Critical') levelLabel = 'Kritis';
            if (levelLabel === 'High') levelLabel = 'Tinggi';
            if (levelLabel === 'Medium') levelLabel = 'Sedang';
            if (levelLabel === 'Low') levelLabel = 'Rendah';

            tbody.innerHTML += `
                <tr>
                    <td>
                        <img src="https://flagcdn.com/20x15/${risk.country.code.toLowerCase()}.png" class="me-2 rounded">
                        <span class="fw-bold">${risk.country.name}</span>
                    </td>
                    <td>
                        <span class="risk-badge ${getRiskBadgeClass(risk.risk_level)}">
                            ${getRiskIcon(risk.risk_level)} ${risk.total_score}
                        </span>
                    </td>
                    <td>${risk.weather_risk}</td>
                    <td>${risk.inflation_risk}</td>
                    <td>${risk.news_risk}</td>
                    <td>
                        <a href="/country/${risk.country.code}" class="btn btn-sm btn-outline-light"><i class="fa-solid fa-arrow-right"></i></a>
                    </td>
                </tr>
            `;
        });

        document.getElementById('criticalRisks').innerText = criticalCount;
        renderRiskChart(dist);
    }

    function renderRiskChart(dist) {
        const ctx = document.getElementById('riskPieChart').getContext('2d');
        
        if (riskChart) riskChart.destroy();
        
        riskChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Kritis', 'Tinggi', 'Sedang', 'Rendah'],
                datasets: [{
                    data: [dist.Critical, dist.High, dist.Medium, dist.Low],
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)'
                    ],
                    borderColor: 'rgba(10, 14, 39, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#e2e8f0' } }
                }
            }
        });
    }
</script>
@endpush
