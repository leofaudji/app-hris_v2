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