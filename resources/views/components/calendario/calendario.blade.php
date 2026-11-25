@props([
    'eventos' => [],
    'inicial' => null,
    'vista' => 'dayGridMonth',
    'url' => null,
    'seleccionable' => false, // si es true, al pulsar un día con sesión se selecciona (evento "calendario:seleccion")
])

@php $id = 'calendario-' . \Illuminate\Support\Str::random(8); @endphp

<div id="{{ $id }}" @class(['calendario-marca', 'calendario-clicable' => $url]) style="min-height: 500px; width: 100%"></div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        {{-- El paquete principal no trae los idiomas: textos en español --}}
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.min.js"></script>
        <style>
            /* ---------- Calendario con los colores de la marca ---------- */
            .calendario-marca {
                --fc-border-color: #e7e5e4;            /* stone-200 */
                --fc-page-bg-color: #ffffff;
                --fc-neutral-bg-color: #fafaf9;        /* stone-50 */
                --fc-today-bg-color: transparent;       /* hoy se marca con el círculo */
                --fc-event-bg-color: #CFE3E4;          /* marca-100 */
                --fc-event-border-color: #1D5C63;      /* marca-700 */
                --fc-event-text-color: #0E3B41;        /* marca-900 */
                --fc-list-event-hover-bg-color: #EEF6F6;
                --fc-highlight-color: rgba(29, 92, 99, .08);
                font-family: inherit;
                font-size: .875rem;
                color: #334155;                        /* slate-700 */
            }

            /* Barra superior */
            .calendario-marca .fc-header-toolbar.fc-toolbar { margin-bottom: 1.25rem; gap: .75rem; flex-wrap: wrap; }
            .calendario-marca .fc-toolbar-title {
                font-size: 1.25rem; font-weight: 700; color: #082A2F; letter-spacing: -.01em;
            }
            .calendario-marca .fc-toolbar-title::first-letter { text-transform: uppercase; }

            /* Botones */
            .calendario-marca .fc-button {
                background: #fff; border: 1px solid #e7e5e4; color: #0E3B41;
                font-size: .8125rem; font-weight: 600; text-transform: none;
                padding: .4rem .8rem; border-radius: .5rem; box-shadow: none;
                transition: background-color .15s, color .15s, border-color .15s;
            }
            .calendario-marca .fc-button:hover { background: #EEF6F6; border-color: #CFE3E4; color: #0E3B41; }
            .calendario-marca .fc-button:focus,
            .calendario-marca .fc-button:focus-visible { box-shadow: 0 0 0 3px rgba(29, 92, 99, .25) !important; outline: none; }
            .calendario-marca .fc-button:disabled { opacity: .45; background: #fff; color: #0E3B41; }
            .calendario-marca .fc-button-primary:not(:disabled).fc-button-active,
            .calendario-marca .fc-button-primary:not(:disabled):active {
                background: #0E3B41; border-color: #0E3B41; color: #fff;
            }
            .calendario-marca .fc-prev-button,
            .calendario-marca .fc-next-button { padding: .4rem .55rem; }
            .calendario-marca .fc-button-group > .fc-button { border-radius: 0; }
            .calendario-marca .fc-button-group > .fc-button:first-child { border-radius: .5rem 0 0 .5rem; }
            .calendario-marca .fc-button-group > .fc-button:last-child { border-radius: 0 .5rem .5rem 0; }
            .calendario-marca .fc-today-button { margin-left: .5rem !important; }

            /* Rejilla: bordes suaves y esquinas redondeadas */
            .calendario-marca .fc-scrollgrid { border-radius: .75rem; overflow: hidden; }
            .calendario-marca .fc-theme-standard .fc-scrollgrid,
            .calendario-marca.fc-theme-standard .fc-scrollgrid { border-color: #e7e5e4; }

            /* Cabecera de días */
            .calendario-marca .fc-col-header { background: #fafaf9; }
            .calendario-marca .fc-col-header-cell-cushion {
                padding: .6rem .25rem; font-size: .7rem; font-weight: 600;
                text-transform: uppercase; letter-spacing: .06em; color: #78716c; /* stone-500 */
                text-decoration: none;
            }

            /* Días */
            .calendario-marca .fc-daygrid-day-top { flex-direction: row; padding: .35rem .4rem 0; }
            .calendario-marca .fc-daygrid-day-number {
                display: inline-flex; align-items: center; justify-content: center;
                min-width: 1.75rem; height: 1.75rem; padding: 0 .35rem;
                border-radius: 9999px; font-size: .8125rem; font-weight: 600;
                color: #334155; text-decoration: none;
            }
            .calendario-marca .fc-day-other .fc-daygrid-day-number { color: #a8a29e; font-weight: 500; } /* stone-400 */
            /* Fines de semana: mismo aspecto que el resto (puede haber clase); solo el nombre del día en otro tono */
            .calendario-marca .fc-col-header-cell.fc-day-sat .fc-col-header-cell-cushion,
            .calendario-marca .fc-col-header-cell.fc-day-sun .fc-col-header-cell-cushion { color: #1D5C63; }

            /* Periodo del curso (evento de fondo) */
            .calendario-marca .fc-bg-event.periodo-curso { background: #EEF6F6; opacity: 1; }
            .calendario-marca .fc-day-today .fc-daygrid-day-number { background: #0E3B41; color: #fff; }

            /* Días clicables (cuando hay url para añadir horario) */
            .calendario-clicable .fc-daygrid-day,
            .calendario-clicable .fc-timegrid-slot { cursor: pointer; }
            .calendario-clicable .fc-daygrid-day:hover .fc-daygrid-day-frame { background: rgba(29, 92, 99, .05); }
            .calendario-clicable .fc-daygrid-day:hover .fc-daygrid-day-number:not(.fc-day-today *) { background: #CFE3E4; color: #0E3B41; }

            /* Día seleccionado */
            .calendario-marca td.dia-seleccionado { background: rgba(29, 92, 99, .08) !important; box-shadow: inset 0 0 0 2px #1D5C63; }
            .calendario-marca td.dia-seleccionado .fc-daygrid-day-number { background: #1D5C63; color: #fff; }

            /* Eventos */
            .calendario-marca .fc-daygrid-event,
            .calendario-marca .fc-timegrid-event {
                border: 0; border-left: 3px solid var(--fc-event-border-color);
                border-radius: .375rem; padding: .15rem .4rem; margin: 1px .3rem 2px;
                font-size: .75rem; font-weight: 600;
            }
            .calendario-marca .fc-timegrid-event { margin: 0 2px; }
            .calendario-marca .fc-daygrid-dot-event { background: var(--fc-event-bg-color); color: var(--fc-event-text-color); }
            .calendario-marca .fc-daygrid-dot-event .fc-daygrid-event-dot { display: none; }
            .calendario-marca .fc-event-main,
            .calendario-marca .fc-daygrid-event .fc-event-title { color: var(--fc-event-text-color); }
            .calendario-marca .fc-daygrid-more-link { font-size: .75rem; font-weight: 600; color: #1D5C63; }

            /* Vista semanal */
            .calendario-marca .fc-timegrid-slot { height: 2.25rem; }
            .calendario-marca .fc-timegrid-slot-label-cushion,
            .calendario-marca .fc-timegrid-axis-cushion { font-size: .7rem; color: #78716c; }
            .calendario-marca .fc-timegrid-slot-minor { border-top-style: dotted; }
            .calendario-marca .fc-timegrid-now-indicator-line { border-color: #1D5C63; }

            /* Vista agenda (lista) */
            .calendario-marca .fc-list { border-radius: .75rem; overflow: hidden; }
            .calendario-marca .fc-list-day-cushion { background: #fafaf9; }
            .calendario-marca .fc-list-day-text,
            .calendario-marca .fc-list-day-side-text { color: #082A2F; font-weight: 700; text-decoration: none; }
            .calendario-marca .fc-list-day-text::first-letter { text-transform: uppercase; }
            .calendario-marca .fc-list-event-dot { border-color: #1D5C63; }
            .calendario-marca .fc-list-empty { background: #fff; color: #78716c; }

            /* Móvil */
            @media (max-width: 640px) {
                .calendario-marca .fc-header-toolbar.fc-toolbar { flex-direction: column; align-items: stretch; }
                .calendario-marca .fc-toolbar-chunk { display: flex; justify-content: center; }
                .calendario-marca .fc-toolbar-title { font-size: 1.1rem; text-align: center; }
            }
        </style>
    @endpush
@endonce

@push('scripts')
    <script>
        (() => {
        const iniciar = () => {
            const el = document.getElementById(@js($id));
            if (!el || el.dataset.iniciado) return;
            el.dataset.iniciado = '1';

            // Si algo falla, se avisa dentro del recuadro en lugar de dejarlo en blanco
            const mostrarError = (mensaje) => {
                el.innerHTML = '';
                const aviso = document.createElement('div');
                aviso.setAttribute('role', 'alert');
                aviso.style.cssText = 'border:1px solid #fca5a5;background:#fef2f2;color:#991b1b;border-radius:.5rem;padding:.75rem 1rem;font-size:.875rem';
                aviso.textContent = mensaje;
                el.appendChild(aviso);
            };

            if (typeof FullCalendar === 'undefined') {
                mostrarError('No se ha podido cargar la librería del calendario (FullCalendar). Comprueba la conexión a internet y recarga la página.');
                return;
            }

            try {
            const url = @js($url);
            const seleccionable = @js((bool) $seleccionable);
            let seleccionada = null;

            // Marca un día como seleccionado y avisa (el evento sube hasta los contenedores)
            const seleccionar = (fecha) => {
                const sesiones = calendar.getEvents().filter(e => e.extendedProps.dia && e.startStr.slice(0, 10) === fecha);
                if (!sesiones.length) return false;

                seleccionada = fecha;
                el.querySelectorAll('td.dia-seleccionado').forEach(c => c.classList.remove('dia-seleccionado'));
                el.querySelectorAll(`td[data-date="${fecha}"]`).forEach(c => c.classList.add('dia-seleccionado'));
                el.dispatchEvent(new CustomEvent('calendario:seleccion', {
                    bubbles: true,
                    detail: { fecha, ...sesiones[0].extendedProps },
                }));
                return true;
            };

            const calendar = new FullCalendar.Calendar(el, {
                initialView: @js($vista),
                initialDate: @js($inicial),
                locale: 'es',
                firstDay: 1,
                height: 'auto',
                events: @js($eventos),
                displayEventTime: false,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth',
                },
                buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', list: 'Agenda' },
                titleFormat: { month: 'long', year: 'numeric' },
                dayHeaderFormat: { weekday: 'short' },
                dayMaxEvents: 2,
                fixedWeekCount: false,
                nowIndicator: true,
                allDaySlot: false,
                slotMinTime: '07:00:00',
                slotMaxTime: '22:00:00',
                views: {
                    timeGridWeek: {
                        displayEventTime: true,
                        titleFormat: { day: 'numeric', month: 'short', year: 'numeric' },
                        dayHeaderFormat: { weekday: 'short', day: 'numeric' },
                    },
                    listMonth: { displayEventTime: true },
                },
                noEventsContent: 'No hay sesiones este mes',
                dayCellClassNames: (arg) => {
                    const d = arg.date;
                    const fecha = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                    return fecha === seleccionada ? ['dia-seleccionado'] : [];
                },
                eventClick: (info) => {
                    if (seleccionable) seleccionar(info.event.startStr.slice(0, 10));
                },
                dateClick: (info) => {
                    const fecha = info.dateStr.slice(0, 10);
                    if (seleccionable && seleccionar(fecha)) return;
                    if (!url) return;
                    window.location.href = url + (url.includes('?') ? '&' : '?') + 'fecha=' + fecha;
                },
            });
            calendar.render();
            new ResizeObserver(() => calendar.updateSize()).observe(el);
            } catch (error) {
                console.error('Calendario:', error);
                mostrarError('El calendario no se ha podido mostrar: ' + (error && error.message ? error.message : error));
            }
        };

        // Se dibuja en cuanto el HTML está listo (sin esperar a que carguen imágenes, fuentes, etc.)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', iniciar);
        } else {
            iniciar();
        }
        })();
    </script>
@endpush