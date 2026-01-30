function initPortalKalenderPage() {
    const calendarEl = document.getElementById('team-calendar');
    if (!calendarEl) return;

    loadLeaveTypesForCalendar();
    loadSisaCutiForCalendar();

    // Validasi: Tanggal selesai tidak boleh sebelum tanggal mulai
    const startInput = document.getElementById('cal_tanggal_mulai');
    const endInput = document.getElementById('cal_tanggal_selesai');
    if (startInput && endInput) {
        startInput.addEventListener('change', function() {
            endInput.min = this.value;
            if (endInput.value && endInput.value < this.value) {
                endInput.value = this.value;
            }
        });
    }

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listWeek'
        },
        locale: 'id',
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            list: 'Daftar'
        },
        selectable: true,
        selectAllow: function(selectInfo) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return selectInfo.start >= today;
        },
        select: function(info) {
            openApplyLeaveModal(info.start, info.end);
        },
        events: {
            url: `${basePath}/api/hr/manajemen-cuti`,
            method: 'GET',
            extraParams: {
                action: 'get_calendar_events'
                // divisi_id ditangani otomatis oleh backend untuk user non-admin
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
            const props = arg.event.extendedProps;
            
            if (props.type === 'holiday') {
                icon = 'bi-flag-fill';
                return {
                    html: `
                        <div class="fc-event-main-frame flex items-center px-1 overflow-hidden text-white rounded" style="background-color: #EF4444;">
                            <i class="bi ${icon} mr-1"></i>
                            <div class="fc-event-title fc-sticky truncate">${arg.event.title}</div>
                        </div>`
                };
            } else if (props.type === 'birthday') {
                icon = 'bi-gift-fill';
                return {
                    html: `
                        <div class="fc-event-main-frame flex items-center px-1 overflow-hidden text-white rounded" style="background-color: #EC4899;">
                            <i class="bi ${icon} mr-1"></i>
                            <div class="fc-event-title fc-sticky truncate">${arg.event.title}</div>
                        </div>`
                };
            }

            const titleLower = arg.event.title.toLowerCase();
            if (titleLower.includes('tahunan')) icon = 'bi-briefcase';
            else if (titleLower.includes('sakit')) icon = 'bi-bandaid';
            else if (titleLower.includes('melahirkan')) icon = 'bi-heart-pulse';

            return {
                html: `
                    <div class="fc-event-main-frame flex items-center px-1 overflow-hidden">
                        <i class="bi ${icon} mr-1"></i>
                        <div class="fc-event-title fc-sticky truncate">${arg.event.title}</div>
                    </div>`
            };
        },
        height: 'auto',
        contentHeight: 600
    });

    calendar.render();
}

function showEventDetail(event) {
    const props = event.extendedProps;
    
    if (props.type === 'holiday') {
        document.getElementById('detail-nama').textContent = 'Hari Libur Nasional';
        document.getElementById('detail-jenis').textContent = '-';
        document.getElementById('detail-durasi').textContent = '1 Hari';
        document.getElementById('detail-keterangan').textContent = event.title;
    } else if (props.type === 'birthday') {
        document.getElementById('detail-nama').textContent = props.employee_name;
        document.getElementById('detail-jenis').textContent = 'Ulang Tahun';
        document.getElementById('detail-durasi').textContent = '1 Hari';
        document.getElementById('detail-keterangan').textContent = props.keterangan;
    } else {
        document.getElementById('detail-nama').textContent = props.employee_name || event.title;
        document.getElementById('detail-jenis').textContent = props.nama_jenis || '-';
        document.getElementById('detail-durasi').textContent = (props.jumlah_hari || 1) + ' Hari';
        document.getElementById('detail-keterangan').textContent = props.keterangan || 'Tidak ada keterangan';
    }
    
    const start = event.start;
    let endDateDisplay = new Date(event.end || event.start);
    if (event.allDay && event.end) {
         endDateDisplay.setDate(endDateDisplay.getDate() - 1);
    }
    
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    const dateStr = start.toLocaleDateString('id-ID', options) + (event.end && start.getTime() !== endDateDisplay.getTime() ? ' s/d ' + endDateDisplay.toLocaleDateString('id-ID', options) : '');
    document.getElementById('detail-tanggal').textContent = dateStr;

    document.getElementById('eventDetailModal').classList.remove('hidden');
}

function closeEventDetailModal() {
    document.getElementById('eventDetailModal').classList.add('hidden');
}

// --- Fitur Pengajuan Cuti dari Kalender ---

function loadLeaveTypesForCalendar() {
    const select = document.getElementById('cal_jenis_cuti');
    if (!select) return;
    
    fetch(`${basePath}/api/hr/jenis-cuti`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                select.innerHTML = '<option value="">Pilih Jenis Cuti</option>';
                res.data.forEach(item => {
                    if (item.is_active) {
                        select.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.nama_jenis}</option>`);
                    }
                });
            }
        });
}

function openApplyLeaveModal(start, end) {
    // FullCalendar end date is exclusive, so subtract 1 day for the form
    let endDate = new Date(end);
    endDate.setDate(endDate.getDate() - 1);

    // Helper untuk format YYYY-MM-DD lokal
    const toLocalISO = (date) => {
        const offset = date.getTimezoneOffset() * 60000;
        return new Date(date.getTime() - offset).toISOString().split('T')[0];
    };

    const todayStr = toLocalISO(new Date());

    document.getElementById('calendar-leave-form').reset();
    
    const startInput = document.getElementById('cal_tanggal_mulai');
    startInput.value = toLocalISO(start);
    startInput.min = todayStr;

    const endInput = document.getElementById('cal_tanggal_selesai');
    endInput.value = toLocalISO(endDate);
    endInput.min = startInput.value;
    
    document.getElementById('applyLeaveModal').classList.remove('hidden');
}

function closeApplyLeaveModal() {
    document.getElementById('applyLeaveModal').classList.add('hidden');
}

function submitCalendarLeave(e) {
    e.preventDefault();
    const form = document.getElementById('calendar-leave-form');
    const formData = new FormData(form);
    formData.append('action', 'save'); // Pastikan action diset

    fetch(`${basePath}/api/hr/manajemen-cuti`, { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeApplyLeaveModal();
            showToast(data.message, 'success');
            initPortalKalenderPage(); // Reload calendar events
        } else {
            showToast(data.message || 'Gagal menyimpan data', 'error');
        }
    })
    .catch(error => {
        console.error(error);
        showToast('Terjadi kesalahan jaringan', 'error');
    });
}

function loadSisaCutiForCalendar() {
    const displayEl = document.getElementById('calendar-sisa-cuti');
    if (!displayEl) return;

    const currentYear = new Date().getFullYear();
    fetch(`${basePath}/api/hr/manajemen-cuti?action=get_sisa_cuti&tahun=${currentYear}`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                displayEl.textContent = `${res.sisa_jatah} Hari`;
            } else {
                displayEl.textContent = '-';
            }
        })
        .catch(err => {
            console.error(err);
            displayEl.textContent = 'Error';
        });
}