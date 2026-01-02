function initAbsensiPage() {
    if (document.getElementById('absensi-table-body')) {
        loadAbsensi();
        loadKaryawanOptionsForAbsensi();
        loadGolonganOptions();
        loadStatusOptions();

        const searchInput = document.getElementById('search-absensi');
        if (searchInput) searchInput.addEventListener('input', debounce(loadAbsensi, 500));

        const filterTanggal = document.getElementById('filter-tanggal');
        if (filterTanggal) filterTanggal.addEventListener('change', loadAbsensi);

        const filterStatus = document.getElementById('filter-status-absensi');
        if (filterStatus) filterStatus.addEventListener('change', loadAbsensi);

        const saveBtn = document.getElementById('save-absensi-btn');
        if (saveBtn) saveBtn.addEventListener('click', saveAbsensi);
    }
}

function loadAbsensi() {
    const search = document.getElementById('search-absensi').value;
    const tanggal = document.getElementById('filter-tanggal').value;
    const status = document.getElementById('filter-status-absensi').value;
    const tbody = document.getElementById('absensi-table-body');

    let url = `${basePath}/api/hr/absensi?search=${encodeURIComponent(search)}`;
    if (tanggal) url += `&tanggal=${tanggal}`;
    if (status) url += `&status=${status}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = '';
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data absensi untuk filter ini.</td></tr>';
                    return;
                }
                
                data.data.forEach(item => {
                    const statusBadge = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${item.badge_class}">${item.status}</span>`;

                    // Escape string untuk keamanan
                    const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");

                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${formatDate(item.tanggal)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.nama_lengkap}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.golongan || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">${item.jam_masuk || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">${item.jam_keluar || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">${item.keterangan || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick='editAbsensi(${itemJson})' class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                <button onclick="deleteAbsensi(${item.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
                            </td>
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

function loadKaryawanOptionsForAbsensi() {
    fetch(`${basePath}/api/hr/karyawan?status=aktif`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('karyawan_id');
                if (select) {
                    select.innerHTML = '<option value="">Pilih Karyawan</option>';
                    data.data.forEach(karyawan => {
                        select.insertAdjacentHTML('beforeend', `<option value="${karyawan.id}">${karyawan.nama_lengkap} (${karyawan.nip})</option>`);
                    });
                }
            }
        });
}

function loadGolonganOptions() {
    fetch(`${basePath}/api/hr/absensi?action=get_golongan`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('golongan');
                if (select) {
                    select.innerHTML = '<option value="">Pilih Golongan</option>';
                    data.data.forEach(item => {
                        select.insertAdjacentHTML('beforeend', `<option value="${item.nama_golongan}">${item.nama_golongan}</option>`);
                    });
                }
            }
        });
}

function loadStatusOptions() {
    fetch(`${basePath}/api/hr/absensi?action=get_status`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const formSelect = document.getElementById('status');
                const filterSelect = document.getElementById('filter-status-absensi');
                
                if (formSelect) formSelect.innerHTML = '';
                if (filterSelect) filterSelect.innerHTML = '<option value="">Semua Status</option>';

                data.data.forEach(item => {
                    const optionHtml = `<option value="${item.nama_status}">${item.nama_status.charAt(0).toUpperCase() + item.nama_status.slice(1)}</option>`;
                    if (formSelect) formSelect.insertAdjacentHTML('beforeend', optionHtml);
                    if (filterSelect) filterSelect.insertAdjacentHTML('beforeend', optionHtml);
                });
            }
        });
}

function openAbsensiModal(reset = true) {
    if (reset) {
        document.getElementById('absensi-form').reset();
        document.getElementById('absensi-id').value = '';
        document.getElementById('absensi-action').value = 'save';
        document.getElementById('modal-title').innerText = 'Tambah Absensi';
        // Set default tanggal hari ini
        document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];
    }
    document.getElementById('absensiModal').classList.remove('hidden');
}

function closeAbsensiModal() {
    document.getElementById('absensiModal').classList.add('hidden');
}

function editAbsensi(item) {
    openAbsensiModal(false);
    document.getElementById('absensi-id').value = item.id;
    document.getElementById('absensi-action').value = 'save';
    document.getElementById('modal-title').innerText = 'Edit Absensi';
    
    document.getElementById('karyawan_id').value = item.karyawan_id;
    document.getElementById('tanggal').value = item.tanggal;
    document.getElementById('golongan').value = item.golongan || '';
    document.getElementById('jam_masuk').value = item.jam_masuk;
    document.getElementById('jam_keluar').value = item.jam_keluar;
    document.getElementById('status').value = item.status;
    document.getElementById('keterangan').value = item.keterangan;
}

function saveAbsensi() {
    const form = document.getElementById('absensi-form');
    const formData = new FormData(form);
    
    fetch(`${basePath}/api/hr/absensi`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeAbsensiModal();
            loadAbsensi();
            showToast('Data absensi berhasil disimpan', 'success');
        } else {
            showToast(data.message || 'Gagal menyimpan data', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteAbsensi(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus data absensi ini?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(`${basePath}/api/hr/absensi`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadAbsensi();
            showToast('Data absensi berhasil dihapus', 'success');
        } else {
            showToast(data.message || 'Gagal menghapus data', 'error');
        }
    });
}