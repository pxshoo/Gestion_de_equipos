<?php

namespace App\Http\Controllers;

use App\Mail\UserAccountMail;
use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('name')->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'can_crear' => ['nullable', 'boolean'],
            'can_editar' => ['nullable', 'boolean'],
            'can_eliminar' => ['nullable', 'boolean'],
        ]);

        $usuario = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'can_crear' => $request->boolean('can_crear'),
            'can_editar' => $request->boolean('can_editar'),
            'can_eliminar' => $request->boolean('can_eliminar'),
        ]);

        $this->enviarBienvenida($usuario, $data['password']);

        return redirect()
            ->route('usuarios.index')
            ->with('status', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'can_crear' => ['nullable', 'boolean'],
            'can_editar' => ['nullable', 'boolean'],
            'can_eliminar' => ['nullable', 'boolean'],
        ]);

        $original = $usuario->only(['name', 'email', 'can_crear', 'can_editar', 'can_eliminar']);

        $usuario->name = $data['name'];
        $usuario->email = $data['email'];

        if (! $usuario->isSuperAdmin()) {
            $usuario->can_crear = $request->boolean('can_crear');
            $usuario->can_editar = $request->boolean('can_editar');
            $usuario->can_eliminar = $request->boolean('can_eliminar');
        }

        $passwordCambiada = filled($data['password'] ?? null);

        if ($passwordCambiada) {
            $usuario->password = Hash::make($data['password']);
        }

        $usuario->save();

        $cambios = $this->buildCambiosUsuario($original, $usuario->only(['name', 'email', 'can_crear', 'can_editar', 'can_eliminar']));

        if ($passwordCambiada) {
            $cambios[] = ['label' => 'Contraseña', 'before' => '••••••••', 'after' => 'Actualizada'];
        }

        if (! empty($cambios)) {
            $this->notificarUsuario($usuario, 'actualizado', $cambios);
        }

        return redirect()
            ->route('usuarios.index')
            ->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $usuario)
    {
        if ($usuario->isSuperAdmin()) {
            return redirect()
                ->route('usuarios.index')
                ->with('status', 'No puedes eliminar a un super admin.');
        }

        if ($usuario->id === $request->user()->id) {
            return redirect()
                ->route('usuarios.index')
                ->with('status', 'No puedes eliminar tu propia cuenta.');
        }

        $snapshot = (object) $usuario->only(['name', 'email']);

        $usuario->delete();

        $this->notificarUsuario($snapshot, 'eliminado');

        return redirect()
            ->route('usuarios.index')
            ->with('status', 'Usuario eliminado correctamente.');
    }

    private function buildCambiosUsuario(array $original, array $actual): array
    {
        $labels = [
            'name' => 'Nombre',
            'email' => 'Correo',
            'can_crear' => 'Puede crear',
            'can_editar' => 'Puede editar',
            'can_eliminar' => 'Puede eliminar',
        ];

        $boolCampos = ['can_crear', 'can_editar', 'can_eliminar'];
        $cambios = [];

        foreach ($actual as $campo => $valor) {
            $antes = $original[$campo] ?? null;

            if ($antes === $valor) {
                continue;
            }

            if (in_array($campo, $boolCampos, true)) {
                $antes = $antes ? 'Sí' : 'No';
                $valor = $valor ? 'Sí' : 'No';
            }

            $cambios[] = [
                'label' => $labels[$campo] ?? ucfirst($campo),
                'before' => (string) ($antes ?? '—'),
                'after' => (string) ($valor ?? '—'),
            ];
        }

        return $cambios;
    }

    private function enviarBienvenida(User $usuario, string $passwordPlano): void
    {
        try {
            Mail::to($usuario->email)->send(new WelcomeUserMail($usuario->name, $usuario->email, $passwordPlano));
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el correo de bienvenida al usuario: ' . $e->getMessage());
        }
    }

    private function notificarUsuario(object $usuario, string $accion, array $cambios = []): void
    {
        if (empty($usuario->email)) {
            return;
        }

        try {
            Mail::to($usuario->email)->send(new UserAccountMail($usuario, $accion, $cambios));
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el correo de notificación de cuenta: ' . $e->getMessage());
        }
    }
}
