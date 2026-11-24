<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
@php
    $tituloCurso = $curso->formacion?->nombre ?? 'Curso ' . $curso->codigo_accion . '/' . $curso->codigo_grupo;
    $action = route('curso.alumno.create', $curso);

    $lista = $personasDisponibles
        ->map(
            fn($p) => [
                'id' => $p->id,
                'nombre' => trim($p->nombre . ' ' . $p->primer_apellido . ' ' . $p->segundo_apellido),
                'doc' => $p->DNI ?: ($p->NIE ?: ''),
            ],
        )
        ->values();
@endphp

<x-interfaz.layout.app titulo="Agregar alumnos · {{ $tituloCurso }}" :rol="$rol" :usuario="$nombre"
    :contadores="$contadores ?? []">

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

    {{-- Estado compartido entre el formulario principal y la ventana emergente --}}
    <div x-data="{
        personas: @js($lista),
        elegidos: [], // [{ id, nombre, doc, datos: { campo: valor } }]
        buscar: '',
        abierto: false,
        pendiente: null, // persona cuyo formulario está abierto
        error: '',
        requeridos: [
            'empresa_id', 'jornada_laboral', 'categoria_profesional', 'erte', 'fijo_discontinuo',
            'nivel_estudio', 'diploma', 'certificado_discapacidad',
            'certificado_afectado_terrorismo', 'certificado_afectado_violencia_genero'
        ],
        get disponibles() {
            const q = this.buscar.toLowerCase();
            return this.personas.filter(p =>
                !this.elegidos.some(e => e.id === p.id) &&
                (p.nombre + ' ' + p.doc).toLowerCase().includes(q));
        },
        abrir(p) {
            this.pendiente = p;
            this.error = '';
            this.$refs.mform.reset();
            this.abierto = true;
        },
        cerrar() {
            this.abierto = false;
            this.pendiente = null;
            this.error = '';
        },
        confirmar() {
            const datos = Object.fromEntries(new FormData(this.$refs.mform));
            delete datos.curso_id;
            delete datos._token;
            const faltan = this.requeridos.filter(c => !datos[c]);
            if (faltan.length) {
                this.error = 'Completa todos los campos obligatorios antes de agregar al alumno.';
                return;
            }
            this.elegidos.push({ ...this.pendiente, datos });
            this.cerrar();
        },
        quitar(p) { this.elegidos = this.elegidos.filter(e => e.id !== p.id); }
    }" @keydown.escape.window="cerrar()">

        {{-- FORMULARIO PRINCIPAL: solo envía los alumnos ya completados --}}
        <form method="POST" action="{{ $action }}">
            @csrf
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <input type="hidden" name="curso_id" value="{{ $curso->id }}">

            <x-interfaz.panel.tarjeta titulo="Personas disponibles">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {{-- Disponibles --}}
                    <div>
                        <input type="search" x-model="buscar" placeholder="Buscar por nombre o DNI..."
                            class="mb-3 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                        <ul
                            class="max-h-80 divide-y divide-stone-200 overflow-y-auto rounded-lg border border-stone-200">
                            <template x-for="p in disponibles" :key="p.id">
                                <li>
                                    <button type="button" @click="abrir(p)"
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

                    {{-- Alumnos ya completados --}}
                    <div>
                        <h3 class="mb-3 py-2 text-sm font-semibold text-slate-700">
                            Alumnos a agregar (<span x-text="elegidos.length"></span>)
                        </h3>

                        <ul
                            class="max-h-80 divide-y divide-stone-200 overflow-y-auto rounded-lg border border-stone-200">
                            <template x-for="p in elegidos" :key="p.id">
                                <li class="flex items-center justify-between px-3 py-2 text-sm">
                                    <span x-text="p.nombre"></span>
                                    <button type="button" @click="quitar(p)"
                                        class="text-xs font-semibold text-red-700 hover:underline">Quitar</button>

                                    <input type="hidden" :name="`alumnos[${p.id}][persona_id]`" :value="p.id">
                                    <template x-for="(valor, campo) in p.datos" :key="campo">
                                        <input type="hidden" :name="`alumnos[${p.id}][${campo}]`"
                                            :value="valor">
                                    </template>
                                </li>
                            </template>
                            <li x-show="elegidos.length === 0" class="px-3 py-4 text-sm text-slate-500">
                                Pulsa una persona y rellena sus datos para añadirla.
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
            </x-interfaz.panel.tarjeta>
        </form>

        {{-- VENTANA EMERGENTE (fuera del <form> principal para no anidar formularios) --}}
        <div x-show="abierto" x-cloak x-transition.opacity
            class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4 sm:p-8"
            @click.self="cerrar()">
            <div class="w-full max-w-4xl rounded-xl bg-white shadow-xl" role="dialog" aria-modal="true">
                <div class="flex items-center justify-between border-b border-stone-200 px-6 py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Datos de matrícula</p>
                        <h2 class="text-lg font-semibold text-slate-900" x-text="pendiente ? pendiente.nombre : ''">
                        </h2>
                    </div>
                    <button type="button" @click="cerrar()" aria-label="Cerrar"
                        class="rounded-lg px-2 py-1 text-xl leading-none text-slate-500 hover:bg-stone-100">×</button>
                </div>

                <form x-ref="mform" @submit.prevent="confirmar()" class="px-6 py-5">
                    @include('curso.formularioAlumno', ['incrustado' => true])

                    <p x-show="error" x-text="error" class="mb-4 text-sm font-semibold text-red-700"></p>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="cerrar()"
                            class="inline-flex items-center rounded-lg border border-stone-300 bg-white px-5 py-2 text-sm font-semibold text-marca-950 hover:bg-stone-50">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="inline-flex items-center rounded-lg bg-marca-800 px-5 py-2 text-sm font-semibold text-white hover:bg-marca-900">
                            Agregar alumno
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-interfaz.layout.app>
