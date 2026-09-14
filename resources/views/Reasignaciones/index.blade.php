<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reasignaciones - Inventario TI</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg?v=2') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @php
        $assetVersion = '10';
    @endphp
    <link rel="stylesheet" href="{{ asset('css/dashboard.css?v=' . $assetVersion) }}">
    <style>
        :root {
            --body-pattern: url('{{ asset('images/circuit-pattern.svg?v=' . $assetVersion) }}');
        }
    </style>
</head>
<body>
    <script>
        try {
            if (localStorage.getItem('inventario-theme') === 'dark') {
                document.body.classList.add('theme-dark');
            }
        } catch (error) {
            // Continuar con el tema claro si el navegador bloquea el almacenamiento.
        }
    </script>
    <div id="pageLoader" aria-live="polite" aria-busy="true">
        <div class="loader-card">
            <div class="loader-orb" aria-hidden="true">
                <img src="{{ asset('images/pc-logo.png?v=' . $assetVersion) }}" alt="PC logo">
            </div>
            <div class="loader-copy">
                <div class="loader-title">Cargando reasignaciones</div>
                <div class="loader-subtitle">Recopilando el historial de equipos...</div>
                <div class="loader-dots" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </div>

    @php
        $formatValue = fn ($value) => filled($value) ? $value : '—';
    @endphp

    <div class="container-fluid py-4 app-shell">
        <div class="topbar rounded-4 p-3 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="brand-logo-wrap">
                        <img src="{{ asset('images/pc-logo.png?v=' . $assetVersion) }}" alt="PC logo" class="brand-mark">
                    </div>
                    <div>
                        <div class="fw-semibold">Historial de reasignaciones</div>
                        <div class="topbar-subtitle">Seguimiento de cambios de asignación de equipos</div>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <nav class="top-nav-links">
                        <a href="{{ route('dashboard') }}" class="top-nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
                        <a href="{{ route('reasignaciones.index') }}" class="top-nav-link active"><i class="bi bi-arrow-repeat"></i> Reasignaciones</a>
                        @if (auth()->user()?->isSuperAdmin())
                            <a href="{{ route('usuarios.index') }}" class="top-nav-link"><i class="bi bi-shield-lock"></i> Accesos</a>
                        @endif
                    </nav>
                    <button type="button" class="theme-toggle" id="themeToggle" aria-label="Cambiar tema" aria-pressed="false">
                        <span class="toggle-track">
                            <span class="toggle-thumb">
                                <i class="bi bi-sun-fill sun-icon"></i>
                                <i class="bi bi-moon-fill moon-icon"></i>
                            </span>
                        </span>
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-power"></i> Cerrar sesión</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card hero border-0 rounded-4 p-4 mb-4 position-relative">
            <div class="row align-items-center g-3">
                <div class="col-lg-7">
                    <span class="badge rounded-pill px-3 py-2 mb-2">Historial</span>
                    <h2 class="fw-bold mb-1">Reasignaciones de equipos</h2>
                    <p class="hero-text-soft mb-0 hero-note">
                        Cada vez que un equipo cambia de responsable o de asignación, queda registrado aquí automáticamente.
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="hero-kpis justify-content-lg-end">
                        <div class="hero-kpi">
                            <div class="label">Total reasignaciones</div>
                            <div class="value">{{ $totalReasignaciones }}</div>
                        </div>
                        <div class="hero-kpi">
                            <div class="label">Equipos afectados</div>
                            <div class="value">{{ $equiposReasignados }}</div>
                        </div>
                        <div class="hero-kpi">
                            <div class="label">Última reasignación</div>
                            <div class="value" style="font-size: .95rem;">
                                {{ $ultimaReasignacion?->fecha_reasignacion?->format('d/m/Y H:i') ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card soft-card rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
                    <div>
                        <div class="section-chip mb-2"><i class="bi bi-arrow-repeat"></i> Resumen rápido</div>
                        <h5 class="fw-bold mb-1">Últimas reasignaciones</h5>
                    </div>
                    <a href="#historial-reasignaciones" class="btn btn-outline-secondary btn-sm">Ir al historial</a>
                </div>

                @if ($ultimasReasignaciones->isEmpty())
                    <div class="text-muted small">No hay reasignaciones registradas aún.</div>
                @else
                    <div class="row g-3">
                        @foreach ($ultimasReasignaciones as $reasignacion)
                            <div class="col-lg-6">
                                <div class="border rounded-4 p-3 h-100" style="background: rgba(148, 163, 184, 0.04); border-color: rgba(15, 23, 42, 0.08);">
                                    <div class="d-flex justify-content-between gap-2 align-items-start mb-2">
                                        <div>
                                            <div class="fw-semibold">{{ $reasignacion->equipo_nombre ?: $reasignacion->codigo_inventario }}</div>
                                            <div class="small text-muted">{{ $reasignacion->codigo_inventario ?? 'Sin código' }}</div>
                                        </div>
                                        <span class="badge text-bg-light text-dark">
                                            {{ $reasignacion->fecha_reasignacion?->format('d/m/Y H:i') ?? '—' }}
                                        </span>
                                    </div>
                                    <div class="small mb-1">
                                        <span class="text-muted">Asignado:</span>
                                        <span class="fw-semibold">{{ $reasignacion->asignado_anterior ?: '—' }}</span>
                                        <span class="mx-1 text-muted">→</span>
                                        <span class="fw-semibold">{{ $reasignacion->asignado_nuevo ?: '—' }}</span>
                                    </div>
                                    @if ($reasignacion->equipo_reasignado_anterior || $reasignacion->equipo_reasignado_nuevo)
                                        <div class="small mb-1">
                                            <span class="text-muted">Equipo reasignado:</span>
                                            <span class="fw-semibold">{{ $reasignacion->equipo_reasignado_anterior ?: '—' }}</span>
                                            <span class="mx-1 text-muted">→</span>
                                            <span class="fw-semibold">{{ $reasignacion->equipo_reasignado_nuevo ?: '—' }}</span>
                                        </div>
                                    @endif
                                    <div class="small">
                                        <span class="text-muted">Realizado por:</span>
                                        <span class="fw-semibold">{{ $reasignacion->cambiado_por_nombre ?: ($reasignacion->cambiadoPor?->name ?? 'Sistema') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="soft-card p-3 p-md-4 mb-4 filter-panel">
            <form method="GET" action="{{ route('reasignaciones.index') }}" class="row g-2 align-items-center">
                <div class="col-md-8 col-lg-6">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Buscar por código, nombre, asignado o marca...">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
                @if ($search !== '')
                    <div class="col-auto">
                        <a href="{{ route('reasignaciones.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-card p-3 p-md-4" id="historial-reasignaciones">
            @if ($reasignaciones->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="bi bi-arrow-repeat"></i></div>
                    <div class="fw-semibold mb-1">Aún no hay reasignaciones registradas</div>
                    <div class="small">Cuando reasignes un equipo desde el dashboard, aparecerá aquí automáticamente.</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Equipo</th>
                                <th>Asignado a</th>
                                <th>Equipo reasignado a</th>
                                <th>Realizado por</th>
                                <th>Ubicación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reasignaciones as $reasignacion)
                                <tr>
                                    <td class="text-nowrap">
                                        {{ $reasignacion->fecha_reasignacion?->format('d/m/Y H:i') ?? '—' }}
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $formatValue($reasignacion->codigo_inventario) }}</div>
                                        <div class="small text-muted">
                                            {{ $formatValue($reasignacion->equipo_nombre) }}
                                            @if ($reasignacion->marca || $reasignacion->modelo)
                                                &middot; {{ trim(($reasignacion->marca ?? '') . ' ' . ($reasignacion->modelo ?? '')) }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            @if ($reasignacion->asignado_anterior)
                                                <span class="text-muted">{{ $reasignacion->asignado_anterior }}</span>
                                                <span class="reasignacion-arrow mx-1">→</span>
                                            @endif
                                            <span class="fw-semibold">{{ $formatValue($reasignacion->asignado_nuevo) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($reasignacion->equipo_reasignado_anterior || $reasignacion->equipo_reasignado_nuevo)
                                            <div class="small">
                                                @if ($reasignacion->equipo_reasignado_anterior)
                                                    <span class="text-muted">{{ $reasignacion->equipo_reasignado_anterior }}</span>
                                                    <span class="reasignacion-arrow mx-1">→</span>
                                                @endif
                                                <span class="fw-semibold">{{ $formatValue($reasignacion->equipo_reasignado_nuevo) }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $formatValue($reasignacion->cambiado_por_nombre ?: ($reasignacion->cambiadoPor?->name ?? 'Sistema')) }}</div>
                                        <div class="small text-muted">
                                            @if ($reasignacion->cambiadoPor?->email)
                                                {{ $reasignacion->cambiadoPor->email }}
                                            @else
                                                Registro del sistema
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $formatValue($reasignacion->ubicacion) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $reasignaciones->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        const pageLoader = document.getElementById('pageLoader');
        const themeToggle = document.getElementById('themeToggle');
        const themeStorageKey = 'inventario-theme';

        const applyTheme = (theme) => {
            const isDark = theme === 'dark';
            document.body.classList.toggle('theme-dark', isDark);
            if (themeToggle) {
                themeToggle.setAttribute('aria-pressed', String(isDark));
            }
        };

        try {
            const savedTheme = localStorage.getItem(themeStorageKey) || 'light';
            applyTheme(savedTheme);
        } catch (error) {
            applyTheme('light');
        }

        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const isDark = !document.body.classList.contains('theme-dark');
                applyTheme(isDark ? 'dark' : 'light');
                try {
                    localStorage.setItem(themeStorageKey, isDark ? 'dark' : 'light');
                } catch (error) {
                    // Ignorar bloqueo del navegador.
                }
            });
        }

        const hidePageLoader = () => {
            if (pageLoader) {
                pageLoader.classList.add('is-hidden');
                document.body.style.overflow = '';
            }
        };

        window.addEventListener('load', () => {
            setTimeout(hidePageLoader, 220);
        });

        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                hidePageLoader();
            }
        });

        document.body.style.overflow = 'hidden';
    </script>
</body>
</html>
