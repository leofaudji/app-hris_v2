function initManajemenCutiPage() {
    if (document.getElementById('cuti-table-body')) {
        loadCuti();
        loadKaryawanOptionsForCuti();
        loadJenisCutiOptions();

        document.getElementById('btn-tampilkan-cuti').addEventListener('click', loadCuti);
        document.getElementById('save-pengajuan-cuti-btn').addEventListener('click', savePengajuanCuti);
        document.getElementById('save-jatah-cuti-btn').addEventListener('click', saveJatahCuti);
        
        document.getElementById('cuti-karyawan-id').addEventListener('change', updateSisaCutiInfo);
        document.getElementById('cuti-jenis-id').addEventListener('change', updateSisaCutiInfo);

        const setForAllCheckbox = document.getElementById('set-for-all-karyawan');
        if(setForAllCheckbox) setForAllCheckbox.addEventListener('change', toggleJatahKaryawanSelection);

        // --- Inject Tombol Monitoring Kuota ---
        // Cari tombol action yang sudah ada (misal tombol Atur Jatah atau Tambah Cuti) untuk menentukan lokasi yang tepat
        const existingActionBtn = document.querySelector('button[onclick*="openJatahCutiModal"]') || document.querySelector('button[onclick*="openPengajuanCutiModal"]');
        
        if (existingActionBtn && !document.getElementById('btn-monitoring-kuota')) {
            const container = existingActionBtn.parentElement;
            
            const btnMonitor = document.createElement('button');
            btnMonitor.id = 'btn-monitoring-kuota';
            btnMonitor.className = 'inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors';
            btnMonitor.innerHTML = '<i class="bi bi-list-check mr-2"></i> Monitoring Kuota';
            btnMonitor.type = 'button';
            btnMonitor.onclick = openQuotaMonitoringModal;
            
            // Sisipkan sebelum tombol yang ditemukan
            container.insertBefore(btnMonitor, existingActionBtn);
        }
    }
}

function loadCuti() {
    const karyawan_id = document.getElementById('filter-karyawan').value;
    const bulan = document.getElementById('filter-bulan').value;
    const tahun = document.getElementById('filter-tahun').value;
    const tbody = document.getElementById('cuti-table-body');
    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Memuat data...</td></tr>';

    fetch(`${basePath}/api/hr/manajemen-cuti?karyawan_id=${karyawan_id}&bulan=${bulan}&tahun=${tahun}`)
        .then(response => response.json())
        .then(res => {
            tbody.innerHTML = '';
            if (res.success && res.data.length > 0) {
                res.data.forEach(item => {
                    let statusBadge;
                    switch(item.status) {
                        case 'approved': statusBadge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>'; break;
                        case 'rejected': statusBadge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>'; break;
                        default: statusBadge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>'; break;
                    }

                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.nama_lengkap}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.nama_jenis}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${formatDate(item.tanggal_mulai)} - ${formatDate(item.tanggal_selesai)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">${item.jumlah_hari} hari</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                ${item.status === 'pending' ? `
                                <button onclick="updateStatusCuti(${item.id}, 'approved')" class="text-green-600 hover:text-green-900 mr-3" title="Setujui"><i class="bi bi-check-circle-fill"></i></button>
                                <button onclick="updateStatusCuti(${item.id}, 'rejected')" class="text-yellow-600 hover:text-yellow-900 mr-3" title="Tolak"><i class="bi bi-x-circle-fill"></i></button>
                                <button onclick="deleteCuti(${item.id})" class="text-red-600 hover:text-red-900" title="Hapus"><i class="bi bi-trash-fill"></i></button>
                                ` : ''}
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data pengajuan cuti.</td></tr>';
            }
        });
}

function loadKaryawanOptionsForCuti() {
    const selects = [
        document.getElementById('filter-karyawan'),
        document.getElementById('cuti-karyawan-id'),
        document.getElementById('jatah-karyawan-id')
    ];
    fetch(`${basePath}/api/hr/karyawan?status=aktif`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                selects.forEach(select => {
                    if (select) {
                        let firstOption = select.options[0];
                        if (!firstOption) {
                            firstOption = document.createElement('option');
                            firstOption.value = "";
                            firstOption.textContent = "Semua Karyawan"; // Default text
                        }
                        select.innerHTML = '';
                        select.appendChild(firstOption.cloneNode(true));
                        res.data.forEach(k => {
                            select.insertAdjacentHTML('beforeend', `<option value="${k.id}">${k.nama_lengkap}</option>`);
                        });
                    }
                });
            }
        });
}

function loadJenisCutiOptions() {
    const select = document.getElementById('cuti-jenis-id');
    fetch(`${basePath}/api/hr/jenis-cuti`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                select.innerHTML = '<option value="">Pilih Jenis Cuti</option>';
                window.jenisCutiData = res.data; // Store for later use
                res.data.forEach(item => {
                    if(item.is_active == 1) {
                        select.insertAdjacentHTML('beforeend', `<option value="${item.id}" data-mengurangi="${item.mengurangi_jatah_cuti}">${item.nama_jenis}</option>`);
                    }
                });
            }
        });
}

async function updateSisaCutiInfo() {
    const infoEl = document.getElementById('sisa-cuti-info');
    const jenisCutiSelect = document.getElementById('cuti-jenis-id');
    const karyawanId = document.getElementById('cuti-karyawan-id').value;
    const selectedOption = jenisCutiSelect.options[jenisCutiSelect.selectedIndex];

    if (karyawanId && selectedOption && selectedOption.dataset.mengurangi == '1') {
        const tahun = new Date().getFullYear();
        const response = await fetch(`${basePath}/api/hr/manajemen-cuti?action=get_sisa_cuti&karyawan_id=${karyawanId}&tahun=${tahun}`);
        const result = await response.json();
        if (result.success) {
            infoEl.querySelector('span').textContent = result.sisa_jatah;
            infoEl.classList.remove('hidden');
        }
    } else {
        infoEl.classList.add('hidden');
    }
}

function openPengajuanCutiModal() {
    document.getElementById('pengajuan-cuti-form').reset();
    document.getElementById('pengajuan-cuti-id').value = '';
    document.getElementById('sisa-cuti-info').classList.add('hidden');
    document.getElementById('pengajuanCutiModal').classList.remove('hidden');
}

function closePengajuanCutiModal() {
    document.getElementById('pengajuanCutiModal').classList.add('hidden');
}

function openJatahCutiModal() {
    document.getElementById('jatah-cuti-form').reset();
    document.getElementById('jatah-tahun').value = new Date().getFullYear();
    toggleJatahKaryawanSelection(); // Reset view
    document.getElementById('jatahCutiModal').classList.remove('hidden');
}

function closeJatahCutiModal() {
    document.getElementById('jatahCutiModal').classList.add('hidden');
}

function toggleJatahKaryawanSelection() {
    const isChecked = document.getElementById('set-for-all-karyawan').checked;
    const container = document.getElementById('jatah-karyawan-container');
    const select = document.getElementById('jatah-karyawan-id');
    if (isChecked) {
        container.classList.add('hidden');
        select.required = false;
    } else {
        container.classList.remove('hidden');
        select.required = true;
    }
}

function savePengajuanCuti() {
    const form = document.getElementById('pengajuan-cuti-form');
    const formData = new FormData(form);
    
    fetch(`${basePath}/api/hr/manajemen-cuti`, { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closePengajuanCutiModal();
            loadCuti();
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Gagal menyimpan data', 'error');
        }
    });
}

function saveJatahCuti() {
    const form = document.getElementById('jatah-cuti-form');
    const formData = new FormData(form);
    
    fetch(`${basePath}/api/hr/manajemen-cuti`, { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeJatahCutiModal();
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Gagal menyimpan data', 'error');
        }
    });
}

function updateStatusCuti(id, status) {
    const statusText = status === 'approved' ? 'menyetujui' : 'menolak';
    if (!confirm(`Apakah Anda yakin ingin ${statusText} pengajuan cuti ini?`)) return;

    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('id', id);
    formData.append('status', status);

    fetch(`${basePath}/api/hr/manajemen-cuti`, { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadCuti();
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Gagal memproses permintaan', 'error');
        }
    });
}

function deleteCuti(id) {
    if (!confirm('Hanya pengajuan PENDING yang bisa dihapus. Lanjutkan?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(`${basePath}/api/hr/manajemen-cuti`, { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadCuti();
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Gagal menghapus data', 'error');
        }
    });
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
}

// --- Fitur Baru: Monitoring Kuota Cuti ---
function openQuotaMonitoringModal() {
    // Cek apakah modal sudah ada, jika belum buat baru
    let modal = document.getElementById('quotaMonitoringModal');
    if (!modal) {
        const modalHtml = `
            <div id="quotaMonitoringModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('quotaMonitoringModal').classList.add('hidden')"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Monitoring Kuota Cuti Karyawan</h3>
                            <div class="flex items-center gap-2">
                                <select id="monitor-tahun" class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:border-primary focus:ring-primary" onchange="loadQuotaList()">
                                    ${Array.from({length: 5}, (_, i) => `<option value="${new Date().getFullYear() - i}">${new Date().getFullYear() - i}</option>`).join('')}
                                </select>
                                <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" onclick="document.getElementById('quotaMonitoringModal').classList.add('hidden')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Karyawan</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Divisi</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jatah Awal</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Terpakai</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pending</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sisa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="quota-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr><td colspan="6" class="text-center py-4">Memuat data...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        modal = document.getElementById('quotaMonitoringModal');
    }
    
    modal.classList.remove('hidden');
    loadQuotaList();
}

function loadQuotaList() {
    const tahun = document.getElementById('monitor-tahun').value;
    const tbody = document.getElementById('quota-table-body');
    
    fetch(`${basePath}/api/hr/manajemen-cuti?action=get_quota_list&tahun=${tahun}`)
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = '';
            if(data.success && data.data.length > 0) {
                data.data.forEach(item => {
                    const terpakai = parseInt(item.jatah_awal) - parseInt(item.sisa_jatah);
                    const pending = parseInt(item.cuti_pending) || 0;
                    const row = `
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                ${item.nama_lengkap}<br><span class="text-xs text-gray-500 font-normal">${item.nip}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.nama_divisi || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">${item.jatah_awal}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-yellow-600 dark:text-yellow-400">${terpakai}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-blue-600 dark:text-blue-400 font-medium">${pending > 0 ? pending : '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold ${item.sisa_jatah < 3 ? 'text-red-600' : 'text-green-600'}">${item.sisa_jatah}</td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Tidak ada data karyawan aktif.</td></tr>';
            }
        });
}