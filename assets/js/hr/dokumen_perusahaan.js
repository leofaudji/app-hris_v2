function initDokumenPerusahaanPage() {
    loadDokumen();
}

function loadDokumen() {
    const tbody = document.getElementById('dokumen-perusahaan-table-body');
    if (!tbody) return;

    fetch(`${basePath}/api/hr/dokumen-perusahaan`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                if (res.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Belum ada dokumen.</td></tr>';
                    return;
                }
                tbody.innerHTML = res.data.map(doc => `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${doc.judul}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">${doc.kategori}</span></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${new Date(doc.created_at).toLocaleDateString('id-ID')}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="${basePath}/${doc.file_path}" target="_blank" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3"><i class="bi bi-download"></i> Unduh</a>
                            <button onclick="deleteDokumen(${doc.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"><i class="bi bi-trash"></i> Hapus</button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Gagal memuat data.</td></tr>';
            }
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Terjadi kesalahan sistem.</td></tr>';
        });
}

function openUploadModal() {
    document.getElementById('upload-form').reset();
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
}

function submitUpload(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'upload');

    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Mengupload...';

    fetch(`${basePath}/api/hr/dokumen-perusahaan`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeUploadModal();
            loadDokumen();
        } else {
            showToast(data.message || 'Gagal mengupload dokumen', 'error');
        }
    })
    .catch(error => {
        console.error(error);
        showToast('Terjadi kesalahan sistem', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function deleteDokumen(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(`${basePath}/api/hr/dokumen-perusahaan`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            loadDokumen();
        } else {
            showToast(data.message || 'Gagal menghapus dokumen', 'error');
        }
    })
    .catch(error => {
        console.error(error);
        showToast('Terjadi kesalahan sistem', 'error');
    });
}