function initPortalKlaimPage() {
    const form = document.getElementById('portal-klaim-form');
    const jenisKlaimSelect = document.getElementById('portal-jenis-klaim');
    const riwayatBody = document.getElementById('portal-riwayat-klaim-body');

    async function loadJenisKlaim() {
        try {
            const response = await fetch(`${basePath}/api/hr/klaim?action=get_types`);
            const result = await response.json();
            if (result.success) {
                jenisKlaimSelect.innerHTML = '<option value="">Pilih Jenis</option>';
                result.data.forEach(item => {
                    let text = item.nama_jenis;
                    if (item.max_plafon > 0) {
                        text += ` (Plafon: ${new Intl.NumberFormat('id-ID').format(item.max_plafon)})`;
                    }
                    jenisKlaimSelect.insertAdjacentHTML('beforeend', `<option value="${item.id}">${text}</option>`);
                });
            }
        } catch (error) { console.error('Error loading jenis klaim:', error); }
    }

    async function loadRiwayatKlaim() {
        riwayatBody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-gray-500">Memuat riwayat...</td></tr>';
        try {
            // API klaim sudah otomatis memfilter by user login jika bukan admin
            const response = await fetch(`${basePath}/api/hr/klaim`); 
            const result = await response.json();
            if (result.success) {
                if (result.data.length > 0) {
                    riwayatBody.innerHTML = result.data.map(item => {
                        let statusBadge;
                        switch(item.status) {
                            case 'approved': statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>'; break;
                            case 'rejected': statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>'; break;
                            case 'paid': statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Dibayar</span>'; break;
                            default: statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>';
                        }

                        const buktiLink = item.bukti_file 
                            ? `<a href="${basePath}/${item.bukti_file}" target="_blank" class="text-blue-600 hover:text-blue-800 ml-2" title="Lihat Bukti"><i class="bi bi-paperclip"></i></a>` 
                            : '';

                        return `
                            <tr class="text-sm">
                                <td class="px-4 py-3">${item.jenis_klaim} ${buktiLink}</td>
                                <td class="px-4 py-3">${formatDate(item.tanggal_klaim)}</td>
                                <td class="px-4 py-3 text-right font-medium">${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(item.jumlah)}</td>
                                <td class="px-4 py-3 text-center">${statusBadge}</td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    riwayatBody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-gray-500">Belum ada riwayat klaim.</td></tr>';
                }
            }
        } catch (error) { console.error('Error loading riwayat klaim:', error); }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const saveBtn = form.querySelector('button[type="submit"]');
        const originalBtnHtml = saveBtn.innerHTML;

        saveBtn.disabled = true;
        saveBtn.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mengajukan...`;

        try {
            const response = await fetch(`${basePath}/api/hr/klaim`, { method: 'POST', body: formData });
            const result = await response.json();
            showToast(result.message, result.success ? 'success' : 'error');
            if (result.success) {
                form.reset();
                loadRiwayatKlaim();
            }
        } catch (error) {
            showToast('Terjadi kesalahan jaringan.', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalBtnHtml;
        }
    });

    // Initial Load
    loadJenisKlaim();
    loadRiwayatKlaim();
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}