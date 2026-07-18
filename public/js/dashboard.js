document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('show');
        });
    }
    
    // Global loader helper
    window.showLoader = () => document.getElementById('globalLoader').classList.add('active');
    window.hideLoader = () => document.getElementById('globalLoader').classList.remove('active');
    
    // Load countries for global search
    loadGlobalCountries();
});

// Helper for Fetch API
async function apiGet(endpoint) {
    try {
        const response = await fetch(`/api/${endpoint}`);
        if (!response.ok) {
            console.warn(`API returned ${response.status} for ${endpoint}`);
            return null;
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API GET Error:', endpoint, error);
        return null;
    }
}

async function apiPost(endpoint, data) {
    try {
        const response = await fetch(`/api/${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        });
        return await response.json();
    } catch (error) {
        console.error('API POST Error:', error);
        return null;
    }
}

function getRiskBadgeClass(level) {
    if (level === 'Critical') return 'risk-critical';
    if (level === 'High') return 'risk-high';
    if (level === 'Medium') return 'risk-medium';
    return 'risk-low';
}

function getRiskIcon(level) {
    if (level === 'Critical') return '<i class="fa-solid fa-triangle-exclamation"></i>';
    if (level === 'High') return '<i class="fa-solid fa-circle-exclamation"></i>';
    if (level === 'Medium') return '<i class="fa-solid fa-circle-info"></i>';
    return '<i class="fa-solid fa-check-circle"></i>';
}

async function loadGlobalCountries() {
    const searchMenu = document.getElementById('globalCountrySearch');
    if (!searchMenu) return;
    
    const countries = await apiGet('countries');
    if (countries && countries.length > 0) {
        // Keep the search input, clear the rest
        const searchInput = document.getElementById('countrySearchInput');
        searchMenu.innerHTML = '';
        
        // Re-add search input
        const searchLi = document.createElement('li');
        searchLi.className = 'px-2 pb-2 sticky-top';
        searchLi.style.background = '#1a1a1a';
        searchLi.innerHTML = '<input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" id="countrySearchInput" placeholder="Ketik nama negara..." autocomplete="off">';
        searchMenu.appendChild(searchLi);
        
        countries.forEach(country => {
            const li = document.createElement('li');
            li.className = 'country-item';
            li.setAttribute('data-name', country.name.toLowerCase());
            li.innerHTML = `<a class="dropdown-item" href="/country/${country.code}">
                <img src="https://flagcdn.com/20x15/${country.code.toLowerCase()}.png" class="me-2"> 
                ${country.name}
            </a>`;
            searchMenu.appendChild(li);
        });
        
        // Add search/filter functionality
        const input = document.getElementById('countrySearchInput');
        if (input) {
            input.addEventListener('keyup', function(e) {
                e.stopPropagation(); // Prevent Bootstrap from closing dropdown
                const query = this.value.toLowerCase();
                document.querySelectorAll('#globalCountrySearch .country-item').forEach(item => {
                    const name = item.getAttribute('data-name');
                    item.style.display = name.includes(query) ? '' : 'none';
                });
            });
            
            // Prevent dropdown from closing when clicking on input
            input.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    } else {
        searchMenu.innerHTML = '<li><span class="dropdown-item text-warning"><i class="fa-solid fa-database me-2"></i>Belum ada data. Jalankan: php artisan dashboard:init</span></li>';
    }
}
