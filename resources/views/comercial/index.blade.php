{{-- Inicio del COMERCIAL: empresas, presupuestos y catálogo --}}
@php
    // Enlace a una ruta solo si existe
    $ruta  = fn (string $nombre, $param = null) => \Illuminate\Support\Facades\Route::has($nombre) ? route($nombre, $param) : '#';
    $euros = fn ($n) => number_format((float) $n, 2, ',', '.') . ' €';

    // Mismos colores de modalidad que el catálogo y la tabla de formaciones
    $modalidades = [
        'PRESENCIAL'    => ['texto' => 'Presencial',    'franja' => 'bg-blue-500',   'chip' => 'bg-blue-100 text-blue-800'],
        'TELEFORMACION' => ['texto' => 'Teleformación', 'franja' => 'bg-violet-500', 'chip' => 'bg-violet-100 text-violet-800'],
        'MIXTA'         => ['texto' => 'Mixta',         'franja' => 'bg-amber-500',  'chip' => 'bg-amber-100 text-amber-800'],
    ];

    $iniciales = fn ($texto) => collect(preg_split('/\s+/', trim($texto)))
        ->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');

    $boton = 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold';
@endphp

<x-interfaz.layout.app titulo="Inicio" :rol="$rol" :usuario="$nombre" :contadores="[]">

    <x-interfaz.panel.cabecera :titulo="'Hola, ' . $primerNombre" subtitulo="Tus empresas, presupuestos y catálogo">
        <x-slot:acciones>
            <a href="{{ $ruta('empresa.create') }}" class="{{ $boton }} border border-stone-300 bg-white text-marca-950 hover:bg-stone-50">
                + Nueva empresa
            </a>
            <a href="{{ $ruta('presupuesto.create') }}" class="{{ $boton }} bg-marca-800 text-white hover:bg-marca-900">
                + Nuevo presupuesto
            </a>
        </x-slot:acciones>
    </x-interfaz.panel.cabecera>

    {{-- Aviso: el crédito caduca a final de año --}}
    <div class="mb-6 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm text-amber-900">
        <svg class="h-5 w-5 shrink-0 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        <p>
            <strong>Quedan {{ $diasParaFinDeAnio }} {{ $diasParaFinDeAnio === 1 ? 'día' : 'días' }}</strong>
            para que caduque el crédito {{ $anio }} de tus empresas. Buen momento para enviar presupuestos.
        </p>
    </div>

    {{-- Indicadores --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-interfaz.panel.metrica titulo="Empresas" :valor="$totalEmpresas" />
        <x-interfaz.panel.metrica titulo="Presupuestos" :valor="$totalPresupuestos" />
        <x-interfaz.panel.metrica titulo="Importe presupuestado" :valor="$euros($importeTotal)" detalle="bonificable en total" />
        <x-interfaz.panel.metrica titulo="Formaciones en catálogo" :valor="$totalFormaciones" />
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-5">

        {{-- Últimos presupuestos --}}
        <x-interfaz.panel.tarjeta titulo="Últimos presupuestos" :enlace="$ruta('presupuesto.index')" class="lg:col-span-3">
            @if ($presupuestos->isEmpty())
                <p class="text-sm text-slate-500">Todavía no hay presupuestos.</p>
            @else
                <ul class="divide-y divide-stone-200">
                    @foreach ($presupuestos as $p)
                        <li class="flex items-center justify-between gap-4 py-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900">{{ $p->nombre_formacion ?? 'Presupuesto #' . $p->id }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ collect([
                                        $p->numero_alumnos ? $p->numero_alumnos . ' alumnos' : null,
                                        $p->horas_totales ? $p->horas_totales . ' h' : null,
                                        $p->fecha_bonificado ? \Illuminate\Support\Carbon::parse($p->fecha_bonificado)->format('d/m/Y') : null,
                                    ])->filter()->implode(' · ') }}
                                </p>
                            </div>
                            <span class="whitespace-nowrap text-sm font-bold text-emerald-700">{{ $euros($p->importe_bonificado) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-interfaz.panel.tarjeta>

        {{-- Empresas recientes --}}
        <x-interfaz.panel.tarjeta titulo="Empresas recientes" :enlace="$ruta('empresa.index')" texto-enlace="Ver todas" class="lg:col-span-2">
            @if ($empresas->isEmpty())
                <p class="text-sm text-slate-500">Todavía no hay empresas.</p>
            @else
                <ul class="divide-y divide-stone-200">
                    @foreach ($empresas as $e)
                        <li class="flex items-center gap-3 py-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-marca-50 text-xs font-bold text-marca-800">
                                {{ $iniciales($e->nombre) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $e->nombre }}</p>
                                <p class="text-xs text-slate-500">CIF {{ $e->cif }}</p>
                            </div>
                            @if ($e->total_trabajadores)
                                <x-interfaz.panel.etiqueta>{{ $e->total_trabajadores }} trab.</x-interfaz.panel.etiqueta>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-interfaz.panel.tarjeta>
    </div>

    {{-- Destacados del catálogo --}}
    <x-interfaz.panel.tarjeta titulo="Destacados del catálogo" :enlace="$ruta('formacion.index')" texto-enlace="Ver catálogo">
        @if ($destacadas->isEmpty())
            <p class="text-sm text-slate-500">El catálogo está vacío.</p>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($destacadas as $f)
                    @php
                        $valorMod = $f->modalidad instanceof \BackedEnum ? $f->modalidad->value : $f->modalidad;
                        $mod = $modalidades[$valorMod] ?? ['texto' => $valorMod, 'franja' => 'bg-stone-400', 'chip' => 'bg-stone-100 text-slate-700'];
                        $hayDetalle = \Illuminate\Support\Facades\Route::has('formacion.detalle');
                    @endphp
                    <article class="flex flex-col overflow-hidden rounded-xl border border-stone-200">
                        <div class="h-2 {{ $mod['franja'] }}"></div>
                        <div class="flex flex-1 flex-col gap-2 p-4">
                            @if ($f->sector)
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $f->sector }}</span>
                            @endif
                            <h3 class="text-base font-bold text-marca-950">{{ $f->nombre }}</h3>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                                @if ($valorMod)
                                    <span class="rounded-full px-2.5 py-0.5 font-semibold {{ $mod['chip'] }}">{{ $mod['texto'] }}</span>
                                @endif
                                @if ($f->horas_totales) <span>{{ $f->horas_totales }} horas</span> @endif
                            </div>
                            <div class="mt-auto flex items-end justify-between gap-3 border-t border-stone-100 pt-3">
                                <div>
                                    <span class="block text-xs text-slate-500">Precio</span>
                                    <span class="text-lg font-bold text-marca-950">{{ $f->precio !== null ? $euros($f->precio) : 'Consultar' }}</span>
                                </div>
                                <a href="{{ $hayDetalle ? route('formacion.detalle', $f->id) : $ruta('formacion.index') }}"
                                   class="rounded-lg bg-marca-800 px-3 py-2 text-xs font-semibold text-white hover:bg-marca-900">
                                    Ver más información
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </x-interfaz.panel.tarjeta>

</x-interfaz.layout.app>
