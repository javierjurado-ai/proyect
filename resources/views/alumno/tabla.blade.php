<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tabla Alumno</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body>
    <x-tabla.tarjeta>
        <x-tabla.tabla :columnas="['Nombre', 'Primer Apellido', 'Segundo Apellido', 'Fecha Nacimiento', 'Genero','Nacionalidad','Telefono','Direccion','Email','DNI','NIE','Seguridad Social','LOPD']" vacio="Todavía no hay alumnos.">
            @foreach ($alumnos as $alumno)
                <tr>
                    <td class="px-4 py-3">{{ $alumno->nombre }}</td>
                    <td class="px-4 py-3">{{ $alumno->primer_apellido }}</td>
                    <td class="px-4 py-3">{{ $alumno->segundo_apellido }}</td>
                    <td class="px-4 py-3">{{ $alumno->fecha_nacimiento }}</td>
                    <td class="px-4 py-3">{{ $alumno->genero }}</td>
                    <td class="px-4 py-3">{{ $alumno->nacionalidad}}</td>
                    <td class="px-4 py-3">{{ $alumno->telefono}}</td>
                    <td class="px-4 py-3">{{ $alumno->direccion}}</td>
                    <td class="px-4 py-3">{{ $alumno->email}}</td>
                    <td class="px-4 py-3">{{ $alumno->DNI}}</td>
                    <td class="px-4 py-3">{{ $alumno->NIE}}</td>
                    <td class="px-4 py-3">{{ $alumno->numero_seguridad_social}}</td>
                    <td class="px-4 py-3">{{ $alumno->LOPD}}</td>
                </tr>
            @endforeach
        </x-tabla.tabla>
    </x-tabla.tarjeta>
</body>
</html>