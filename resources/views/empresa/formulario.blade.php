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
       
        <!-- 16-11-26  Agregamos mensaje de exito al guardar -->
        @if(session('success'))
            <div id="success-alert" class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-700 shadow-sm">
                {{ session('success') }}
            </div>
        @endif
        <!-- Dejamos el action vacío por ahora para que no intente enviar nada ni de error.   16-11-26: Modificamos agregando "/empresa" para que apunte al endpoint e id="empresa-form para el mensaje -->  
        <form action="/empresa" method="POST" id="empresa-form">
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

    <!-- El script para el mensaje, va aquí abajo, cuando el formulario ya ha sido cargado -->
    @if(session('success'))
    <script>
        document.getElementById('empresa-form').addEventListener('focusin', () => {
            document.getElementById('success-alert')?.remove();
        }, { once: true });
    </script>
    @endif

</body>
</html>
