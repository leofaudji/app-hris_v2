async function initLemburPage() {
    loadLembur();
    
    const karyawanSelect = document.getElementById('lembur_karyawan_id');
    if (karyawanSelect) {
        loadKaryawanLembur();
    }

    document.getElementById('filter-bulan-lembur').addEventListener('change', loadLembur);
    document.getElementById('filter-tahun-lembur').addEventListener('change', loadLembur);

    const form = document.getElementById('lembur-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const response = await fetch(`${basePath}/api/hr/lembur`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    Swal.fire('Berhasil', result.message, 'success');
                    closeModal('lemburModal');
                    e.target.reset();
                    loadLembur();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                console.error(error);
            }
        });
    }

    const tbody = document.getElementById('lembur-table-body');
    if (tbody) {
        tbody.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-action]');
            if (btn) {
                const action = btn.dataset.action;
                const id = btn.dataset.id;
                if (action === 'approve') updateStatusLembur(id, 'approved');
                else if (action === 'reject') updateStatusLembur(id, 'rejected');
                else if (action === 'delete') deleteLembur(id);
            }
        });
    }
}

async function loadLembur() {
    const tbody = document.getElementById('lembur-table-body');
    const bulan = document.getElementById('filter-bulan-lembur').value;
    const tahun = document.getElementById('filter-tahun-lembur').value;
    
    if (!tbody) return;
    
    try {
        const response = await fetch(`${basePath}/api/hr/lembur?action=list&bulan=${bulan}&tahun=${tahun}`);
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            const isAdmin = (typeof userRole !== 'undefined' && (userRole === 'admin' || userRole === 'Admin'));

            tbody.innerHTML = result.data.map(item => {
                let actionButtons = '';
                if (item.status === 'pending') {
                    if (isAdmin) {
                        actionButtons = `
                            <button data-action="approve" data-id="${item.id}" class="text-green-600 hover:text-green-900 mr-2" title="Setujui"><i class="bi bi-check-lg"></i></button>
                            <button data-action="reject" data-id="${item.id}" class="text-red-600 hover:text-red-900 mr-2" title="Tolak"><i class="bi bi-x-lg"></i></button>
                        `;
                    }
                    actionButtons += `<button data-action="delete" data-id="${item.id}" class="text-gray-600 hover:text-gray-900" title="Hapus"><i class="bi bi-trash"></i></button>`;
                } else if (item.status === 'approved') {
                    actionButtons += `<button onclick="cetakSPL(${item.id})" class="text-blue-600 hover:text-blue-900 mr-2" title="Cetak SPL"><i class="bi bi-printer"></i></button>`;
                }

                return `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${formatDate(item.tanggal)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        ${item.nama_lengkap}<br><span class="text-xs text-gray-500">${item.nip}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">${item.jam_mulai.substring(0,5)} - ${item.jam_selesai.substring(0,5)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">${item.durasi}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">${item.keterangan}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            ${item.status === 'approved' ? 'bg-green-100 text-green-800' : 
                              (item.status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')}">
                            ${item.status.toUpperCase()}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        ${actionButtons}
                    </td>
                </tr>
            `}).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-gray-500">Belum ada data lembur.</td></tr>';
        }
    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-red-500">Gagal memuat data.</td></tr>';
    }
}

async function loadKaryawanLembur() {
    const select = document.getElementById('lembur_karyawan_id');
    if (!select) return;
    const response = await fetch(`${basePath}/api/hr/karyawan`); 
    const result = await response.json();
    if (result.success) {
        result.data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = `${item.nama_lengkap} - ${item.nip}`;
            select.appendChild(option);
        });
    }
}

async function updateStatusLembur(id, status) {
    if (!confirm(`Ubah status menjadi ${status}?`)) return;
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('id', id);
    formData.append('status', status);
    await fetch(`${basePath}/api/hr/lembur`, { method: 'POST', body: formData });
    loadLembur();
}

function cetakSPL(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${basePath}/api/pdf`;
    form.target = '_blank';

    const params = {
        report: 'spl',
        id: id
    };

    for (const key in params) {
        const hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = key;
        hiddenField.value = params[key];
        form.appendChild(hiddenField);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

async function deleteLembur(id) {
    if (!confirm('Hapus pengajuan lembur ini?')) return;
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    await fetch(`${basePath}/api/hr/lembur`, { method: 'POST', body: formData });
    loadLembur();
}