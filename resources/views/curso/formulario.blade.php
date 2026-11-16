<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Formulario Curso</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    <form method="POST" action="{{ route('curso.agregarCurso') }}">
        @csrf

        <x-formulario.tarjeta>
            @if (session('status'))
                <p class="mb-4 text-sm text-green-600">{{ session('status') }}</p>
            @endif

            @error('api')
                <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <x-formulario.seccion titulo="Datos Curso">
                <x-formulario.entrada name="codigo_curso" label="Código*" type="number" />
                <x-formulario.entrada name="empresa_formadora" label="Empresa" type="text" />
                <x-formulario.entrada name="fecha_inicio" label="Fecha Inicio*" type="date" />
                <x-formulario.entrada name="fecha_fin" label="Fecha Fin*" type="date" />
                <x-formulario.entrada name="hora_inicio" label="Hora Inicio" type="time" />
                <x-formulario.entrada name="hora_final" label="Hora Fin" type="time" />
                <x-formulario.radio name="turno" label="Turno*" required :options="$turnos" />
                <x-formulario.entrada name="aula" label="Aula" type="text" />
                <x-formulario.entrada name="aula_virtual" label="Aula Virtual" type="text" />
                <x-formulario.entrada name="tecnico_comunidad" label="Técnico Comunidad" type="text" />
                <x-formulario.entrada name="observaciones" label="Observaciones" type="text" />
                <x-formulario.selector name="formacion_id" label="Formación*" placeholder="Busca una formación..."
                    :options="$formaciones" />
                <x-formulario.selector name="centro_id" label="Centro" placeholder="Busca un centro..."
                    :options="$centros" />
            </x-formulario.seccion>

            <div class="flex justify-end gap-3">
                <x-formulario.boton type="submit" class="w-full">Guardar</x-formulario.boton>
            </div>
        </x-formulario.tarjeta>
    </form>

</body>

</html>
