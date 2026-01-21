async function initPengumumanPage() {
    loadPengumuman();

    const form = document.getElementById('pengumuman-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const response = await fetch(`${basePath}/api/hr/pengumuman`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    Swal.fire('Berhasil', result.message, 'success');
                    closeModal('pengumumanModal');
                    loadPengumuman();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                console.error(error);
            }
        });
    }
}

async function loadPengumuman() {
    const tbody = document.getElementById('pengumuman-table-body');
    try {
        const response = await fetch(`${basePath}/api/hr/pengumuman?action=list`);
        const result = await response.json();
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => {
                const isPublished = item.is_published == 1;
                const statusBadge = isPublished
                    ? `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Published</span>`
                    : `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Draft</span>`;
                
                const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");

                return `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.judul}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.created_by_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${formatDate(item.created_at)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick="togglePublish(${item.id}, ${isPublished ? 0 : 1})" class="text-blue-600 hover:text-blue-900 mr-2" title="${isPublished ? 'Unpublish' : 'Publish'}"><i class="bi bi-send-fill"></i></button>
                        <button onclick='editPengumuman(${itemJson})' class="text-indigo-600 hover:text-indigo-900 mr-2" title="Edit"><i class="bi bi-pencil-fill"></i></button>
                        <button onclick="deletePengumuman(${item.id})" class="text-red-600 hover:text-red-900" title="Hapus"><i class="bi bi-trash-fill"></i></button>
                    </td>
                </tr>
            `}).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">Belum ada pengumuman.</td></tr>';
        }
    } catch (error) {
        console.error(error);
    }
}

function openPengumumanModal() {
    document.getElementById('pengumuman-form').reset();
    document.getElementById('pengumuman_id').value = '';
    document.getElementById('modal-title').textContent = 'Buat Pengumuman Baru';
    document.getElementById('current-attachment').innerHTML = '';
    openModal('pengumumanModal');
}

function editPengumuman(item) {
    document.getElementById('pengumuman_id').value = item.id;
    document.getElementById('judul').value = item.judul;
    document.getElementById('isi').value = item.isi;
    document.getElementById('is_published').checked = item.is_published == 1;
    
    const attachmentInfo = document.getElementById('current-attachment');
    if (item.lampiran_file) {
        attachmentInfo.innerHTML = `Lampiran saat ini: <a href="${basePath}/${item.lampiran_file}" target="_blank" class="text-blue-500 hover:underline">${item.lampiran_file.split('/').pop()}</a>. Kosongkan jika tidak ingin mengubah.`;
    } else {
        attachmentInfo.innerHTML = '';
    }

    document.getElementById('modal-title').textContent = 'Edit Pengumuman';
    openModal('pengumumanModal');
}

async function deletePengumuman(id) {
    if (!confirm('Hapus pengumuman ini?')) return;
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    await fetch(`${basePath}/api/hr/pengumuman`, { method: 'POST', body: formData });
    loadPengumuman();
}

async function togglePublish(id, status) {
    const actionText = status === 1 ? 'mempublikasikan' : 'menyimpan sebagai draft';
    if (!confirm(`Anda yakin ingin ${actionText} pengumuman ini?`)) return;

    const formData = new FormData();
    formData.append('action', 'toggle_publish');
    formData.append('id', id);
    formData.append('status', status);
    
    const response = await fetch(`${basePath}/api/hr/pengumuman`, { method: 'POST', body: formData });
    const result = await response.json();
    if (result.success) {
        showToast(result.message, 'success');
        loadPengumuman();
    } else {
        showToast(result.message, 'error');
    }
}