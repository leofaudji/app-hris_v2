let kpiChartInstance = null;

function initPortalKpiPage() {
    const yearSelect = document.getElementById('kpi-filter-tahun');
    if (yearSelect) {
        const currentYear = new Date().getFullYear();
        for (let i = 0; i < 5; i++) {
            const year = currentYear - i;
            yearSelect.add(new Option(year, year));
        }
        yearSelect.value = currentYear;
        yearSelect.addEventListener('change', loadKpiHistory);
    }
    loadKpiHistory();
}

async function loadKpiHistory() {
    const tbody = document.getElementById('portal-kpi-body');
    const year = document.getElementById('kpi-filter-tahun')?.value || new Date().getFullYear();
    if (!tbody) return;

    try {
        const response = await fetch(`${basePath}/api/hr/portal/kpi?action=list&tahun=${year}`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

            tbody.innerHTML = result.data.map(item => {
                const periode = `${monthNames[item.periode_bulan - 1]} ${item.periode_tahun}`;
                // Escape data untuk JSON string di tombol
                const itemJson = JSON.stringify(item).replace(/"/g, '&quot;');

                return `
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${periode}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.nama_template}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 text-sm font-bold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">${item.total_skor}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${item.nama_penilai || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="showKpiDetail(${itemJson})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3" title="Lihat Detail"><i class="bi bi-eye"></i> Detail</button>
                            <button onclick="printKpi(${item.id})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Cetak PDF"><i class="bi bi-printer"></i> Cetak</button>
                        </td>
                    </tr>
                `;
            }).join('');
            
            renderKpiChart(result.data);
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-6 text-gray-500">Belum ada penilaian kinerja yang selesai.</td></tr>';
            renderKpiChart([]); // Clear chart
        }
    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-6 text-red-500">Gagal memuat data.</td></tr>';
    }
}

function renderKpiChart(data) {
    const ctx = document.getElementById('kpi-chart');
    if (!ctx) return;

    if (kpiChartInstance) {
        kpiChartInstance.destroy();
    }

    const months = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
    const chartData = new Array(12).fill(null);

    // Data dari API urut DESC, kita perlu memetakan ke array bulan (0-11)
    data.forEach(item => {
        const monthIndex = parseInt(item.periode_bulan) - 1;
        if (monthIndex >= 0 && monthIndex < 12) {
            chartData[monthIndex] = parseFloat(item.total_skor);
        }
    });

    kpiChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Total Skor',
                data: chartData,
                borderColor: '#3B82F6', // Blue-500
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.3,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100, // Asumsi skor maksimal 100
                    grid: {
                        color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
                    },
                    ticks: {
                        color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#4b5563'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#4b5563'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            }
        }
    });
}

async function showKpiDetail(item) {
    const modalTitle = document.getElementById('modal-kpi-title');
    const modalScore = document.getElementById('modal-kpi-score');
    const modalNote = document.getElementById('modal-kpi-note');
    const tbody = document.getElementById('modal-kpi-details-body');

    modalTitle.textContent = `Detail: ${item.nama_template}`;
    modalScore.textContent = item.total_skor;
    modalNote.textContent = `Catatan: ${item.catatan || '-'}`;
    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4">Memuat detail...</td></tr>';

    document.getElementById('kpiDetailModal').classList.remove('hidden');

    try {
        const response = await fetch(`${basePath}/api/hr/portal/kpi?action=detail&id=${item.id}`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map(det => `
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${det.indikator}</td>
                    <td class="px-4 py-2 text-sm text-center text-gray-500 dark:text-gray-400">${det.bobot}%</td>
                    <td class="px-4 py-2 text-sm text-center font-medium text-gray-900 dark:text-white">${det.skor}</td>
                    <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 italic">${det.komentar || '-'}</td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-gray-500">Tidak ada detail indikator.</td></tr>';
        }
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-red-500">Gagal memuat detail.</td></tr>';
    }
}

function closeKpiDetailModal() {
    document.getElementById('kpiDetailModal').classList.add('hidden');
}

function printKpi(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${basePath}/api/pdf`;
    form.target = '_blank';

    const params = { report: 'rapor-kpi', id: id };
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