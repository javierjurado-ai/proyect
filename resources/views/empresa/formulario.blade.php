<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Empresa</title>
    <script>
        const expira = Number(sessionStorage.getItem('expira')); 

        if (!token || Date.now() / 1000 > expira) {
            sessionStorage.clear();
            window.location.href = '/persona/iniciarSesion';
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="min-h-screen bg-slate-100 px-4 py-10">
    <x-formulario.tarjeta titulo="Empresa" subtitulo="Los campos marcados con * son obligatorios">
        <form id="empresa-form" method="POST" action="/empresa">
            @csrf

            @if (session('success'))
                <div id="success-alert" class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-700 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700 shadow-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-formulario.seccion titulo="Datos de la empresa">
                <x-formulario.entrada name="cif" label="CIF *" placeholder="A12345678" maxlength="15" />
                <x-formulario.entrada name="nombre" label="Nombre *" maxlength="100"
                    contenedor="md:col-span-2" />

                <x-formulario.entrada name="total_trabajadores" label="Total trabajadores" type="number" min="0"
                    placeholder="0" />
            </x-formulario.seccion>

            <div class="flex justify-end gap-3">
                <x-formulario.boton type="reset" variante="secundario">Limpiar</x-formulario.boton>
                <x-formulario.boton type="submit">Guardar empresa</x-formulario.boton>
            </div>
        </form>
    </x-formulario.tarjeta>

    @if (session('success'))
    <script>
        document.getElementById('empresa-form').addEventListener('focusin', () => {
            document.getElementById('success-alert')?.remove();
        }, { once: true });
    </script>
    @endif

</body>

</html>