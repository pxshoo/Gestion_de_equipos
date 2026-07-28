<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_super_admin', 'can_crear', 'can_editar', 'can_eliminar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'can_crear' => 'boolean',
            'can_editar' => 'boolean',
            'can_eliminar' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function puedeCrear(): bool
    {
        return $this->isSuperAdmin() || (bool) $this->can_crear;
    }

    public function puedeEditar(): bool
    {
        return $this->isSuperAdmin() || (bool) $this->can_editar;
    }

    public function puedeEliminar(): bool
    {
        return $this->isSuperAdmin() || (bool) $this->can_eliminar;
    }
}
