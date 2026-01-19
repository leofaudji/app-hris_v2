function initPortalAbsensiPage() {
    if (document.getElementById('portal-absensi-table-body')) {
        loadPortalAbsensi();
        document.getElementById('portal-btn-tampilkan-absensi').addEventListener('click', loadPortalAbsensi);
    }
}

function loadPortalAbsensi() {
    const bulan = document.getElementById('portal-filter-bulan').value;
    const tahun = document.getElementById('portal-filter-tahun').value;
    const tbody = document.getElementById('portal-absensi-table-body');
    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Memuat data...</td></tr>';

    fetch(`${basePath}/api/hr/portal/absensi?bulan=${bulan}&tahun=${tahun}`)
        .then(response => response.json())
        .then(res => {
            tbody.innerHTML = '';
            if (res.success && res.data.length > 0) {
                res.data.forEach(item => {
                    const statusBadge = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${item.badge_class}">${item.status}</span>`;
                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${formatDate(item.tanggal)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">${item.jam_masuk || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">${item.jam_keluar || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">${item.keterangan || '-'}</td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data absensi untuk periode ini.</td></tr>';
            }
        });
}