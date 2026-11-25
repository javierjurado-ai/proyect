<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller
{
    public function mostrarInicioSesion()
    {
        return view('auth.inicioSesion');
    }

    public function iniciarSesion(Request $request)
    {
        $credenciales = $request->validate([
            'usuario'    => ['required', 'string'],
            'contrasena' => ['required', 'string'],
        ]);

        $usuario = Usuario::where('usuario', $credenciales['usuario'])->first();

        if (!$usuario || !Hash::check($credenciales['contrasena'], $usuario->contrasena)) {
            return response()->json(['message' => 'Usuario o contraseña incorrectos.'], 401);
        }

        Auth::login($usuario, $request->boolean('recordar'));
        $request->session()->regenerate();

        $expira = now()->addMinutes(30)->timestamp;

        $token = Crypt::encryptString(json_encode([
            'usuario_id' => $usuario->id,
            'expira'     => $expira,
        ]));
        return response()->json([
            'token'    => $token,
            'expira'   => $expira,
            'redirect' => '/view/inicio/',
        ]);
    }

    // Solo un administrador conectado puede crear usuarios (la ruta está dentro de 'auth')
    public function crearUsuario(Request $request)
    {
       // $rol = \App\Models\Rol::where('persona_id', Auth::user()->persona_id)->value('rol')?->value;
       // abort_unless($rol === 'ADMINISTRADOR', 403, 'Solo un administrador puede crear usuarios.');
        $datos = $request->validate([
            'usuario'    => ['required', 'string', 'max:45', 'unique:usuario,usuario'],
            'contrasena' => ['required', 'min:8', 'max:250', 'confirmed'],
            // Una persona solo puede tener un usuario
            'persona_id' => ['required', 'exists:persona,id', 'unique:usuario,persona_id'],
        ], [
            'persona_id.unique' => 'Esta persona ya tiene un usuario.',
            'contrasena.min'    => 'La contraseña debe tener al menos :min caracteres.',
        ]);

        Usuario::create($datos);

        // El administrador sigue conectado como él mismo (antes se cambiaba al usuario nuevo)
        return redirect('/persona/listar')->with('ok', 'Usuario creado correctamente.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Vuelve al login (antes iba a /iniciarSesion, que no existe → 404)
        return redirect()->route('login');
    }
}