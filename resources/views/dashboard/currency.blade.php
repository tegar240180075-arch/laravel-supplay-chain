@extends('layouts.app')

@section('title', 'Dampak Mata Uang')
@section('page_title', 'Dasbor Dampak Mata Uang')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 col-md-4">
        <div class="glass-card h-100">
            <h5 class="border-bottom border-secondary pb-2 mb-3">Kalkulator Konversi</h5>
            
            <div class="mb-3">
                <label class="form-label text-muted small">Jumlah</label>
                <input type="number" id="convAmount" class="form-control bg-dark text-white border-secondary" value="1000">
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label text-muted small">Dari</label>
                    <select id="convFrom" class="form-select bg-dark text-white border-secondary">
                        <option value="USD">Memuat mata uang...</option>
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label text-muted small">Ke</label>
                    <select id="convTo" class="form-select bg-dark text-white border-secondary">
                        <option value="IDR">Memuat mata uang...</option>
                    </select>
                </div>
            </div>
            
            <button class="btn btn-primary w-100 mb-4" onclick="convertCurrency()">Hitung</button>
            
            <div class="text-center p-3 border border-primary rounded" style="background: rgba(0, 212, 255, 0.1);">
                <div class="text-muted small mb-1" id="convResultLabel">Hasil</div>
                <h3 class="text-gradient mb-0" id="convResultValue">0.00</h3>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-8">
        <div class="glass-card h-100">
            <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-3">
                <h5 class="mb-0">Nilai Tukar Langsung (Basis: USD)</h5>
            </div>
            
            <div class="table-responsive" style="max-height: 400px;">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>Kode Mata Uang</th>
                            <th>Nilai (per 1 USD)</th>
                            <th>Tren</th>
                        </tr>
                    </thead>
                    <tbody id="ratesTableBody">
                        <tr><td colspan="3" class="text-center text-muted">Memuat nilai tukar...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadRates();
    });

    async function loadRates() {
        showLoader();
        try {
            const [ratesData, countries] = await Promise.all([
                apiGet('currency/rates?base=USD'),
                apiGet('countries')
            ]);
            
            const tbody = document.getElementById('ratesTableBody');
            
            if (ratesData && countries) {
                let ratesMap = {};
                if (Array.isArray(ratesData)) {
                    ratesData.forEach(r => ratesMap[r.target_currency] = r.rate);
                } else {
                    ratesMap = ratesData;
                }
                
                let addedCurrencies = new Set();
                let html = '';
                let optionsHtml = '';
                
                // Tambahkan USD sebagai basis jika tidak ada
                if (!ratesMap['USD']) {
                    ratesMap['USD'] = 1;
                }
                
                countries.forEach(country => {
                    const currencyCode = country.currency_code;
                    if (currencyCode && ratesMap[currencyCode] && !addedCurrencies.has(currencyCode)) {
                        addedCurrencies.add(currencyCode);
                        const rate = ratesMap[currencyCode];
                        
                        html += `
                            <tr>
                                <td>
                                    <img src="https://flagcdn.com/20x15/${country.code.toLowerCase()}.png" class="me-2 rounded" alt="${country.name}">
                                    <span class="fw-bold">${currencyCode}</span>
                                    <div class="text-muted small" style="font-size: 0.75rem;">${country.name}</div>
                                </td>
                                <td class="align-middle">${parseFloat(rate).toFixed(4)}</td>
                                <td class="align-middle text-success"><i class="fa-solid fa-arrow-right"></i> Stabil</td>
                            </tr>
                        `;
                        
                        optionsHtml += `<option value="${currencyCode}">${currencyCode} - ${country.name}</option>`;
                    }
                });
                
                tbody.innerHTML = html || '<tr><td colspan="3" class="text-center text-muted">Data nilai tukar tidak tersedia</td></tr>';
                
                const convFrom = document.getElementById('convFrom');
                const convTo = document.getElementById('convTo');
                
                // Simpan nilai pilihan sebelumnya jika ada
                const oldFrom = convFrom.value;
                const oldTo = convTo.value;
                
                convFrom.innerHTML = optionsHtml;
                convTo.innerHTML = optionsHtml;
                
                // Set default pilihan
                if (addedCurrencies.has(oldFrom)) convFrom.value = oldFrom;
                else if (addedCurrencies.has('USD')) convFrom.value = 'USD';
                
                if (addedCurrencies.has(oldTo)) convTo.value = oldTo;
                else if (addedCurrencies.has('IDR')) convTo.value = 'IDR';
                
            } else {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Gagal memuat data</td></tr>';
            }
        } catch (e) {
            console.error(e);
            document.getElementById('ratesTableBody').innerHTML = '<tr><td colspan="3" class="text-center text-danger">Terjadi kesalahan</td></tr>';
        } finally {
            hideLoader();
        }
    }
    
    async function convertCurrency() {
        const from = document.getElementById('convFrom').value;
        const to = document.getElementById('convTo').value;
        const amount = document.getElementById('convAmount').value;
        
        if (!amount || amount <= 0) return;
        
        showLoader();
        try {
            const result = await apiGet(`currency/convert?from=${from}&to=${to}&amount=${amount}`);
            if (result && result.converted_amount) {
                document.getElementById('convResultLabel').innerText = `${amount} ${from} =`;
                document.getElementById('convResultValue').innerText = `${result.converted_amount.toLocaleString()} ${to}`;
            }
        } catch (e) {
            console.error(e);
        } finally {
            hideLoader();
        }
    }
</script>
@endpush
