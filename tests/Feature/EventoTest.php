<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventoTest extends TestCase
{
    use RefreshDatabase;

    private User $organizadorA;

    private User $organizadorB;

    private Evento $eventoDeA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizadorA = User::factory()->create(['name' => 'Organizador A', 'role' => User::ROLE_ORGANIZADOR]);
        $this->organizadorB = User::factory()->create(['name' => 'Organizador B', 'role' => User::ROLE_ORGANIZADOR]);
        $this->eventoDeA = Evento::factory()->for($this->organizadorA, 'organizador')->create([
            'titulo' => 'Evento de A',
            'cupo_maximo' => 100,
        ]);
    }

    private function datosValidos(array $cambios = []): array
    {
        return array_merge(['titulo' => 'Título nuevo', 'descripcion' => 'Detalle', 'cupo_maximo' => 250], $cambios);
    }

    public function test_cualquiera_puede_listar_eventos_sin_token_con_el_nombre_del_organizador(): void
    {
        $this->getJson('/api/eventos')
            ->assertOk()
            ->assertJsonPath('data.0.titulo', 'Evento de A')
            ->assertJsonPath('data.0.organizador.name', 'Organizador A');
    }

    public function test_el_organizador_dueno_puede_modificar_su_evento(): void
    {
        Sanctum::actingAs($this->organizadorA);

        $this->putJson("/api/eventos/{$this->eventoDeA->id}", $this->datosValidos())
            ->assertOk()
            ->assertJsonPath('evento.titulo', 'Título nuevo')
            ->assertJsonPath('evento.cupo_maximo', 250);

        $this->assertDatabaseHas('eventos', ['id' => $this->eventoDeA->id, 'titulo' => 'Título nuevo', 'cupo_maximo' => 250]);
    }

    public function test_otro_organizador_no_puede_modificar_el_evento_403(): void
    {
        Sanctum::actingAs($this->organizadorB);

        $this->putJson("/api/eventos/{$this->eventoDeA->id}", $this->datosValidos(['titulo' => 'Hackeado']))
            ->assertForbidden()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('eventos', ['id' => $this->eventoDeA->id, 'titulo' => 'Evento de A']);
    }

    public function test_ni_el_admin_ni_un_asistente_pueden_modificar_eventos_ajenos(): void
    {
        foreach ([User::ROLE_ADMIN, User::ROLE_ASISTENTE] as $rol) {
            Sanctum::actingAs(User::factory()->create(['role' => $rol]));

            $this->putJson("/api/eventos/{$this->eventoDeA->id}", $this->datosValidos())->assertForbidden();
        }
    }

    public function test_sin_token_no_se_puede_modificar_401(): void
    {
        $this->putJson("/api/eventos/{$this->eventoDeA->id}", $this->datosValidos())->assertUnauthorized();
    }

    public function test_la_autorizacion_se_verifica_antes_que_la_validacion(): void
    {
        Sanctum::actingAs($this->organizadorB);

        // Datos inválidos + usuario no dueño → 403 (no 422): no revelamos nada a quien no tiene permiso.
        $this->putJson("/api/eventos/{$this->eventoDeA->id}", ['titulo' => ''])->assertForbidden();
    }

    public function test_datos_invalidos_retornan_422(): void
    {
        Sanctum::actingAs($this->organizadorA);

        $this->putJson("/api/eventos/{$this->eventoDeA->id}", [
            'titulo' => str_repeat('x', 101),
            'cupo_maximo' => 0,
        ])->assertUnprocessable()->assertJsonValidationErrors(['titulo', 'cupo_maximo']);

        $this->putJson("/api/eventos/{$this->eventoDeA->id}", ['descripcion' => 'sin campos obligatorios'])
            ->assertUnprocessable()->assertJsonValidationErrors(['titulo', 'cupo_maximo']);
    }

    public function test_no_se_puede_cambiar_el_dueno_del_evento_por_asignacion_masiva(): void
    {
        Sanctum::actingAs($this->organizadorA);

        $this->putJson("/api/eventos/{$this->eventoDeA->id}", $this->datosValidos(['user_id' => $this->organizadorB->id]))
            ->assertOk();

        $this->assertSame($this->organizadorA->id, $this->eventoDeA->fresh()->user_id);
    }

    public function test_evento_inexistente_retorna_404(): void
    {
        Sanctum::actingAs($this->organizadorA);

        $this->putJson('/api/eventos/9999', $this->datosValidos())->assertNotFound();
    }
}
