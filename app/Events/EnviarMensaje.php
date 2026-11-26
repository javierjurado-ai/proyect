<?php

namespace App\Events;

use App\Models\PeticionMensajes;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
class EnviarMensaje implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public PeticionMensajes $peticion) {}

    public function broadcastOn(): array
    {
        return [new Channel('chat')];
    }

    public function broadcastWith(): array
{
    return [
        'mensaje' => $this->peticion->mensaje,
        'autor'   => $this->peticion->persona?->nombre ?? 'Anónimo',
        'fecha'   => Carbon::parse($this->peticion->fecha_creada)->format('d/m/Y'),
        'hora'    => $this->peticion->hora_creada,
    ];
}
}
