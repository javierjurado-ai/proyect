
<x-interfaz.layout.app titulo="Tickets">
    <x-ticket.contenedor :activa="(bool) $conversacionActiva">

        {{-- ===== Lista de tickets ===== --}}
        <x-slot:lista>
            <x-ticket.buscador titulo="Tickets">
                <p class="text-sm text-slate-500">{{ $conversaciones->count() }}</p>
            </x-ticket.buscador>

            <div class="flex-1 overflow-y-auto">
                @forelse ($conversaciones as $conversacion)
                    @php $ultimo = $conversacion->ultimoMensaje; @endphp

                    <x-ticket.conversacion :href="route('ticket.index', [$curso, $conversacion])" :nombre="$conversacion->titulo ?? 'Ticket #' . $conversacion->id" :ultimo-mensaje="$ultimo?->mensaje" :hora="$ultimo
                        ? ($ultimo->fecha_creada?->isToday()
                            ? substr($ultimo->hora_creada, 0, 5)
                            : $ultimo->fecha_creada?->format('d/m/Y'))
                        : null"
                        :activa="$conversacionActiva?->is($conversacion)" />
                @empty
                    <p class="p-4 text-sm text-slate-500">Todavía no hay tickets.</p>
                @endforelse
            </div>
        </x-slot:lista>

        {{-- ===== Ticket abierto ===== --}}
        @if ($conversacionActiva)
            <x-ticket.cabecera :nombre="$conversacionActiva->titulo ?? 'Ticket #' . $conversacionActiva->id" :subtitulo="'Pedido #' . $conversacionActiva->peticiones_logistica_id" :volver="route('ticket.index', $curso)" />

            <div class="flex-1 overflow-y-auto bg-slate-100 px-4 py-3" data-chat-mensajes>
                @php $fechaAnterior = null; @endphp

                @forelse ($mensajes as $mensaje)
                    @php $fecha = $mensaje->fecha_creada?->format('Y-m-d'); @endphp

                    @if ($fecha && $fecha !== $fechaAnterior)
                        <x-ticket.separador-fecha :fecha="$mensaje->fecha_creada->isToday()
                            ? 'Hoy'
                            : ($mensaje->fecha_creada->isYesterday()
                                ? 'Ayer'
                                : $mensaje->fecha_creada->format('d/m/Y'))" />
                        @php $fechaAnterior = $fecha; @endphp
                    @endif

                    <x-ticket.mensaje :propio="$mensaje->persona_id == $personaId" :autor="$mensaje->persona?->nombre"
                        :hora="substr($mensaje->hora_creada, 0, 5)">{{ $mensaje->mensaje }}</x-ticket.mensaje>
                @empty
                    <div class="flex h-full items-center justify-center">
                        <p class="text-sm text-slate-500">Aún no hay mensajes. ¡Escribe el primero!</p>
                    </div>
                @endforelse
            </div>

            <x-ticket.formulario :action="route('ticket.mensajes.store', [$curso, $conversacionActiva])" />
        @else
            <div class="flex flex-1 flex-col items-center justify-center bg-slate-100 p-6 text-center">
                <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-teal-800/10 text-teal-800">
                    <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM2.25 12c0 4.556 4.03 8.25 9 8.25a9.764 9.764 0 0 0 2.555-.337A5.972 5.972 0 0 0 18 21a5.969 5.969 0 0 0 .474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C20.21 16.47 21.75 14.38 21.75 12c0-4.556-4.03-8.25-9-8.25s-9 3.694-9 8.25Z" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-700">Selecciona un ticket</p>
                <p class="mt-1 text-sm text-slate-500">Elige un ticket de la lista para ver los mensajes.</p>
            </div>
        @endif
    </x-ticket.contenedor>

    @if ($conversacionActiva)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const ticketId = @js($conversacionActiva->id);
                const personaId = @js($personaId);
                const contenedor = document.querySelector('[data-chat-mensajes]');
                const formulario = document.querySelector('[data-chat-formulario]');
                const input = document.querySelector('[data-chat-input]');

                const bajar = () => contenedor.scrollTop = contenedor.scrollHeight;
                bajar();

                // Pinta una burbuja con las mismas clases que el componente x-ticket.mensaje
                const pintar = (m) => {
                    contenedor.querySelector('[data-chat-vacio]')?.remove();

                    const propio = m.persona_id == personaId;

                    const fila = document.createElement('div');
                    fila.className = 'mb-1.5 flex ' + (propio ? 'justify-end' : 'justify-start');

                    const burbuja = document.createElement('div');
                    burbuja.className = 'max-w-[80%] rounded-lg px-3 py-2 shadow-sm sm:max-w-[65%] ' +
                        (propio ? 'rounded-br-sm bg-teal-800 text-white' : 'rounded-bl-sm bg-white text-slate-700');

                    if (!propio && m.autor) {
                        const autor = document.createElement('p');
                        autor.className = 'mb-0.5 text-xs font-semibold text-teal-800';
                        autor.textContent = m.autor;
                        burbuja.appendChild(autor);
                    }

                    const texto = document.createElement('p');
                    texto.className = 'text-sm break-words whitespace-pre-line';
                    texto.textContent = m.mensaje; // textContent: evita inyectar HTML
                    burbuja.appendChild(texto);

                    const hora = document.createElement('div');
                    hora.className = 'mt-1 flex items-center justify-end gap-1 text-[11px] ' +
                        (propio ? 'text-teal-100' : 'text-slate-400');
                    hora.textContent = m.hora;
                    burbuja.appendChild(hora);

                    fila.appendChild(burbuja);
                    contenedor.appendChild(fila);
                    bajar();
                };

                // Recibir los mensajes de los demás en tiempo real
                window.Echo.private(`ticket.${ticketId}`)
                    .listen('.mensaje.enviado', (m) => pintar(m));

                // Enviar sin recargar la página
                formulario.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const contenido = input.value.trim();
                    if (!contenido) return;

                    const respuesta = await fetch(formulario.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': formulario.querySelector('[name=_token]').value,
                            'X-Socket-ID': window.Echo.socketId() ?? '',
                        },
                        body: JSON.stringify({
                            contenido
                        }),
                    });

                    if (!respuesta.ok) {
                        alert('No se ha podido enviar el mensaje.');
                        return;
                    }

                    pintar(await respuesta.json());
                    input.value = '';
                    input.style.height = 'auto';
                    input.focus();
                });

                // El textarea crece según el texto
                input.addEventListener('input', () => {
                    input.style.height = 'auto';
                    input.style.height = input.scrollHeight + 'px';
                });

                // Enter envía, Mayús+Enter añade una línea
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        formulario.requestSubmit();
                    }
                });

                input.focus();
            });
        </script>
    @endif
</x-interfaz.layout.app>
