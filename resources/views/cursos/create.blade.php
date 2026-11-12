<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nuevo Curso</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9; }
        .form-container { background: white; padding: 30px; border-radius: 8px; max-width: 600px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-top: 0; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #444; }
        input, select, textarea { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
        input:focus, select:focus, textarea:focus { border-color: #4f46e5; outline: none; }
        .error-message { color: #ef4444; font-size: 13px; margin-top: 5px; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        button { background: #4f46e5; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #4338ca; }
        .btn-volver { display: inline-block; margin-top: 15px; color: #6b7280; text-decoration: none; }
        .btn-volver:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="form-container">
    <h1>Registrar Nuevo Curso</h1>

    {{-- Muestra errores generales de validación si los hay --}}
    @if ($errors->any())
        <div class="alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="/cursos" method="POST">
        @csrf

        {{-- Selección de Formación (curso_idcurso) --}}
        <div class="form-group">
            <label for="curso_idcurso">Formación:</label>
            <select name="curso_idcurso" id="curso_idcurso" required>
                <option value="">-- Seleccione una formación --</option>
                @foreach ($formaciones as $formacion)
                    <option value="{{ $formacion->idcurso }}" {{ old('curso_idcurso') == $formacion->idcurso ? 'selected' : '' }}>
                        {{ $formacion->nombre }} ({{ $formacion->codigo_accion }})
                    </option>
                @endforeach
            </select>
            @error('curso_idcurso')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        {{-- Código de Grupo --}}
        <div class="form-group">
            <label for="codigo_grupo">Código de Grupo:</label>
            <input type="text" id="codigo_grupo" name="codigo_grupo" value="{{ old('codigo_grupo') }}" required>
            @error('codigo_grupo')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        {{-- Selección de Centro --}}
        <div class="form-group">
            <label for="centro_id">Centro:</label>
            <select name="centro_id" id="centro_id" required>
                <option value="">-- Seleccione un centro --</option>
                @foreach ($centros as $centro)
                    <option value="{{ $centro->id }}" {{ old('centro_id') == $centro->id ? 'selected' : '' }}>
                        {{ $centro->nombre }}
                    </option>
                @endforeach
            </select>
            @error('centro_id')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        {{-- Turno --}}
        <div class="form-group">
            <label for="turno">Turno:</label>
            <select name="turno" id="turno" required>
                <option value="">-- Seleccione un turno --</option>
                <option value="Mañana" {{ old('turno') == 'Mañana' ? 'selected' : '' }}>Mañana</option>
                <option value="Tarde" {{ old('turno') == 'Tarde' ? 'selected' : '' }}>Tarde</option>
                <option value="Mixto" {{ old('turno') == 'Mixto' ? 'selected' : '' }}>Mixto</option>               
            </select>
            @error('turno')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        {{-- Fecha de Inicio --}}
        <div class="form-group">
            <label for="fecha_inicio">Fecha de Inicio:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio') }}" required>
            @error('fecha_inicio')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        {{-- Fecha de Fin --}}
        <div class="form-group">
            <label for="fecha_fin">Fecha de Fin:</label>
            <input type="date" id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin') }}" required>
            @error('fecha_fin')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        {{-- Empresa Formadora --}}
        <div class="form-group">
            <label for="empresa_formadora">Empresa Formadora:</label>
            <input type="text" id="empresa_formadora" name="empresa_formadora" value="{{ old('empresa_formadora') }}">
            @error('empresa_formadora')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        {{-- Horarios (Hora Inicio y Hora Fin) --}}
        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label for="hora_inicio">Hora de Inicio:</label>
                <input type="time" id="hora_inicio" name="hora_inicio" value="{{ old('hora_inicio') }}">
                @error('hora_inicio')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="flex: 1;">
                <label for="hora_final">Hora de Fin:</label>
                <input type="time" id="hora_final" name="hora_final" value="{{ old('hora_final') }}">
                @error('hora_final')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Aulas y Técnico --}}
        <div class="form-group">
            <label for="aula">Aula:</label>
            <input type="text" id="aula" name="aula" value="{{ old('aula') }}">
            @error('aula')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="aula_virtual">Aula Virtual:</label>
            <input type="text" id="aula_virtual" name="aula_virtual" value="{{ old('aula_virtual') }}">
            @error('aula_virtual')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="tecnico_comunidad">Técnico de Comunidad:</label>
            <input type="text" id="tecnico_comunidad" name="tecnico_comunidad" value="{{ old('tecnico_comunidad') }}">
            @error('tecnico_comunidad')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        {{-- Observaciones --}}
        <div class="form-group">
            <label for="observaciones">Observaciones:</label>
            <textarea id="observaciones" name="observaciones" rows="3" maxlength="200">{{ old('observaciones') }}</textarea>
            @error('observaciones')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit">Guardar Curso</button>
    </form>

    <a href="/cursos" class="btn-volver">&larr; Volver al listado</a>
</div>

</body>
</html>