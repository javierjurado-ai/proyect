<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body>
    <x-formulario.tarjeta>
        <form method="POST" >
            @csrf

            <x-formulario.entrada name="name" label="Nombre" />
            <x-formulario.entrada name="email" label="Correo" type="email" />

            <x-formulario.boton type="submit" class="w-full">Guardar</x-formulario.boton>
        </form>
    </x-formulario.tarjeta>
</body>

</html>
