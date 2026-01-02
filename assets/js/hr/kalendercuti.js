function initKalenderCutiPage() {
    const calendarEl = document.getElementById('leave-calendar');
    if (!calendarEl) return;

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
            extraParams: {
                action: 'get_calendar_events'
            },
            failure: function() {
                showToast('Gagal memuat data kalender.', 'error');
            }
        },
        eventDisplay: 'block', // Makes events look more solid
        eventTimeFormat: { // Don't show time for all-day events
            hour: '2-digit',
            minute: '2-digit',
            meridiem: false
        }
    });

    calendar.render();
}