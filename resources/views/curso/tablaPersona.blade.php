<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
@php
    $tituloCurso = $curso->formacion?->nombre ?? ('Curso ' . $curso->codigo_accion . '/' . $curso->codigo_grupo);
    $action = route('curso.alumno.create', $curso);

    $lista = $personasDisponibles->map(fn ($p) => [
        'id'     => $p->id,
        'nombre' => trim($p->nombre . ' ' . $p->primer_apellido . ' ' . $p->segundo_apellido),
        'doc'    => $p->DNI ?: ($p->NIE ?: ''),
    ])->values();
@endphp

<x-interfaz.layout.app titulo="Agregar alumnos · {{ $tituloCurso }}" :rol="$rol" :usuario="$nombre" :contadores="$contadores ?? []">

    <x-interfaz.panel.cabecera titulo="Agregar alumnos" subtitulo="{{ $tituloCurso }}">
        <x-slot:acciones>
            <a href="{{ route('curso.visualizar', $curso) }}"
               class="inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                ← Volver al curso
            </a>
            <a href="{{ route('persona.create') }}"
               class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                Nueva persona
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    <x-interfaz.panel.tarjeta titulo="Personas disponibles">
        <form method="POST" action="{{ $action }}"
            x-data="{
                personas: @js($lista),
                elegidos: [],
                buscar: '',
                get disponibles() {
                    const q = this.buscar.toLowerCase();
                    return this.personas.filter(p =>
                        !this.elegidos.some(e => e.id === p.id) &&
                        (p.nombre + ' ' + p.doc).toLowerCase().includes(q));
                },
                agregar(p) { this.elegidos.push(p); },
                quitar(p) { this.elegidos = this.elegidos.filter(e => e.id !== p.id); }
            }">
            @csrf

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- Disponibles --}}
                <div>
                    <input type="search" x-model="buscar" placeholder="Buscar por nombre o DNI..."
                        class="mb-3 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                    <ul class="max-h-80 divide-y divide-stone-200 overflow-y-auto rounded-lg border border-stone-200">
                        <template x-for="p in disponibles" :key="p.id">
                            <li>
                                <button type="button" @click="agregar(p)"
                                    class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-stone-50">
                                    <span x-text="p.nombre"></span>
                                    <span class="font-mono text-xs text-slate-500" x-text="p.doc"></span>
                                </button>
                            </li>
                        </template>
                        <li x-show="disponibles.length === 0" class="px-3 py-4 text-sm text-slate-500">
                            No hay personas que mostrar.
                        </li>
                    </ul>
                </div>

                {{-- Elegidos --}}
                <div>
                    <h3 class="mb-3 py-2 text-sm font-semibold text-slate-700">
                        Alumnos a agregar (<span x-text="elegidos.length"></span>)
                    </h3>

                    <ul class="max-h-80 divide-y divide-stone-200 overflow-y-auto rounded-lg border border-stone-200">
                        <template x-for="p in elegidos" :key="p.id">
                            <li class="flex items-center justify-between px-3 py-2 text-sm">
                                <span x-text="p.nombre"></span>
                                <button type="button" @click="quitar(p)"
                                    class="text-xs font-semibold text-red-700 hover:underline">Quitar</button>
                                <input type="hidden" name="personas[]" :value="p.id">
                            </li>
                        </template>
                        <li x-show="elegidos.length === 0" class="px-3 py-4 text-sm text-slate-500">
                            Pulsa una persona para añadirla.
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-5 flex justify-end">
                <button type="submit" :disabled="elegidos.length === 0"
                    class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900 disabled:cursor-not-allowed disabled:opacity-50">
                    Guardar alumnos
                </button>
            </div>
        </form>
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>