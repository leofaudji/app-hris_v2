function initDivisiPage() {
    if (document.getElementById('divisi-table-body')) {
        loadDivisi();

        const searchInput = document.getElementById('search-divisi');
        if (searchInput) searchInput.addEventListener('input', debounce(loadDivisi, 500));

        const saveBtn = document.getElementById('save-divisi-btn');
        if (saveBtn) saveBtn.addEventListener('click', saveDivisi);
    }
}

function loadDivisi() {
    const search = document.getElementById('search-divisi').value;
    const tbody = document.getElementById('divisi-table-body');

    fetch(`${basePath}/api/hr/divisi?search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = '';
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="2" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data divisi.</td></tr>';
                    return;
                }
                
                data.data.forEach(item => {
                    const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");
                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.nama_divisi}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick='editDivisi(${itemJson})' class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                <button onclick="deleteDivisi(${item.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="2" class="px-6 py-4 text-center text-red-500">Gagal memuat data: ${data.message}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="2" class="px-6 py-4 text-center text-red-500">Terjadi kesalahan saat memuat data.</td></tr>';
        });
}

function openDivisiModal(reset = true) {
    if (reset) {
        document.getElementById('divisi-form').reset();
        document.getElementById('divisi-id').value = '';
        document.getElementById('modal-title').innerText = 'Tambah Divisi';
    }
    document.getElementById('divisiModal').classList.remove('hidden');
}

function closeDivisiModal() {
    document.getElementById('divisiModal').classList.add('hidden');
}

function editDivisi(item) {
    openDivisiModal(false);
    document.getElementById('divisi-id').value = item.id;
    document.getElementById('modal-title').innerText = 'Edit Divisi';
    document.getElementById('nama_divisi').value = item.nama_divisi;
}

function saveDivisi() {
    const form = document.getElementById('divisi-form');
    const formData = new FormData(form);
    
    fetch(`${basePath}/api/hr/divisi`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeDivisiModal();
            loadDivisi();
            showToast('Data divisi berhasil disimpan', 'success');
        } else {
            showToast(data.message || 'Gagal menyimpan data', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteDivisi(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus divisi ini?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(`${basePath}/api/hr/divisi`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadDivisi();
            showToast('Data divisi berhasil dihapus', 'success');
        } else {
            showToast(data.message || 'Gagal menghapus data', 'error');
        }
    });
}