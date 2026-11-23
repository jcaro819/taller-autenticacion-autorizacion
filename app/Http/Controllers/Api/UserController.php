<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /** RF 1.2 — GET /api/perfil: datos del dueño del token. */
    public function perfil(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
