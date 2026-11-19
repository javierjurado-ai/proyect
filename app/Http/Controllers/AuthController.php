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

    public function crearUsuario(Request $request)
    {
        $datos = $request->validate([
            'usuario'    => ['required', 'string', 'max:45', 'unique:usuario,usuario'],
            'contrasena' => ['required', 'max:250', 'confirmed'],
            'persona_id' => ['required', 'exists:persona,id'],
        ]);

        $usuario = Usuario::create($datos);

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect('/persona/listar');
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