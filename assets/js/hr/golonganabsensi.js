function initGolonganAbsensiPage() {
    if (document.getElementById('golongan-table-body')) {
        loadGolongan();

        const searchInput = document.getElementById('search-golongan');
        if (searchInput) searchInput.addEventListener('input', debounce(loadGolongan, 500));

        const saveBtn = document.getElementById('save-golongan-btn');
        if (saveBtn) saveBtn.addEventListener('click', saveGolongan);
    }
}

function loadGolongan() {
    const search = document.getElementById('search-golongan').value;
    const tbody = document.getElementById('golongan-table-body');

    fetch(`${basePath}/api/hr/golongan-absensi?search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = '';
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data golongan absensi.</td></tr>';
                    return;
                }
                
                data.data.forEach(item => {
                    const statusBadge = item.is_active == 1 
                        ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Aktif</span>'
                        : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Non-Aktif</span>';

                    const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");
                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.nama_golongan}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick='editGolongan(${itemJson})' class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                <button onclick="deleteGolongan(${item.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
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

function openGolonganModal(reset = true) {
    if (reset) {
        document.getElementById('golongan-form').reset();
        document.getElementById('golongan-id').value = '';
        document.getElementById('modal-title').innerText = 'Tambah Golongan';
    }
    document.getElementById('golonganModal').classList.remove('hidden');
}

function closeGolonganModal() {
    document.getElementById('golonganModal').classList.add('hidden');
}

function editGolongan(item) {
    openGolonganModal(false);
    document.getElementById('golongan-id').value = item.id;
    document.getElementById('modal-title').innerText = 'Edit Golongan';
    document.getElementById('nama_golongan').value = item.nama_golongan;
    document.getElementById('is_active').value = item.is_active;
}

function saveGolongan() {
    const form = document.getElementById('golongan-form');
    const formData = new FormData(form);
    
    fetch(`${basePath}/api/hr/golongan-absensi`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeGolonganModal();
            loadGolongan();
            showToast('Data golongan berhasil disimpan', 'success');
        } else {
            showToast(data.message || 'Gagal menyimpan data', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteGolongan(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus golongan ini?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(`${basePath}/api/hr/golongan-absensi`, { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadGolongan();
            showToast('Data golongan berhasil dihapus', 'success');
        } else {
            showToast(data.message || 'Gagal menghapus data', 'error');
        }
    });
}