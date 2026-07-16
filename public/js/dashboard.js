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
        if (!response.ok) throw new Error('API Error');
        return await response.json();
    } catch (error) {
        console.error('API GET Error:', error);
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
        searchMenu.innerHTML = '';
        countries.forEach(country => {
            searchMenu.innerHTML += `
                <li><a class="dropdown-item" href="/country/${country.code}">
                    <img src="https://flagcdn.com/20x15/${country.code.toLowerCase()}.png" class="me-2"> 
                    ${country.name}
                </a></li>
            `;
        });
    }
}
