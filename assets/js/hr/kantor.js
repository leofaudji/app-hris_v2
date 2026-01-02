function initKantorPage() {
    if (document.getElementById('kantor-table-body')) {
        loadKantor();

        const searchInput = document.getElementById('search-kantor');
        if (searchInput) searchInput.addEventListener('input', debounce(loadKantor, 500));

        const saveBtn = document.getElementById('save-kantor-btn');
        if (saveBtn) saveBtn.addEventListener('click', saveKantor);
    }
}

function loadKantor() {
    const search = document.getElementById('search-kantor').value;
    const tbody = document.getElementById('kantor-table-body');

    fetch(`${basePath}/api/hr/kantor?search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = '';
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data kantor.</td></tr>';
                    return;
                }
                
                data.data.forEach(item => {
                    const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");
                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.nama_kantor}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">${item.jenis_kantor}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">${item.alamat || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick='editKantor(${itemJson})' class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                <button onclick="deleteKantor(${item.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
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

function openKantorModal(reset = true) {
    if (reset) {
        document.getElementById('kantor-form').reset();
        document.getElementById('kantor-id').value = '';
        document.getElementById('modal-title').innerText = 'Tambah Kantor';
    }
    document.getElementById('kantorModal').classList.remove('hidden');
}

function closeKantorModal() {
    document.getElementById('kantorModal').classList.add('hidden');
}

function editKantor(item) {
    openKantorModal(false);
    document.getElementById('kantor-id').value = item.id;
    document.getElementById('modal-title').innerText = 'Edit Kantor';
    document.getElementById('nama_kantor').value = item.nama_kantor;
    document.getElementById('jenis_kantor').value = item.jenis_kantor;
    document.getElementById('alamat').value = item.alamat;
}

function saveKantor() {
    const form = document.getElementById('kantor-form');
    const formData = new FormData(form);
    
    fetch(`${basePath}/api/hr/kantor`, { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeKantorModal();
            loadKantor();
            showToast('Data kantor berhasil disimpan', 'success');
        } else {
            showToast(data.message || 'Gagal menyimpan data', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteKantor(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus kantor ini?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(`${basePath}/api/hr/kantor`, { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadKantor();
            showToast('Data kantor berhasil dihapus', 'success');
        } else {
            showToast(data.message || 'Gagal menghapus data', 'error');
        }
    });
}