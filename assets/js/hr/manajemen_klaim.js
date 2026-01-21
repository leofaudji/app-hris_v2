async function initManajemenKlaimPage() {
    loadKlaim();
    loadJenisKlaim();
    
    // Load karyawan hanya jika elemen select ada (hanya untuk admin)
    const karyawanSelect = document.getElementById('karyawan_id');
    if (karyawanSelect) {
        loadKaryawan();
    }

    const form = document.getElementById('klaim-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const response = await fetch(`${basePath}/api/hr/klaim`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    Swal.fire('Berhasil', result.message, 'success');
                    closeModal('klaimModal');
                    e.target.reset();
                    loadKlaim();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                console.error(error);
            }
        });
    }

    // Event delegation untuk tombol update status (Approve/Reject/Bayar)
    const tbody = document.getElementById('klaim-table-body');
    if (tbody) {
        tbody.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-action="update-status"]');
            if (btn) {
                const id = btn.dataset.id;
                const status = btn.dataset.status;
                updateStatus(id, status);
            }
        });
    }
}

async function loadKlaim() {
    const tbody = document.getElementById('klaim-table-body');
    if (!tbody) return;
    
    try {
        const response = await fetch(`${basePath}/api/hr/klaim?action=list`);
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            // Cek apakah user adalah admin berdasarkan variabel global userRole dari header.php
            const isAdmin = (typeof userRole !== 'undefined' && (userRole === 'admin' || userRole === 'Admin'));

            tbody.innerHTML = result.data.map(item => {
                let actionButtons = '';
                if (isAdmin) {
                    if (item.status === 'pending') {
                        actionButtons = `
                            <button data-action="update-status" data-id="${item.id}" data-status="approved" class="text-green-600 hover:text-green-900 mr-2">Approve</button>
                            <button data-action="update-status" data-id="${item.id}" data-status="rejected" class="text-red-600 hover:text-red-900">Reject</button>
                        `;
                    } else if (item.status === 'approved') {
                        actionButtons = `<button data-action="update-status" data-id="${item.id}" data-status="paid" class="text-blue-600 hover:text-blue-900">Bayar</button>`;
                    }
                }

                return `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.tanggal_klaim}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        ${item.nama_lengkap}<br><span class="text-xs text-gray-500">${item.nip}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.jenis_klaim}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">${item.keterangan}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">Rp ${new Intl.NumberFormat('id-ID').format(item.jumlah)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            ${item.status === 'approved' ? 'bg-green-100 text-green-800' : 
                              (item.status === 'rejected' ? 'bg-red-100 text-red-800' : 
                              (item.status === 'paid' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'))}">
                            ${item.status.toUpperCase()}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        ${actionButtons}
                    </td>
                </tr>
            `}).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-gray-500">Belum ada data klaim.</td></tr>';
        }
    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-red-500">Gagal memuat data.</td></tr>';
    }
}

async function loadJenisKlaim() {
    const select = document.getElementById('jenis_klaim_id');
    if (!select) return;
    const response = await fetch(`${basePath}/api/hr/klaim?action=get_types`);
    const result = await response.json();
    if (result.success) {
        select.innerHTML = result.data.map(item => `<option value="${item.id}">${item.nama_jenis} (Max: ${item.max_plafon > 0 ? item.max_plafon : 'Unlimited'})</option>`).join('');
    }
}

async function loadKaryawan() {
    const select = document.getElementById('karyawan_id');
    if (!select) return;
    const response = await fetch(`${basePath}/api/hr/karyawan`); 
    const result = await response.json();
    if (result.success) {
        // Simpan opsi default jika ada
        const defaultOption = select.querySelector('option[value=""]');
        select.innerHTML = '';
        if(defaultOption) select.appendChild(defaultOption);
        
        result.data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = `${item.nama_lengkap} - ${item.nip}`;
            select.appendChild(option);
        });
    }
}

async function updateStatus(id, status) {
    if (!confirm(`Ubah status menjadi ${status}?`)) return;
    
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('id', id);
    formData.append('status', status);

    try {
        const response = await fetch(`${basePath}/api/hr/klaim`, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            Swal.fire('Berhasil', result.message, 'success');
            loadKlaim();
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    } catch (error) {
        console.error(error);
    }
}
