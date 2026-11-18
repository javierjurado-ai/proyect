<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\Usuario;

class TokenTemporal
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $data = json_decode(Crypt::decryptString($request->bearerToken()), true);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        if (!isset($data['usuario_id'], $data['expira'])) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        if ($data['expira'] < now()->timestamp) {
            return response()->json(['message' => 'Token caducado'], 401);
        }

        $usuario = Usuario::find($data['usuario_id']);

        if (!$usuario) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        Auth::setUser($usuario);

        return $next($request);
    }
}