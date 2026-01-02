function initPortalSlipGajiPage() {
    if (document.getElementById('portal-slipgaji-table-body')) {
        loadPortalSlipGaji();
        document.getElementById('portal-filter-tahun-gaji').addEventListener('change', loadPortalSlipGaji);
    }
}

function loadPortalSlipGaji() {
    const tahun = document.getElementById('portal-filter-tahun-gaji').value;
    const tbody = document.getElementById('portal-slipgaji-table-body');
    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Memuat data...</td></tr>';

    const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
    const months = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

    fetch(`${basePath}/api/portal/slip-gaji?tahun=${tahun}`)
        .then(response => response.json())
        .then(res => {
            tbody.innerHTML = '';
            if (res.success && res.data.length > 0) {
                res.data.forEach(item => {
                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${months[item.periode_bulan]} ${item.periode_tahun}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">${formatter.format(item.gaji_pokok)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">${formatter.format(item.tunjangan)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-500 dark:text-red-400">${formatter.format(item.potongan)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900 dark:text-white">${formatter.format(item.total_gaji)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="printSlipGaji(${item.id})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Cetak Slip Gaji"><i class="bi bi-printer-fill"></i> Cetak</button>
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data slip gaji untuk tahun ini.</td></tr>';
            }
        });
}

// Re-use the existing print function from penggajian.js
function printSlipGaji(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${basePath}/api/pdf`;
    form.target = '_blank';
    const params = { report: 'slip-gaji', id: id };
    for (const key in params) {
        const hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = key;
        hiddenField.value = params[key];
        form.appendChild(hiddenField);
    }
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}