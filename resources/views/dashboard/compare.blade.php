@extends('layouts.app')

@section('title', 'Perbandingan Negara')
@section('page_title', 'Mesin Perbandingan Negara')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="glass-card">
            <div class="row align-items-end">
                <div class="col-md-5">
                    <label class="form-label text-muted">Negara A</label>
                    <select class="form-select bg-dark text-white border-secondary" id="compare1">
                        <option value="US">Amerika Serikat</option>
                        <option value="DE">Jerman</option>
                        <option value="CN">Tiongkok</option>
                    </select>
                </div>
                <div class="col-md-2 text-center py-2">
                    <div class="badge bg-secondary rounded-circle p-2 fs-5">VS</div>
                </div>
                <div class="col-md-5">
                    <label class="form-label text-muted">Negara B</label>
                    <select class="form-select bg-dark text-white border-secondary" id="compare2">
                        <option value="CN" selected>Tiongkok</option>
                        <option value="ID">Indonesia</option>
                        <option value="AU">Australia</option>
                    </select>
                </div>
                <div class="col-12 mt-3 text-center">
                    <button class="btn btn-primary px-5" onclick="compareCountries()"><i class="fa-solid fa-code-compare me-2"></i> Bandingkan Risiko</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4 d-none" id="comparisonResults">
    <div class="col-12 col-md-6 text-center">
        <div class="glass-card">
            <img id="flag1" src="" class="rounded mb-3" width="60">
            <h4 id="name1" class="mb-3">-</h4>
            <div class="display-4 fw-bold mb-2" id="score1">-</div>
            <div class="text-muted small mb-4">Total Skor Risiko</div>
            
            <div class="text-start">
                <div class="d-flex justify-content-between border-bottom border-secondary pb-2 mb-2">
                    <span class="text-muted">GDP</span>
                    <span class="fw-bold" id="gdp1">-</span>
                </div>
                <div class="d-flex justify-content-between border-bottom border-secondary pb-2 mb-2">
                    <span class="text-muted">Inflasi</span>
                    <span class="fw-bold" id="inf1">-</span>
                </div>
                <div class="d-flex justify-content-between border-bottom border-secondary pb-2 mb-2">
                    <span class="text-muted">Risiko Cuaca</span>
                    <span class="fw-bold" id="wr1">-</span>
                </div>
                <div class="d-flex justify-content-between border-bottom border-secondary pb-2 mb-2">
                    <span class="text-muted">Sentimen Berita</span>
                    <span class="fw-bold" id="ns1">-</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-6 text-center">
        <div class="glass-card" style="background: rgba(30, 38, 77, 0.4);">
            <img id="flag2" src="" class="rounded mb-3" width="60">
            <h4 id="name2" class="mb-3">-</h4>
            <div class="display-4 fw-bold mb-2" id="score2">-</div>
            <div class="text-muted small mb-4">Total Skor Risiko</div>
            
            <div class="text-start">
                <div class="d-flex justify-content-between border-bottom border-secondary pb-2 mb-2">
                    <span class="text-muted">GDP</span>
                    <span class="fw-bold" id="gdp2">-</span>
                </div>
                <div class="d-flex justify-content-between border-bottom border-secondary pb-2 mb-2">
                    <span class="text-muted">Inflasi</span>
                    <span class="fw-bold" id="inf2">-</span>
                </div>
                <div class="d-flex justify-content-between border-bottom border-secondary pb-2 mb-2">
                    <span class="text-muted">Risiko Cuaca</span>
                    <span class="fw-bold" id="wr2">-</span>
                </div>
                <div class="d-flex justify-content-between border-bottom border-secondary pb-2 mb-2">
                    <span class="text-muted">Sentimen Berita</span>
                    <span class="fw-bold" id="ns2">-</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12">
        <div class="glass-card">
            <h5 class="border-bottom border-secondary pb-2 mb-3">Perbandingan Radar Risiko</h5>
            <div style="height: 400px; position: relative;">
                <canvas id="radarChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let radarChart = null;

    async function compareCountries() {
        const c1 = document.getElementById('compare1').value;
        const c2 = document.getElementById('compare2').value;
        
        if (c1 === c2) {
            alert('Silakan pilih dua negara yang berbeda untuk dibandingkan.');
            return;
        }
        
        showLoader();
        try {
            const results = await apiGet(`risk/compare?countries=${c1},${c2}`);
            
            if (results && results.length >= 2) {
                document.getElementById('comparisonResults').classList.remove('d-none');
                
                const data1 = results.find(r => r.country.code === c1);
                const data2 = results.find(r => r.country.code === c2);
                
                const econ1 = await apiGet(`countries/${c1}/economic`);
                const econ2 = await apiGet(`countries/${c2}/economic`);
                
                document.getElementById('flag1').src = `https://flagcdn.com/80x60/${c1.toLowerCase()}.png`;
                document.getElementById('name1').innerText = data1.country.name;
                document.getElementById('score1').innerText = data1.risk ? data1.risk.total_score : 'N/A';
                document.getElementById('wr1').innerText = data1.risk ? data1.risk.weather_risk : 'N/A';
                document.getElementById('ns1').innerText = data1.risk ? data1.risk.news_risk : 'N/A';
                if(econ1 && econ1.length) {
                    document.getElementById('gdp1').innerText = `$${parseFloat(econ1[0].gdp_billions).toFixed(2)}M`;
                    document.getElementById('inf1').innerText = `${parseFloat(econ1[0].inflation_rate).toFixed(2)}%`;
                }
                
                document.getElementById('flag2').src = `https://flagcdn.com/80x60/${c2.toLowerCase()}.png`;
                document.getElementById('name2').innerText = data2.country.name;
                document.getElementById('score2').innerText = data2.risk ? data2.risk.total_score : 'N/A';
                document.getElementById('wr2').innerText = data2.risk ? data2.risk.weather_risk : 'N/A';
                document.getElementById('ns2').innerText = data2.risk ? data2.risk.news_risk : 'N/A';
                if(econ2 && econ2.length) {
                    document.getElementById('gdp2').innerText = `$${parseFloat(econ2[0].gdp_billions).toFixed(2)}M`;
                    document.getElementById('inf2').innerText = `${parseFloat(econ2[0].inflation_rate).toFixed(2)}%`;
                }
                
                renderRadar(data1, data2);
            }
        } catch (e) {
            console.error(e);
        } finally {
            hideLoader();
        }
    }
    
    function renderRadar(d1, d2) {
        const ctx = document.getElementById('radarChart').getContext('2d');
        if (radarChart) radarChart.destroy();
        
        radarChart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: ['Cuaca', 'Inflasi', 'Berita/Politik', 'Mata Uang', 'Total'],
                datasets: [
                    {
                        label: d1.country.name,
                        data: [
                            d1.risk ? d1.risk.weather_risk : 0, 
                            d1.risk ? d1.risk.inflation_risk : 0, 
                            d1.risk ? d1.risk.news_risk : 0, 
                            d1.risk ? d1.risk.currency_risk : 0,
                            d1.risk ? d1.risk.total_score : 0
                        ],
                        backgroundColor: 'rgba(0, 212, 255, 0.2)',
                        borderColor: '#00d4ff',
                        pointBackgroundColor: '#00d4ff',
                        pointBorderColor: '#fff',
                    },
                    {
                        label: d2.country.name,
                        data: [
                            d2.risk ? d2.risk.weather_risk : 0, 
                            d2.risk ? d2.risk.inflation_risk : 0, 
                            d2.risk ? d2.risk.news_risk : 0, 
                            d2.risk ? d2.risk.currency_risk : 0,
                            d2.risk ? d2.risk.total_score : 0
                        ],
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        borderColor: '#ef4444',
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#fff',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { color: 'rgba(255,255,255,0.1)' },
                        grid: { color: 'rgba(255,255,255,0.1)' },
                        pointLabels: { color: '#e2e8f0', font: { size: 14 } },
                        ticks: { display: false, min: 0, max: 100 }
                    }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#fff' } }
                }
            }
        });
    }
</script>
@endpush
