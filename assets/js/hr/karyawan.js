function initKaryawanPage() {
    if (document.getElementById('karyawan-table-body')) {
        loadKaryawan();
        loadJabatanOptions();
        loadDivisiOptions();
        loadJadwalKerjaOptions();
        loadKantorOptions();
        loadGolonganGajiOptions();

        // Event Listeners
        const searchInput = document.getElementById('search-karyawan');
        if (searchInput) searchInput.addEventListener('input', debounce(loadKaryawan, 500));

        const filterStatus = document.getElementById('filter-status');
        if (filterStatus) filterStatus.addEventListener('change', loadKaryawan);

        const saveBtn = document.getElementById('save-karyawan-btn');
        if (saveBtn) saveBtn.addEventListener('click', saveKaryawan);
    }
}

function loadKaryawan() {
    const search = document.getElementById('search-karyawan').value;
    const status = document.getElementById('filter-status').value;
    const tbody = document.getElementById('karyawan-table-body');

    // Menggunakan basePath dari global variable (header.php)
    fetch(`${basePath}/api/hr/karyawan?search=${encodeURIComponent(search)}&status=${status}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = '';
                if (data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="10" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data karyawan.</td></tr>';
                    return;
                }
                data.data.forEach(item => {
                    const statusBadge = item.status === 'aktif'
                        ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Aktif</span>'
                        : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Nonaktif</span>';

                    // Escape string untuk keamanan (mencegah XSS sederhana dan error kutip)
                    const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");

                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.nip}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.nama_lengkap}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.nama_divisi || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.nama_kantor || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.nama_golongan_gaji || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.nama_jadwal || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.nama_jabatan || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.tanggal_masuk}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${statusBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick='editKaryawan(${itemJson})' class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</button>
                                <button onclick="deleteKaryawan(${item.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="9" class="px-6 py-4 text-center text-red-500">Gagal memuat data: ${data.message}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="10" class="px-6 py-4 text-center text-red-500">Terjadi kesalahan saat memuat data.</td></tr>';
        });
}

function loadJabatanOptions() {
    fetch(`${basePath}/api/hr/jabatan`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('jabatan_id');
                if (select) {
                    select.innerHTML = '<option value="">Pilih Jabatan</option>';
                    data.data.forEach(jabatan => {
                        select.insertAdjacentHTML('beforeend', `<option value="${jabatan.id}">${jabatan.nama_jabatan}</option>`);
                    });
                }
            }
        });
}

function loadDivisiOptions() {
    fetch(`${basePath}/api/hr/divisi`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('divisi_id');
                if (select) {
                    select.innerHTML = '<option value="">Pilih Divisi</option>';
                    data.data.forEach(divisi => {
                        select.insertAdjacentHTML('beforeend', `<option value="${divisi.id}">${divisi.nama_divisi}</option>`);
                    });
                }
            }
        });
}

function loadJadwalKerjaOptions() {
    fetch(`${basePath}/api/hr/jadwal-kerja`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('jadwal_kerja_id');
                if (select) {
                    select.innerHTML = '<option value="">Pilih Jadwal</option>';
                    data.data.forEach(jadwal => {
                        select.insertAdjacentHTML('beforeend', `<option value="${jadwal.id}">${jadwal.nama_jadwal} (${jadwal.jam_masuk} - ${jadwal.jam_pulang})</option>`);
                    });
                }
            }
        });
}

function loadKantorOptions() {
    fetch(`${basePath}/api/hr/kantor`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('kantor_id');
                if (select) {
                    select.innerHTML = '<option value="">Pilih Kantor</option>';
                    data.data.forEach(kantor => {
                        select.insertAdjacentHTML('beforeend', `<option value="${kantor.id}">${kantor.nama_kantor} (${kantor.jenis_kantor})</option>`);
                    });
                }
            }
        });
}

function loadGolonganGajiOptions() {
    fetch(`${basePath}/api/hr/golongan-gaji`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('golongan_gaji_id');
                if (select) {
                    select.innerHTML = '<option value="">Pilih Golongan Gaji</option>';
                    data.data.forEach(golongan => {
                        select.insertAdjacentHTML('beforeend', `<option value="${golongan.id}">${golongan.nama_golongan}</option>`);
                    });
                }
            }
        });
}

function openKaryawanModal(reset = true) {
    if (reset) {
        document.getElementById('karyawan-form').reset();
        document.getElementById('karyawan-id').value = '';
        document.getElementById('karyawan-action').value = 'add';
        document.getElementById('modal-title').innerText = 'Tambah Karyawan';
    }
    document.getElementById('karyawanModal').classList.remove('hidden');
}

function closeKaryawanModal() {
    document.getElementById('karyawanModal').classList.add('hidden');
}

function editKaryawan(item) {
    openKaryawanModal(false);
    document.getElementById('karyawan-id').value = item.id;
    document.getElementById('karyawan-action').value = 'edit';
    document.getElementById('modal-title').innerText = 'Edit Karyawan';

    document.getElementById('nip').value = item.nip;
    document.getElementById('nama_lengkap').value = item.nama_lengkap;
    document.getElementById('jabatan_id').value = item.jabatan_id;
    document.getElementById('tanggal_masuk').value = item.tanggal_masuk;
    document.getElementById('tanggal_berakhir_kontrak').value = item.tanggal_berakhir_kontrak || '';
    document.getElementById('status').value = item.status;
    document.getElementById('npwp').value = item.npwp || '';
    document.getElementById('status_ptkp').value = item.status_ptkp || 'TK/0';
    document.getElementById('ikut_bpjs_kes').checked = item.ikut_bpjs_kes == 1;
    document.getElementById('ikut_bpjs_tk').checked = item.ikut_bpjs_tk == 1;
}

function saveKaryawan() {
    const form = document.getElementById('karyawan-form');
    const formData = new FormData(form);

    fetch(`${basePath}/api/hr/karyawan`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeKaryawanModal();
                loadKaryawan();
                alert('Data berhasil disimpan');
            } else {
                alert('Gagal menyimpan: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function deleteKaryawan(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus data karyawan ini?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch(`${basePath}/api/hr/karyawan`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadKaryawan();
            } else {
                alert('Gagal menghapus: ' + data.message);
            }
        });
}