<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curso</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    <x-formulario.tarjeta titulo="Curso" subtitulo="Los campos marcados con * son obligatorios">
        <form id="curso-form" method="POST" action="{{ route('curso.agregarCurso') }}">
            @csrf



            <x-formulario.seccion titulo="Datos del curso">
                <x-formulario.entrada name="codigo_curso" label="Código *" type="number" />
                <x-formulario.entrada name="empresa_formadora" label="Empresa formadora *"
                    contenedor="md:col-span-2" />

                <x-formulario.selector name="formacion_id" label="Formación *" placeholder="Busca una formación..."
                    :options="$formaciones" contenedor="md:col-span-2" />
                <x-formulario.selector name="centro_id" label="Centro" placeholder="Busca un centro..."
                    :options="$centros" />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Fechas y horario">
                <x-formulario.entrada name="fecha_inicio" label="Fecha inicio *" type="date" />
                <x-formulario.entrada name="fecha_fin" label="Fecha fin *" type="date" />
                <x-formulario.radio name="turno" label="Turno *" :options="$turnos" />

                <x-formulario.entrada name="hora_inicio" label="Hora inicio *" type="time" />
                <x-formulario.entrada name="hora_final" label="Hora fin *" type="time" />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Aula y contacto">
                <x-formulario.entrada name="aula" label="Aula *" />
                <x-formulario.entrada name="aula_virtual" label="Aula virtual *" />
                <x-formulario.entrada name="tecnico_comunidad" label="Técnico Comunidad *" />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Observaciones" columnas="1">
                <x-formulario.entrada name="observaciones" label="Observaciones *" />
            </x-formulario.seccion>

            <div class="flex justify-end gap-3">
                <x-formulario.boton type="reset" variante="secundario">Limpiar</x-formulario.boton>
                <x-formulario.boton type="submit">Guardar curso</x-formulario.boton>
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