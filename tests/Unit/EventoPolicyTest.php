<?php

namespace Tests\Unit;

use App\Models\Evento;
use App\Models\User;
use App\Policies\EventoPolicy;
use PHPUnit\Framework\TestCase;

/**
 * Prueba la EventoPolicy de forma aislada (sin base de datos ni HTTP).
 */
class EventoPolicyTest extends TestCase
{
    private function usuario(int $id, string $rol = User::ROLE_ORGANIZADOR): User
    {
        return (new User)->forceFill(['id' => $id, 'role' => $rol]);
    }

    private function eventoDe(int $userId): Evento
    {
        return (new Evento)->forceFill(['id' => 10, 'user_id' => $userId]);
    }

    public function test_el_dueno_puede_actualizar_su_evento(): void
    {
        $this->assertTrue((new EventoPolicy)->update($this->usuario(1), $this->eventoDe(1))->allowed());
    }

    public function test_otro_organizador_no_puede_actualizar_el_evento(): void
    {
        $respuesta = (new EventoPolicy)->update($this->usuario(2), $this->eventoDe(1));

        $this->assertTrue($respuesta->denied());
        $this->assertStringContainsString('organizador', $respuesta->message());
    }

    public function test_ser_admin_no_da_permiso_sobre_eventos_ajenos(): void
    {
        $this->assertTrue((new EventoPolicy)->update($this->usuario(3, User::ROLE_ADMIN), $this->eventoDe(1))->denied());
    }

    public function test_cualquiera_puede_ver_el_catalogo(): void
    {
        $this->assertTrue((new EventoPolicy)->viewAny(null));
    }
}
