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
                        
                        if (riskData) {
                            if (riskData.weather_risk > 70) color = '#ef4444'; // Red (Danger)
                            else if (riskData.weather_risk > 40) color = '#f59e0b'; // Yellow (Warning)
                        }
                        
                        const circle = L.circleMarker([country.lat, country.lng], {
                            color: color,
                            fillColor: color,
                            fillOpacity: 0.7,
                            radius: 8
                        }).addTo(map);
                        
                        circle.bindPopup(`
                            <div class="text-dark p-1">
                                <h6 class="mb-1 fw-bold">${country.name}</h6>
                                <div class="small">Risiko Cuaca: <strong>${riskData ? riskData.weather_risk : 'N/A'}</strong></div>
                                <a href="/country/${country.code}" class="btn btn-sm btn-primary mt-2 w-100">Lihat Detail</a>
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
