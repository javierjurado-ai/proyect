<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presupuesto</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    <x-formulario.tarjeta titulo="Presupuesto" subtitulo="Los campos marcados con * son obligatorios">
        <form id="formPresupuesto" method="POST" action="{{ route('presupuesto.store') }}">
            @csrf

            @if (session('ok'))
                <div class="mb-6 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('ok') }}
                </div>
            @endif

            <x-formulario.seccion titulo="Formación">
                <x-formulario.entrada name="nombre_formacion" label="Nombre de la formación *" maxlength="45" required
                    contenedor="md:col-span-2" />
                <x-formulario.entrada name="horas_totales" label="Horas totales *" type="number" min="1" step="1" required />

                <x-formulario.entrada name="numero_alumnos" label="Número de alumnos *" type="number" min="1" step="1" required />
                <x-formulario.entrada name="ubicacion_formacion" label="Ubicación de la formación *" maxlength="90" required
                    contenedor="md:col-span-2" />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Contenido" columnas="1">
                <x-formulario.area name="objetivos" label="Objetivos *" maxlength="150" rows="3" required />
                <x-formulario.area name="contenidos" label="Contenidos *" maxlength="450" rows="6" required />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Bonificación" columnas="2">
                <x-formulario.entrada name="importe_bonificado" label="Importe bonificado (€) *" type="number"
                    min="0" max="99999999.99" step="0.01" placeholder="0.00" required />
                <x-formulario.entrada name="fecha_bonificado" label="Fecha de bonificación *" type="date"
                :min="now()->toDateString()" required />  
              </x-formulario.seccion>

            <div class="flex justify-end gap-3">
                <x-formulario.boton type="reset" variante="secundario">Limpiar</x-formulario.boton>
                <x-formulario.boton type="submit">Guardar presupuesto</x-formulario.boton>
            </div>
        </form>
    </x-formulario.tarjeta>

    <script>
        // Contador de caracteres de los textarea (objetivos, contenidos)
        document.querySelectorAll('[data-contador]').forEach(span => {
            const campo = document.getElementById(span.dataset.contador);
            const actualizar = () => span.textContent = campo.value.length;
            campo.addEventListener('input', actualizar);
            document.getElementById('formPresupuesto').addEventListener('reset', () => setTimeout(actualizar));
        });
    </script>
</body>

</html>