<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de accesos - Inventario TI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @php
        $assetVersion = '7';
    @endphp
    <link rel="stylesheet" href="{{ asset('css/dashboard.css?v=' . $assetVersion) }}">
    <style>
        :root {
            --body-pattern: url('{{ asset('images/circuit-pattern.svg?v=' . $assetVersion) }}');
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4 app-shell">
        <div class="topbar rounded-4 p-3 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="brand-logo-wrap">
                        <img src="{{ asset('images/pc-logo.png?v=' . $assetVersion) }}" alt="PC logo" class="brand-mark">
                    </div>
                    <div>
                        <div class="fw-semibold">Administración de accesos</div>
                        <div class="topbar-subtitle">Crea usuarios y define sus privilegios</div>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <nav class="top-nav-links">
                        <a href="{{ route('dashboard') }}" class="top-nav-link">📊 Dashboard</a>
                        <a href="{{ route('reasignaciones.index') }}" class="top-nav-link">🔁 Reasignaciones</a>
                        <a href="{{ route('usuarios.index') }}" class="top-nav-link active">🔐 Accesos</a>
                    </nav>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">⏻ Cerrar sesión</button>
                    </form>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-info">{{ session('status') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold mb-0">Usuarios</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#usuarioFormModal" onclick="abrirNuevoUsuario()">
                + Nuevo usuario
            </button>
        </div>

        <div class="table-card p-3 p-md-4">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Crear</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                            <th class="table-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $usuario)
                            <tr>
                                <td class="fw-semibold">{{ $usuario->name }}</td>
                                <td>{{ $usuario->email }}</td>
                                <td>
                                    @if ($usuario->isSuperAdmin())
                                        <span class="badge bg-dark">Super Admin</span>
                                    @else
                                        <span class="badge bg-secondary">Usuario</span>
                                    @endif
                                </td>
                                <td>{{ $usuario->puedeCrear() ? 'Sí' : 'No' }}</td>
                                <td>{{ $usuario->puedeEditar() ? 'Sí' : 'No' }}</td>
                                <td>{{ $usuario->puedeEliminar() ? 'Sí' : 'No' }}</td>
                                <td class="table-actions">
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#usuarioFormModal"
                                        onclick='abrirEditarUsuario(@json($usuario))'
                                        @disabled($usuario->isSuperAdmin())
                                    >
                                        Editar
                                    </button>
                                    @unless ($usuario->isSuperAdmin() || $usuario->id === auth()->id())
                                        <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este usuario?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="usuarioFormModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="usuarioForm" method="POST" action="{{ route('usuarios.store') }}">
                    @csrf
                    <div id="usuarioMethodField"></div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="usuarioFormTitle">Nuevo usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="name" id="usuario_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="usuario_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" id="usuario_password_label">Contraseña</label>
                            <input type="password" class="form-control" name="password" id="usuario_password" minlength="8">
                            <div class="form-text" id="usuario_password_hint">Mínimo 8 caracteres.</div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Privilegios</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="can_crear" value="1" id="usuario_can_crear" checked>
                                <label class="form-check-label" for="usuario_can_crear">Puede crear equipos</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="can_editar" value="1" id="usuario_can_editar" checked>
                                <label class="form-check-label" for="usuario_can_editar">Puede editar equipos</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="can_eliminar" value="1" id="usuario_can_eliminar" checked>
                                <label class="form-check-label" for="usuario_can_eliminar">Puede eliminar equipos</label>
                            </div>
                            <div class="form-text">Todos los usuarios pueden ver el dashboard, aunque no tengan estos privilegios.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const usuarioForm = document.getElementById('usuarioForm');
        const usuarioFormTitle = document.getElementById('usuarioFormTitle');
        const usuarioMethodField = document.getElementById('usuarioMethodField');
        const usuarioPasswordInput = document.getElementById('usuario_password');
        const usuarioPasswordHint = document.getElementById('usuario_password_hint');

        function abrirNuevoUsuario() {
            usuarioForm.reset();
            usuarioForm.action = "{{ route('usuarios.store') }}";
            usuarioMethodField.innerHTML = '';
            usuarioFormTitle.textContent = 'Nuevo usuario';
            usuarioPasswordInput.required = true;
            usuarioPasswordHint.textContent = 'Mínimo 8 caracteres.';
        }

        function abrirEditarUsuario(usuario) {
            usuarioForm.reset();
            usuarioForm.action = '/usuarios/' + usuario.id;
            usuarioMethodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            usuarioFormTitle.textContent = 'Editar usuario';
            document.getElementById('usuario_name').value = usuario.name ?? '';
            document.getElementById('usuario_email').value = usuario.email ?? '';
            document.getElementById('usuario_can_crear').checked = !!usuario.can_crear;
            document.getElementById('usuario_can_editar').checked = !!usuario.can_editar;
            document.getElementById('usuario_can_eliminar').checked = !!usuario.can_eliminar;
            usuarioPasswordInput.required = false;
            usuarioPasswordHint.textContent = 'Deja en blanco para mantener la contraseña actual.';
        }
    </script>
</body>
</html>
