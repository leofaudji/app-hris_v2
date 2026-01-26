function initPortalPengajuanCutiPage() {
    const form = document.getElementById('portal-cuti-form');
    const jenisCutiSelect = document.getElementById('portal-jenis-cuti');
    const riwayatBody = document.getElementById('portal-riwayat-cuti-body');
    const sisaCutiDisplay = document.getElementById('sisa-cuti-display');

    async function loadJenisCuti() {
        try {
            const response = await fetch(`${basePath}/api/hr/jenis-cuti`);
            const result = await response.json();
            if (result.success) {
                jenisCutiSelect.innerHTML = '<option value="">Pilih Jenis</option>';
                result.data.forEach(item => {
                    if (item.is_active) {
                        jenisCutiSelect.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.nama_jenis}</option>`);
                    }
                });
            }
        } catch (error) { console.error('Error loading jenis cuti:', error); }
    }

    async function loadRiwayatCuti() {
        riwayatBody.innerHTML = '<tr><td colspan="5" class="text-center py-6 text-gray-500">Memuat riwayat...</td></tr>';
        try {
            const response = await fetch(`${basePath}/api/hr/manajemen-cuti`); // API ini sudah otomatis memfilter by user login
            const result = await response.json();
            if (result.success) {
                if (result.data.length > 0) {
                    riwayatBody.innerHTML = result.data.map(item => {
                        let statusBadge;
                        switch(item.status) {
                            case 'approved': statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>'; break;
                            case 'rejected': statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>'; break;
                            default: statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>';
                        }
                        
                        const lampiranLink = item.lampiran_file 
                            ? `<a href="${basePath}/${item.lampiran_file}" target="_blank" class="text-blue-600 hover:text-blue-800 ml-2" title="Lihat Lampiran"><i class="bi bi-paperclip"></i></a>` 
                            : '';

                        return `
                            <tr class="text-sm">
                                <td class="px-4 py-3">${item.nama_jenis} ${lampiranLink}</td>
                                <td class="px-4 py-3">${formatDate(item.tanggal_mulai)} - ${formatDate(item.tanggal_selesai)}</td>
                                <td class="px-4 py-3 text-center">${item.jumlah_hari} hari</td>
                                <td class="px-4 py-3 text-center">${statusBadge}</td>
                                <td class="px-4 py-3 text-right">
                                    ${item.status === 'pending' ? `<button onclick="cancelCuti(${item.id})" class="text-red-500 hover:text-red-700" title="Batalkan"><i class="bi bi-x-circle"></i></button>` : ''}
                                </td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    riwayatBody.innerHTML = '<tr><td colspan="5" class="text-center py-6 text-gray-500">Belum ada riwayat pengajuan.</td></tr>';
                }
            }
        } catch (error) { console.error('Error loading riwayat cuti:', error); }
    }

    async function loadSisaCuti() {
        try {
            const response = await fetch(`${basePath}/api/hr/manajemen-cuti?action=get_sisa_cuti&tahun=${new Date().getFullYear()}`);
            const result = await response.json();
            if (result.success) {
                sisaCutiDisplay.textContent = result.sisa_jatah;
            }
        } catch (error) { console.error('Error loading sisa cuti:', error); }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        try {
            const response = await fetch(`${basePath}/api/hr/manajemen-cuti`, { method: 'POST', body: formData });
            const result = await response.json();
            showToast(result.message, result.success ? 'success' : 'error');
            if (result.success) {
                form.reset();
                loadRiwayatCuti();
                loadSisaCuti();
            }
        } catch (error) {
            showToast('Terjadi kesalahan jaringan.', 'error');
        }
    });

    window.cancelCuti = async (id) => {
        const confirmed = await Swal.fire({
            title: 'Anda yakin?',
            text: "Anda akan membatalkan pengajuan cuti ini.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, batalkan!',
            cancelButtonText: 'Tidak'
        });

        if (confirmed.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            try {
                const response = await fetch(`${basePath}/api/hr/manajemen-cuti`, { method: 'POST', body: formData });
                const result = await response.json();
                showToast(result.message, result.success ? 'success' : 'error');
                if (result.success) {
                    loadRiwayatCuti();
                    loadSisaCuti();
                }
            } catch (error) { showToast('Terjadi kesalahan jaringan.', 'error'); }
        }
    };

    // Initial Load
    loadJenisCuti();
    loadRiwayatCuti();
    loadSisaCuti();
}