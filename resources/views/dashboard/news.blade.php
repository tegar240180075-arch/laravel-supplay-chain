@extends('layouts.app')

@section('title', 'Intelijen Berita')
@section('page_title', 'Intelijen Berita')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 col-lg-8">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-3 mb-3">
                <h5 class="mb-0">Berita Rantai Pasok Global</h5>
                <div class="d-flex">
                    <input type="text" id="newsSearch" class="form-control form-control-sm bg-dark text-white border-secondary me-2" placeholder="Cari berita...">
                    <button class="btn btn-sm btn-primary" onclick="searchNews()"><i class="fa-solid fa-search"></i></button>
                </div>
            </div>
            
            <div id="globalNewsContainer">
                <div class="text-center py-5 text-muted"><i class="fa-solid fa-spinner fa-spin fa-2x mb-3"></i><br>Memuat feed berita cerdas...</div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-lg-4">
        <div class="glass-card mb-4">
            <h5 class="border-bottom border-secondary pb-2 mb-3">Distribusi Sentimen</h5>
            <div style="height: 250px; position: relative;">
                <canvas id="sentimentChart"></canvas>
            </div>
        </div>
        
        <div class="glass-card">
            <h5 class="border-bottom border-secondary pb-2 mb-3">Statistik Mesin Leksikon</h5>
            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom border-secondary" style="border-bottom-color: rgba(255,255,255,0.05)!important">
                <span class="text-muted">Kata Kamus Positif</span>
                <span class="badge bg-success">100+</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted">Kata Kamus Negatif</span>
                <span class="badge bg-danger">100+</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let sentimentChart = null;

    document.addEventListener('DOMContentLoaded', function() {
        loadNews();
    });

    async function loadNews(query = '') {
        const container = document.getElementById('globalNewsContainer');
        showLoader();
        
        try {
            let endpoint = 'news';
            if (query) {
                endpoint = `news/search?q=${encodeURIComponent(query)}`;
            }
            
            const news = await apiGet(endpoint);
            container.innerHTML = '';
            
            let pos = 0, neg = 0, neu = 0;
            
            if (news && news.length > 0) {
                news.forEach(item => {
                    let sentBadge = '';
                    let borderClass = 'border-secondary';
                    
                    if(item.sentiment) {
                        if (item.sentiment.sentiment_label === 'Positive') {
                            pos++;
                            sentBadge = `<span class="badge bg-success float-end"><i class="fa-regular fa-face-smile me-1"></i> Positif (+${item.sentiment.positive_score})</span>`;
                            borderClass = 'border-success border-opacity-50';
                        } else if (item.sentiment.sentiment_label === 'Negative') {
                            neg++;
                            sentBadge = `<span class="badge bg-danger float-end"><i class="fa-regular fa-face-frown me-1"></i> Negatif (-${item.sentiment.negative_score})</span>`;
                            borderClass = 'border-danger border-opacity-50';
                        } else {
                            neu++;
                            sentBadge = `<span class="badge bg-secondary float-end">Netral</span>`;
                        }
                    }
                    
                    const countryBadge = item.country ? `<span class="badge bg-primary text-white me-2">${item.country.name}</span>` : '';
                    
                    container.innerHTML += `
                        <div class="p-3 mb-3 border ${borderClass} rounded" style="background: rgba(255,255,255,0.02); transition: all 0.2s;">
                            ${sentBadge}
                            <h5 class="mb-2"><a href="${item.url}" target="_blank" class="text-white text-decoration-none hover-primary">${item.title}</a></h5>
                            <div class="small text-muted mb-2">
                                ${countryBadge}
                                <span><i class="fa-solid fa-building me-1"></i> ${item.source_name}</span> &bull; 
                                <span><i class="fa-regular fa-clock me-1"></i> ${new Date(item.published_at).toLocaleString()}</span>
                            </div>
                            <p class="mb-0 text-light opacity-75">${item.description || 'Tidak ada deskripsi yang diberikan.'}</p>
                        </div>
                    `;
                });
                
                renderChart(pos, neg, neu);
            } else {
                container.innerHTML = '<div class="text-center py-5 text-muted">Tidak ada artikel berita ditemukan.</div>';
            }
        } catch (e) {
            console.error(e);
        } finally {
            hideLoader();
        }
    }
    
    function searchNews() {
        const q = document.getElementById('newsSearch').value;
        loadNews(q);
    }
    
    function renderChart(pos, neg, neu) {
        const ctx = document.getElementById('sentimentChart').getContext('2d');
        if (sentimentChart) sentimentChart.destroy();
        
        sentimentChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Positif', 'Negatif', 'Netral'],
                datasets: [{
                    data: [pos, neg, neu],
                    backgroundColor: ['#10b981', '#ef4444', '#64748b'],
                    borderWidth: 0
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
<style>
    .hover-primary:hover { color: var(--primary) !important; }
</style>
@endpush
