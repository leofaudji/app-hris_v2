function initJabatanPage() {
    if (document.getElementById('jabatan-table-body')) {
        loadJabatan();

        const searchInput = document.getElementById('search-jabatan');
        if (searchInput) searchInput.addEventListener('input', debounce(loadJabatan, 500));

        const saveBtn = document.getElementById('save-jabatan-btn');
        if (saveBtn) saveBtn.addEventListener('click', saveJabatan);
    }
}

function loadJabatan() {
    const search = document.getElementById('search-jabatan').value;
    const tbody = document.getElementById('jabatan-table-body');

    fetch(`${basePath}/api/hr/jabatan?search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = '';
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data jabatan.</td></tr>';
                    return;
                }
                
                const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

                data.data.forEach(item => {
                    const tunjangan = parseFloat(item.tunjangan);
                    
                    // Escape string untuk keamanan
                    const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");

                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.nama_jabatan}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-700 dark:text-gray-300">${formatter.format(tunjangan)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick='editJabatan(${itemJson})' class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                <button onclick="deleteJabatan(${item.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="3" class="px-6 py-4 text-center text-red-500">Gagal memuat data: ${data.message}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center text-red-500">Terjadi kesalahan saat memuat data.</td></tr>';
        });
}

function openJabatanModal(reset = true) {
    if (reset) {
        document.getElementById('jabatan-form').reset();
        document.getElementById('jabatan-id').value = '';
        document.getElementById('jabatan-action').value = 'save';
        document.getElementById('modal-title').innerText = 'Tambah Jabatan';
    }
    document.getElementById('jabatanModal').classList.remove('hidden');
}

function closeJabatanModal() {
    document.getElementById('jabatanModal').classList.add('hidden');
}

function editJabatan(item) {
    openJabatanModal(false);
    document.getElementById('jabatan-id').value = item.id;
    document.getElementById('jabatan-action').value = 'save';
    document.getElementById('modal-title').innerText = 'Edit Jabatan';
    
    document.getElementById('nama_jabatan').value = item.nama_jabatan;
    document.getElementById('tunjangan').value = parseFloat(item.tunjangan);
}

function saveJabatan() {
    const form = document.getElementById('jabatan-form');
    const formData = new FormData(form);
    
    fetch(`${basePath}/api/hr/jabatan`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeJabatanModal();
            loadJabatan();
            showToast('Data jabatan berhasil disimpan', 'success');
        } else {
            showToast(data.message || 'Gagal menyimpan data', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteJabatan(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus jabatan ini?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(`${basePath}/api/hr/jabatan`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadJabatan();
            showToast('Data jabatan berhasil dihapus', 'success');
        } else {
            showToast(data.message || 'Gagal menghapus data', 'error');
        }
    });
}