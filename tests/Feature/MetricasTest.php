<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MetricasTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_admin_puede_ver_las_metricas(): void
    {
        Evento::factory()->count(3)->create(['cupo_maximo' => 100]);
        Sanctum::actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]));

        $this->getJson('/api/admin/metricas')
            ->assertOk()
            ->assertJsonPath('total_eventos', 3)
            ->assertJsonPath('cupo_total_ofertado', 300)
            ->assertJsonStructure(['total_eventos', 'monto_global_recaudado', 'moneda']);
    }

    public function test_organizadores_y_asistentes_reciben_403(): void
    {
        foreach ([User::ROLE_ORGANIZADOR, User::ROLE_ASISTENTE] as $rol) {
            Sanctum::actingAs(User::factory()->create(['role' => $rol]));

            $this->getJson('/api/admin/metricas')->assertForbidden()->assertJsonStructure(['message']);
        }
    }

    public function test_sin_token_retorna_401(): void
    {
        $this->getJson('/api/admin/metricas')->assertUnauthorized();
    }
}
