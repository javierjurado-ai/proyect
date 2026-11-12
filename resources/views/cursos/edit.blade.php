<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Curso</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9; }
        .form-container { background: white; padding: 30px; border-radius: 8px; max-width: 600px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-top: 0; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #444; }
        input, select, textarea { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
        input:focus, select:focus, textarea:focus { border-color: #f59e0b; outline: none; }
        .error-message { color: #ef4444; font-size: 13px; margin-top: 5px; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        button { background: #f59e0b; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #d97706; }
        .btn-volver { display: inline-block; margin-top: 15px; color: #6b7280; text-decoration: none; }
        .btn-volver:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="form-container">
    <h1>Editar Curso: {{ $curso->codigo_grupo }}</h1>

    @if ($errors->any())
        <div class="alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- El formulario apunta a la ruta PUT enviando la clave primaria idgrupo --}}
    <form action="/cursos/{{ $curso->idgrupo }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Selección de Formación --}}
        <div class="form-group">
            <label for="curso_idcurso">Formación:</label>
            <select name="curso_idcurso" id="curso_idcurso" required>
                @foreach ($formaciones as $formacion)
                    <option value="{{ $formacion->idcurso }}" {{ (old('curso_idcurso', $curso->curso_idcurso) == $formacion->idcurso) ? 'selected' : '' }}>
                        {{ $formacion->nombre }} ({{ $formacion->codigo_accion }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Código de Grupo --}}
        <div class="form-group">
            <label for="codigo_grupo">Código de Grupo:</label>
            <input type="text" id="codigo_grupo" name="codigo_grupo" value="{{ old('codigo_grupo', $curso->codigo_grupo) }}" required>
        </div>

        {{-- Selección de Centro --}}
        <div class="form-group">
            <label for="centro_id">Centro:</label>
            <select name="centro_id" id="centro_id" required>
                @foreach ($centros as $centro)
                    <option value="{{ $centro->id }}" {{ (old('centro_id', $curso->centro_id) == $centro->id) ? 'selected' : '' }}>
                        {{ $centro->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Turno --}}
        <div class="form-group">
            <label for="turno">Turno:</label>
            <select name="turno" id="turno" required>
                <option value="Mañana" {{ (old('turno', $curso->turno) == 'Mañana') ? 'selected' : '' }}>Mañana</option>
                <option value="Tarde" {{ (old('turno', $curso->turno) == 'Tarde') ? 'selected' : '' }}>Tarde</option>
                <option value="Noche" {{ (old('turno', $curso->turno) == 'Noche') ? 'selected' : '' }}>Noche</option>
                <option value="Rotativo" {{ (old('turno', $curso->turno) == 'Rotativo') ? 'selected' : '' }}>Rotativo</option>
            </select>
        </div>

        {{-- Fecha de Inicio --}}
        <div class="form-group">
            <label for="fecha_inicio">Fecha de Inicio:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', optional($curso->fecha_inicio)->format('Y-m-d')) }}" required>
        </div>

        {{-- Fecha de Fin --}}
        <div class="form-group">
            <label for="fecha_fin">Fecha de Fin:</label>
            <input type="date" id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin', optional($curso->fecha_fin)->format('Y-m-d')) }}" required>
        </div>

        <button type="submit">Actualizar Curso</button>
    </form>

    <a href="/cursos" class="btn-volver">&larr; Cancelar y volver</a>
</div>

</body>
</html>