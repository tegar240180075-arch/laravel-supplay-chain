<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Intelijen Risiko Rantai Pasok Global | @yield('title', 'Dasbor')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Icons (FontAwesome) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Leaflet.js CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    
    @stack('styles')
</head>
<body>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="globalLoader">
        <div class="spinner"></div>
        <h5 class="mt-3 text-primary">Memproses Data...</h5>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="p-4 text-center border-bottom border-secondary mb-3">
            <h4 class="text-gradient mb-0"><i class="fa-solid fa-earth-americas me-2"></i>SC-Risk Intel</h4>
            <p class="text-muted small mt-1 mb-0">Rantai Pasok Global</p>
        </div>
        
        <a href="{{ route('dashboard.index') }}" class="sidebar-link {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-line"></i> Ringkasan Global
        </a>
        
        <div class="text-muted small fw-bold px-4 mt-4 mb-2">INTELIJEN</div>
        
        <a href="{{ route('dashboard.weather') }}" class="sidebar-link {{ request()->routeIs('dashboard.weather') ? 'active' : '' }}">
            <i class="fa-solid fa-cloud-bolt"></i> Pantauan Cuaca
        </a>
        <a href="{{ route('dashboard.currency') }}" class="sidebar-link {{ request()->routeIs('dashboard.currency') ? 'active' : '' }}">
            <i class="fa-solid fa-money-bill-trend-up"></i> Dampak Mata Uang
        </a>
        <a href="{{ route('dashboard.news') }}" class="sidebar-link {{ request()->routeIs('dashboard.news') ? 'active' : '' }}">
            <i class="fa-regular fa-newspaper"></i> Sentimen Berita
        </a>
        <a href="{{ route('dashboard.ports') }}" class="sidebar-link {{ request()->routeIs('dashboard.ports') ? 'active' : '' }}">
            <i class="fa-solid fa-anchor"></i> Lokasi Pelabuhan
        </a>
        
        <div class="text-muted small fw-bold px-4 mt-4 mb-2">ANALISIS</div>
        
        <a href="{{ route('dashboard.analytics') }}" class="sidebar-link {{ request()->routeIs('dashboard.analytics') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-pie"></i> Visualisasi Data
        </a>
        <a href="{{ route('dashboard.compare') }}" class="sidebar-link {{ request()->routeIs('dashboard.compare') ? 'active' : '' }}">
            <i class="fa-solid fa-code-compare"></i> Bandingkan Negara
        </a>
        <a href="{{ route('dashboard.watchlist') }}" class="sidebar-link {{ request()->routeIs('dashboard.watchlist') ? 'active' : '' }}">
            <i class="fa-solid fa-star"></i> Daftar Pantauan
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar d-flex justify-content-between align-items-center rounded-3 mb-4">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light d-lg-none me-3" id="sidebarToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h5 class="mb-0 text-white">@yield('page_title', 'Dasbor')</h5>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-dark dropdown-toggle glass-card py-1 px-3" type="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-magnifying-glass me-2"></i> Cari Negara
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark" id="globalCountrySearch">
                        <!-- Populated by JS -->
                        <li><span class="dropdown-item text-muted">Memuat...</span></li>
                    </ul>
                </div>
                
                <div class="text-end ms-3 border-start border-secondary ps-3">
                    <div class="fw-bold text-white">Admin User</div>
                    <div class="small text-muted">Admin Sistem</div>
                </div>
                <img src="https://ui-avatars.com/api/?name=Admin+User&background=0D8ABC&color=fff" class="rounded-circle" width="40" height="40" alt="User">
            </div>
        </nav>

        <!-- Page Content -->
        @yield('content')
        
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.css"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script src="{{ asset('js/dashboard.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
