// Inicialización del calendario
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    if (!calendarEl) return;

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        locale: 'es',
        navLinks: true,
        height: 'auto',
        contentHeight: 'auto',
        events: window.calendarEvents || [],
        eventClassNames: 'rounded-lg shadow-sm border-0',
        dayMaxEvents: 2,
        eventDisplay: 'block'
    });

    calendar.render();
});
