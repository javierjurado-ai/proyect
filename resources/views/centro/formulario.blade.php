<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    <x-formulario.tarjeta titulo="Centro" subtitulo="Los campos marcados con * son obligatorios">
        <form method="POST" action="{{ route('centro.store') }}">
            @csrf

            <x-formulario.seccion titulo="Datos del centro">
                <x-formulario.entrada name="nombre" label="Nombre *" maxlength="45" required
                    contenedor="md:col-span-2" />
                <x-formulario.seleccion name="tipo_centro" label="Tipo de centro *" :options="$tiposCentro" required />

                <x-formulario.selector name="empresa_id" label="Empresa *" placeholder="Busca una empresa..."
                    :options="$empresas" contenedor="md:col-span-2" />
                <x-formulario.entrada name="numero_total_aula" label="Nº total de aulas" type="number" min="0" />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Ubicación">
                <x-formulario.entrada name="direccion" label="Dirección *" maxlength="45" required
                    contenedor="md:col-span-3" />
                <x-formulario.entrada name="municipio" label="Municipio *" maxlength="45" required />
                <x-formulario.entrada name="provincia" label="Provincia *" maxlength="45" required />
                <x-formulario.entrada name="codigo_postal" label="Código postal *" maxlength="5"
                    inputmode="numeric" pattern="[0-9]{5}" placeholder="28001" required />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Contacto" columnas="2">
                <x-formulario.entrada name="telefono" label="Teléfono *" type="tel" maxlength="45" required />
                <x-formulario.entrada name="email" label="Email" type="email" maxlength="45" />
            </x-formulario.seccion>

            <div class="flex justify-end gap-3">
                <x-formulario.boton type="reset" variante="secundario">Limpiar</x-formulario.boton>
                <x-formulario.boton type="submit">Guardar centro</x-formulario.boton>
            </div>
        </form>
    </x-formulario.tarjeta>
</body>

</html>