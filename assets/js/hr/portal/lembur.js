function initPortalLemburPage() {
    loadRiwayatLembur();

    const form = document.getElementById('portal-lembur-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            
            // Validasi jam
            const start = formData.get('jam_mulai');
            const end = formData.get('jam_selesai');
            if (start >= end) {
                showToast('Jam selesai harus lebih besar dari jam mulai.', 'error');
                return;
            }

            try {
                const response = await fetch(`${basePath}/api/hr/portal/lembur`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    form.reset();
                    // Reset tanggal ke hari ini
                    form.querySelector('input[name="tanggal"]').value = new Date().toISOString().split('T')[0];
                    loadRiwayatLembur();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error(error);
                showToast('Terjadi kesalahan sistem.', 'error');
            }
        });
    }
}

async function loadRiwayatLembur() {
    const tbody = document.getElementById('portal-lembur-body');
    if (!tbody) return;

    try {
        const response = await fetch(`${basePath}/api/hr/portal/lembur`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => {
                let statusBadge = '';
                switch(item.status) {
                    case 'approved': statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>'; break;
                    case 'rejected': statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>'; break;
                    default: statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>';
                }

                return `
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${new Date(item.tanggal).toLocaleDateString('id-ID')}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">${item.jam_mulai.substring(0,5)} - ${item.jam_selesai.substring(0,5)}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs" title="${item.keterangan}">${item.keterangan}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            ${item.status === 'pending' ? `<button onclick="cancelLembur(${item.id})" class="text-red-600 hover:text-red-800" title="Batalkan"><i class="bi bi-x-circle"></i></button>` : ''}
                        </td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-6 text-gray-500">Belum ada riwayat lembur.</td></tr>';
        }
    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-6 text-red-500">Gagal memuat data.</td></tr>';
    }
}

async function cancelLembur(id) {
    if (!confirm('Batalkan pengajuan lembur ini?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    try {
        const response = await fetch(`${basePath}/api/hr/portal/lembur`, { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            showToast(result.message, 'success');
            loadRiwayatLembur();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error(error);
    }
}