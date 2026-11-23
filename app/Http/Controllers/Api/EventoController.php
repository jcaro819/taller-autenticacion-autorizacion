<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEventoRequest;
use App\Http\Resources\EventoResource;
use App\Models\Evento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventoController extends Controller
{
    /** RF 2.1 — GET /api/eventos (público, sin token). */
    public function index(): AnonymousResourceCollection
    {
        $eventos = Evento::with('organizador:id,name')->latest()->get();

        return EventoResource::collection($eventos);
    }

    /**
     * RF 2.2 / 2.3 — PUT /api/eventos/{evento}
     * Cuando se llega aquí, UpdateEventoRequest ya verificó la Policy (403)
     * y validó los datos (422).
     */
    public function update(UpdateEventoRequest $request, Evento $evento): JsonResponse
    {
        $evento->update($request->validated());

        return response()->json([
            'message' => 'Evento actualizado con éxito.',
            'evento' => new EventoResource($evento->load('organizador:id,name')),
        ]);
    }
}
