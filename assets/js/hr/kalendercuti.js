function initKalenderCutiPage() {
    const calendarEl = document.getElementById('leave-calendar');
    if (!calendarEl) return;

    loadDivisiOptionsForCalendar();
    loadKaryawanOptionsForEdit();
    loadJenisCutiOptionsForEdit();

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        locale: 'id',
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu',
            list: 'Daftar'
        },
        events: {
            url: `${basePath}/api/hr/manajemen-cuti`,
            method: 'GET',
            extraParams: function() {
                return {
                    action: 'get_calendar_events',
                    divisi_id: document.getElementById('filter-divisi-kalender') ? document.getElementById('filter-divisi-kalender').value : ''
                };
            },
            failure: function() {
                showToast('Gagal memuat data kalender.', 'error');
            }
        },
        eventClick: function(info) {
            showEventDetail(info.event);
        },
        eventDisplay: 'block',
        eventContent: function(arg) {
            let icon = 'bi-calendar-check';
            const titleLower = arg.event.title.toLowerCase();
            
            if (titleLower.includes('tahunan')) icon = 'bi-briefcase';
            else if (titleLower.includes('sakit')) icon = 'bi-bandaid';
            else if (titleLower.includes('melahirkan')) icon = 'bi-heart-pulse';
            else if (titleLower.includes('menikah')) icon = 'bi-heart';

            return {
                html: `
                    <div class="fc-event-main-frame flex items-center px-1 overflow-hidden">
                        <i class="bi ${icon} mr-1"></i>
                        <div class="fc-event-title fc-sticky truncate">${arg.event.title}</div>
                    </div>`
            };
        },
        eventTimeFormat: { // Don't show time for all-day events
            hour: '2-digit',
            minute: '2-digit',
            meridiem: false
        }
    });

    calendar.render();

    const filterDivisi = document.getElementById('filter-divisi-kalender');
    if (filterDivisi) {
        filterDivisi.addEventListener('change', () => {
            calendar.refetchEvents();
        });
    }

    // Event listener untuk tombol simpan edit
    const btnSimpan = document.getElementById('btn-simpan-edit');
    if (btnSimpan) btnSimpan.addEventListener('click', saveEditedCuti);

}

function loadDivisiOptionsForCalendar() {
    fetch(`${basePath}/api/hr/divisi`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('filter-divisi-kalender');
                if (select) {
                    data.data.forEach(divisi => {
                        select.insertAdjacentHTML('beforeend', `<option value="${divisi.id}">${divisi.nama_divisi}</option>`);
                    });
                }
            }
        });
}

function loadKaryawanOptionsForEdit() {
    const select = document.getElementById('edit-karyawan-id');
    if (!select) return;
    fetch(`${basePath}/api/hr/karyawan?status=aktif`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                select.innerHTML = '<option value="">Pilih Karyawan</option>';
                res.data.forEach(k => {
                    select.insertAdjacentHTML('beforeend', `<option value="${k.id}">${k.nama_lengkap}</option>`);
                });
            }
        });
}

function loadJenisCutiOptionsForEdit() {
    const select = document.getElementById('edit-jenis-id');
    if (!select) return;
    fetch(`${basePath}/api/hr/jenis-cuti`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                select.innerHTML = '<option value="">Pilih Jenis Cuti</option>';
                res.data.forEach(item => {
                    select.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.nama_jenis}</option>`);
                });
            }
        });
}

window.closeEventDetailModal = function() {
    document.getElementById('eventDetailModal').classList.add('hidden');
}

function showEventDetail(event) {
    const props = event.extendedProps;
    
    // Isi data ke dalam modal
    document.getElementById('detail-nama').textContent = props.employee_name || event.title;
    document.getElementById('detail-jenis').textContent = props.nama_jenis || '-';
    document.getElementById('detail-durasi').textContent = (props.jumlah_hari || 1) + ' Hari';
    document.getElementById('detail-keterangan').textContent = props.keterangan || 'Tidak ada keterangan';
    
    // Format tanggal
    const start = event.start;
    // FullCalendar end date is exclusive, so subtract 1 day for display if needed
    let endDateDisplay = new Date(event.end || event.start);
    if (event.allDay && event.end) {
         endDateDisplay.setDate(endDateDisplay.getDate() - 1);
    }
    
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    const dateStr = start.toLocaleDateString('id-ID', options) + (event.end && start.getTime() !== endDateDisplay.getTime() ? ' s/d ' + endDateDisplay.toLocaleDateString('id-ID', options) : '');
    document.getElementById('detail-tanggal').textContent = dateStr;
    
    // Setup tombol Edit
    const btnEdit = document.getElementById('btn-edit-cuti');
    if (btnEdit) {
        btnEdit.onclick = function() {
            closeEventDetailModal();
            openEditCutiModal(event);
        };
    }

    // Tampilkan modal
    document.getElementById('eventDetailModal').classList.remove('hidden');
}

function openEditCutiModal(event) {
    const props = event.extendedProps;
    document.getElementById('edit-cuti-id').value = event.id;
    document.getElementById('edit-karyawan-id').value = props.karyawan_id;
    document.getElementById('edit-jenis-id').value = props.jenis_cuti_id;
    document.getElementById('edit-tanggal-mulai').value = event.startStr;
    
    // Handle end date (FullCalendar end is exclusive)
    let endDate = new Date(event.end || event.start);
    if (event.allDay && event.end) endDate.setDate(endDate.getDate() - 1);
    document.getElementById('edit-tanggal-selesai').value = endDate.toISOString().split('T')[0];
    
    document.getElementById('edit-keterangan').value = props.keterangan || '';
    
    document.getElementById('editCutiModal').classList.remove('hidden');
}

function saveEditedCuti() {
    const form = document.getElementById('edit-cuti-form');
    const formData = new FormData(form);
    
    fetch(`${basePath}/api/hr/manajemen-cuti`, { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('editCutiModal').classList.add('hidden');
            showToast(data.message, 'success');
            // Refresh kalender
            const calendarEl = document.getElementById('leave-calendar');
            // Karena kita tidak punya akses langsung ke instance calendar di sini (scope lokal init),
            // kita trigger navigasi ulang ke halaman ini untuk refresh, atau gunakan event custom.
            // Cara paling mudah di SPA ini adalah memanggil initKalenderCutiPage lagi atau reload data.
            // Namun, karena instance calendar ada di dalam init, kita bisa memaksa refresh dengan klik tombol filter divisi (hacky)
            // atau lebih baik:
            initKalenderCutiPage(); 
        } else {
            showToast(data.message || 'Gagal menyimpan perubahan', 'error');
        }
    });
}