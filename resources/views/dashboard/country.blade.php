@extends('layouts.app')

@section('title', 'Detail Negara')
@section('page_title', 'Dasbor Negara')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 col-xl-4">
        <div class="glass-card h-100 text-center pb-4">
            <img src="https://flagcdn.com/80x60/{{ strtolower($code) }}.png" class="rounded shadow mb-3 mt-2" alt="Flag">
            <h3 id="countryName">Memuat...</h3>
            <p class="text-muted" id="countryRegion">-</p>
            
            <div class="d-inline-block mt-2 mb-4">
                <span class="risk-badge" id="countryRiskBadge">
                    <i class="fa-solid fa-spinner fa-spin"></i> Menghitung Risiko...
                </span>
            </div>
            
            <div class="row text-start mt-2">
                <div class="col-6 mb-3">
                    <div class="small text-muted"><i class="fa-solid fa-coins me-1"></i> Mata Uang</div>
                    <div class="fw-bold fs-5" id="countryCurrency">-</div>
                </div>
                <div class="col-6 mb-3">
                    <div class="small text-muted"><i class="fa-solid fa-users me-1"></i> Populasi</div>
                    <div class="fw-bold fs-5" id="countryPopulation">-</div>
                </div>
                <div class="col-6 mb-3">
                    <div class="small text-muted"><i class="fa-solid fa-money-bill-wave me-1"></i> GDP</div>
                    <div class="fw-bold fs-5" id="countryGDP">-</div>
                </div>
                <div class="col-6 mb-3">
                    <div class="small text-muted"><i class="fa-solid fa-arrow-trend-up me-1"></i> Inflasi</div>
                    <div class="fw-bold fs-5" id="countryInflation">-</div>
                </div>
            </div>
            
            <button class="btn btn-primary w-100 mt-3" onclick="calculateRisk()"><i class="fa-solid fa-calculator me-2"></i> Hitung Ulang Risiko</button>
        </div>
    </div>
    
    <div class="col-12 col-xl-8">
        <div class="row g-4 h-100">
            <!-- Weather -->
            <div class="col-12 col-md-6">
                <div class="glass-card h-100 d-flex flex-column">
                    <h5 class="border-bottom border-secondary pb-2 mb-3"><i class="fa-solid fa-cloud me-2 text-info"></i> Cuaca & Prakiraan</h5>
                    
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <div class="display-4 fw-bold" id="weatherTemp">-</div>
                            <div class="text-muted fs-5" id="weatherCondition">Memuat...</div>
                        </div>
                        <div class="text-end">
                            <div class="fs-4 text-primary"><i class="fa-solid fa-wind"></i></div>
                            <div class="fw-bold mt-1" id="weatherWind">-</div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="small text-muted mb-2"><i class="fa-solid fa-calendar-days me-1"></i> Prakiraan 7 Hari Kedepan</div>
                        <div class="d-flex overflow-auto pb-2 gap-2" id="forecastContainer" style="scrollbar-width: thin;">
                            <div class="text-muted small">Memuat prakiraan...</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Risk Breakdown -->
            <div class="col-12 col-md-6">
                <div class="glass-card h-100">
                    <h5 class="border-bottom border-secondary pb-2 mb-3"><i class="fa-solid fa-chart-bar me-2 text-warning"></i> Rincian Risiko</h5>
                    
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Risiko Cuaca (30%)</span>
                            <span id="riskW">-</span>
                        </div>
                        <div class="progress" style="height: 8px; background: rgba(255,255,255,0.1);">
                            <div class="progress-bar bg-info" id="barW" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Risiko Inflasi (20%)</span>
                            <span id="riskI">-</span>
                        </div>
                        <div class="progress" style="height: 8px; background: rgba(255,255,255,0.1);">
                            <div class="progress-bar bg-danger" id="barI" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Risiko Sentimen Berita (40%)</span>
                            <span id="riskN">-</span>
                        </div>
                        <div class="progress" style="height: 8px; background: rgba(255,255,255,0.1);">
                            <div class="progress-bar bg-warning" id="barN" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Risiko Mata Uang (10%)</span>
                            <span id="riskC">-</span>
                        </div>
                        <div class="progress" style="height: 8px; background: rgba(255,255,255,0.1);">
                            <div class="progress-bar bg-success" id="barC" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- News -->
            <div class="col-12">
                <div class="glass-card">
                    <h5 class="border-bottom border-secondary pb-2 mb-3"><i class="fa-regular fa-newspaper me-2 text-primary"></i> Berita Logistik & Perdagangan Terkini</h5>
                    <div id="newsContainer" class="row g-3">
                        <div class="col-12 text-center text-muted py-3">Memuat berita...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const code = '{{ $code }}';

    document.addEventListener('DOMContentLoaded', function() {
        loadCountryData();
    });

    async function loadCountryData() {
        showLoader();
        
        try {
            // 1. Basic Info
            const country = await apiGet(`countries/${code}`);
            if (country) {
                document.getElementById('countryName').innerText = country.name;
                document.getElementById('countryRegion').innerText = country.region;
                document.getElementById('countryCurrency').innerText = country.currency_code;
            }
            
            // 2. Economic Data
            const econ = await apiGet(`countries/${code}/economic`);
            if (econ && econ.length > 0) {
                const latest = econ[0];
                document.getElementById('countryPopulation').innerText = latest.population ? formatNumber(latest.population) : 'Belum tersedia';
                
                const gdp = parseFloat(latest.gdp_billions);
                document.getElementById('countryGDP').innerText = !isNaN(gdp) && gdp > 0 ? `$${gdp.toFixed(2)} Miliar` : 'Belum tersedia';
                
                const inf = parseFloat(latest.inflation_rate);
                document.getElementById('countryInflation').innerText = !isNaN(inf) ? `${inf.toFixed(2)}%` : 'Belum tersedia';
            } else {
                document.getElementById('countryPopulation').innerText = 'Belum tersedia';
                document.getElementById('countryGDP').innerText = 'Belum tersedia';
                document.getElementById('countryInflation').innerText = 'Belum tersedia';
            }
            
            // 3. Weather & Forecast
            const weatherRes = await apiGet(`weather/forecast/${code}`);
            if (weatherRes) {
                // Current Weather
                if (weatherRes.current) {
                    document.getElementById('weatherTemp').innerText = `${weatherRes.current.temperature}°C`;
                    document.getElementById('weatherCondition').innerText = weatherRes.current.condition;
                    document.getElementById('weatherWind').innerText = `${weatherRes.current.wind_speed} km/j`;
                }

                // 7-day Forecast
                const forecastContainer = document.getElementById('forecastContainer');
                if (weatherRes.forecast && weatherRes.forecast.length > 0) {
                    forecastContainer.innerHTML = '';
                    weatherRes.forecast.forEach(day => {
                        const dateObj = new Date(day.date);
                        const dayName = dateObj.toLocaleDateString('id-ID', { weekday: 'short' });
                        
                        let icon = 'fa-cloud';
                        if (day.condition === 'Clear') icon = 'fa-sun text-warning';
                        else if (day.condition === 'Rain' || day.condition === 'Rain Showers') icon = 'fa-cloud-rain text-info';
                        else if (day.condition === 'Storm') icon = 'fa-cloud-bolt text-danger';

                        forecastContainer.innerHTML += `
                            <div class="text-center p-2 rounded border border-secondary" style="min-width: 70px; background: rgba(255,255,255,0.02)">
                                <div class="small fw-bold mb-1">${dayName}</div>
                                <i class="fa-solid ${icon} mb-1 fs-5"></i>
                                <div class="small">${Math.round(day.temp_max)}°</div>
                            </div>
                        `;
                    });
                } else {
                    forecastContainer.innerHTML = '<div class="text-muted small">Prakiraan tidak tersedia.</div>';
                }
            }
            
            // 4. Risk Score
            await updateRiskUI();
            
            // 5. News
            const news = await apiGet(`news/country/${code}`);
            const newsContainer = document.getElementById('newsContainer');
            if (news && news.length > 0) {
                newsContainer.innerHTML = '';
                news.forEach(item => {
                    let sentBadge = '';
                    if(item.sentiment) {
                        let badgeClass = item.sentiment.sentiment_label === 'Positive' ? 'bg-success' : (item.sentiment.sentiment_label === 'Negative' ? 'bg-danger' : 'bg-secondary');
                        let labelText = item.sentiment.sentiment_label === 'Positive' ? 'Positif' : (item.sentiment.sentiment_label === 'Negative' ? 'Negatif' : 'Netral');
                        sentBadge = `<span class="badge ${badgeClass} float-end">${labelText}</span>`;
                    }
                    
                    newsContainer.innerHTML += `
                        <div class="col-12 col-md-6">
                            <div class="p-3 border border-secondary rounded" style="background: rgba(255,255,255,0.02)">
                                ${sentBadge}
                                <h6 class="text-truncate mb-1"><a href="${item.url}" target="_blank" class="text-white text-decoration-none">${item.title}</a></h6>
                                <div class="small text-muted mb-2">${item.source_name} &bull; ${new Date(item.published_at).toLocaleDateString()}</div>
                                <p class="small text-muted mb-0 text-truncate">${item.description || 'Tidak ada deskripsi.'}</p>
                            </div>
                        </div>
                    `;
                });
            } else {
                newsContainer.innerHTML = '<div class="col-12 text-center text-muted py-3">Tidak ada berita terbaru.</div>';
            }
            
        } catch (error) {
            console.error(error);
        } finally {
            hideLoader();
        }
    }
    
    async function updateRiskUI() {
        const risk = await apiGet(`risk/${code}`);
        if (risk) {
            const badge = document.getElementById('countryRiskBadge');
            badge.className = `risk-badge ${getRiskBadgeClass(risk.risk_level)}`;
            
            let levelLabel = risk.risk_level;
            if (levelLabel === 'Critical') levelLabel = 'Kritis';
            if (levelLabel === 'High') levelLabel = 'Tinggi';
            if (levelLabel === 'Medium') levelLabel = 'Sedang';
            if (levelLabel === 'Low') levelLabel = 'Rendah';

            badge.innerHTML = `${getRiskIcon(risk.risk_level)} ${risk.total_score} - Risiko ${levelLabel}`;
            
            const wRisk = parseFloat(risk.weather_risk) || 0;
            document.getElementById('riskW').innerText = risk.weather_risk !== undefined ? risk.weather_risk : '0.00';
            document.getElementById('barW').style.width = Math.max(wRisk, 2) + '%';
            
            const iRisk = parseFloat(risk.inflation_risk) || 0;
            document.getElementById('riskI').innerText = risk.inflation_risk !== undefined ? risk.inflation_risk : '0.00';
            document.getElementById('barI').style.width = Math.max(iRisk, 2) + '%';
            
            const nRisk = parseFloat(risk.news_risk) || 0;
            document.getElementById('riskN').innerText = risk.news_risk !== undefined ? risk.news_risk : '0.00';
            document.getElementById('barN').style.width = Math.max(nRisk, 2) + '%';
            
            const cRisk = parseFloat(risk.currency_risk) || 0;
            document.getElementById('riskC').innerText = risk.currency_risk !== undefined ? risk.currency_risk : '0.00';
            document.getElementById('barC').style.width = Math.max(cRisk, 2) + '%';
        }
    }

    async function calculateRisk() {
        showLoader();
        await apiPost(`risk/${code}/calculate`, {});
        await updateRiskUI();
        hideLoader();
    }
    
    function formatNumber(num) {
        if (!num) return '-';
        if (num >= 1000000000) return (num / 1000000000).toFixed(1) + 'M';
        if (num >= 1000000) return (num / 1000000).toFixed(1) + 'Jt';
        return num.toLocaleString();
    }
</script>
@endpush
