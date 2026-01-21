async function initPeringatanKontrakPage() {
    loadExpiringContracts();
    loadKaryawanSelect();

    const select = document.getElementById('doc_karyawan_select');
    if (select) {
        select.addEventListener('change', (e) => {
            const id = e.target.value;
            if (id) {
                document.getElementById('document-list-container').classList.remove('hidden');
                document.getElementById('document-placeholder').classList.add('hidden');
                document.getElementById('upload_karyawan_id').value = id;
                loadDocuments(id);
            } else {
                document.getElementById('document-list-container').classList.add('hidden');
                document.getElementById('document-placeholder').classList.remove('hidden');
            }
        });
    }

    const form = document.getElementById('upload-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const response = await fetch(`${basePath}/api/hr/dokumen`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    Swal.fire('Berhasil', result.message, 'success');
                    closeModal('uploadModal');
                    e.target.reset();
                    // Reset hidden ID if needed, but usually we keep it for next upload
                    document.getElementById('upload_karyawan_id').value = formData.get('karyawan_id'); 
                    loadDocuments(formData.get('karyawan_id'));
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Gagal mengupload dokumen.', 'error');
            }
        });
    }
}

async function loadExpiringContracts() {
    const tbody = document.getElementById('contract-alert-body');
    if (!tbody) return;

    try {
        const response = await fetch(`${basePath}/api/hr/dokumen?action=expiring_contracts`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => {
                const sisa = parseInt(item.sisa_hari);
                let statusClass = 'text-yellow-600';
                let statusText = `${sisa} hari lagi`;
                
                if (sisa < 0) {
                    statusClass = 'text-red-600 font-bold';
                    statusText = `Expired (${Math.abs(sisa)} hari lalu)`;
                } else if (sisa <= 7) {
                    statusClass = 'text-red-500 font-bold';
                }

                return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            ${item.nama_lengkap}<br><span class="text-xs text-gray-500">${item.nip}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            ${item.nama_jabatan}<br><span class="text-xs text-gray-400">${item.nama_divisi}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${formatDate(item.tanggal_berakhir_kontrak)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm ${statusClass}">
                            ${statusText}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="selectKaryawanForDoc(${item.id})" class="text-blue-600 hover:text-blue-900">Lihat Dokumen</button>
                        </td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">Tidak ada kontrak yang akan berakhir dalam 30 hari ke depan.</td></tr>';
        }
    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-red-500">Gagal memuat data.</td></tr>';
    }
}

async function loadKaryawanSelect() {
    const select = document.getElementById('doc_karyawan_select');
    if (!select) return;
    
    try {
        const response = await fetch(`${basePath}/api/hr/karyawan?status=aktif`);
        const result = await response.json();
        if (result.success) {
            result.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = `${item.nama_lengkap} - ${item.nip}`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error(error);
    }
}

async function loadDocuments(karyawanId) {
    const tbody = document.getElementById('document-table-body');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Memuat...</td></tr>';

    try {
        const response = await fetch(`${basePath}/api/hr/dokumen?action=list_documents&karyawan_id=${karyawanId}`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => `
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${item.jenis_dokumen}</td>
                    <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs" title="${item.nama_file}">
                        <a href="${basePath}/${item.path_file}" target="_blank" class="text-blue-600 hover:underline">${item.nama_file}</a>
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">${formatDate(item.created_at)}</td>
                    <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">${item.tanggal_kadaluarsa ? formatDate(item.tanggal_kadaluarsa) : '-'}</td>
                    <td class="px-4 py-2 text-right text-sm">
                        <button onclick="deleteDocument(${item.id}, ${karyawanId})" class="text-red-600 hover:text-red-900"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">Belum ada dokumen yang diupload.</td></tr>';
        }
    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-red-500">Gagal memuat dokumen.</td></tr>';
    }
}

async function deleteDocument(id, karyawanId) {
    if (!confirm('Hapus dokumen ini?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    try {
        const response = await fetch(`${basePath}/api/hr/dokumen`, { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            showToast('Dokumen dihapus', 'success');
            loadDocuments(karyawanId);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error(error);
    }
}

function selectKaryawanForDoc(id) {
    const select = document.getElementById('doc_karyawan_select');
    if (select) {
        select.value = id;
        select.dispatchEvent(new Event('change'));
        // Scroll to document section
        select.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}