<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Tabla</title>
</head>

<body>
    <x-tabla.tarjeta>
        <x-tabla.tabla :columnas="['Código', 'Empresa', 'Fecha Inicio', 'Fecha Fin', 'Turno','Hora Inicio','Hora Fin','Aula','Aula Virtual','Tecnico Comunidad','Observaciones']" vacio="Todavía no hay cursos.">
            @foreach ($cursos as $curso)
                <tr>
                    <td class="px-4 py-3">{{ $curso->codigo_curso }}</td>
                    <td class="px-4 py-3">{{ $curso->empresa_formadora }}</td>
                    <td class="px-4 py-3">{{ $curso->fecha_inicio }}</td>
                    <td class="px-4 py-3">{{ $curso->fecha_fin }}</td>
                    <td class="px-4 py-3">{{ $curso->turno->value ?? $curso->turno }}</td>
                    <td class="px-4 py-3">{{ $curso->hora_inicio}}</td>
                    <td class="px-4 py-3">{{ $curso->hora_final}}</td>
                    <td class="px-4 py-3">{{ $curso->aula}}</td>
                    <td class="px-4 py-3">{{ $curso->aula_virtual}}</td>
                    <td class="px-4 py-3">{{ $curso->tecnico_comunidad}}</td>
                    <td class="px-4 py-3">{{ $curso->observaciones}}</td>
                    
                </tr>
            @endforeach
        </x-tabla.tabla>
    </x-tabla.tarjeta>
</body>

</html>
