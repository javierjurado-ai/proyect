{{--
    Inicio de sesión (vista de AuthController@mostrarInicioSesion).
    Envía el login con fetch, guarda el token en sessionStorage y redirige.
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión · {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    {{-- Los mismos colores de marca que components/interfaz/layout/app.blade.php --}}
    <style type="text/tailwindcss">
        @theme {
            --font-sans: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            --color-marca-50: #EEF6F6;
            --color-marca-100: #CFE3E4;
            --color-marca-700: #1D5C63;
            --color-marca-800: #144950;
            --color-marca-900: #0E3B41;
            --color-marca-950: #082A2F;
            --color-acento: #F2A541;
        }
    </style>
</head>
<body class="min-h-screen bg-white font-sans text-marca-950">
<div class="flex min-h-screen flex-col lg:flex-row">

    {{-- ========== Izquierda: panel de marca ========== --}}
    <section class="flex flex-col justify-between gap-12 bg-marca-800 px-8 py-10 text-white lg:w-1/2 lg:px-14 lg:py-12">
        <div class="flex items-center gap-3 text-xl font-extrabold">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-marca-800">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10L12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/></svg>
            </span>
            {{ config('app.name') }}
        </div>

        <div class="flex max-w-md flex-col gap-5">
            <h1 class="text-4xl font-extrabold leading-tight tracking-tight lg:text-5xl">Un único acceso. Cada uno, a su panel.</h1>
            <p class="text-lg leading-relaxed text-marca-100">Acceso para el equipo de gestión. Al entrar, la plataforma te lleva directamente a lo que necesitas según tu perfil.</p>

            <ul class="mt-2 flex flex-col gap-2.5">
                <li class="flex items-center gap-4 rounded-xl bg-white/10 px-4 py-3">
                    <span class="h-3 w-3 shrink-0 rounded-full bg-acento"></span>
                    <span class="w-32 font-bold">Coordinador</span>
                    <span class="text-sm text-marca-100">Cursos, aulas, alumnos y FUNDAE</span>
                </li>
                <li class="flex items-center gap-4 rounded-xl bg-white/10 px-4 py-3">
                    <span class="h-3 w-3 shrink-0 rounded-full bg-[#8EC5FF]"></span>
                    <span class="w-32 font-bold">Comercial</span>
                    <span class="text-sm text-marca-100">Empresas, crédito y presupuestos</span>
                </li>
                <li class="flex items-center gap-4 rounded-xl bg-white/10 px-4 py-3">
                    <span class="h-3 w-3 shrink-0 rounded-full bg-[#C9B6F2]"></span>
                    <span class="w-32 font-bold">Administrador</span>
                    <span class="text-sm text-marca-100">Usuarios, roles y configuración</span>
                </li>
            </ul>
        </div>

        <small class="text-marca-100/80">© {{ date('Y') }} {{ config('app.name') }} · Formación bonificada FUNDAE</small>
    </section>

    {{-- ========== Derecha: formulario ========== --}}
    <section class="flex items-center justify-center px-6 py-12 lg:w-1/2">
        {{-- route('login') = /persona/iniciarSesion, que también recibe el POST --}}
        <form id="form-login" method="POST" action="{{ route('login') }}" class="flex w-full max-w-md flex-col gap-5" novalidate>
            @csrf

            <div>
                <h2 class="text-3xl font-extrabold tracking-tight">Iniciar sesión</h2>
                <p class="mt-1 text-slate-500">Introduce los datos de tu cuenta.</p>
            </div>

            {{-- Errores de una recarga normal (respaldo si falla el JavaScript) --}}
            @if ($errors->any())
                <div role="alert" class="flex items-start gap-3 rounded-xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                    <svg class="mt-0.5 shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Errores del fetch (se muestran con JavaScript) --}}
            <div id="error-login" role="alert"
                 class="hidden items-start gap-3 rounded-xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
            </div>

            {{-- Usuario --}}
            <div class="flex flex-col gap-2">
                <label for="usuario" class="text-sm font-bold">Usuario</label>
                <input id="usuario" name="usuario" type="text" value="{{ old('usuario') }}"
                       autocomplete="username" placeholder="Tu nombre de usuario" required autofocus
                       @if ($errors->any()) aria-invalid="true" @endif
                       class="w-full rounded-xl border-[1.5px] bg-white px-4 py-3.5 outline-none placeholder:text-slate-400 focus:ring-4 focus:ring-acento/50 {{ $errors->any() ? 'border-red-600' : 'border-stone-300' }}">
            </div>

            {{-- Contraseña --}}
            <div class="flex flex-col gap-2">
                <div class="flex items-baseline justify-between gap-3">
                    <label for="contrasena" class="text-sm font-bold">Contraseña</label>
                    {{-- No hay recuperación de contraseña: la restablece un administrador --}}
                    <span class="text-xs text-slate-500" title="Un administrador puede restablecerla">¿Olvidaste la contraseña? Avisa a un administrador</span>
                </div>
                <div class="relative">
                    <input id="contrasena" name="contrasena" type="password" autocomplete="current-password" required
                           @if ($errors->any()) aria-invalid="true" @endif
                           class="w-full rounded-xl border-[1.5px] bg-white py-3.5 pl-4 pr-14 outline-none focus:ring-4 focus:ring-acento/50 {{ $errors->any() ? 'border-red-600' : 'border-stone-300' }}">
                    <button type="button" id="ver-contrasena" aria-label="Mostrar contraseña"
                            class="absolute right-1 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-lg text-slate-600 hover:bg-stone-100">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            {{-- Recordarme --}}
            <label class="flex items-center gap-3 text-sm text-slate-600">
                <input type="checkbox" id="recordar" name="recordar" value="1" @checked(old('recordar')) class="h-5 w-5 accent-marca-800">
                Mantener la sesión iniciada
            </label>

            <button type="submit"
                    class="rounded-xl bg-marca-800 py-4 text-lg font-bold text-white transition hover:bg-marca-900 focus:outline-none focus-visible:ring-4 focus-visible:ring-acento/60">
                Entrar
            </button>

            <div class="flex items-start gap-3 rounded-xl bg-marca-50 p-4 text-sm leading-relaxed text-slate-600">
                <svg class="mt-0.5 shrink-0 text-marca-800" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                <span>Acceso exclusivo para el equipo interno. Las cuentas las crea el administrador; si te han dado de alta, revisa tu email para crear tu contraseña.</span>
            </div>
        </form>
    </section>
</div>

<script>
    // Botón del ojo: mostrar / ocultar la contraseña
    const verContrasena = document.getElementById('ver-contrasena');
    const contrasena = document.getElementById('contrasena');
    verContrasena.addEventListener('click', () => {
        const mostrar = contrasena.type === 'password';
        contrasena.type = mostrar ? 'text' : 'password';
        verContrasena.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });

    // Login con fetch: guarda el token y redirige
    const form = document.getElementById('form-login');
    const errorLogin = document.getElementById('error-login');

    function mostrarError(mensaje) {
        errorLogin.textContent = mensaje;
        errorLogin.classList.remove('hidden');
        errorLogin.classList.add('flex');
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        errorLogin.classList.add('hidden');
        errorLogin.classList.remove('flex');

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    usuario: form.usuario.value,
                    contrasena: form.contrasena.value,
                    recordar: form.recordar.checked,
                }),
            });

            const data = await res.json().catch(() => ({}));

            if (res.ok) {
                sessionStorage.setItem('token', data.token);
                window.location.href = data.redirect;
                return;
            }

            mostrarError(data.message ?? 'No se pudo iniciar sesión.');
        } catch (err) {
            mostrarError('No se pudo conectar con el servidor.');
        }
    });
</script>
</body>
</html>