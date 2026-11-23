<?php

namespace App\Policies;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * RNF 2.1 — Autorización granular del modelo Evento.
 * Laravel la descubre automáticamente por convención (Evento → EventoPolicy).
 */
class EventoPolicy
{
    /** Cualquiera (incluso sin cuenta) puede ver el catálogo: la ruta es pública. */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * RF 2.3 — Sólo el organizador que creó el evento (user_id) puede modificarlo.
     * Si devuelve deny, Laravel responde 403 Forbidden con este mensaje.
     */
    public function update(User $user, Evento $evento): Response
    {
        return $user->id === $evento->user_id
            ? Response::allow()
            : Response::deny('No tienes permiso para modificar este evento: sólo su organizador puede hacerlo.');
    }
}
