
@props([
    'rol' => '',
    'contadores' => [],
])

<aside id="menu"
    class="fixed inset-y-0 left-0 z-30 flex w-60 shrink-0 -translate-x-full flex-col overflow-y-auto bg-marca-950 px-4 py-5 text-white transition-transform lg:static lg:translate-x-0">

    <a href="{{ \Illuminate\Support\Facades\Route::has('inicio') ? route('inicio') : url('/') }}"
        class="mb-8 flex items-center gap-3 px-2 text-lg font-bold">
        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-400 text-marca-950">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 10 12 5 2 10l10 5 10-5Z" />
                <path d="M6 12v5c3 2 9 2 12 0v-5" />
            </svg>
        </span>
        {{ config('app.name') }}
    </a>

    <nav aria-label="Menú principal" class="flex flex-col gap-1">
        <x-layout.menu-enlace :rol="$rol" ruta="inicio">Inicio</x-layout.menu-enlace>

        {{-- Formación --}}
        <x-layout.menu-enlace :rol="$rol" ruta="curso.crearTablaCurso" roles="ADMINISTRADOR,COORDINADOR,COMERCIAL">Cursos</x-layout.menu-enlace>
        <x-layout.menu-enlace :rol="$rol" ruta="formacion.index" roles="ADMINISTRADOR,COORDINADOR">Formaciones</x-layout.menu-enlace>
        <x-layout.menu-enlace :rol="$rol" ruta="aula.index" roles="ADMINISTRADOR,COORDINADOR">Aulas</x-layout.menu-enlace>
        <x-layout.menu-enlace :rol="$rol" ruta="centro.index" roles="ADMINISTRADOR,COORDINADOR">Centros</x-layout.menu-enlace>
        <x-layout.menu-enlace :rol="$rol" ruta="alumno.index" roles="ADMINISTRADOR,COORDINADOR">Alumnos</x-layout.menu-enlace>
        <x-layout.menu-enlace :rol="$rol" ruta="docente.index" roles="ADMINISTRADOR,COORDINADOR">Docentes</x-layout.menu-enlace>
        <x-layout.menu-enlace :rol="$rol" ruta="logistica.index" roles="ADMINISTRADOR,COORDINADOR" :contador="$contadores['logistica'] ?? null">Logística</x-layout.menu-enlace>
        <x-layout.menu-enlace :rol="$rol" ruta="fundae.index" roles="ADMINISTRADOR,COORDINADOR" :contador="$contadores['fundae'] ?? null">Comunicaciones FUNDAE</x-layout.menu-enlace>

        {{-- Comercial --}}
        <x-layout.menu-enlace :rol="$rol" ruta="empresa.index" roles="ADMINISTRADOR,COMERCIAL">Empresas</x-layout.menu-enlace>
        <x-layout.menu-enlace :rol="$rol" ruta="presupuesto.index" roles="ADMINISTRADOR,COMERCIAL" :contador="$contadores['presupuestos'] ?? null">Presupuestos</x-layout.menu-enlace>
        <x-layout.menu-enlace :rol="$rol" ruta="credito.index" roles="ADMINISTRADOR,COMERCIAL">Créditos</x-layout.menu-enlace>

        {{-- Administración --}}
        <x-layout.menu-enlace :rol="$rol" ruta="usuario.index" roles="ADMINISTRADOR" :contador="$contadores['usuarios'] ?? null">Usuarios y roles</x-layout.menu-enlace>
    </nav>
</aside>
