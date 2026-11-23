<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_con_credenciales_validas_retorna_token_bearer(): void
    {
        User::factory()->create(['email' => 'ana@eventhub.com', 'password' => 'secreto123']);

        $this->postJson('/api/login', ['email' => 'ana@eventhub.com', 'password' => 'secreto123'])
            ->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'name', 'email', 'role']])
            ->assertJsonPath('token_type', 'Bearer');
    }

    public function test_login_con_contrasena_incorrecta_retorna_401(): void
    {
        User::factory()->create(['email' => 'ana@eventhub.com', 'password' => 'secreto123']);

        $this->postJson('/api/login', ['email' => 'ana@eventhub.com', 'password' => 'otra'])
            ->assertUnauthorized()
            ->assertJsonMissingPath('token');
    }

    public function test_login_con_email_inexistente_retorna_401(): void
    {
        $this->postJson('/api/login', ['email' => 'nadie@eventhub.com', 'password' => 'x'])->assertUnauthorized();
    }

    public function test_login_sin_datos_retorna_422(): void
    {
        $this->postJson('/api/login', [])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_las_contrasenas_se_guardan_hasheadas(): void
    {
        $user = User::factory()->create(['password' => 'secreto123']);

        $this->assertNotSame('secreto123', $user->getRawOriginal('password'));
        $this->assertTrue(Hash::check('secreto123', $user->getRawOriginal('password')));
    }

    public function test_perfil_con_token_retorna_los_datos_del_usuario(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ORGANIZADOR]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/perfil')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.role', 'organizador')
            ->assertJsonMissingPath('data.password');
    }

    public function test_perfil_sin_token_retorna_401_en_json(): void
    {
        // Incluso sin el header Accept: application/json la API responde JSON.
        $this->get('/api/perfil')->assertUnauthorized()->assertJsonStructure(['message']);
    }

    public function test_perfil_con_token_invalido_retorna_401(): void
    {
        $this->withToken('1|token-falso')->getJson('/api/perfil')->assertUnauthorized();
    }

    public function test_logout_revoca_el_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->assertSame(0, $user->tokens()->count());

        $this->app['auth']->forgetGuards(); // olvidar el usuario cacheado entre peticiones del test
        $this->withToken($token)->getJson('/api/perfil')->assertUnauthorized();
    }
}
