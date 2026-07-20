@extends('layouts.app')

@section('title', 'Pantauan Cuaca Global')
@section('page_title', 'Pantauan Cuaca Global')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fa-solid fa-cloud-bolt text-info me-2"></i> Pemantauan Cuaca Langsung</h5>
                <div class="d-flex gap-2">
                    <span class="badge bg-success">Aman</span>
                    <span class="badge bg-warning text-dark">Peringatan</span>
                    <span class="badge bg-danger">Badai/Risiko Tinggi</span>
                </div>
            </div>
            
            <div id="weatherMap" class="map-container w-100"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
    });

    async function initMap() {
        const map = L.map('weatherMap').setView([20, 0], 2);
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        showLoader();
        
        try {
            const countries = await apiGet('countries');
            
            if (countries) {
                const risks = await apiGet('risk');
                const riskMap = {};
                if(risks) {
                    risks.forEach(r => { riskMap[r.country_id] = r; });
                }
                
                countries.forEach(country => {
                    if (country.lat && country.lng) {
                        let color = '#10b981'; // Green (Good)
                        let riskData = riskMap[country.id];
                        
                        // Jangan tampilkan negara yang belum memiliki data analisis
                        if (!riskData) return;

                        if (riskData.weather_risk > 70) color = '#ef4444'; // Red (Danger)
                        else if (riskData.weather_risk > 40) color = '#f59e0b'; // Yellow (Warning)
                        
                        const circle = L.circleMarker([country.lat, country.lng], {
                            color: color,
                            fillColor: color,
                            fillOpacity: 0.7,
                            radius: 8
                        }).addTo(map);
                        
                        let badgeColor = 'secondary';
                        let totalScore = 'N/A';
                        if (riskData) {
                            totalScore = riskData.total_score;
                            if (riskData.risk_level === 'Critical') badgeColor = 'danger';
                            else if (riskData.risk_level === 'High') badgeColor = 'warning text-dark';
                            else if (riskData.risk_level === 'Medium') badgeColor = 'info text-dark';
                            else if (riskData.risk_level === 'Low') badgeColor = 'success';
                        }
                        
                        circle.bindPopup(`
                            <div class="p-1" style="min-width: 150px;">
                                <div class="d-flex align-items-center border-bottom pb-2 mb-2">
                                    <img src="https://flagcdn.com/20x15/${country.code.toLowerCase()}.png" class="me-2 border" alt="${country.name}">
                                    <h6 class="mb-0 fw-bold">${country.name}</h6>
                                </div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Total Risiko:</span>
                                    <span class="badge bg-${badgeColor}">${totalScore}</span>
                                </div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Cuaca:</span>
                                    <strong>${riskData ? riskData.weather_risk : '-'}</strong>
                                </div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Inflasi:</span>
                                    <strong>${riskData ? riskData.inflation_risk : '-'}</strong>
                                </div>
                                <div class="d-flex justify-content-between small mb-2">
                                    <span class="text-muted">Sentimen Berita:</span>
                                    <strong>${riskData ? riskData.news_risk : '-'}</strong>
                                </div>
                                <a href="/country/${country.code}" class="btn btn-sm btn-primary w-100">Analisis Penuh</a>
                            </div>
                        `);
                    }
                });
            }
        } catch (e) {
            console.error(e);
        } finally {
            hideLoader();
        }
    }
</script>
@endpush
