function initGolonganGajiPage() {
    if (document.getElementById('golongan-gaji-table-body')) {
        loadGolonganGaji();

        const saveBtn = document.getElementById('save-golongan-gaji-btn');
        if (saveBtn) saveBtn.addEventListener('click', saveGolonganGaji);
    }
}

function loadGolonganGaji() {
    const tbody = document.getElementById('golongan-gaji-table-body');

    fetch(`${basePath}/api/hr/golongan-gaji`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = '';
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Belum ada golongan gaji yang dibuat.</td></tr>';
                    return;
                }
                
                const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

                data.data.forEach(item => {
                    const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");
                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.nama_golongan}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">${formatter.format(item.gaji_pokok)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.keterangan || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick='editGolonganGaji(${itemJson})' class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                <button onclick="deleteGolonganGaji(${item.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
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

function openGolonganGajiModal(reset = true) {
    if (reset) {
        document.getElementById('golongan-gaji-form').reset();
        document.getElementById('golongan-gaji-id').value = '';
        document.getElementById('modal-title').innerText = 'Tambah Golongan Gaji';
    }
    document.getElementById('golonganGajiModal').classList.remove('hidden');
}

function closeGolonganGajiModal() {
    document.getElementById('golonganGajiModal').classList.add('hidden');
}

function editGolonganGaji(item) {
    openGolonganGajiModal(false);
    document.getElementById('golongan-gaji-id').value = item.id;
    document.getElementById('modal-title').innerText = 'Edit Golongan Gaji';
    document.getElementById('nama_golongan').value = item.nama_golongan;
    document.getElementById('gaji_pokok').value = parseFloat(item.gaji_pokok);
    document.getElementById('keterangan').value = item.keterangan;
}

function saveGolonganGaji() {
    const form = document.getElementById('golongan-gaji-form');
    const formData = new FormData(form);
    
    fetch(`${basePath}/api/hr/golongan-gaji`, { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeGolonganGajiModal();
            loadGolonganGaji();
            showToast('Data golongan gaji berhasil disimpan', 'success');
        } else {
            showToast(data.message || 'Gagal menyimpan data', 'error');
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteGolonganGaji(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus golongan gaji ini?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(`${basePath}/api/hr/golongan-gaji`, { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadGolonganGaji();
            showToast('Data golongan gaji berhasil dihapus', 'success');
        } else {
            showToast(data.message || 'Gagal menghapus data', 'error');
        }
    });
}