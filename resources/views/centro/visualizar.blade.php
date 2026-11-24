<x-interfaz.layout.app :titulo="$centro->nombre" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera :titulo="$centro->nombre" subtitulo="{{ $centro->direccion }} · {{ $centro->municipio }}">
        <x-slot:acciones>
            <a href="{{ route('centro.index') }}"
               class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                ← Volver a centros
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    {{-- Sistema de Pestañas --}}
    <div class="mb-6 border-b border-stone-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button onclick="cambiarPestana('informacion')" id="tab-informacion"
                class="border-marca-800 text-marca-800 whitespace-nowrap border-b-2 py-4 px-1 text-sm font-semibold">
                Información
            </button>
            <button onclick="cambiarPestana('aulas')" id="tab-aulas"
                class="border-transparent text-stone-500 hover:border-stone-300 hover:text-stone-700 whitespace-nowrap border-b-2 py-4 px-1 text-sm font-semibold">
                Aulas
            </button>
        </nav>
    </div>

    {{-- Contenido Pestaña Información --}}
    <div id="contenido-informacion" class="space-y-6">
        <section class="rounded-xl border border-stone-200 bg-white p-6 lg:p-8">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-marca-800">Datos generales</h3>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <span class="block text-xs font-medium text-stone-500">Tipo de centro</span>
                    <span class="text-sm font-semibold text-stone-900">{{ ucfirst(strtolower($centro->tipo_centro->value)) }}</span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-stone-500">Empresa asociada</span>
                    <span class="text-sm font-semibold text-stone-900">{{ $centro->empresa?->nombre ?? '—' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-stone-500">Municipio y Provincia</span>
                    <span class="text-sm font-semibold text-stone-900">{{ $centro->municipio }} ({{ $centro->provincia }})</span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-stone-500">Dirección / C.P.</span>
                    <span class="text-sm font-semibold text-stone-900">{{ $centro->direccion }} · {{ $centro->codigo_postal }}</span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-stone-500">Teléfono</span>
                    <span class="text-sm font-semibold text-stone-900">{{ $centro->telefono }}</span>
                </div>
                <div>
                    <span class="block text-xs font-medium text-stone-500">Email</span>
                    <span class="text-sm font-semibold text-stone-900">{{ $centro->email }}</span>
                </div>
            </div>
        </section>
    </div>

    {{-- Contenido Pestaña Aulas (Se carga dinámicamente o por AJAX) --}}
    <div id="contenido-aulas" class="hidden space-y-6">
        <div id="tabla-aulas-container">
            <div class="rounded-xl border border-stone-200 bg-white p-8 text-center text-stone-500">
                Cargando aulas...
            </div>
        </div>
    </div>

    <script>
        function cambiarPestana(pestana) {
            const tabInfo = document.getElementById('tab-informacion');
            const tabAulas = document.getElementById('tab-aulas');
            const contInfo = document.getElementById('contenido-informacion');
            const contAulas = document.getElementById('contenido-aulas');

            if (pestana === 'informacion') {
                tabInfo.className = 'border-marca-800 text-marca-800 whitespace-nowrap border-b-2 py-4 px-1 text-sm font-semibold';
                tabAulas.className = 'border-transparent text-stone-500 hover:border-stone-300 hover:text-stone-700 whitespace-nowrap border-b-2 py-4 px-1 text-sm font-semibold';
                contInfo.classList.remove('hidden');
                contAulas.classList.add('hidden');
            } else {
                tabAulas.className = 'border-marca-800 text-marca-800 whitespace-nowrap border-b-2 py-4 px-1 text-sm font-semibold';
                tabInfo.className = 'border-transparent text-stone-500 hover:border-stone-300 hover:text-stone-700 whitespace-nowrap border-b-2 py-4 px-1 text-sm font-semibold';
                contAulas.classList.remove('hidden');
                contInfo.classList.add('hidden');

                // Cargar datos de aulas si está vacío
                const contenedor = document.getElementById('tabla-aulas-container');
                if (contenedor.dataset.loaded !== 'true') {
                    fetch("{{ route('centro.aulas', $centro->id) }}")
                        .then(res => res.text())
                        .then(html => {
                            contenedor.innerHTML = html;
                            contenedor.dataset.loaded = 'true';
                        });
                }
            }
        }
    </script>

</x-interfaz.layout.app>