<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_el_seeder_crea_los_usuarios_y_eventos_del_enunciado(): void
    {
        $this->assertSame(1, User::where('role', User::ROLE_ADMIN)->count());
        $this->assertSame(2, User::where('role', User::ROLE_ORGANIZADOR)->count());
        $this->assertSame(1, User::where('role', User::ROLE_ASISTENTE)->count());

        $organizadorA = User::where('email', 'organizador.a@eventhub.com')->firstOrFail();
        $this->assertGreaterThanOrEqual(1, Evento::where('user_id', $organizadorA->id)->count());
    }

    public function test_los_usuarios_semilla_pueden_iniciar_sesion(): void
    {
        $this->postJson('/api/login', ['email' => 'admin@eventhub.com', 'password' => 'password'])->assertOk();
    }
}
