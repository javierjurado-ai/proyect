@props([
    'eventos' => [],
    'inicial' => null,
    'vista' => 'dayGridMonth',
    'url' => null,
])

@php $id = 'calendario-' . \Illuminate\Support\Str::random(8); @endphp

<div id="{{ $id }}" style="min-height: 500px; width: 100%"></div>

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    @endpush
@endonce

@push('scripts')
    <script>
        window.addEventListener('load', () => {
            const el = document.getElementById(@js($id));
            if (!el) return;

            const url = @js($url);

            const calendar = new FullCalendar.Calendar(el, {
                initialView: @js($vista),
                initialDate: @js($inicial),
                locale: 'es',
                firstDay: 1,
                events: @js($eventos),
                displayEventTime: false,
                dateClick: (info) => {
                    if (!url) return;
                    window.location.href = url + (url.includes('?') ? '&' : '?') + 'fecha=' + info.dateStr;
                },
            });
            calendar.render();
            new ResizeObserver(() => calendar.updateSize()).observe(el);
        });
    </script>
@endpush