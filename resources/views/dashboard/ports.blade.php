@extends('layouts.app')

@section('title', 'Peta Lokasi Pelabuhan')
@section('page_title', 'Peta Lokasi Pelabuhan')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 col-md-4">
        <div class="glass-card h-100">
            <h5 class="border-bottom border-secondary pb-2 mb-3">Cari Pelabuhan</h5>
            <div class="mb-3">
                <input type="text" id="portSearch" class="form-control bg-dark text-white border-secondary" placeholder="Cari berdasarkan nama...">
            </div>
            <button class="btn btn-primary w-100 mb-4" onclick="searchPorts()">Cari</button>
            
            <div id="portList" style="max-height: 350px; overflow-y: auto;">
                <div class="text-muted small text-center">Masukkan kata kunci atau klik penanda di peta.</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-8">
        <div class="glass-card h-100 p-0 overflow-hidden">
            <div id="portsMap" class="map-container w-100" style="height: 600px; border: none; border-radius: 1rem;"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let map;
    let markers = [];

    document.addEventListener('DOMContentLoaded', function() {
        initMap();
    });

    async function initMap() {
        map = L.map('portsMap').setView([20, 0], 2);
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '...',
            subdomains: 'abcd',
            maxZoom: 18
        }).addTo(map);

        showLoader();
        try {
            const ports = await apiGet('ports');
            if (ports) {
                renderPorts(ports);
            }
        } catch (e) {
            console.error(e);
        } finally {
            hideLoader();
        }
    }
    
    function renderPorts(ports) {
        markers.forEach(m => map.removeLayer(m));
        markers = [];
        
        const portIcon = L.divIcon({
            html: '<i class="fa-solid fa-anchor text-info fa-lg"></i>',
            className: 'custom-div-icon',
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });

        ports.forEach(port => {
            if (port.lat && port.lng) {
                const marker = L.marker([port.lat, port.lng], {icon: portIcon}).addTo(map);
                marker.bindPopup(`
                    <div class="p-1">
                        <h6 class="mb-1 fw-bold">${port.name}</h6>
                        <div class="small mb-1">Negara: ${port.country ? port.country.name : 'Tidak diketahui'}</div>
                        <div class="small mb-1">Kota: <span class="port-city" data-lat="${port.lat}" data-lng="${port.lng}">Mencari lokasi...</span></div>
                        <div class="small mb-1">Tipe: ${port.type || 'N/A'}</div>
                        <div class="small">Ukuran: <span class="badge bg-secondary">${port.size || 'N/A'}</span></div>
                    </div>
                `);
                markers.push(marker);
            }
        });

        // Load city dynamically when popup opens to save API requests
        map.on('popupopen', async function(e) {
            const popupNode = e.popup._contentNode;
            if (!popupNode) return;
            
            const cityEl = popupNode.querySelector('.port-city');
            if (cityEl && cityEl.innerText === 'Mencari lokasi...') {
                try {
                    const lat = cityEl.getAttribute('data-lat');
                    const lng = cityEl.getAttribute('data-lng');
                    
                    // Call OpenStreetMap Nominatim for reverse geocoding
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=10&accept-language=id`);
                    const data = await response.json();
                    
                    // Try to get the most specific location name
                    const address = data.address;
                    let city = 'Tidak diketahui';
                    
                    if (address) {
                        city = address.city || address.town || address.municipality || address.village || address.county || address.state || 'Tidak diketahui';
                    }
                    
                    cityEl.innerText = city;
                } catch (err) {
                    console.error('Geocoding error:', err);
                    cityEl.innerText = 'Tidak ditemukan';
                }
            }
        });
    }
    
    async function searchPorts() {
        const q = document.getElementById('portSearch').value;
        if (!q) return;
        
        showLoader();
        try {
            const ports = await apiGet(`ports/search?q=${encodeURIComponent(q)}`);
            const list = document.getElementById('portList');
            list.innerHTML = '';
            
            if (ports && ports.length > 0) {
                renderPorts(ports);
                
                ports.forEach(port => {
                    list.innerHTML += `
                        <div class="p-2 border-bottom border-secondary" style="cursor:pointer;" onclick="focusPort(${port.lat}, ${port.lng})">
                            <div class="fw-bold text-info"><i class="fa-solid fa-anchor me-2"></i>${port.name}</div>
                            <div class="small text-muted">${port.country ? port.country.name : ''} - ${port.size || ''}</div>
                        </div>
                    `;
                });
                
                if (ports[0].lat && ports[0].lng) {
                    focusPort(ports[0].lat, ports[0].lng);
                }
            } else {
                list.innerHTML = '<div class="text-muted small text-center mt-3">Tidak ada pelabuhan ditemukan.</div>';
            }
        } catch (e) {
            console.error(e);
        } finally {
            hideLoader();
        }
    }
    
    function focusPort(lat, lng) {
        map.setView([lat, lng], 8);
    }
</script>
<style>
    .custom-div-icon {
        background: rgba(10, 14, 39, 0.8);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--info);
    }
</style>
@endpush
