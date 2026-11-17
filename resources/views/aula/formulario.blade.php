<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aula</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    <x-formulario.tarjeta titulo="Aula" subtitulo="Los campos marcados con * son obligatorios">
        <form id="formAula" method="POST" action="{{ route('aula.store') }}">
            @csrf

            @if (session('ok'))
                <div class="mb-6 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('ok') }}
                </div>
            @endif

            <x-formulario.seccion titulo="Datos del aula">
                <x-formulario.entrada name="nombre_aula" label="Nombre del aula *" maxlength="45" required
                    contenedor="md:col-span-2" />
                <x-formulario.seleccion name="curso_id" label="Curso *" :options="$cursos"
                    placeholder="-- Selecciona un curso --" required />

                <x-formulario.entrada name="capacidad" label="Capacidad (personas) *" type="number" min="1" step="1" required />
                <x-formulario.entrada name="dimension" label="Dimensión (m²)" type="number" min="0" step="1" />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Estado y uso" columnas="2">
                <x-formulario.seleccion name="estado" label="Estado *" :options="$estados" required />
                <x-formulario.seleccion name="uso_aula" label="Uso del aula *" :options="$usos" required />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Observaciones" columnas="1">
                <x-formulario.area name="observaciones" label="Observaciones" maxlength="450" rows="3" />
            </x-formulario.seccion>

            <div class="flex justify-end gap-3">
                <x-formulario.boton type="reset" variante="secundario">Limpiar</x-formulario.boton>
                <x-formulario.boton type="submit">Guardar aula</x-formulario.boton>
            </div>
        </form>
    </x-formulario.tarjeta>

    <script>
        // Contador de caracteres de observaciones
        document.querySelectorAll('[data-contador]').forEach(span => {
            const campo = document.getElementById(span.dataset.contador);
            const actualizar = () => span.textContent = campo.value.length;
            campo.addEventListener('input', actualizar);
            document.getElementById('formAula').addEventListener('reset', () => setTimeout(actualizar));
        });
    </script>
</body>

</html>