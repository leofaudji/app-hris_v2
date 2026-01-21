async function initRekrutmenPage() {
    loadVacancies();
    loadApplicants();
    loadDropdowns();

    const filterStatus = document.getElementById('filter-applicant-status');
    if (filterStatus) {
        filterStatus.addEventListener('change', loadApplicants);
    }

    // Tab Switching Logic
    const tabs = document.querySelectorAll('[data-tabs-target]');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('[role="tabpanel"]').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('[role="tab"]').forEach(el => {
                el.classList.remove('text-blue-600', 'border-blue-600', 'dark:text-blue-500', 'dark:border-blue-500');
                el.classList.add('border-transparent');
            });
            
            const target = document.querySelector(tab.dataset.tabsTarget);
            target.classList.remove('hidden');
            tab.classList.add('text-blue-600', 'border-blue-600', 'dark:text-blue-500', 'dark:border-blue-500');
            tab.classList.remove('border-transparent');
        });
    });

    // Form Listeners
    setupForm('vacancy-form', 'vacancyModal', loadVacancies);
    setupForm('applicant-form', 'applicantModal', loadApplicants);
    setupForm('hire-form', 'hireModal', loadApplicants);
    setupForm('upload-spk-form', 'uploadSPKModal', loadApplicants);

    // Logika Status Karyawan di Modal Hire
    const hireStatus = document.getElementById('hire_status_karyawan');
    const hireDate = document.getElementById('hire_tanggal_masuk');
    
    if (hireStatus && hireDate) {
        hireStatus.addEventListener('change', function() {
            const container = document.getElementById('hire_end_date_container');
            const endDateInput = document.getElementById('hire_tanggal_berakhir');
            const startDate = new Date(hireDate.value);
            
            if (this.value === 'kontrak' || this.value === 'probation') {
                container.classList.remove('hidden');
                
                // Auto calculate date
                let endDate = new Date(startDate);
                if (this.value === 'probation') {
                    endDate.setMonth(endDate.getMonth() + 3); // Default 3 bulan
                } else {
                    endDate.setFullYear(endDate.getFullYear() + 1); // Default 1 tahun
                }
                // Format YYYY-MM-DD
                endDateInput.value = endDate.toISOString().split('T')[0];
            } else {
                container.classList.add('hidden');
                endDateInput.value = '';
            }
        });
    }
}

function setupForm(formId, modalId, reloadFunc) {
    const form = document.getElementById(formId);
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const response = await fetch(`${basePath}/api/hr/rekrutmen`, { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    Swal.fire('Berhasil', result.message, 'success');
                    closeModal(modalId);
                    if (reloadFunc) reloadFunc();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                console.error(error);
            }
        });
    }
}

async function loadVacancies() {
    const container = document.getElementById('vacancy-list-container');
    try {
        const response = await fetch(`${basePath}/api/hr/rekrutmen?action=list_vacancies`);
        const result = await response.json();
        if (result.success && result.data.length > 0) {
            container.innerHTML = result.data.map(item => `
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border-l-4 ${item.status === 'buka' ? 'border-green-500' : 'border-red-500'}">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white">${item.judul}</h3>
                        <span class="px-2 py-1 text-xs rounded-full ${item.status === 'buka' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${item.status.toUpperCase()}</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1"><i class="bi bi-briefcase mr-1"></i> ${item.nama_jabatan} - ${item.nama_divisi}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3"><i class="bi bi-people mr-1"></i> Kuota: ${item.kuota} | Pelamar: ${item.total_pelamar}</p>
                    <div class="flex justify-end gap-2 mt-4">
                        <button onclick="editVacancy(${item.id})" class="text-blue-600 hover:text-blue-800 text-sm">Edit</button>
                        <button onclick="deleteVacancy(${item.id})" class="text-red-600 hover:text-red-800 text-sm">Hapus</button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="text-center py-8 col-span-full text-gray-500">Belum ada lowongan.</div>';
        }
    } catch (error) {
        console.error(error);
    }
}

async function loadApplicants() {
    const tbody = document.getElementById('applicant-table-body');
    const filterStatus = document.getElementById('filter-applicant-status') ? document.getElementById('filter-applicant-status').value : '';

    try {
        const response = await fetch(`${basePath}/api/hr/rekrutmen?action=list_applicants`);
        const result = await response.json();
        if (result.success && result.data.length > 0) {
            const filteredData = filterStatus 
                ? result.data.filter(item => item.status === filterStatus)
                : result.data;

            if (filteredData.length > 0) {
            tbody.innerHTML = filteredData.map(item => {
                let statusColor = 'bg-gray-100 text-gray-800';
                let actionButtons = '';
                if (item.status === 'hired') {
                    statusColor = 'bg-green-100 text-green-800';
                    actionButtons = `<button onclick="cetakSPK(${item.id})" class="text-blue-600 hover:text-blue-900 mr-2" title="Cetak SPK"><i class="bi bi-printer"></i></button>`;
                    actionButtons += `<button onclick="openUploadSPKModal(${item.id}, '${item.nama_lengkap}')" class="text-orange-600 hover:text-orange-900 mr-2" title="Upload SPK Ttd"><i class="bi bi-upload"></i></button>`;
                }
                else if (item.status === 'rejected') statusColor = 'bg-red-100 text-red-800';
                else if (item.status === 'interview') statusColor = 'bg-blue-100 text-blue-800';

                let docsLinks = '';
                if (item.file_cv) {
                    docsLinks += `<a href="${basePath}/${item.file_cv}" target="_blank" class="text-blue-600 hover:underline text-xs block"><i class="bi bi-file-earmark-text"></i> CV</a>`;
                }
                if (item.file_spk_signed) {
                    docsLinks += `<a href="${basePath}/${item.file_spk_signed}" target="_blank" class="text-green-600 hover:underline text-xs block mt-1"><i class="bi bi-file-earmark-check"></i> SPK (Ttd)</a>`;
                }
                if (!docsLinks) docsLinks = '<span class="text-gray-400 text-xs">-</span>';

                return `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">${item.nama_lengkap}</div>
                        <div class="text-xs text-gray-500">${item.pendidikan_terakhir || '-'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.judul_lowongan}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        <div>${item.email}</div>
                        <div>${item.no_hp || '-'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        ${docsLinks}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusColor}">
                            ${item.status.toUpperCase()}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        ${actionButtons}
                        <select onchange="updateApplicantStatus(${item.id}, this.value, '${item.nama_lengkap}')" class="text-xs border-gray-300 rounded shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600">
                            <option value="applied" ${item.status === 'applied' ? 'selected' : ''}>Applied</option>
                            <option value="screening" ${item.status === 'screening' ? 'selected' : ''}>Screening</option>
                            <option value="interview" ${item.status === 'interview' ? 'selected' : ''}>Interview</option>
                            <option value="offering" ${item.status === 'offering' ? 'selected' : ''}>Offering</option>
                            <option value="hired" ${item.status === 'hired' ? 'selected' : ''}>Hired</option>
                            <option value="rejected" ${item.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                        </select>
                    </td>
                </tr>
            `}).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Tidak ada pelamar dengan status ini.</td></tr>';
            }
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Belum ada pelamar.</td></tr>';
        }
    } catch (error) {
        console.error(error);
    }
}

async function updateApplicantStatus(id, status, nama) {
    if (status === 'hired') {
        // Open Hire Modal
        document.getElementById('hire_pelamar_id').value = id;
        document.getElementById('hire_nama_pelamar').textContent = nama;
        openModal('hireModal');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update_applicant_status');
    formData.append('id', id);
    formData.append('status', status);

    await fetch(`${basePath}/api/hr/rekrutmen`, { method: 'POST', body: formData });
    loadApplicants();
}

async function loadDropdowns() {
    // Load Jabatan & Divisi for Vacancy Form
    const jabatanRes = await fetch(`${basePath}/api/hr/jabatan`);
    const jabatanData = await jabatanRes.json();
    const vacJabatanSelect = document.getElementById('vac_jabatan_id');
    if (vacJabatanSelect && jabatanData.success) {
        vacJabatanSelect.innerHTML = jabatanData.data.map(j => `<option value="${j.id}">${j.nama_jabatan}</option>`).join('');
    }

    const divisiRes = await fetch(`${basePath}/api/hr/divisi`);
    const divisiData = await divisiRes.json();
    const vacDivisiSelect = document.getElementById('vac_divisi_id');
    if (vacDivisiSelect && divisiData.success) {
        vacDivisiSelect.innerHTML = divisiData.data.map(d => `<option value="${d.id}">${d.nama_divisi}</option>`).join('');
    }

    // Load Vacancies for Applicant Form
    const vacRes = await fetch(`${basePath}/api/hr/rekrutmen?action=list_vacancies`);
    const vacData = await vacRes.json();
    const appVacSelect = document.getElementById('app_lowongan_id');
    if (appVacSelect && vacData.success) {
        appVacSelect.innerHTML = vacData.data.map(v => `<option value="${v.id}">${v.judul}</option>`).join('');
    }

    // Load Master Data for Hire Modal
    const kantorRes = await fetch(`${basePath}/api/hr/kantor`);
    const kantorData = await kantorRes.json();
    const hireKantor = document.getElementById('hire_kantor_id');
    if (hireKantor && kantorData.success) {
        hireKantor.innerHTML = kantorData.data.map(k => `<option value="${k.id}">${k.nama_kantor}</option>`).join('');
    }

    const jadwalRes = await fetch(`${basePath}/api/hr/jadwal-kerja`);
    const jadwalData = await jadwalRes.json();
    const hireJadwal = document.getElementById('hire_jadwal_id');
    if (hireJadwal && jadwalData.success) {
        hireJadwal.innerHTML = jadwalData.data.map(j => `<option value="${j.id}">${j.nama_jadwal}</option>`).join('');
    }

    const golRes = await fetch(`${basePath}/api/hr/golongan-gaji`);
    const golData = await golRes.json();
    const hireGol = document.getElementById('hire_golongan_id');
    if (hireGol && golData.success) {
        hireGol.innerHTML = golData.data.map(g => `<option value="${g.id}">${g.nama_golongan}</option>`).join('');
    }
}

function openVacancyModal() {
    document.getElementById('vacancy-form').reset();
    document.getElementById('vacancy_id').value = '';
    document.getElementById('vacancy-modal-title').textContent = 'Buat Lowongan Baru';
    openModal('vacancyModal');
}

function openApplicantModal() {
    document.getElementById('applicant-form').reset();
    openModal('applicantModal');
}

function openUploadSPKModal(id, nama) {
    document.getElementById('upload-spk-form').reset();
    document.getElementById('upload_spk_pelamar_id').value = id;
    document.getElementById('upload_spk_nama').textContent = nama;
    openModal('uploadSPKModal');
}

async function editVacancy(id) {
    // Fetch single vacancy detail logic here if needed, or pass data via params
    // For simplicity, just alert or implement get_vacancy action
    alert("Fitur edit detail belum diimplementasikan di demo ini.");
}

async function deleteVacancy(id) {
    if (!confirm('Hapus lowongan ini? Data pelamar terkait juga akan dihapus.')) return;
    const formData = new FormData();
    formData.append('action', 'delete_vacancy');
    formData.append('id', id);
    await fetch(`${basePath}/api/hr/rekrutmen`, { method: 'POST', body: formData });
    loadVacancies();
}

function cetakSPK(pelamarId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${basePath}/api/pdf`;
    form.target = '_blank';

    const params = { report: 'spk', id: pelamarId };
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