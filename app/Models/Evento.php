<?php

namespace App\Models;

use Database\Factories\EventoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * El modelo NO contiene reglas de autorización (RNF 2.1):
 * quién puede modificarlo lo decide App\Policies\EventoPolicy.
 */
class Evento extends Model
{
    /** @use HasFactory<EventoFactory> */
    use HasFactory;

    protected $table = 'eventos';

    /**
     * user_id NO es asignable masivamente: así nadie puede "regalarse" o
     * cambiar el dueño de un evento enviándolo en el JSON del PUT.
     */
    protected $fillable = [
        'titulo',
        'descripcion',
        'cupo_maximo',
    ];

    protected function casts(): array
    {
        return [
            'cupo_maximo' => 'integer',
        ];
    }

    /** Organizador responsable (dueño) del evento. */
    public function organizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
