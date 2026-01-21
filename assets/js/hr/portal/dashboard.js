function initPortalDashboardPage() {
    if (document.getElementById('portal-nama-karyawan')) {
        loadPengumumanPortal();

        fetch(`${basePath}/api/hr/portal/dashboard`)
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    const data = res.data;
                    document.getElementById('portal-nama-karyawan').textContent = data.nama_karyawan;
                    document.getElementById('portal-sisa-cuti').textContent = data.sisa_cuti;
                    document.getElementById('portal-kehadiran').textContent = data.kehadiran_bulan_ini;
                    document.getElementById('portal-cuti-pending').textContent = data.cuti_pending;
                } else {
                    showToast(res.message, 'error');
                }
            });
    }
}

async function loadPengumumanPortal() {
    const container = document.getElementById('portal-pengumuman-list');
    if (!container) return;

    try {
        const response = await fetch(`${basePath}/api/hr/pengumuman?action=list_published`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            container.innerHTML = result.data.map(item => `
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5 border-l-4 border-primary">
                    <div class="flex justify-between items-start">
                        <h4 class="font-bold text-gray-900 dark:text-white">${item.judul}</h4>
                        <span class="text-xs text-gray-500 dark:text-gray-400">${formatDate(item.created_at)}</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">${item.isi.replace(/\n/g, '<br>')}</p>
                    ${item.lampiran_file ? `
                        <div class="mt-3">
                            <a href="${basePath}/${item.lampiran_file}" target="_blank" class="text-sm text-blue-600 hover:underline">
                                <i class="bi bi-paperclip"></i> Lihat Lampiran
                            </a>
                        </div>
                    ` : ''}
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 text-center text-gray-500">Tidak ada pengumuman saat ini.</div>';
        }
    } catch (error) {
        container.innerHTML = '<div class="bg-red-100 text-red-700 p-4 rounded-lg text-center">Gagal memuat pengumuman.</div>';
    }
}