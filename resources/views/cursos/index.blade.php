<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9; }
        h1 { color: #333; }
        .alert-success { background-color: #d1e7dd; color: #0f5132; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        .btn-crear { display: inline-block; margin-bottom: 20px; background: #10b981; color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; }
        .btn-crear:hover { background: #059669; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #1a5dba; color: white; }
        .btn-editar { color: #d97706; text-decoration: none; font-weight: bold; margin-right: 10px; }
        .btn-eliminar { background: #ef4444; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; }
        .btn-eliminar:hover { background: #dc2626; }
        form.inline { display: inline; }
    </style>
</head>
<body>

    <h1>Gestión de Cursos</h1>

    {{-- Mensaje de éxito si viene de guardar o eliminar --}}
    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <a href="/cursos/create" class="btn-crear" style="margin-bottom: 0;">+ Crear Nuevo Curso</a>

        {{-- Formulario de búsqueda rápida por ID --}}
        <form action="" method="GET" onsubmit="event.preventDefault(); window.location.href='/cursos/' + document.getElementById('search_id').value;" style="display: flex; gap: 8px;">
            <input type="number" id="search_id" placeholder="Buscar por ID de grupo..." required style="padding: 8px; border: 1px solid #ccc; border-radius: 6px; width: 200px;">
            <button type="submit" style="background: #4f46e5; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-weight: bold;">Buscar</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Grupo</th>
                <th>Código Grupo</th>
                <th>Formación</th>
                <th>Centro</th>
                <th>Turno</th>
                <th>Fechas</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($cursos as $curso)
                <tr>
                    <td>{{ $curso->idgrupo }}</td>
                    <td><strong>{{ $curso->codigo_grupo }}</strong></td>
                    <td>{{ $curso->formacion->nombre ?? 'N/A' }}</td>
                    <td>{{ $curso->centro->nombre ?? 'N/A' }}</td>
                    <td>{{ $curso->turno }}</td>
                    <td>
                        {{ $curso->fecha_inicio ? $curso->fecha_inicio->format('d/m/Y') : '' }} - 
                        {{ $curso->fecha_fin ? $curso->fecha_fin->format('d/m/Y') : '' }}
                    </td>
                    <td>
                        <a href="/cursos/{{ $curso->idgrupo }}/edit" class="btn-editar">Editar</a>
                        <a href="/cursos/{{ $curso->idgrupo }}" style="color: #4f46e5; text-decoration: none; font-weight: bold; margin-right: 10px;">Ver</a>

                        <form action="/cursos/{{ $curso->idgrupo }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-eliminar" onclick="return confirm('¿Estás seguro de eliminar este curso?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #777;">No hay cursos registrados actualmente.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>