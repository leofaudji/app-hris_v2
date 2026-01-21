async function initPenilaianKinerjaPage() {
    loadAppraisals();
    loadKaryawanOptions();
    loadTemplateOptions();

    document.getElementById('filter-bulan').addEventListener('change', loadAppraisals);
    document.getElementById('filter-tahun').addEventListener('change', loadAppraisals);

    const form = document.getElementById('appraisal-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Collect scores
            const rows = document.querySelectorAll('.score-row');
            const scores = [];
            rows.forEach(row => {
                scores.push({
                    indikator_id: row.dataset.id,
                    skor: row.querySelector('.score-input').value,
                    komentar: row.querySelector('.comment-input').value
                });
            });

            const formData = new FormData(e.target);
            formData.append('scores', JSON.stringify(scores));

            try {
                const response = await fetch(`${basePath}/api/hr/kpi`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    Swal.fire('Berhasil', result.message, 'success');
                    closeModal('appraisalModal');
                    loadAppraisals();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                console.error(error);
            }
        });
    }
}

async function loadAppraisals() {
    const tbody = document.getElementById('appraisal-table-body');
    const bulan = document.getElementById('filter-bulan').value;
    const tahun = document.getElementById('filter-tahun').value;
    
    try {
        const response = await fetch(`${basePath}/api/hr/kpi?action=list_appraisals&bulan=${bulan}&tahun=${tahun}`);
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(item => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        ${item.nama_lengkap}<br><span class="text-xs text-gray-500">${item.nip}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.nama_template}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-primary">${item.total_skor}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${item.status === 'final' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                            ${item.status.toUpperCase()}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick="cetakRapor(${item.id})" class="text-green-600 hover:text-green-900 mr-2" title="Cetak Rapor"><i class="bi bi-printer"></i></button>
                        <button onclick="editAppraisal(${item.id})" class="text-blue-600 hover:text-blue-900 mr-2">Edit</button>
                        <button onclick="deleteAppraisal(${item.id})" class="text-red-600 hover:text-red-900">Hapus</button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">Belum ada data penilaian.</td></tr>';
        }
    } catch (error) {
        console.error(error);
    }
}

async function loadKaryawanOptions() {
    const select = document.getElementById('karyawan_id');
    const response = await fetch(`${basePath}/api/hr/karyawan?status=aktif`);
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

async function loadTemplateOptions() {
    const select = document.getElementById('template_id');
    const response = await fetch(`${basePath}/api/hr/kpi?action=list_templates`);
    const result = await response.json();
    if (result.success) {
        result.data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.nama_template;
            select.appendChild(option);
        });
    }
}

async function loadTemplateIndicators(templateId, existingDetails = []) {
    const tbody = document.getElementById('appraisal-indicators-body');
    if (!templateId) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-gray-500">Pilih template terlebih dahulu.</td></tr>';
        return;
    }

    const response = await fetch(`${basePath}/api/hr/kpi?action=get_template_detail&id=${templateId}`);
    const result = await response.json();
    
    if (result.success) {
        tbody.innerHTML = result.data.map(ind => {
            // Find existing value if editing
            const existing = existingDetails.find(d => d.indikator_id == ind.id);
            const score = existing ? existing.skor : 0;
            const comment = existing ? existing.komentar : '';

            return `
            <tr class="score-row" data-id="${ind.id}" data-weight="${ind.bobot}">
                <td class="p-2 align-top">
                    <div class="font-medium text-gray-800 dark:text-white">${ind.indikator}</div>
                </td>
                <td class="p-2 text-center align-top text-gray-500">${ind.bobot}%</td>
                <td class="p-2 align-top">
                    <input type="number" class="score-input w-full rounded border-gray-300 text-center text-sm" min="0" max="100" value="${score}" oninput="calculateFinalScore()" required>
                </td>
                <td class="p-2 align-top">
                    <textarea class="comment-input w-full rounded border-gray-300 text-sm" rows="1" placeholder="Komentar...">${comment}</textarea>
                </td>
            </tr>
        `}).join('');
        calculateFinalScore();
    }
}

function calculateFinalScore() {
    let total = 0;
    document.querySelectorAll('.score-row').forEach(row => {
        const weight = parseFloat(row.dataset.weight);
        const score = parseFloat(row.querySelector('.score-input').value) || 0;
        total += (score * (weight / 100));
    });
    document.getElementById('final-score').textContent = total.toFixed(2);
}

function openAppraisalModal() {
    document.getElementById('appraisal-form').reset();
    document.getElementById('appraisal_id').value = '';
    document.getElementById('appraisal-indicators-body').innerHTML = '<tr><td colspan="4" class="text-center py-4 text-gray-500">Pilih template terlebih dahulu.</td></tr>';
    document.getElementById('final-score').textContent = '0';
    openModal('appraisalModal');
}

async function editAppraisal(id) {
    const response = await fetch(`${basePath}/api/hr/kpi?action=get_appraisal_detail&id=${id}`);
    const result = await response.json();
    if (result.success) {
        const h = result.data.header;
        document.getElementById('appraisal_id').value = h.id;
        document.getElementById('karyawan_id').value = h.karyawan_id;
        document.getElementById('template_id').value = h.template_id;
        document.getElementById('form_bulan').value = h.periode_bulan;
        document.getElementById('form_tahun').value = h.periode_tahun;
        document.getElementById('tanggal_penilaian').value = h.tanggal_penilaian;
        document.getElementById('catatan').value = h.catatan;
        document.getElementById('status').value = h.status;
        
        loadTemplateIndicators(h.template_id, result.data.details);
        openModal('appraisalModal');
    }
}

async function deleteAppraisal(id) {
    if (!confirm('Hapus penilaian ini?')) return;
    const formData = new FormData();
    formData.append('action', 'delete_appraisal');
    formData.append('id', id);
    await fetch(`${basePath}/api/hr/kpi`, { method: 'POST', body: formData });
    loadAppraisals();
}

// Expose globally
window.loadTemplateIndicators = loadTemplateIndicators;
window.calculateFinalScore = calculateFinalScore;

function cetakRapor(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${basePath}/api/pdf`;
    form.target = '_blank';

    const params = {
        report: 'rapor-kpi',
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