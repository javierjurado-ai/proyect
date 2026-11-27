<?php

namespace App\Events;

use App\Models\PeticionMensajes;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnviarMensaje implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public PeticionMensajes $peticion) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('ticket.' . $this->peticion->peticion_ticket_id)];
    }

    public function broadcastAs(): string
    {
        return 'mensaje.enviado';
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->peticion->id,
            'mensaje'    => $this->peticion->mensaje,
            'persona_id' => $this->peticion->persona_id,
            'autor'      => $this->peticion->persona?->nombre ?? 'Anónimo',
            'hora'       => substr($this->peticion->hora_creada, 0, 5),
        ];
    }
}