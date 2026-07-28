<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'can_crear' => $request->boolean('can_crear'),
            'can_editar' => $request->boolean('can_editar'),
            'can_eliminar' => $request->boolean('can_eliminar'),
        ]);

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

        $usuario->name = $data['name'];
        $usuario->email = $data['email'];

        if (! $usuario->isSuperAdmin()) {
            $usuario->can_crear = $request->boolean('can_crear');
            $usuario->can_editar = $request->boolean('can_editar');
            $usuario->can_eliminar = $request->boolean('can_eliminar');
        }

        if (filled($data['password'] ?? null)) {
            $usuario->password = Hash::make($data['password']);
        }

        $usuario->save();

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

        $usuario->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('status', 'Usuario eliminado correctamente.');
    }
}
