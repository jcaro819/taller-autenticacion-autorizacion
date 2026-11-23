<?php

namespace Database\Seeders;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos semilla del taller. Todos los usuarios tienen la contraseña "password"
     * (se guarda hasheada gracias al cast 'hashed' del modelo User).
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Global',
            'email' => 'admin@eventhub.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $organizadorA = User::create([
            'name' => 'Organizador A',
            'email' => 'organizador.a@eventhub.com',
            'password' => 'password',
            'role' => User::ROLE_ORGANIZADOR,
        ]);

        $organizadorB = User::create([
            'name' => 'Organizador B',
            'email' => 'organizador.b@eventhub.com',
            'password' => 'password',
            'role' => User::ROLE_ORGANIZADOR,
        ]);

        User::create([
            'name' => 'Asistente Demo',
            'email' => 'asistente@eventhub.com',
            'password' => 'password',
            'role' => User::ROLE_ASISTENTE,
        ]);

        // Eventos del Organizador A (user_id se asigna por la relación, no por fillable)
        $organizadorA->eventos()->createMany([
            [
                'titulo' => 'Laravel Conf Colombia 2026',
                'descripcion' => 'Conferencia sobre desarrollo backend con Laravel, APIs y buenas prácticas.',
                'cupo_maximo' => 300,
            ],
            [
                'titulo' => 'Taller de Seguridad en APIs REST',
                'descripcion' => 'Autenticación con tokens, autorización con Gates y Policies.',
                'cupo_maximo' => 40,
            ],
        ]);

        // Un evento del Organizador B (para probar que A NO puede modificarlo → 403)
        $organizadorB->eventos()->create([
            'titulo' => 'Cloud & DevOps Summit',
            'descripcion' => 'Contenedores, CI/CD y despliegue en la nube.',
            'cupo_maximo' => 150,
        ]);
    }
}
