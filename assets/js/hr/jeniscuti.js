function initJenisCutiPage() {
    if (document.getElementById('jenis-cuti-table-body')) {
        loadJenisCuti();

        const saveBtn = document.getElementById('save-jenis-cuti-btn');
        if (saveBtn) saveBtn.addEventListener('click', saveJenisCuti);
    }
}

function loadJenisCuti() {
    const tbody = document.getElementById('jenis-cuti-table-body');

    fetch(`${basePath}/api/hr/jenis-cuti`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = '';
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Belum ada jenis cuti yang dibuat.</td></tr>';
                    return;
                }
                
                data.data.forEach(item => {
                    const statusBadge = item.is_active == 1 
                        ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Aktif</span>'
                        : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Non-Aktif</span>';

                    const mengurangiBadge = item.mengurangi_jatah_cuti == 1
                        ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Ya</span>'
                        : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Tidak</span>';

                    const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");
                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.nama_jenis}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${mengurangiBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick='editJenisCuti(${itemJson})' class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                <button onclick="deleteJenisCuti(${item.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
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

function openJenisCutiModal(reset = true) {
    if (reset) {
        document.getElementById('jenis-cuti-form').reset();
        document.getElementById('jenis-cuti-id').value = '';
        document.getElementById('modal-title').innerText = 'Tambah Jenis Cuti';
    }
    document.getElementById('jenisCutiModal').classList.remove('hidden');
}

function closeJenisCutiModal() {
    document.getElementById('jenisCutiModal').classList.add('hidden');
}

function editJenisCuti(item) {
    openJenisCutiModal(false);
    document.getElementById('jenis-cuti-id').value = item.id;
    document.getElementById('modal-title').innerText = 'Edit Jenis Cuti';
    document.getElementById('nama_jenis').value = item.nama_jenis;
    document.getElementById('mengurangi_jatah_cuti').value = item.mengurangi_jatah_cuti;
    document.getElementById('is_active').value = item.is_active;
}

function saveJenisCuti() {
    const form = document.getElementById('jenis-cuti-form');
    const formData = new FormData(form);
    
    fetch(`${basePath}/api/hr/jenis-cuti`, { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeJenisCutiModal();
            loadJenisCuti();
            showToast('Data jenis cuti berhasil disimpan', 'success');
        } else {
            showToast(data.message || 'Gagal menyimpan data', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteJenisCuti(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus jenis cuti ini?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(`${basePath}/api/hr/jenis-cuti`, { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadJenisCuti();
            showToast('Data jenis cuti berhasil dihapus', 'success');
        } else {
            showToast(data.message || 'Gagal menghapus data', 'error');
        }
    });
}