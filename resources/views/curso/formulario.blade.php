<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($curso) ? 'Editar Curso' : 'Crear Curso' }}</title>

    {{-- Si no hay token válido, vuelve al inicio de sesión --}}
    <script>
        const expira = Number(sessionStorage.getItem('expira')); 

        if (!token || Date.now() / 1000 > expira) {
            sessionStorage.clear();
            window.location.href = '/persona/iniciarSesion';
        }
    </script>

    <title>Curso</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    <x-formulario.tarjeta 
        titulo="{{ isset($curso) ? 'Editar Curso' : 'Curso' }}" 
        subtitulo="Los campos marcados con * son obligatorios">
        
        <form id="curso-form" method="POST" action="{{ isset($curso) ? route('curso.update', $curso->id) : route('curso.agregarCurso') }}">
            @csrf
            @isset($curso)
                @method('PUT')
            @endisset

            <x-formulario.seccion titulo="Datos del curso">
                <x-formulario.entrada 
                    name="codigo_curso" 
                    label="Código *" 
                    type="number" 
                    value="{{ old('codigo_curso', $curso->codigo_curso ?? '') }}" 
                />
                <x-formulario.entrada 
                    name="empresa_formadora" 
                    label="Empresa formadora *"
                    contenedor="md:col-span-2" 
                    value="{{ old('empresa_formadora', $curso->empresa_formadora ?? '') }}" 
                />

                <x-formulario.selector 
                    name="formacion_id" 
                    label="Formación *" 
                    placeholder="Busca una formación..."
                    :options="$formaciones" 
                    contenedor="md:col-span-2" 
                    value="{{ old('formacion_id', $curso->formacion_id ?? '') }}" 
                />
                <x-formulario.selector 
                    name="centro_id" 
                    label="Centro" 
                    placeholder="Busca un centro..."
                    :options="$centros" 
                    value="{{ old('centro_id', $curso->centro_id ?? '') }}" 
                />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Fechas y horario">
                <x-formulario.entrada 
                    name="fecha_inicio" 
                    label="Fecha inicio *" 
                    type="date" 
                    value="{{ old('fecha_inicio', isset($curso->fecha_inicio) ? \Illuminate\Support\Carbon::parse($curso->fecha_inicio)->format('Y-m-d') : '') }}" 
                />
                <x-formulario.entrada 
                    name="fecha_fin" 
                    label="Fecha fin *" 
                    type="date" 
                    value="{{ old('fecha_fin', isset($curso->fecha_fin) ? \Illuminate\Support\Carbon::parse($curso->fecha_fin)->format('Y-m-d') : '') }}" 
                />
                <x-formulario.radio 
                    name="turno" 
                    label="Turno *" 
                    :options="$turnos" 
                    value="{{ old('turno', $curso->turno->value ?? $curso->turno ?? '') }}" 
                />

                <x-formulario.entrada 
                    name="hora_inicio" 
                    label="Hora inicio *" 
                    type="time" 
                    value="{{ old('hora_inicio', isset($curso->hora_inicio) ? substr($curso->hora_inicio, 0, 5) : '') }}" 
                />
                <x-formulario.entrada 
                    name="hora_final" 
                    label="Hora fin *" 
                    type="time" 
                    value="{{ old('hora_final', isset($curso->hora_final) ? substr($curso->hora_final, 0, 5) : '') }}" 
                />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Aula y contacto">
                <x-formulario.entrada 
                    name="aula" 
                    label="Aula *" 
                    value="{{ old('aula', $curso->aula ?? '') }}" 
                />
                <x-formulario.entrada 
                    name="aula_virtual" 
                    label="Aula virtual *" 
                    value="{{ old('aula_virtual', $curso->aula_virtual ?? '') }}" 
                />
                <x-formulario.entrada 
                    name="tecnico_comunidad" 
                    label="Técnico Comunidad *" 
                    value="{{ old('tecnico_comunidad', $curso->tecnico_comunidad ?? '') }}" 
                />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Observaciones" columnas="1">
                <x-formulario.entrada 
                    name="observaciones" 
                    label="Observaciones *" 
                    value="{{ old('observaciones', $curso->observaciones ?? '') }}" 
                />
            </x-formulario.seccion>

            <div class="flex justify-end gap-3">
                <x-formulario.boton type="reset" variante="secundario">Limpiar</x-formulario.boton>
                <x-formulario.boton type="submit">
                    {{ isset($curso) ? 'Actualizar curso' : 'Guardar curso' }}
                </x-formulario.boton>
            </div>
        </form>
    </x-formulario.tarjeta>

    @if (session('ok'))
    <script>
        document.getElementById('curso-form').addEventListener('focusin', () => {
            document.getElementById('success-alert')?.remove();
        }, { once: true });
    </script>
    @endif

</body>

</html>