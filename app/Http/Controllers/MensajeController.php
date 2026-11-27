<?php

namespace App\Http\Controllers;

use App\Events\EnviarMensaje;
use App\Models\Curso;
use App\Models\PeticionTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MensajeController extends Controller
{
    public function visualizarTicket(Request $request, Curso $curso, ?PeticionTicket $ticket = null)
    {
        $conversaciones = PeticionTicket::with('ultimoMensaje')
            ->whereHas('peticionLogistica', fn($q) => $q->where('curso_id', $curso->id))
            ->get();

        $mensajes = $ticket
            ? $ticket->mensajes()
            ->with('persona')
            ->orderBy('fecha_creada')
            ->orderBy('hora_creada')
            ->orderBy('id')
            ->get()
            : collect();

        return view('ticket.index', [
            'curso' => $curso,
            'conversaciones' => $conversaciones,
            'conversacionActiva' => $ticket,
            'mensajes' => $mensajes,
            'personaId' => Auth::user()->persona_id,
        ]);
    }

    public function enviarMensaje(Request $request, Curso $curso, PeticionTicket $ticket)
    {
        $request->validate(['contenido' => 'required|string|max:2000']);

        $mensaje = $ticket->mensajes()->create([
            'mensaje'      => $request->contenido,
            'fecha_creada' => now()->toDateString(),
            'hora_creada'  => now()->format('H:i:s'),
            'persona_id'   => Auth::user()->persona_id,
        ]);

        $mensaje->load('persona');

        // Lo envía a los demás que tienen este ticket abierto, pero no a quien lo escribe
        broadcast(new EnviarMensaje($mensaje))->toOthers();

        if ($request->expectsJson()) {
            return response()->json((new EnviarMensaje($mensaje))->broadcastWith());
        }

        return redirect()->route('ticket.index', [$curso, $ticket]);
    }
}
