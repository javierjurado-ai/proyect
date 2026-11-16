<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tabla Docente</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body>
    <x-tabla.tarjeta>
        <x-tabla.tabla :columnas="['Nombre', 'Primer Apellido', 'Segundo Apellido', 'Fecha Nacimiento', 'Genero','Nacionalidad','Telefono','Direccion','Email','DNI','NIE','Seguridad Social','LOPD']" vacio="Todavía no hay alumnos.">
            @foreach ($docentes as $docente)
                <tr>
                    <td class="px-4 py-3">{{ $docente->nombre }}</td>
                    <td class="px-4 py-3">{{ $docente->primer_apellido }}</td>
                    <td class="px-4 py-3">{{ $docente->segundo_apellido }}</td>
                    <td class="px-4 py-3">{{ $docente->fecha_nacimiento }}</td>
                    <td class="px-4 py-3">{{ $docente->genero }}</td>
                    <td class="px-4 py-3">{{ $docente->nacionalidad}}</td>
                    <td class="px-4 py-3">{{ $docente->telefono}}</td>
                    <td class="px-4 py-3">{{ $docente->direccion}}</td>
                    <td class="px-4 py-3">{{ $docente->email}}</td>
                    <td class="px-4 py-3">{{ $docente->DNI}}</td>
                    <td class="px-4 py-3">{{ $docente->NIE}}</td>
                    <td class="px-4 py-3">{{ $docente->numero_seguridad_social}}</td>
                    <td class="px-4 py-3">{{ $docente->LOPD}}</td>
                </tr>
            @endforeach
        </x-tabla.tabla>
    </x-tabla.tarjeta>
</body>
</html>