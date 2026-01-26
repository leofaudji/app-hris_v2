function initKaryawanPage() {
    if (document.getElementById('karyawan-table-body')) {
        loadKaryawan();
        loadJabatanOptions();
        loadDivisiOptions();
        loadAtasanOptions();
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

    const offboardingForm = document.getElementById('initiate-offboarding-form');
    if (offboardingForm) {
        offboardingForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const response = await fetch(`${basePath}/api/hr/offboarding`, { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    Swal.fire('Berhasil', result.message, 'success');
                    closeModal('initiateOffboardingModal');
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) { console.error(error); }
        });
    }

    const probationForm = document.getElementById('probation-form');
    if (probationForm) {
        probationForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const response = await fetch(`${basePath}/api/hr/karyawan`, { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    Swal.fire('Berhasil', result.message, 'success');
                    closeModal('probationModal');
                    loadKaryawan(); // Reload untuk update status jika berubah
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) { console.error(error); }
        });
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
                    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data karyawan.</td></tr>';
                    return;
                }
                data.data.forEach(item => {
                    const statusBadge = item.status === 'aktif'
                        ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Aktif</span>'
                        : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Nonaktif</span>';
                    
                    const probationBadge = item.status === 'probation' 
                        ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Probation</span>' 
                        : '';

                    // Escape string untuk keamanan (mencegah XSS sederhana dan error kutip)
                    const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");

                    const row = `
                        <tr id="karyawan-${item.id}" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${item.nip}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800 dark:text-gray-200">${item.nama_lengkap}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <div class="font-medium text-gray-900 dark:text-white">${item.nama_jabatan || '-'}</div>
                                <div class="text-xs text-gray-500 mb-1">${item.nama_divisi || '-'} &bull; ${item.nama_kantor || '-'}</div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 mr-1" title="Golongan Gaji"><i class="bi bi-cash-coin mr-1"></i> ${item.nama_golongan_gaji || '-'}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300" title="Tanggal Masuk"><i class="bi bi-calendar-event mr-1"></i> ${formatDate(item.tanggal_masuk)}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.nama_jadwal || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">${item.status === 'probation' ? probationBadge : statusBadge}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <button onclick='editKaryawan(${itemJson})' class="p-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-md transition-colors" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                    <button onclick="deleteKaryawan(${item.id})" class="p-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-md transition-colors" title="Hapus"><i class="bi bi-trash"></i></button>
                                ${item.status === 'aktif' ? 
                                    `<button onclick="initiateOffboarding(${item.id}, '${item.nama_lengkap}')" class="p-1.5 bg-orange-50 text-orange-600 hover:bg-orange-100 rounded-md transition-colors" title="Mulai Offboarding"><i class="bi bi-person-dash"></i></button>` 
                                    : ''}
                                ${item.status === 'probation' ? 
                                    `<button onclick="evaluateProbation(${item.id}, '${item.nama_lengkap}')" class="p-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-md transition-colors" title="Evaluasi Probation"><i class="bi bi-clipboard-check"></i></button>` 
                                    : ''}
                                ${item.latest_probation_evaluation_id ? 
                                    `<button onclick="cetakEvaluasiProbation(${item.latest_probation_evaluation_id})" class="p-1.5 bg-green-50 text-green-600 hover:bg-green-100 rounded-md transition-colors" title="Cetak Evaluasi"><i class="bi bi-printer"></i></button>` 
                                    : ''}
                                ${item.probation_history_count > 0 ? 
                                    `<button onclick="viewProbationHistory(${item.id}, '${item.nama_lengkap}')" class="p-1.5 bg-purple-50 text-purple-600 hover:bg-purple-100 rounded-md transition-colors" title="Riwayat Evaluasi"><i class="bi bi-clock-history"></i></button>` 
                                    : ''}
                                ${item.salary_history_count > 0 ? 
                                    `<button onclick="viewSalaryHistory(${item.id}, '${item.nama_lengkap}')" class="p-1.5 bg-teal-50 text-teal-600 hover:bg-teal-100 rounded-md transition-colors" title="Riwayat Gaji"><i class="bi bi-cash-stack"></i></button>` 
                                    : ''}
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Gagal memuat data: ${data.message}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Terjadi kesalahan saat memuat data.</td></tr>';
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

function loadAtasanOptions() {
    fetch(`${basePath}/api/hr/karyawan?status=aktif`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('atasan_id');
                if (select) {
                    select.innerHTML = '<option value="">Pilih Atasan</option>';
                    data.data.forEach(karyawan => {
                        select.insertAdjacentHTML('beforeend', `<option value="${karyawan.id}">${karyawan.nama_lengkap} - ${karyawan.nama_jabatan || ''}</option>`);
                    });
                }
            }
        });
}

function initiateOffboarding(id, nama) {
    document.getElementById('offboarding_karyawan_id').value = id;
    document.getElementById('offboarding_karyawan_nama').textContent = nama;
    openModal('initiateOffboardingModal');
}

function evaluateProbation(id, nama) {
    document.getElementById('probation_karyawan_id').value = id;
    document.getElementById('probation_karyawan_nama').textContent = nama;
    openModal('probationModal');
}

function cetakEvaluasiProbation(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${basePath}/api/pdf`;
    form.target = '_blank';

    const params = { report: 'evaluasi-probation', id: id };
    params.csrf_token = getCsrfToken(); // Menggunakan helper function
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

async function viewProbationHistory(id, nama) {
    document.getElementById('history_karyawan_nama').textContent = nama;
    const thead = document.getElementById('probation-history-head');
    const tbody = document.getElementById('probation-history-body');
    
    thead.innerHTML = `
        <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Skor Teknis</th>
            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Skor Budaya</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rekomendasi</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Penilai</th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
        </tr>
    `;
    tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-4 text-center text-gray-500">Memuat riwayat...</td></tr>';
    
    openModal('probationHistoryModal');

    const formData = new FormData();
    formData.append('action', 'get_probation_history');
    formData.append('karyawan_id', id);

    try {
        const response = await fetch(`${basePath}/api/hr/karyawan`, { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => {
                let rekText = item.rekomendasi.replace('_', ' ').toUpperCase();
                let rekClass = item.rekomendasi === 'angkat_tetap' ? 'text-green-600' : (item.rekomendasi === 'terminasi' ? 'text-red-600' : 'text-yellow-600');
                
                return `
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${formatDate(item.tanggal_evaluasi)}</td>
                        <td class="px-4 py-2 text-sm text-center text-gray-900 dark:text-white">${item.skor_teknis}</td>
                        <td class="px-4 py-2 text-sm text-center text-gray-900 dark:text-white">${item.skor_budaya}</td>
                        <td class="px-4 py-2 text-sm font-medium ${rekClass}">${rekText}</td>
                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">${item.nama_penilai || '-'}</td>
                        <td class="px-4 py-2 text-sm text-right">
                            <button onclick="cetakEvaluasiProbation(${item.id})" class="text-blue-600 hover:text-blue-900" title="Cetak PDF"><i class="bi bi-printer"></i></button>
                        </td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-4 text-center text-gray-500">Tidak ada riwayat evaluasi.</td></tr>';
        }
    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-4 text-center text-red-500">Gagal memuat data.</td></tr>';
    }
}

async function viewSalaryHistory(id, nama) {
    document.getElementById('salary_history_karyawan_nama').textContent = nama;
    const thead = document.getElementById('salary-history-head');
    const tbody = document.getElementById('salary-history-body');
    
    thead.innerHTML = `
        <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Gaji Lama</th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Gaji Baru</th>
            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Kenaikan</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
        </tr>
    `;
    tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Memuat riwayat...</td></tr>';
    
    openModal('salaryHistoryModal');

    const formData = new FormData();
    formData.append('action', 'get_salary_history');
    formData.append('karyawan_id', id);

    try {
        const response = await fetch(`${basePath}/api/hr/karyawan`, { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => {
                const diff = parseFloat(item.gaji_baru) - parseFloat(item.gaji_lama);
                const diffClass = diff >= 0 ? 'text-green-600' : 'text-red-600';
                const diffIcon = diff >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
                
                return `
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${formatDate(item.tanggal_perubahan)}</td>
                        <td class="px-4 py-2 text-sm text-right text-gray-500 dark:text-gray-400">${formatCurrencyAccounting(item.gaji_lama)}</td>
                        <td class="px-4 py-2 text-sm text-right font-medium text-gray-900 dark:text-white">${formatCurrencyAccounting(item.gaji_baru)}</td>
                        <td class="px-4 py-2 text-sm text-center ${diffClass}">
                            <i class="bi ${diffIcon}"></i> ${formatCurrencyAccounting(diff)}
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">${item.keterangan || '-'}</td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Tidak ada riwayat perubahan gaji.</td></tr>';
        }
    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-4 text-center text-red-500">Gagal memuat data.</td></tr>';
    }
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

    document.getElementById('nip').value = item.nip || '';
    document.getElementById('nama_lengkap').value = item.nama_lengkap || '';
    document.getElementById('jabatan_id').value = item.jabatan_id || '';
    document.getElementById('atasan_id').value = item.atasan_id || '';
    document.getElementById('divisi_id').value = item.divisi_id || '';
    document.getElementById('kantor_id').value = item.kantor_id || '';
    document.getElementById('golongan_gaji_id').value = item.golongan_gaji_id || '';
    document.getElementById('jadwal_kerja_id').value = item.jadwal_kerja_id || '';
    document.getElementById('tanggal_masuk').value = item.tanggal_masuk || '';
    document.getElementById('tanggal_berakhir_kontrak').value = item.tanggal_berakhir_kontrak || '';
    document.getElementById('status').value = item.status || 'aktif';
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