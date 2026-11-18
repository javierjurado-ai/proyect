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
        <form method="POST" action="{{ isset($centro) ? route('centro.update', $centro->id) : route('centro.store') }}">
            @csrf
            @if(isset($centro))
                @method('PUT')
            @endif

            <x-formulario.seccion titulo="Datos del centro">
                <x-formulario.entrada name="nombre" label="Nombre *" maxlength="45" required
                    contenedor="md:col-span-2" :value="$centro?->nombre ?? old('nombre')" />
                <x-formulario.seleccion name="tipo_centro" label="Tipo de centro *" :options="$tiposCentro" :value="$centro?->tipo_centro?->value ?? old('tipo_centro')" required />

                <x-formulario.selector name="empresa_id" label="Empresa *" placeholder="Busca una empresa..."
                    :options="$empresas" contenedor="md:col-span-2" :value="$centro?->empresa_id ?? old('empresa_id')" />
                <x-formulario.entrada name="numero_total_aula" label="Nº total de aulas" type="number" min="0" :value="$centro?->numero_total_aula ?? old('numero_total_aula')" />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Ubicación">
                <x-formulario.entrada name="direccion" label="Dirección *" maxlength="45" required
                    contenedor="md:col-span-3" :value="$centro?->direccion ?? old('direccion')" />
                <x-formulario.entrada name="municipio" label="Municipio *" maxlength="45" required :value="$centro?->municipio ?? old('municipio')" />
                <x-formulario.entrada name="provincia" label="Provincia *" maxlength="45" required :value="$centro?->provincia ?? old('provincia')" />
                <x-formulario.entrada name="codigo_postal" label="Código postal *" maxlength="5"
                    inputmode="numeric" pattern="[0-9]{5}" placeholder="28001" required :value="$centro?->codigo_postal ?? old('codigo_postal')" />
            </x-formulario.seccion>

            <x-formulario.seccion titulo="Contacto" columnas="2">
                <x-formulario.entrada name="telefono" label="Teléfono *" type="tel" maxlength="45" required :value="$centro?->telefono ?? old('telefono')" />
                <x-formulario.entrada name="email" label="Email" type="email" maxlength="45" :value="$centro?->email ?? old('email')" />
            </x-formulario.seccion>

            <div class="flex justify-end gap-3">
                <x-formulario.boton type="reset" variante="secundario">Limpiar</x-formulario.boton>
                <x-formulario.boton type="submit">{{ isset($centro) ? 'Actualizar centro' : 'Guardar centro' }}</x-formulario.boton>
            </div>
        </form>
    </x-formulario.tarjeta>
</body>

</html>