<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, sans-serif;
            background: #f3f4f6;
        }
        .tarjeta {
            width: 100%;
            max-width: 380px;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .1);
        }
        h1 { margin: 0 0 1.5rem; font-size: 1.5rem; text-align: center; }
        label { display: block; margin-bottom: .25rem; font-size: .9rem; }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: .6rem;
            margin-bottom: 1rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 1rem;
        }
        .recordar { display: flex; align-items: center; gap: .5rem; margin-bottom: 1rem; }
        .recordar label { margin: 0; }
        button {
            width: 100%;
            padding: .7rem;
            border: 0;
            border-radius: 4px;
            background: #2563eb;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover { background: #1d4ed8; }
        .error {
            margin-bottom: 1rem;
            padding: .6rem;
            border-radius: 4px;
            background: #fee2e2;
            color: #991b1b;
            font-size: .9rem;
        }
    </style>
</head>
<body>
    <div class="tarjeta">
        <h1>Iniciar sesión</h1>

        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ url('/iniciarSesion') }}">
            @csrf

            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario" value="{{ old('usuario') }}" required autofocus>

            <label for="contrasena">Contraseña</label>
            <input type="password" id="contrasena" name="contrasena" required>

            <div class="recordar">
                <input type="checkbox" id="recordar" name="recordar" value="1">
                <label for="recordar">Recordarme</label>
            </div>

            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>