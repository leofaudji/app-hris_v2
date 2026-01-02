function initPortalProfilPage() {
    const container = document.getElementById('profil-content');
    if (container) {
        fetch(`${basePath}/api/portal/profil`)
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    const data = res.data;
                    const contentHtml = `
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-1 text-center">
                                <i class="bi bi-person-circle text-8xl text-gray-400"></i>
                                <h3 class="text-xl font-bold mt-2 text-gray-900 dark:text-white">${data.nama_lengkap}</h3>
                                <p class="text-gray-500 dark:text-gray-400">${data.nip}</p>
                            </div>
                            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div><strong class="block text-gray-500">Jabatan</strong> ${data.nama_jabatan || '-'}</div>
                                <div><strong class="block text-gray-500">Divisi</strong> ${data.nama_divisi || '-'}</div>
                                <div><strong class="block text-gray-500">Kantor</strong> ${data.nama_kantor || '-'}</div>
                                <div><strong class="block text-gray-500">Golongan Gaji</strong> ${data.nama_golongan_gaji || '-'}</div>
                                <div><strong class="block text-gray-500">Tanggal Masuk</strong> ${formatDate(data.tanggal_masuk)}</div>
                                <div><strong class="block text-gray-500">Status Karyawan</strong> <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">${data.status}</span></div>
                                <div class="sm:col-span-2"><strong class="block text-gray-500">NPWP</strong> ${data.npwp || '-'}</div>
                                <div class="sm:col-span-2"><strong class="block text-gray-500">Status PTKP</strong> ${data.status_ptkp || '-'}</div>
                            </div>
                        </div>
                    `;
                    container.innerHTML = contentHtml;
                } else {
                    container.innerHTML = `<p class="text-center text-red-500">${res.message}</p>`;
                }
            });
    }
}