function initMasterDashboardPage() {
    loadMasterDashboardData();
}

function loadMasterDashboardData() {
    const container = document.getElementById('master-dashboard-content');
    
    fetch(`${basePath}/api/hr/master-dashboard`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const data = res.data;
                
                // Render Cards
                container.innerHTML = `
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 mr-4">
                            <i class="bi bi-people text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Karyawan</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">${data.total_karyawan}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400 mr-4">
                            <i class="bi bi-building text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Divisi</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">${data.total_divisi}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400 mr-4">
                            <i class="bi bi-briefcase text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Jabatan</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">${data.total_jabatan}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700 flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400 mr-4">
                            <i class="bi bi-geo-alt text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Kantor</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">${data.total_kantor}</p>
                        </div>
                    </div>
                `;

                // Render Charts
                renderDivisiChart(data.divisi_distribution);
                renderStatusChart(data.status_distribution);
                renderExpiringContracts(data.expiring_contracts);
            } else {
                showToast(res.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Gagal memuat data dashboard.', 'error');
        });
}

function renderDivisiChart(data) {
    const ctx = document.getElementById('divisi-chart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.nama_divisi),
            datasets: [{
                label: 'Jumlah Karyawan',
                data: data.map(d => d.total),
                backgroundColor: 'rgba(59, 130, 246, 0.6)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

function renderExpiringContracts(data) {
    const tbody = document.getElementById('expiring-contracts-body');
    if (!tbody) return;
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada kontrak yang akan berakhir dalam waktu dekat.</td></tr>';
        return;
    }

    let html = '';
    data.forEach(item => {
        const date = new Date(item.tanggal_berakhir_kontrak);
        const formattedDate = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        
        let sisaClass = 'text-gray-900 dark:text-white';
        if (item.sisa_hari <= 30) sisaClass = 'text-red-600 font-bold';
        else if (item.sisa_hari <= 60) sisaClass = 'text-yellow-600 font-bold';

        html += `
            <tr>
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">${item.nama_lengkap}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">${item.nip}</div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                    ${formattedDate}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center text-sm ${sisaClass}">
                    ${item.sisa_hari} Hari
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function renderStatusChart(data) {
    const ctx = document.getElementById('status-chart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(d => d.status.charAt(0).toUpperCase() + d.status.slice(1)),
            datasets: [{
                data: data.map(d => d.total),
                backgroundColor: [
                    'rgba(16, 185, 129, 0.6)', // Green for active
                    'rgba(239, 68, 68, 0.6)'   // Red for inactive
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}
