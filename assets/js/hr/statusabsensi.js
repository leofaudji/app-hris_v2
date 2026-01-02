function initStatusAbsensiPage() {
    if (document.getElementById('status-table-body')) {
        loadStatus();

        const saveBtn = document.getElementById('save-status-btn');
        if (saveBtn) saveBtn.addEventListener('click', saveStatus);
    }
}

function loadStatus() {
    const tbody = document.getElementById('status-table-body');

    fetch(`${basePath}/api/hr/status-absensi`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = '';
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data status absensi.</td></tr>';
                    return;
                }
                
                data.data.forEach(item => {
                    const statusAktifBadge = item.is_active == 1 
                        ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Aktif</span>'
                        : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Non-Aktif</span>';

                    const contohBadge = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${item.badge_class}">${item.nama_status}</span>`;

                    const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");
                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.nama_status}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${contohBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${statusAktifBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick='editStatus(${itemJson})' class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                <button onclick="deleteStatus(${item.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Gagal memuat data: ${data.message}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Terjadi kesalahan saat memuat data.</td></tr>';
        });
}

function openStatusModal(reset = true) {
    if (reset) {
        document.getElementById('status-form').reset();
        document.getElementById('status-id').value = '';
        document.getElementById('modal-title').innerText = 'Tambah Status Absensi';
    }
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

function editStatus(item) {
    openStatusModal(false);
    document.getElementById('status-id').value = item.id;
    document.getElementById('modal-title').innerText = 'Edit Status Absensi';
    document.getElementById('nama_status').value = item.nama_status;
    document.getElementById('badge_class').value = item.badge_class;
    document.getElementById('is_active').value = item.is_active;
}

function saveStatus() {
    const form = document.getElementById('status-form');
    const formData = new FormData(form);
    
    fetch(`${basePath}/api/hr/status-absensi`, { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeStatusModal();
            loadStatus();
            showToast('Data status berhasil disimpan', 'success');
        } else {
            showToast(data.message || 'Gagal menyimpan data', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteStatus(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus status ini?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(`${basePath}/api/hr/status-absensi`, { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadStatus();
            showToast('Data status berhasil dihapus', 'success');
        } else {
            showToast(data.message || 'Gagal menghapus data', 'error');
        }
    });
}