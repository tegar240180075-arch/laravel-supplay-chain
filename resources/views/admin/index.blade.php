@extends('layouts.app')

@section('title', 'Administrasi Sistem')
@section('page_title', 'Administrasi Sistem')

@section('content')

{{-- System Stats --}}
<div class="row g-4 mb-4" id="adminStats">
    <div class="col-6 col-md-3">
        <div class="glass-card text-center py-3">
            <div class="text-primary fs-1 fw-bold" id="statUsers">-</div>
            <div class="text-muted small">Pengguna</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="glass-card text-center py-3">
            <div class="text-success fs-1 fw-bold" id="statPorts">-</div>
            <div class="text-muted small">Pelabuhan</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="glass-card text-center py-3">
            <div class="text-info fs-1 fw-bold" id="statRisk">-</div>
            <div class="text-muted small">Risk Scores</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="glass-card text-center py-3">
            <div class="text-warning fs-1 fw-bold" id="statNews">-</div>
            <div class="text-muted small">Berita Cache</div>
        </div>
    </div>
</div>

{{-- System Actions --}}
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="glass-card">
            <h5 class="border-bottom border-secondary pb-3 mb-3">⚙️ Aksi Sistem</h5>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="p-3 border border-secondary rounded" style="background:rgba(0,212,255,0.05)">
                        <h6 class="text-info"><i class="fa-solid fa-rotate me-2"></i>Perbarui Risk Engine</h6>
                        <p class="text-muted small mb-3">Hitung ulang skor risiko semua negara menggunakan data cuaca, inflasi, dan sentimen berita terbaru.</p>
                        <button class="btn btn-primary w-100" onclick="runEngine()" id="btnEngine">
                            <i class="fa-solid fa-play me-2"></i>Jalankan Risk Engine
                        </button>
                        <div class="mt-2 small d-none" id="engineStatus"></div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 border border-secondary rounded" style="background:rgba(245,158,11,0.05)">
                        <h6 class="text-warning"><i class="fa-solid fa-money-bill-trend-up me-2"></i>Perbarui Kurs Mata Uang</h6>
                        <p class="text-muted small mb-3">Ambil kurs terbaru dari ExchangeRate API dan simpan riwayat hari ini untuk semua mata uang.</p>
                        <button class="btn btn-warning text-dark w-100" onclick="refreshRates()" id="btnRates">
                            <i class="fa-solid fa-refresh me-2"></i>Perbarui Kurs
                        </button>
                        <div class="mt-2 small d-none" id="ratesStatus"></div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 border border-secondary rounded" style="background:rgba(239,68,68,0.05)">
                        <h6 class="text-danger"><i class="fa-solid fa-trash-can me-2"></i>Bersihkan Cache Lama</h6>
                        <p class="text-muted small mb-3">Hapus berita >30 hari, data cuaca >1 hari, dan kurs >2 hari untuk menghemat ruang database.</p>
                        <button class="btn btn-outline-danger w-100" onclick="clearCache()" id="btnCache">
                            <i class="fa-solid fa-broom me-2"></i>Bersihkan Cache
                        </button>
                        <div class="mt-2 small d-none" id="cacheStatus"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabs: Users / Ports / Articles --}}
<div class="glass-card mb-4">
    <ul class="nav nav-tabs border-secondary mb-4" id="adminTabs">
        <li class="nav-item">
            <a class="nav-link active text-white" id="tabUsers" href="#" onclick="showTab('users',this)">
                <i class="fa-solid fa-users me-1"></i>Pengguna
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-muted" id="tabPorts" href="#" onclick="showTab('ports',this)">
                <i class="fa-solid fa-anchor me-1"></i>Pelabuhan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-muted" id="tabArticles" href="#" onclick="showTab('articles',this)">
                <i class="fa-solid fa-file-pen me-1"></i>Artikel
            </a>
        </li>
    </ul>

    {{-- Users Panel --}}
    <div id="panelUsers">
        <div class="table-responsive">
            <table class="table table-dark table-hover">
                <thead><tr><th>ID</th><th>Nama</th><th>Email</th><th>Bergabung</th><th>Aksi</th></tr></thead>
                <tbody id="usersBody"><tr><td colspan="5" class="text-center text-muted">Memuat...</td></tr></tbody>
            </table>
        </div>
    </div>

    {{-- Ports Panel --}}
    <div id="panelPorts" class="d-none">
        <div class="d-flex justify-content-between mb-3">
            <h6 class="text-muted">Manajemen Pelabuhan</h6>
            <button class="btn btn-sm btn-success" onclick="showAddPortForm()">
                <i class="fa-solid fa-plus me-1"></i>Tambah Pelabuhan
            </button>
        </div>

        {{-- Add Port Form --}}
        <div id="addPortForm" class="p-3 border border-secondary rounded mb-3 d-none" style="background:rgba(16,185,129,0.05)">
            <h6 class="mb-3 text-success">Tambah Pelabuhan Baru</h6>
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" id="portName" class="form-control bg-dark text-white border-secondary" placeholder="Nama Pelabuhan *">
                </div>
                <div class="col-md-3">
                    <select id="portCountry" class="form-select bg-dark text-white border-secondary">
                        <option value="">Pilih Negara *</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="portSize" class="form-select bg-dark text-white border-secondary">
                        <option>Small</option><option>Medium</option><option selected>Large</option><option>Very Large</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" id="portLat" class="form-control bg-dark text-white border-secondary" placeholder="Latitude *" step="0.0001">
                </div>
                <div class="col-md-3">
                    <input type="number" id="portLng" class="form-control bg-dark text-white border-secondary" placeholder="Longitude *" step="0.0001">
                </div>
                <div class="col-md-3">
                    <select id="portType" class="form-select bg-dark text-white border-secondary">
                        <option>Seaport</option><option>River Port</option><option>Dry Port</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-success flex-fill" onclick="addPort()">Simpan</button>
                    <button class="btn btn-outline-secondary flex-fill" onclick="document.getElementById('addPortForm').classList.add('d-none')">Batal</button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover">
                <thead><tr><th>ID</th><th>Nama</th><th>Negara</th><th>Tipe</th><th>Ukuran</th><th>Koordinat</th><th>Aksi</th></tr></thead>
                <tbody id="portsBody"><tr><td colspan="7" class="text-center text-muted">Memuat...</td></tr></tbody>
            </table>
        </div>
        <div id="portsPagination" class="mt-2 text-center"></div>
    </div>

    {{-- Articles Panel --}}
    <div id="panelArticles" class="d-none">
        <div class="d-flex justify-content-between mb-3">
            <h6 class="text-muted">Artikel Intelijen Analisis</h6>
            <button class="btn btn-sm btn-primary" onclick="showAddArticleForm()">
                <i class="fa-solid fa-plus me-1"></i>Tulis Artikel
            </button>
        </div>

        {{-- Add Article Form --}}
        <div id="addArticleForm" class="p-3 border border-secondary rounded mb-3 d-none" style="background:rgba(59,130,246,0.05)">
            <h6 class="mb-3 text-primary">Tulis Artikel Baru</h6>
            <div class="mb-2">
                <input type="text" id="articleTitle" class="form-control bg-dark text-white border-secondary" placeholder="Judul Artikel *">
            </div>
            <div class="mb-2">
                <textarea id="articleContent" class="form-control bg-dark text-white border-secondary" rows="5" placeholder="Konten artikel..."></textarea>
            </div>
            <div class="d-flex gap-2">
                <select id="articleStatus" class="form-select bg-dark text-white border-secondary" style="width:150px">
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
                <button class="btn btn-primary" onclick="addArticle()">Terbitkan</button>
                <button class="btn btn-outline-secondary" onclick="document.getElementById('addArticleForm').classList.add('d-none')">Batal</button>
            </div>
        </div>

        <div id="articlesBody">
            <div class="text-center text-muted py-3">Memuat artikel...</div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Inisialisasi ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadUsers();
    loadCountriesForPortForm();
});

// ── Stats ───────────────────────────────────────────────────────────────────
async function loadStats() {
    const s = await apiGet('admin/stats');
    if (s) {
        document.getElementById('statUsers').innerText = s.users;
        document.getElementById('statPorts').innerText = s.ports;
        document.getElementById('statRisk').innerText  = s.risk_scores;
        document.getElementById('statNews').innerText  = s.news_cached;
    }
}

// ── Tab Switcher ────────────────────────────────────────────────────────────
function showTab(name, el) {
    event.preventDefault();
    document.querySelectorAll('#adminTabs .nav-link').forEach(a => {
        a.classList.remove('active','text-white');
        a.classList.add('text-muted');
    });
    el.classList.add('active','text-white');
    el.classList.remove('text-muted');

    document.getElementById('panelUsers').classList.add('d-none');
    document.getElementById('panelPorts').classList.add('d-none');
    document.getElementById('panelArticles').classList.add('d-none');
    document.getElementById(`panel${name.charAt(0).toUpperCase()+name.slice(1)}`).classList.remove('d-none');

    if (name === 'ports')    loadPorts();
    if (name === 'articles') loadArticles();
}

// ── System Actions ──────────────────────────────────────────────────────────
async function runEngine() {
    const btn    = document.getElementById('btnEngine');
    const status = document.getElementById('engineStatus');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Memproses...';
    status.classList.remove('d-none');
    status.innerHTML = '<span class="text-warning"><i class="fa-solid fa-spinner fa-spin me-1"></i>Menghitung risk score semua negara...</span>';

    try {
        const res = await apiPost('admin/engine/run', {});
        if (res && res.success) {
            status.innerHTML = `<span class="text-success"><i class="fa-solid fa-check-circle me-1"></i>${res.message}</span>`;
            loadStats();
        } else {
            status.innerHTML = '<span class="text-danger">Gagal menjalankan engine.</span>';
        }
    } catch(e) {
        status.innerHTML = '<span class="text-danger">Error: ' + e.message + '</span>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-play me-2"></i>Jalankan Risk Engine';
    }
}

async function refreshRates() {
    const btn    = document.getElementById('btnRates');
    const status = document.getElementById('ratesStatus');
    btn.disabled = true;
    status.classList.remove('d-none');
    status.innerHTML = '<span class="text-warning"><i class="fa-solid fa-spinner fa-spin me-1"></i>Mengambil kurs terbaru...</span>';
    try {
        const res = await apiPost('admin/engine/rates', {});
        if (res && res.success) {
            status.innerHTML = `<span class="text-success"><i class="fa-solid fa-check-circle me-1"></i>${res.message}</span>`;
        } else {
            status.innerHTML = '<span class="text-danger">Gagal memperbarui kurs.</span>';
        }
    } catch(e) {
        status.innerHTML = '<span class="text-danger">Error: ' + e.message + '</span>';
    } finally {
        btn.disabled = false;
    }
}

async function clearCache() {
    if (!confirm('Yakin ingin membersihkan cache lama dari database?')) return;
    const btn    = document.getElementById('btnCache');
    const status = document.getElementById('cacheStatus');
    btn.disabled = true;
    status.classList.remove('d-none');
    status.innerHTML = '<span class="text-warning"><i class="fa-solid fa-spinner fa-spin me-1"></i>Membersihkan...</span>';
    try {
        const res = await apiPost('admin/cache/clear', {});
        if (res && res.success) {
            status.innerHTML = `<span class="text-success"><i class="fa-solid fa-check-circle me-1"></i>${res.message}</span>`;
            loadStats();
        } else {
            status.innerHTML = '<span class="text-danger">Gagal membersihkan cache.</span>';
        }
    } catch(e) {
        status.innerHTML = '<span class="text-danger">Error: ' + e.message + '</span>';
    } finally {
        btn.disabled = false;
    }
}

// ── USERS ───────────────────────────────────────────────────────────────────
async function loadUsers() {
    const tbody = document.getElementById('usersBody');
    const users = await apiGet('admin/users');
    if (!users || users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Tidak ada pengguna.</td></tr>';
        return;
    }
    tbody.innerHTML = users.map(u => `
        <tr>
            <td>${u.id}</td>
            <td><i class="fa-solid fa-user me-2 text-muted"></i>${u.name}</td>
            <td>${u.email}</td>
            <td>${new Date(u.created_at).toLocaleDateString('id-ID')}</td>
            <td>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${u.id},'${u.name}')">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

async function deleteUser(id, name) {
    if (!confirm(`Hapus pengguna "${name}"?`)) return;
    const res = await fetch(`/api/admin/users/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    const data = await res.json();
    if (data.success) {
        loadUsers(); loadStats();
    } else {
        alert(data.message || 'Gagal menghapus pengguna.');
    }
}

// ── PORTS ───────────────────────────────────────────────────────────────────
let portsPage = 1;

async function loadPorts(page = 1) {
    portsPage = page;
    const tbody = document.getElementById('portsBody');
    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Memuat...</td></tr>';

    const data = await apiGet(`admin/ports?page=${page}`);
    if (!data || !data.data || data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Tidak ada pelabuhan ditemukan.</td></tr>';
        return;
    }
    tbody.innerHTML = data.data.map(p => `
        <tr>
            <td>${p.id}</td>
            <td class="fw-bold"><i class="fa-solid fa-anchor text-info me-1"></i>${p.name}</td>
            <td>${p.country ? `<img src="https://flagcdn.com/20x15/${p.country.code.toLowerCase()}.png" class="me-1">${p.country.name}` : 'N/A'}</td>
            <td>${p.type || 'N/A'}</td>
            <td><span class="badge bg-secondary">${p.size || 'N/A'}</span></td>
            <td class="small text-muted">${parseFloat(p.lat).toFixed(4)}, ${parseFloat(p.lng).toFixed(4)}</td>
            <td>
                <button class="btn btn-sm btn-outline-danger" onclick="deletePort(${p.id},'${p.name}')">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');

    // Pagination
    const pg = document.getElementById('portsPagination');
    let html = '';
    if (data.last_page > 1) {
        for (let i = 1; i <= data.last_page; i++) {
            html += `<button class="btn btn-sm ${i === page ? 'btn-primary' : 'btn-outline-secondary'} mx-1" onclick="loadPorts(${i})">${i}</button>`;
        }
    }
    pg.innerHTML = html;
}

async function loadCountriesForPortForm() {
    const select = document.getElementById('portCountry');
    const countries = await apiGet('countries');
    if (countries) {
        countries.forEach(c => {
            select.innerHTML += `<option value="${c.code}">${c.name}</option>`;
        });
    }
}

function showAddPortForm() {
    document.getElementById('addPortForm').classList.toggle('d-none');
}

async function addPort() {
    const payload = {
        name:         document.getElementById('portName').value,
        country_code: document.getElementById('portCountry').value,
        lat:          parseFloat(document.getElementById('portLat').value),
        lng:          parseFloat(document.getElementById('portLng').value),
        type:         document.getElementById('portType').value,
        size:         document.getElementById('portSize').value,
    };
    if (!payload.name || !payload.country_code || isNaN(payload.lat) || isNaN(payload.lng)) {
        alert('Nama, negara, latitude, dan longitude wajib diisi.');
        return;
    }
    const res = await apiPost('admin/ports', payload);
    if (res && res.success) {
        document.getElementById('addPortForm').classList.add('d-none');
        document.getElementById('portName').value = '';
        document.getElementById('portLat').value  = '';
        document.getElementById('portLng').value  = '';
        loadPorts(portsPage);
        loadStats();
    } else {
        alert('Gagal menyimpan pelabuhan. Periksa input Anda.');
    }
}

async function deletePort(id, name) {
    if (!confirm(`Hapus pelabuhan "${name}"?`)) return;
    const res = await fetch(`/api/admin/ports/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    const data = await res.json();
    if (data.success) { loadPorts(portsPage); loadStats(); }
    else alert('Gagal menghapus pelabuhan.');
}

// ── ARTICLES ────────────────────────────────────────────────────────────────
async function loadArticles() {
    const container = document.getElementById('articlesBody');
    const articles  = await apiGet('admin/articles');

    if (!articles || articles.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-5"><i class="fa-solid fa-file-circle-xmark fa-2x mb-2"></i><br>Belum ada artikel. Klik "Tulis Artikel" untuk membuat yang pertama.</div>';
        return;
    }

    container.innerHTML = articles.map(a => `
        <div class="p-3 border border-secondary rounded mb-3" style="background:rgba(255,255,255,0.02)">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="badge ${a.status === 'published' ? 'bg-success' : 'bg-secondary'} me-2">${a.status === 'published' ? 'Published' : 'Draft'}</span>
                    <h6 class="d-inline">${a.title}</h6>
                </div>
                <button class="btn btn-sm btn-outline-danger ms-2" onclick="deleteArticle(${a.id},'${a.title.replace(/'/g,"\\'")}')">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
            <p class="text-muted small mt-2 mb-1">${(a.content || '').substring(0, 200)}${a.content && a.content.length > 200 ? '...' : ''}</p>
            <div class="small text-muted">Oleh: ${a.user ? a.user.name : 'Admin'} &bull; ${new Date(a.created_at).toLocaleDateString('id-ID')}</div>
        </div>
    `).join('');
}

function showAddArticleForm() {
    document.getElementById('addArticleForm').classList.toggle('d-none');
}

async function addArticle() {
    const payload = {
        title:   document.getElementById('articleTitle').value,
        content: document.getElementById('articleContent').value,
        status:  document.getElementById('articleStatus').value,
    };
    if (!payload.title || !payload.content) {
        alert('Judul dan konten wajib diisi.');
        return;
    }
    const res = await apiPost('admin/articles', payload);
    if (res && res.success) {
        document.getElementById('addArticleForm').classList.add('d-none');
        document.getElementById('articleTitle').value   = '';
        document.getElementById('articleContent').value = '';
        loadArticles();
    } else {
        alert('Gagal menyimpan artikel.');
    }
}

async function deleteArticle(id, title) {
    if (!confirm(`Hapus artikel "${title}"?`)) return;
    const res = await fetch(`/api/admin/articles/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    const data = await res.json();
    if (data.success) loadArticles();
    else alert('Gagal menghapus artikel.');
}
</script>
@endpush
