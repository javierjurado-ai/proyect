<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Empresa</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-100 min-h-screen py-10 px-4">
    
    <!-- Tarjeta con ancho ampliado y fondo blanco -->
    <x-formulario.tarjeta class="max-w-xl bg-white">
        <!-- Dejamos el action vacío por ahora para que no intente enviar nada ni de error -->
        <form action="#" method="POST">
            @csrf

            <h2 class="text-2xl font-bold text-slate-800 mb-1">Empresa</h2>
            <p class="text-sm text-slate-500 mb-6">Los campos marcados con * son obligatorios</p>

            <div class="space-y-4 mb-6">
                <x-formulario.entrada name="cif" label="CIF *" placeholder="A12345678" maxlength="15" />
                <x-formulario.entrada name="nombre" label="Nombre *" maxlength="100" />
                <x-formulario.entrada name="total_trabajadores" label="Total Trabajadores" type="number" min="0" placeholder="0" />
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="reset" class="px-5 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 transition">Limpiar</button>
                <x-formulario.boton type="submit">Guardar empresa</x-formulario.boton>
            </div>
        </form>
    </x-formulario.tarjeta>

</body>
</html>