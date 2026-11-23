<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_ORGANIZADOR = 'organizador';

    public const ROLE_ASISTENTE = 'asistente';

    public const ROLES = [self::ROLE_ADMIN, self::ROLE_ORGANIZADOR, self::ROLE_ASISTENTE];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // RNF 1.2: al asignar la contraseña se guarda su hash (bcrypt) automáticamente.
            'password' => 'hashed',
        ];
    }

    /** Eventos creados por el usuario (cuando es organizador). */
    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class);
    }
}
