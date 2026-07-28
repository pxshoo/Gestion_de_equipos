<?php

namespace App\Http\Controllers;

use App\Models\NotificationEmail;
use Illuminate\Http\Request;

class NotificationEmailController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:notification_emails,email'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ], [
            'email.unique' => 'Ese correo ya está en la lista de notificaciones.',
        ]);

        NotificationEmail::create($data);

        return redirect()
            ->back()
            ->with('status', 'Correo de notificación agregado correctamente.');
    }

    public function destroy(NotificationEmail $notificationEmail)
    {
        $notificationEmail->delete();

        return redirect()
            ->back()
            ->with('status', 'Correo de notificación eliminado correctamente.');
    }
}
