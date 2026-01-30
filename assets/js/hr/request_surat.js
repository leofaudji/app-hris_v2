function initRequestSuratPage() {
    loadRequestSurat();

    const filterStatus = document.getElementById('filter-status-surat');
    if (filterStatus) {
        filterStatus.addEventListener('change', loadRequestSurat);
    }

    const form = document.getElementById('process-request-form');
    if (form) {
        form.addEventListener('submit', submitProcessRequest);
    }
}

function loadRequestSurat() {
    const tbody = document.getElementById('request-surat-table-body');
    const status = document.getElementById('filter-status-surat').value;
    
    if (!tbody) return;

    fetch(`${basePath}/api/hr/request-surat?status=${status}`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                if (res.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Belum ada request surat.</td></tr>';
                    return;
                }
                tbody.innerHTML = res.data.map(req => {
                    let statusBadge = '';
                    switch(req.status) {
                        case 'pending': statusBadge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>'; break;
                        case 'processed': statusBadge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Diproses</span>'; break;
                        case 'completed': statusBadge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>'; break;
                        case 'rejected': statusBadge = '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>'; break;
                    }

                    const jenisLabel = req.jenis_surat.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    
                    // Escape data untuk JSON string di tombol
                    const reqData = JSON.stringify(req).replace(/"/g, '&quot;');

                    return `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${new Date(req.created_at).toLocaleDateString('id-ID')}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                ${req.nama_lengkap}<br>
                                <span class="text-xs text-gray-500 font-normal">${req.nip}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${jenisLabel}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs" title="${req.keterangan}">${req.keterangan}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="openProcessModal(${reqData})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    <i class="bi bi-pencil-square"></i> Proses
                                </button>
                                ${req.file_path ? `<a href="${basePath}/${req.file_path}" target="_blank" class="ml-2 text-green-600 hover:text-green-900 dark:text-green-400" title="Lihat Surat"><i class="bi bi-file-earmark-pdf"></i></a>` : ''}
                            </td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Gagal memuat data.</td></tr>';
            }
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Terjadi kesalahan sistem.</td></tr>';
        });
}

function openProcessModal(data) {
    document.getElementById('process_request_id').value = data.id;
    document.getElementById('detail_karyawan').textContent = data.nama_lengkap;
    document.getElementById('detail_jenis').textContent = data.jenis_surat.replace(/_/g, ' ').toUpperCase();
    document.getElementById('detail_keterangan').textContent = data.keterangan;
    
    document.getElementById('process_status').value = data.status === 'pending' ? 'processed' : data.status;
    document.getElementById('process_note').value = data.admin_note || '';
    
    toggleUploadField();
    openModal('processRequestModal');
}

function toggleUploadField() {
    const status = document.getElementById('process_status').value;
    const uploadContainer = document.getElementById('upload-surat-container');
    if (status === 'completed') {
        uploadContainer.classList.remove('hidden');
    } else {
        uploadContainer.classList.add('hidden');
    }
}

function submitProcessRequest(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Menyimpan...';

    fetch(`${basePath}/api/hr/request-surat`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeModal('processRequestModal');
            loadRequestSurat();
        } else {
            showToast(data.message || 'Gagal memproses request', 'error');
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
