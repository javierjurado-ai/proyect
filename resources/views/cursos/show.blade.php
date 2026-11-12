<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Curso</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9; }
        .card { background: white; padding: 30px; border-radius: 8px; max-width: 600px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .detail-group { margin-bottom: 15px; border-bottom: 1px solid #f9f9f9; padding-bottom: 8px; }
        .label { font-weight: bold; color: #555; display: inline-block; width: 180px; }
        .value { color: #222; }
        .btn-volver { display: inline-block; margin-top: 20px; background: #4f46e5; color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; }
        .btn-volver:hover { background: #4338ca; }
    </style>
</head>
<body>

<div class="card">
    <h1>Detalles del Grupo: {{ $curso->codigo_grupo }}</h1>

    <div class="detail-group">
        <span class="label">ID Grupo:</span>
        <span class="value">{{ $curso->idgrupo }}</span>
    </div>

    <div class="detail-group">
        <span class="label">Código de Grupo:</span>
        <span class="value">{{ $curso->codigo_grupo }}</span>
    </div>

    <div class="detail-group">
        <span class="label">Formación:</span>
        <span class="value">{{ $curso->formacion->nombre ?? 'N/A' }}</span>
    </div>

    <div class="detail-group">
        <span class="label">Centro:</span>
        <span class="value">{{ $curso->centro->nombre ?? 'N/A' }}</span>
    </div>

    <div class="detail-group">
        <span class="label">Turno:</span>
        <span class="value">{{ $curso->turno }}</span>
    </div>

    <div class="detail-group">
        <span class="label">Fecha de Inicio:</span>
        <span class="value">{{ optional($curso->fecha_inicio)->format('d/m/Y') }}</span>
    </div>

    <div class="detail-group">
        <span class="label">Fecha de Fin:</span>
        <span class="value">{{ optional($curso->fecha_fin)->format('d/m/Y') }}</span>
    </div>

    <div class="detail-group">
        <span class="label">Aula:</span>
        <span class="value">{{ $curso->aula ?? 'No asignada' }}</span>
    </div>

    <div class="detail-group">
        <span class="label">Observaciones:</span>
        <span class="value">{{ $curso->observaciones ?? 'Ninguna' }}</span>
    </div>

    <a href="/cursos" class="btn-volver">&larr; Volver al listado</a>
</div>

</body>
</html>