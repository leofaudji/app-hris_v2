function initLaporanPage() {
    if (document.getElementById('laporan-table-body')) {
        loadLaporan();

        const btnTampilkan = document.getElementById('btn-tampilkan');
        if (btnTampilkan) btnTampilkan.addEventListener('click', loadLaporan);
    }
}

function loadLaporan() {
    const bulan = document.getElementById('filter-bulan').value;
    const tahun = document.getElementById('filter-tahun').value;
    const tbody = document.getElementById('laporan-table-body');

    // Reset summary
    document.getElementById('summary-gaji-pokok').innerText = 'Rp 0';
    document.getElementById('summary-tunjangan').innerText = 'Rp 0';
    document.getElementById('summary-potongan').innerText = 'Rp 0';
    document.getElementById('summary-total').innerText = 'Rp 0';

    fetch(`${basePath}/api/hr/laporan?bulan=${bulan}&tahun=${tahun}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = '';
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data laporan untuk periode ini.</td></tr>';
                    return;
                }
                
                const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

                // Update Summary
                document.getElementById('summary-gaji-pokok').innerText = formatter.format(data.totals.gaji_pokok);
                document.getElementById('summary-tunjangan').innerText = formatter.format(data.totals.tunjangan);
                document.getElementById('summary-potongan').innerText = formatter.format(data.totals.potongan);
                document.getElementById('summary-total').innerText = formatter.format(data.totals.total_gaji);

                data.data.forEach(item => {
                    let statusBadge = item.status === 'final' 
                        ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Final</span>'
                        : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Draft</span>';

                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.nip}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.nama_lengkap}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.nama_jabatan || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">${formatter.format(item.gaji_pokok)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">${formatter.format(item.tunjangan)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-500 dark:text-red-400">${formatter.format(item.potongan)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900 dark:text-white">${formatter.format(item.total_gaji)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">Gagal memuat data: ${data.message}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-red-500">Terjadi kesalahan saat memuat data.</td></tr>';
        });
}