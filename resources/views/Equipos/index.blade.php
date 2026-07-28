<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario TI - Gestión de Equipos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js para Gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @php
        $assetVersion = '7';
    @endphp
    <style>
        :root {
            --bg: #eef6f5;
            --surface: rgba(255, 255, 255, 0.86);
            --surface-strong: #ffffff;
            --panel-border: rgba(15, 23, 42, 0.08);
            --ink: #0f172a;
            --muted: #475569;
            --brand: #0e6f6a;
            --brand-dark: #0e6f6a;
            --accent: #1fc8bc;
            --accent-soft: rgba(24, 184, 174, 0.12);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background:
                radial-gradient(circle at top left, rgba(24, 184, 174, 0.14), transparent 28%),
                radial-gradient(circle at top right, rgba(14, 111, 106, 0.12), transparent 26%),
                linear-gradient(180deg, #f8fafc 0%, var(--bg) 100%);
            color: var(--ink);
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -2;
            background: url('{{ asset('images/circuit-pattern.svg?v=' . $assetVersion) }}') no-repeat right top / min(980px, 82vw) auto;
            opacity: 0.55;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -3;
            background:
                radial-gradient(circle at 18% 14%, rgba(24, 184, 174, 0.16), transparent 18%),
                radial-gradient(circle at 82% 78%, rgba(31, 200, 188, 0.12), transparent 20%),
                linear-gradient(180deg, #ffffff 0%, #f6fbfb 100%);
        }

        #pageLoader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at top left, rgba(24, 184, 174, 0.16), transparent 30%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(240, 251, 250, 0.98));
            backdrop-filter: blur(10px);
            transition: opacity .35s ease, visibility .35s ease;
        }

        #pageLoader.is-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loader-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            border-radius: 1.5rem;
            background: rgba(255, 255, 255, 0.84);
            border: 1px solid rgba(24, 184, 174, 0.16);
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
        }

        .loader-orb {
            width: 260px;
            height: 78px;
            border-radius: 1.5rem;
            border: 1px solid rgba(24, 184, 174, 0.28);
            background: linear-gradient(135deg, #0f172a, #1e293b);
            display: grid;
            place-items: center;
            overflow: hidden;
            position: relative;
            animation: float 1.8s ease-in-out infinite;
        }

        .loader-orb::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 30% 20%, rgba(24, 184, 174, 0.14), transparent 50%);
        }

        .loader-orb img {
            width: 250px;
            height: 70px;
            object-fit: contain;
            position: relative;
            z-index: 1;
        }

        .loader-copy {
            min-width: 180px;
        }

        .loader-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: .2rem;
        }

        .loader-subtitle {
            color: var(--muted);
            font-size: .9rem;
        }

        .loader-dots {
            display: flex;
            gap: .35rem;
            margin-top: .75rem;
        }

        .loader-dots span {
            width: .55rem;
            height: .55rem;
            border-radius: 999px;
            background: var(--brand);
            opacity: .35;
            animation: pulse 1.1s infinite ease-in-out;
        }

        .loader-dots span:nth-child(2) { animation-delay: .15s; }
        .loader-dots span:nth-child(3) { animation-delay: .3s; }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        @keyframes pulse {
            0%, 80%, 100% { transform: translateY(0); opacity: .35; }
            40% { transform: translateY(-4px); opacity: 1; }
        }

        .app-shell {
            max-width: 1480px;
        }

        .topbar {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.78), rgba(240, 251, 250, 0.88));
            border: 1px solid var(--panel-border);
            backdrop-filter: blur(14px);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        .brand-mark {
            width: clamp(160px, 15vw, 230px);
            height: auto;
            display: block;
            filter: drop-shadow(0 10px 18px rgba(15, 23, 42, 0.08));
        }

        .brand-logo-wrap {
            flex: 0 0 auto;
            padding: .6rem 1rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero {
            background:
                linear-gradient(135deg, rgba(14, 111, 106, 0.98), rgba(24, 184, 174, 0.96) 54%, rgba(31, 200, 188, 0.96)),
                linear-gradient(135deg, #0f766e 0%, #18b8ae 50%, #1fc8bc 100%);
            color: #fff;
            border: 0;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25);
            overflow: hidden;
        }

        .hero::after {
            content: '';
            position: absolute;
            inset: auto -90px -110px auto;
            width: 240px;
            height: 240px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.02) 62%, transparent 70%);
            pointer-events: none;
        }

        .hero .hero-note {
            position: relative;
        }

        .hero .hero-note::before {
            content: '';
            position: absolute;
            left: -18px;
            top: 4px;
            bottom: 4px;
            width: 5px;
            border-radius: 999px;
            background: linear-gradient(180deg, #fff, rgba(255, 255, 255, 0.2));
        }

        .hero .badge {
            background: rgba(255, 255, 255, 0.92) !important;
            color: var(--brand-dark) !important;
            font-weight: 700;
        }

        .hero h1 {
            font-size: clamp(1.5rem, 2.4vw + 1rem, 2.5rem);
        }

        .hero-kpis {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
        }

        .hero-kpi {
            min-width: 140px;
            padding: .85rem 1rem;
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
        }

        .hero-kpi .label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(255, 255, 255, 0.92);
        }

        .hero-kpi .value {
            font-size: 1.15rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .soft-card,
        .table-card,
        .chart-card {
            background: var(--surface);
            border: 1px solid var(--panel-border);
            backdrop-filter: blur(12px);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            border-radius: 1.5rem;
        }

        .hero-note {
            border-left: 4px solid rgba(255, 255, 255, 0.35);
            padding-left: 1rem;
        }

        .stat-card {
            border: 0;
            color: #fff;
            overflow: hidden;
            position: relative;
            min-height: 120px;
            border-radius: 1.35rem;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            inset: auto -20px -40px auto;
            width: 140px;
            height: 140px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
        }

        .stat-total { background: linear-gradient(135deg, #1e293b, #334155); }
        .stat-ok { background: linear-gradient(135deg, #0f5f5a, #1fc8bc); }
        .stat-good { background: linear-gradient(135deg, #155e75, #0891b2); }
        .stat-review { background: linear-gradient(135deg, #b45309, #f59e0b); }
        .stat-mini { background: linear-gradient(135deg, #334155, #475569); }

        .metric-label {
            text-transform: uppercase;
            letter-spacing: .08em;
            font-size: .72rem;
            opacity: .95;
        }

        .metric-value {
            font-size: clamp(1.4rem, 1.6vw + 1rem, 1.9rem);
            font-weight: 800;
            line-height: 1;
        }

        .metric-footnote {
            font-size: .82rem;
            opacity: .92;
        }

        .section-chip {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .45rem .75rem;
            border-radius: 999px;
            background: #fff;
            color: var(--muted);
            border: 1px solid var(--panel-border);
            font-size: .82rem;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        }

        .estado-badge {
            font-weight: 600;
        }

        .estado-excelente {
            background-color: #157347;
            color: #ffffff;
        }

        .estado-bueno {
            background-color: #0a58ca;
            color: #ffffff;
        }

        .estado-regular {
            background-color: #997404;
            color: #ffffff;
        }

        .estado-baja {
            background-color: #b02a37;
            color: #ffffff;
        }

        .notify-banner {
            background: var(--accent-soft);
            border: 1px solid rgba(24, 184, 174, 0.3);
            border-radius: 1rem;
            padding: .85rem 1rem;
        }

        .notify-banner-icon {
            font-size: 1.1rem;
            color: var(--brand-dark);
            line-height: 1.4;
        }

        .notify-banner-title {
            font-weight: 700;
            font-size: .85rem;
            color: var(--brand-dark);
            margin-bottom: .25rem;
        }

        .notify-banner-text {
            font-size: .82rem;
            color: var(--ink);
        }

        .notify-email-badge {
            background-color: #ffffff;
            color: var(--brand-dark);
            border: 1px solid rgba(24, 184, 174, 0.35);
            font-weight: 600;
            margin-left: .35rem;
        }

        .table thead th {
            white-space: nowrap;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #334155;
        }

        .table tbody tr {
            transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(29, 78, 216, 0.03);
        }

        .table-card .table > :not(caption) > * > * {
            padding-top: .9rem;
            padding-bottom: .9rem;
        }

        .table-card .table thead {
            background: #f8fafc;
        }

        .table-card .table thead th:first-child {
            border-top-left-radius: 1rem;
        }

        .table-card .table thead th:last-child {
            border-top-right-radius: 1rem;
        }

        .filter-panel .form-control,
        .filter-panel .form-select {
            border-color: rgba(15, 23, 42, 0.12);
            border-radius: .9rem;
            min-height: 46px;
        }

        .btn {
            border-radius: .9rem;
        }

        .subtle-label {
            color: var(--muted);
            font-size: .875rem;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .detail-item {
            background: #f8fafc;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1rem;
            padding: .85rem 1rem;
        }

        .detail-label {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
            margin-bottom: .25rem;
        }

        .detail-value {
            font-weight: 600;
            color: #0f172a;
            word-break: break-word;
        }

        .equipo-form-modal .modal-dialog {
            max-width: min(1180px, calc(100vw - 1rem));
        }

        .equipo-form-modal .modal-content {
            max-height: calc(100vh - 1rem);
        }

        .equipo-form-modal .modal-body {
            overflow-y: auto;
            max-height: calc(100vh - 11rem);
        }

        .table-actions {
            white-space: nowrap;
        }

        .brand-subtle {
            color: rgba(255, 255, 255, 0.92);
            font-size: .85rem;
        }

        .hero-text-soft {
            color: rgba(255, 255, 255, 0.88);
        }

        .topbar-subtitle {
            color: var(--muted);
            font-size: .85rem;
        }

        .top-nav-links {
            display: flex;
            gap: .3rem;
            background: rgba(15, 23, 42, 0.04);
            padding: .3rem;
            border-radius: 999px;
            border: 1px solid var(--panel-border);
        }

        .top-nav-link {
            padding: .5rem 1.05rem;
            border-radius: 999px;
            font-size: .85rem;
            font-weight: 600;
            color: var(--muted);
            text-decoration: none;
            transition: background-color .15s ease, color .15s ease, box-shadow .15s ease;
            white-space: nowrap;
        }

        .top-nav-link:hover {
            color: var(--brand-dark);
            background: rgba(255, 255, 255, 0.75);
        }

        .top-nav-link.active {
            background: linear-gradient(135deg, var(--brand), var(--accent));
            color: #fff;
            box-shadow: 0 8px 20px rgba(14, 111, 106, 0.28);
        }



        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 1400px) {
            .app-shell {
                max-width: 100%;
            }
        }

        @media (max-width: 992px) {
            .topbar .brand-logo-wrap {
                padding: .45rem .75rem;
            }

            .hero .card-body {
                padding: 1.5rem !important;
            }

            .top-nav-link {
                padding: .4rem .75rem;
                font-size: .8rem;
            }
        }
    </style>
</head>
<body>
    <div id="pageLoader" aria-live="polite" aria-busy="true">
        <div class="loader-card">
            <div class="loader-orb" aria-hidden="true">
                <img src="{{ asset('images/pc-logo.png?v=' . $assetVersion) }}" alt="PC logo">
            </div>
            <div class="loader-copy">
                <div class="loader-title">Cargando inventario</div>
                <div class="loader-subtitle">Preparando el panel de equipos...</div>
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
        $formatMoney = fn ($value) => filled($value) ? '$ ' . number_format((float) $value, 0, ',', '.') : '—';
    @endphp
    @php
        $generatedCodes = [
            'Computador' => $nextCodigoComputador,
            'Telefono' => $nextCodigoTelefono,
        ];
    @endphp

    <div class="container-fluid py-4 app-shell">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
        @endif

        <div class="topbar rounded-4 p-3 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="brand-logo-wrap">
                        <img src="{{ asset('images/pc-logo.png?v=' . $assetVersion) }}" alt="PC logo" class="brand-mark">
                    </div>
                    <div>
                        <div class="fw-semibold">Panel de inventario</div>
                        <div class="topbar-subtitle">Gestión de equipos y control de reasignaciones</div>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <nav class="top-nav-links">
                        <a href="{{ route('dashboard') }}" class="top-nav-link active">📊 Dashboard</a>
                        <a href="{{ route('reasignaciones.index') }}" class="top-nav-link">🔁 Reasignaciones</a>
                        @if (auth()->user()?->isSuperAdmin())
                            <a href="{{ route('usuarios.index') }}" class="top-nav-link">🔐 Accesos</a>
                        @endif
                    </nav>
                    <button
                        type="button"
                        class="btn btn-outline-primary btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#notificationEmailsModal">
                        ✉ Correos de notificación ({{ $notificationEmails->count() }})
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">⏻ Cerrar sesión</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card hero rounded-4 mb-4 position-relative">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                    <div class="hero-note">
                        <span class="badge rounded-pill mb-3">Inventario TI</span>
                        <h1 class="display-6 fw-bold mb-2">Gestión profesional de activos</h1>
                        <p class="mb-3 hero-text-soft">Una vista principal más ordenada, limpia y preparada para integrarse con la identidad visual de la empresa.</p>
                        <div class="hero-kpis">
                            <div class="hero-kpi">
                                <div class="label">Total equipos</div>
                                <div class="value">{{ $totalEquipos }}</div>
                            </div>
                            <div class="hero-kpi">
                                <div class="label">Valor inventario</div>
                                <div class="value">$ {{ number_format($valorTotal, 0, ',', '.') }}</div>
                            </div>
                            <div class="hero-kpi">
                                <div class="label">Última carga</div>
                                <div class="value">OK</div>
                            </div>
                        </div>
                    </div>
                    <div class="text-lg-end">
                        <div class="subtle-label hero-text-soft">Última actualización</div>
                        <div class="fs-5 fw-semibold">{{ now()->format('d/m/Y H:i') }}</div>
                        <div class="brand-subtle mt-1">Plantilla lista para branding corporativo</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card stat-card stat-total shadow-sm h-100">
                    <div class="card-body">
                        <div class="metric-label">Total equipos</div>
                        <div class="metric-value mb-1">{{ $totalEquipos }}</div>
                        <div class="metric-footnote">Registros cargados en la base</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card stat-card stat-ok shadow-sm h-100">
                    <div class="card-body">
                        <div class="metric-label">Excelente</div>
                        <div class="metric-value mb-1">{{ $excelentes }}</div>
                        <div class="metric-footnote">Estado óptimo</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card stat-card stat-good shadow-sm h-100">
                    <div class="card-body">
                        <div class="metric-label">Bueno</div>
                        <div class="metric-value mb-1">{{ $buenos }}</div>
                        <div class="metric-footnote">Operativo</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card stat-card stat-review shadow-sm h-100">
                    <div class="card-body">
                        <div class="metric-label">Revisión / Baja</div>
                        <div class="metric-value mb-1">{{ $revision }}</div>
                        <div class="metric-footnote">Pendiente de evaluación</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card soft-card h-100">
                    <div class="card-body p-4">
                        <div class="section-chip mb-3">Valor total del inventario</div>
                        <h3 class="fw-bold mb-2">$ {{ number_format($valorTotal, 0, ',', '.') }}</h3>
                        <div class="text-muted">Suma del costo actual importado desde el archivo.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card soft-card h-100">
                    <div class="card-body p-4">
                        <div class="section-chip mb-3">Criterio de tipo</div>
                        <h3 class="fw-bold mb-2">Computador, Telefono</h3>
                        <div class="text-muted">Notebook y Desktop se consolidan como Computador para evitar duplicidad de categorías.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-4">
                <div class="card stat-card stat-mini shadow-sm h-100">
                    <div class="card-body">
                        <div class="metric-label">Computadores</div>
                        <div class="metric-value mb-1">{{ $computadores }}</div>
                        <div class="metric-footnote">Equipos principales</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card stat-card stat-mini shadow-sm h-100">
                    <div class="card-body">
                        <div class="metric-label">Teléfonos</div>
                        <div class="metric-value mb-1">{{ $telefonos }}</div>
                        <div class="metric-footnote">Inventario móvil</div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-xl-4">
                <div class="card chart-card h-100">
                    <div class="card-body">
                        <div class="section-chip mb-3">Distribución por tipo</div>
                        <div style="height: 170px;">
                            <canvas id="tiposChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-4">
                <div class="card soft-card h-100">
                    <div class="card-body p-4">
                        <div class="subtle-label mb-1">Resumen operativo</div>
                        <h3 class="fw-bold mb-2">Inventario consolidado</h3>
                        <div class="text-muted">Toda la información del equipo se puede abrir desde el botón <strong>Ver detalle</strong> en cada fila.</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card soft-card h-100">
                    <div class="card-body p-4">
                        <div class="subtle-label mb-1">Promedio de valor</div>
                        <h3 class="fw-bold mb-2">
                            $ {{ $totalEquipos > 0 ? number_format($valorTotal / $totalEquipos, 0, ',', '.') : '0' }}
                        </h3>
                        <div class="text-muted">Referencia rápida del costo medio por registro.</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card soft-card h-100">
                    <div class="card-body p-4">
                        <div class="subtle-label mb-1">Cobertura de estado</div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: {{ $totalEquipos > 0 ? round(($excelentes / $totalEquipos) * 100) : 0 }}%"></div>
                            <div class="progress-bar bg-info" style="width: {{ $totalEquipos > 0 ? round(($buenos / $totalEquipos) * 100) : 0 }}%"></div>
                            <div class="progress-bar bg-warning" style="width: {{ $totalEquipos > 0 ? round(($revision / $totalEquipos) * 100) : 0 }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 small text-muted">
                            <span>Excelente / Bueno / Revisión</span>
                            <span>{{ $totalEquipos }} total</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card soft-card rounded-4 mb-4 filter-panel">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Filtros</h5>
                        <div class="subtle-label">Busca por nombre asignado, marca, modelo o serie.</div>
                    </div>
                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
                </div>

                <form method="GET" action="{{ url()->current() }}" class="row g-3">
                    <div class="col-lg-5">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Nombre asignado, código, marca, modelo, serie...">
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="">Todos</option>
                            @foreach (['Excelente', 'Bueno', 'Regular', 'Malo', 'De Baja'] as $estado)
                                <option value="{{ $estado }}" @selected($estadoSeleccionado === $estado)>{{ $estado }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($tiposDisponibles as $tipo)
                                <option value="{{ $tipo }}" @selected($tipoSeleccionado === $tipo)>{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Aplicar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card table-card rounded-4 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">Listado de equipos</h5>
                        <div class="subtle-label">Ordenado por nombre asignado</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="subtle-label">{{ $equipos->count() }} registros visibles</div>
                        @if (auth()->user()?->puedeCrear())
                            <button type="button" class="btn btn-primary btn-sm" id="newEquipoButton">Nuevo equipo</button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pt-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-borderless">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Asignado a</th>
                                <th>Tipo</th>
                                <th>Marca / Modelo</th>
                                <th>Costo actual</th>
                                <th>Nº Serie</th>
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($equipos as $equipo)
                                @php
                                    $equipoDetail = [
                                        'id' => $equipo->id,
                                        'codigo' => $equipo->codigo_inventario,
                                        'nombre' => $equipo->nombre,
                                        'asignado_a' => $equipo->asignado_a,
                                        'categoria' => $equipo->categoria,
                                        'tipo' => $equipo->tipo,
                                        'marca' => $equipo->marca,
                                        'modelo' => $equipo->modelo,
                                        'serie' => $equipo->numero_serie,
                                        'usuario_pc' => $equipo->usuario_pc,
                                        'procesador' => $equipo->procesador,
                                        'disco' => $equipo->tipo_disco_duro,
                                        'ram' => $equipo->ram_instalada,
                                        'pantalla_externa' => $equipo->pantalla_externa,
                                        'marca_monitor' => $equipo->marca_monitor,
                                        'modelo_monitor' => $equipo->modelo_monitor,
                                        'serie_monitor' => $equipo->numero_serie_monitor,
                                        'teclado' => $equipo->teclado,
                                        'mouse' => $equipo->mouse,
                                        'base_notebook' => $equipo->base_notebook,
                                        'onedrive_funcionando' => $equipo->onedrive_funcionando,
                                        'respaldo_onedrive' => $equipo->respaldo_onedrive,
                                        'equipo_reasignado_a' => $equipo->equipo_reasignado_a,
                                        'valoracion_equipo' => $equipo->valoracion_equipo,
                                        'valoracion_monitor' => $equipo->valoracion_monitor,
                                        'valoracion_equipo_actual' => $equipo->valoracion_equipo_actual,
                                        'valoracion_equipo_actual_numero' => $equipo->valoracion_equipo_actual_numero,
                                        'mantencion_realizada' => $equipo->mantencion_realizada,
                                        'ubicacion' => $equipo->ubicacion,
                                        'estado' => $equipo->estado,
                                        'observaciones' => $equipo->observaciones,
                                    ];
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $equipo->codigo_inventario }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $equipo->nombre ?? $equipo->asignado_a ?? 'Sin nombre' }}</div>
                                        <div class="small text-muted">{{ $equipo->categoria }}</div>
                                    </td>
                                    <td><span class="badge text-bg-secondary">{{ $equipo->tipo }}</span></td>
                                    <td>{{ $equipo->marca }} {{ $equipo->modelo }}</td>
                                    <td class="fw-semibold">$ {{ number_format((float) $equipo->valoracion_equipo_actual_numero, 0, ',', '.') }}</td>
                                    <td><code>{{ $equipo->numero_serie }}</code></td>
                                    <td>{{ $equipo->ubicacion ?? 'Sin ubicación' }}</td>
                                    <td>
                                        <span class="badge rounded-pill estado-badge
                                            @if($equipo->estado == 'Excelente') estado-excelente
                                            @elseif($equipo->estado == 'Bueno') estado-bueno
                                            @elseif($equipo->estado == 'Regular') estado-regular
                                            @else estado-baja @endif">
                                            {{ $equipo->estado }}
                                        </span>
                                    </td>
                                    <td class="text-end table-actions">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary open-equipo-modal"
                                        data-bs-toggle="modal"
                                        data-bs-target="#equipoDetailModal"
                                        data-equipo='@json($equipoDetail)'>
                                        Ver detalle
                                    </button>
                                    @if (auth()->user()?->puedeEditar())
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary ms-1 open-equipo-form"
                                            data-equipo='@json($equipoDetail)'>
                                            Editar
                                        </button>
                                    @endif
                                    @if (auth()->user()?->puedeEliminar())
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger ms-1 delete-equipo-btn"
                                            data-id="{{ $equipo->id }}"
                                            data-nombre="{{ $equipo->nombre ?? $equipo->asignado_a ?? $equipo->codigo_inventario }}">
                                            Eliminar
                                        </button>
                                    @endif
                                </td>
                                </tr>
                            @empty
                                <tr>
                                <td colspan="9" class="text-center text-muted py-5">No hay resultados con los filtros seleccionados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <form id="deleteEquipoForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <div class="modal fade" id="notificationEmailsModal" tabindex="-1" aria-labelledby="notificationEmailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 px-4 pt-4 pb-2">
                    <div>
                        <div class="subtle-label">Notificaciones automáticas</div>
                        <h5 class="modal-title fw-bold" id="notificationEmailsModalLabel">Correos de notificación</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    @if ($errors->any())
                        <div class="alert alert-danger border-0 rounded-4">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="subtle-label">Cada correo agregado aquí recibirá un aviso automático cuando se cree, edite, reasigne o elimine un equipo.</p>

                    <div class="table-responsive mb-4">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Correo</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($notificationEmails as $notifEmail)
                                    <tr>
                                        <td class="fw-semibold">{{ $notifEmail->email }}</td>
                                        <td class="text-muted">{{ $notifEmail->descripcion ?: '—' }}</td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('notificacion-correos.destroy', $notifEmail->id) }}" onsubmit="return confirm('¿Eliminar el correo {{ $notifEmail->email }} de la lista de notificaciones?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Quitar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No hay correos configurados todavía.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <hr class="my-2">

                    <h6 class="fw-bold text-uppercase text-muted small mb-2">Agregar nuevo correo</h6>
                    <form method="POST" action="{{ route('notificacion-correos.store') }}" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" name="email" class="form-control" placeholder="nombre@dominio.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Descripción (opcional)</label>
                            <input type="text" name="descripcion" class="form-control" placeholder="Ej: Jefe de TI, Soporte, etc.">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Agregar correo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="equipoDetailModal" tabindex="-1" aria-labelledby="equipoDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 px-4 pt-4 pb-2">
                    <div>
                        <div class="subtle-label">Detalle del equipo</div>
                        <h5 class="modal-title fw-bold" id="equipoDetailModalLabel">—</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <div class="detail-grid">
                        <div class="detail-item"><div class="detail-label">Asignado a</div><div class="detail-value" id="detailNombre">—</div></div>
                        <div class="detail-item"><div class="detail-label">Categoría / Tipo</div><div class="detail-value" id="detailCategoriaTipo">—</div></div>
                        <div class="detail-item"><div class="detail-label">Código</div><div class="detail-value" id="detailCodigo">—</div></div>
                        <div class="detail-item"><div class="detail-label">Estado</div><div class="detail-value" id="detailEstado">—</div></div>
                        <div class="detail-item"><div class="detail-label">Marca / Modelo</div><div class="detail-value" id="detailMarcaModelo">—</div></div>
                        <div class="detail-item"><div class="detail-label">Nº Serie</div><div class="detail-value" id="detailSerie">—</div></div>
                        <div class="detail-item"><div class="detail-label">Usuario PC</div><div class="detail-value" id="detailUsuarioPc">—</div></div>
                        <div class="detail-item"><div class="detail-label">Procesador</div><div class="detail-value" id="detailProcesador">—</div></div>
                        <div class="detail-item"><div class="detail-label">Disco / RAM</div><div class="detail-value" id="detailDiscoRam">—</div></div>
                        <div class="detail-item"><div class="detail-label">Pantalla externa</div><div class="detail-value" id="detailPantallaExterna">—</div></div>
                        <div class="detail-item"><div class="detail-label">Monitor</div><div class="detail-value" id="detailMonitor">—</div></div>
                        <div class="detail-item"><div class="detail-label">Periféricos</div><div class="detail-value" id="detailPerifericos">—</div></div>
                        <div class="detail-item"><div class="detail-label">OneDrive / Respaldo</div><div class="detail-value" id="detailOneDrive">—</div></div>
                        <div class="detail-item"><div class="detail-label">Reasignado a</div><div class="detail-value" id="detailReasignado">—</div></div>
                        <div class="detail-item"><div class="detail-label">Valoración equipo</div><div class="detail-value" id="detailValorEquipo">—</div></div>
                        <div class="detail-item"><div class="detail-label">Valoración monitor</div><div class="detail-value" id="detailValorMonitor">—</div></div>
                        <div class="detail-item"><div class="detail-label">Costo actual</div><div class="detail-value" id="detailCostoActual">—</div></div>
                        <div class="detail-item"><div class="detail-label">Mantención</div><div class="detail-value" id="detailMantencion">—</div></div>
                        <div class="detail-item"><div class="detail-label">Ubicación</div><div class="detail-value" id="detailUbicacion">—</div></div>
                        <div class="detail-item" style="grid-column: 1 / -1;">
                            <div class="detail-label">Observaciones</div>
                            <div class="detail-value" id="detailObservaciones">—</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    @if (auth()->user()?->puedeEditar())
                        <button type="button" class="btn btn-primary" id="editFromDetailButton">Editar equipo</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade equipo-form-modal" id="equipoFormModal" tabindex="-1" aria-labelledby="equipoFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form id="equipoForm" method="POST" action="{{ route('equipos.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="equipoFormMethod" value="POST">
                    <div class="modal-header border-0 px-4 pt-4 pb-2">
                        <div>
                            <div class="subtle-label" id="equipoFormSubtitle">Nuevo registro</div>
                            <h5 class="modal-title fw-bold" id="equipoFormModalLabel">Agregar equipo</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body px-4 pb-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="notify-banner d-flex align-items-start gap-2">
                                    <span class="notify-banner-icon">✉</span>
                                    <div class="flex-grow-1">
                                        <div class="notify-banner-title">Notificación automática por correo</div>
                                        <div class="notify-banner-text mb-2">Selecciona a quién avisar al guardar este equipo:</div>
                                        <div class="d-flex flex-wrap gap-3 mb-2" id="notificarAContainer">
                                            @forelse ($notificationEmails as $notifEmail)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="notificar_a[]" value="{{ $notifEmail->email }}" id="notificar_a_{{ $notifEmail->id }}" checked>
                                                    <label class="form-check-label" for="notificar_a_{{ $notifEmail->id }}" title="{{ $notifEmail->descripcion }}">
                                                        {{ $notifEmail->email }}
                                                    </label>
                                                </div>
                                            @empty
                                                <span class="text-muted small">Aún no hay destinatarios configurados. Agrega uno abajo.</span>
                                            @endforelse
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="toggleNuevoDestinatario">+ Agregar otro destinatario</button>
                                        <div class="row g-2 mt-2 d-none" id="nuevoDestinatarioFields">
                                            <div class="col-md-6">
                                                <input type="email" class="form-control form-control-sm" name="nuevo_destinatario_email" id="nuevo_destinatario_email" placeholder="nuevo-correo@dominio.com">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control form-control-sm" name="nuevo_destinatario_descripcion" id="nuevo_destinatario_descripcion" placeholder="Descripción (opcional)">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <h6 class="fw-bold text-uppercase text-muted small mb-2">Identificación</h6>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Código inventario</label>
                                <input type="text" class="form-control" id="codigo_inventario" readonly>
                                <div class="form-text">Se genera automáticamente según el tipo seleccionado.</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Asignado a</label>
                                <input type="text" class="form-control" name="asignado_a" id="asignado_a">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" id="tipo" required>
                                    <option value="Computador">Computador</option>
                                    <option value="Telefono">Telefono</option>
                                </select>
                                <input type="hidden" name="tipo" id="tipo_hidden" value="Computador">
                            </div>
                            <input type="hidden" name="nombre" id="nombre">
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado" id="estado" required>
                                    <option value="Excelente">Excelente</option>
                                    <option value="Bueno">Bueno</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Malo">Malo</option>
                                    <option value="De Baja">De Baja</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Ubicación</label>
                                <input type="text" class="form-control" name="ubicacion" id="ubicacion">
                            </div>
                            <div class="col-12">
                                <hr class="my-2">
                            </div>
                            <div class="col-12">
                                <h6 class="fw-bold text-uppercase text-muted small mb-2">Especificaciones</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Marca</label>
                                <select class="form-select spec-choice" id="marca_choice" data-field="marca">
                                    <option value="">Selecciona una marca...</option>
                                    @foreach ($specOptions['marca'] as $opcion)
                                        <option value="{{ $opcion }}">{{ $opcion }}</option>
                                    @endforeach
                                    <option value="__otro__">Otro (agregar nuevo)</option>
                                </select>
                                <input type="text" class="form-control mt-2 d-none" name="marca" id="marca" placeholder="Escribe la nueva marca" required readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Modelo</label>
                                <select class="form-select spec-choice" id="modelo_choice" data-field="modelo">
                                    <option value="">Selecciona un modelo...</option>
                                    @foreach ($specOptions['modelo'] as $opcion)
                                        <option value="{{ $opcion }}">{{ $opcion }}</option>
                                    @endforeach
                                    <option value="__otro__">Otro (agregar nuevo)</option>
                                </select>
                                <input type="text" class="form-control mt-2 d-none" name="modelo" id="modelo" placeholder="Escribe el nuevo modelo" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nº Serie</label>
                                <input type="text" class="form-control" name="numero_serie" id="numero_serie">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Usuario PC</label>
                                <input type="text" class="form-control" name="usuario_pc" id="usuario_pc">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Procesador</label>
                                <select class="form-select spec-choice" id="procesador_choice" data-field="procesador">
                                    <option value="">Selecciona un procesador...</option>
                                    @foreach ($specOptions['procesador'] as $opcion)
                                        <option value="{{ $opcion }}">{{ $opcion }}</option>
                                    @endforeach
                                    <option value="__otro__">Otro (agregar nuevo)</option>
                                </select>
                                <input type="text" class="form-control mt-2 d-none" name="procesador" id="procesador" placeholder="Escribe el nuevo procesador" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo disco duro</label>
                                <select class="form-select spec-choice" id="tipo_disco_duro_choice" data-field="tipo_disco_duro">
                                    <option value="">Selecciona un tipo...</option>
                                    @foreach ($specOptions['tipo_disco_duro'] as $opcion)
                                        <option value="{{ $opcion }}">{{ $opcion }}</option>
                                    @endforeach
                                    <option value="__otro__">Otro (agregar nuevo)</option>
                                </select>
                                <input type="text" class="form-control mt-2 d-none" name="tipo_disco_duro" id="tipo_disco_duro" placeholder="Escribe el nuevo tipo de disco" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RAM instalada</label>
                                <select class="form-select spec-choice" id="ram_instalada_choice" data-field="ram_instalada">
                                    <option value="">Selecciona una RAM...</option>
                                    @foreach ($specOptions['ram_instalada'] as $opcion)
                                        <option value="{{ $opcion }}">{{ $opcion }}</option>
                                    @endforeach
                                    <option value="__otro__">Otro (agregar nuevo)</option>
                                </select>
                                <input type="text" class="form-control mt-2 d-none" name="ram_instalada" id="ram_instalada" placeholder="Escribe la nueva RAM" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pantalla externa</label>
                                <select class="form-select" name="pantalla_externa" id="pantalla_externa">
                                    <option value="">--</option>
                                    <option value="SI">SI</option>
                                    <option value="NO">NO</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <hr class="my-2">
                            </div>
                            <div class="col-12">
                                <h6 class="fw-bold text-uppercase text-muted small mb-2">Monitor y periféricos</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Marca monitor</label>
                                <input type="text" class="form-control" name="marca_monitor" id="marca_monitor">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Modelo monitor</label>
                                <input type="text" class="form-control" name="modelo_monitor" id="modelo_monitor">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nº Serie monitor</label>
                                <input type="text" class="form-control" name="numero_serie_monitor" id="numero_serie_monitor">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Teclado</label>
                                <select class="form-select" name="teclado" id="teclado">
                                    <option value="">--</option>
                                    <option value="SI">SI</option>
                                    <option value="NO">NO</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mouse</label>
                                <select class="form-select" name="mouse" id="mouse">
                                    <option value="">--</option>
                                    <option value="SI">SI</option>
                                    <option value="NO">NO</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Base notebook</label>
                                <select class="form-select" name="base_notebook" id="base_notebook">
                                    <option value="">--</option>
                                    <option value="SI">SI</option>
                                    <option value="NO">NO</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Reasignado a</label>
                                <input type="text" class="form-control" name="equipo_reasignado_a" id="equipo_reasignado_a">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">OneDrive funciona</label>
                                <select class="form-select" name="onedrive_funcionando" id="onedrive_funcionando">
                                    <option value="">--</option>
                                    <option value="SI">SI</option>
                                    <option value="NO">NO</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Respaldo OneDrive</label>
                                <select class="form-select" name="respaldo_onedrive" id="respaldo_onedrive">
                                    <option value="">--</option>
                                    <option value="SI">SI</option>
                                    <option value="NO">NO</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mantención realizada</label>
                                <select class="form-select" name="mantencion_realizada" id="mantencion_realizada">
                                    <option value="">--</option>
                                    <option value="SI">SI</option>
                                    <option value="NO">NO</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <hr class="my-2">
                            </div>
                            <div class="col-12">
                                <h6 class="fw-bold text-uppercase text-muted small mb-2">Valores y notas</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Valoración equipo</label>
                                <input type="text" class="form-control" name="valoracion_equipo" id="valoracion_equipo" placeholder="$ 0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Valoración monitor</label>
                                <input type="text" class="form-control" name="valoracion_monitor" id="valoracion_monitor" placeholder="$ 0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Valor actual</label>
                                <input type="text" class="form-control" name="valoracion_equipo_actual" id="valoracion_equipo_actual" placeholder="$ 0">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" name="observaciones" id="observaciones" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="equipoFormSubmitButton">Guardar equipo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const pageLoader = document.getElementById('pageLoader');

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

        const tiposData = @json($tiposData);
        const canvas = document.getElementById('tiposChart');
        const detailModal = document.getElementById('equipoDetailModal');
        const formModalElement = document.getElementById('equipoFormModal');
        const equipoForm = document.getElementById('equipoForm');
        const formTitle = document.getElementById('equipoFormModalLabel');
        const formSubtitle = document.getElementById('equipoFormSubtitle');
        const formSubmitButton = document.getElementById('equipoFormSubmitButton');
        const formMethod = document.getElementById('equipoFormMethod');
        const tipoSelect = document.getElementById('tipo');
        const tipoHidden = document.getElementById('tipo_hidden');
        const codigoInventarioInput = document.getElementById('codigo_inventario');
        const updateBaseUrl = @json(url('/equipos'));
        const generatedCodes = @json($generatedCodes);
        let currentEquipo = null;
        let pendingEquipoForForm = null;

        const formFields = [
            'asignado_a', 'estado', 'ubicacion', 'marca', 'modelo',
            'numero_serie', 'usuario_pc', 'procesador', 'tipo_disco_duro', 'ram_instalada', 'pantalla_externa',
            'marca_monitor', 'modelo_monitor', 'numero_serie_monitor', 'teclado', 'mouse', 'base_notebook',
            'equipo_reasignado_a', 'onedrive_funcionando', 'respaldo_onedrive', 'mantencion_realizada',
            'valoracion_equipo', 'valoracion_monitor', 'valoracion_equipo_actual', 'observaciones'
        ];

        const specFields = ['marca', 'modelo', 'procesador', 'tipo_disco_duro', 'ram_instalada'];

        const syncSpecField = (field, value) => {
            const select = document.getElementById(`${field}_choice`);
            const input = document.getElementById(field);

            if (!select || !input) {
                return;
            }

            const val = value ? String(value) : '';
            const existe = Array.from(select.options).some((option) => option.value !== '' && option.value !== '__otro__' && option.value === val);

            if (val !== '' && existe) {
                select.value = val;
                input.value = val;
                input.readOnly = true;
                input.classList.add('d-none');
            } else if (val !== '') {
                select.value = '__otro__';
                input.value = val;
                input.readOnly = false;
                input.classList.remove('d-none');
            } else {
                select.value = '';
                input.value = '';
                input.readOnly = true;
                input.classList.add('d-none');
            }
        };

        specFields.forEach((field) => {
            const select = document.getElementById(`${field}_choice`);
            const input = document.getElementById(field);

            if (!select || !input) {
                return;
            }

            select.addEventListener('change', function () {
                if (this.value === '__otro__') {
                    input.value = '';
                    input.readOnly = false;
                    input.classList.remove('d-none');
                    input.focus();
                } else {
                    input.value = this.value;
                    input.readOnly = true;
                    input.classList.add('d-none');
                }
            });
        });

        const formatValue = (value) => value && String(value).trim() !== '' ? value : '—';
        const formatBoolean = (value) => value === 'SI' ? 'Sí' : value === 'NO' ? 'No' : '—';
        const formatMoney = (value, rawMoney) => {
            if (rawMoney && String(rawMoney).trim() !== '') {
                return rawMoney;
            }

            if (!value || String(value).trim() === '') {
                return '—';
            }

            const numeric = Number(value);

            if (Number.isNaN(numeric)) {
                return value;
            }

            return '$ ' + new Intl.NumberFormat('es-CL').format(numeric);
        };

        const getModal = (element) => bootstrap.Modal.getOrCreateInstance(element);

        const nuevoDestinatarioToggle = document.getElementById('toggleNuevoDestinatario');
        const nuevoDestinatarioFields = document.getElementById('nuevoDestinatarioFields');

        if (nuevoDestinatarioToggle && nuevoDestinatarioFields) {
            nuevoDestinatarioToggle.addEventListener('click', () => {
                nuevoDestinatarioFields.classList.toggle('d-none');

                if (!nuevoDestinatarioFields.classList.contains('d-none')) {
                    document.getElementById('nuevo_destinatario_email')?.focus();
                }
            });
        }

        @if ($errors->any())
            const notificationEmailsModal = document.getElementById('notificationEmailsModal');
            if (notificationEmailsModal) {
                getModal(notificationEmailsModal).show();
            }
        @endif

        const syncTipoState = (tipo, allowChange = true) => {
            if (tipoHidden) {
                tipoHidden.value = tipo;
            }

            if (tipoSelect) {
                tipoSelect.value = tipo;
                tipoSelect.disabled = !allowChange;
            }

            if (codigoInventarioInput) {
                codigoInventarioInput.value = generatedCodes[tipo] || generatedCodes.Computador;
            }
        };

        const syncNombreFromAsignado = () => {
            const asignadoInput = document.getElementById('asignado_a');

            if (!asignadoInput || !equipoForm.elements.nombre) {
                return;
            }

            if (!equipoForm.elements.nombre.value || equipoForm.dataset.syncName !== 'manual') {
                equipoForm.elements.nombre.value = asignadoInput.value;
            }
        };

        const resetEquipoForm = () => {
            equipoForm.reset();
            formMethod.value = 'POST';
            equipoForm.action = @json(route('equipos.store'));
            formTitle.textContent = 'Agregar equipo';
            formSubtitle.textContent = 'Nuevo registro';
            formSubmitButton.textContent = 'Guardar equipo';
            syncTipoState('Computador', true);
            equipoForm.dataset.syncName = 'auto';
            document.getElementById('estado').value = 'Bueno';
            document.getElementById('nombre').value = '';
            specFields.forEach((field) => syncSpecField(field, ''));
            nuevoDestinatarioFields?.classList.add('d-none');
        };

        const fillEquipoForm = (equipo) => {
            resetEquipoForm();

            if (!equipo) {
                return;
            }

            formMethod.value = 'PUT';
            equipoForm.action = `${updateBaseUrl}/${equipo.id}`;
            formTitle.textContent = 'Editar equipo';
            formSubtitle.textContent = equipo.codigo || 'Registro existente';
            formSubmitButton.textContent = 'Actualizar equipo';
            syncTipoState(equipo.tipo || 'Computador', false);
            equipoForm.dataset.syncName = 'auto';

            formFields.forEach((fieldName) => {
                const input = equipoForm.elements[fieldName];

                if (input) {
                    input.value = equipo[fieldName] ?? '';
                }
            });

            specFields.forEach((field) => syncSpecField(field, equipo[field]));

            if (codigoInventarioInput) {
                codigoInventarioInput.value = equipo.codigo || '';
            }

            if (equipoForm.elements.nombre) {
                equipoForm.elements.nombre.value = equipo.nombre ?? equipo.asignado_a ?? '';
            }
        };

        const openEquipoForm = (equipo = null) => {
            currentEquipo = equipo;
            fillEquipoForm(equipo);
            getModal(formModalElement).show();
        };

        if (document.getElementById('newEquipoButton')) {
            document.getElementById('newEquipoButton').addEventListener('click', function () {
                openEquipoForm(null);
            });
        }

        if (tipoSelect) {
            tipoSelect.addEventListener('change', function () {
                syncTipoState(this.value, true);
                syncNombreFromAsignado();
            });
        }

        const asignadoInput = document.getElementById('asignado_a');

        if (asignadoInput) {
            asignadoInput.addEventListener('input', syncNombreFromAsignado);
        }

        document.querySelectorAll('.open-equipo-form').forEach((button) => {
            button.addEventListener('click', function () {
                openEquipoForm(JSON.parse(this.getAttribute('data-equipo')));
            });
        });

        const deleteEquipoForm = document.getElementById('deleteEquipoForm');

        document.querySelectorAll('.delete-equipo-btn').forEach((button) => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre') || 'este equipo';

                if (!confirm(`¿Seguro que deseas eliminar "${nombre}"? Esta acción no se puede deshacer y se notificará por correo a los destinatarios configurados.`)) {
                    return;
                }

                deleteEquipoForm.action = `${updateBaseUrl}/${id}`;
                deleteEquipoForm.submit();
            });
        });

        const editFromDetailButton = document.getElementById('editFromDetailButton');

        if (detailModal) {
            detailModal.addEventListener('hidden.bs.modal', function () {
                if (pendingEquipoForForm) {
                    openEquipoForm(pendingEquipoForForm);
                    pendingEquipoForForm = null;
                }
            });

            detailModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const equipo = JSON.parse(button.getAttribute('data-equipo'));

                currentEquipo = equipo;

                document.getElementById('equipoDetailModalLabel').textContent = formatValue(equipo.codigo);
                document.getElementById('detailCodigo').textContent = formatValue(equipo.codigo);
                document.getElementById('detailNombre').textContent = formatValue(equipo.nombre || equipo.asignado_a);
                document.getElementById('detailCategoriaTipo').textContent = `${formatValue(equipo.categoria)} / ${formatValue(equipo.tipo)}`;
                document.getElementById('detailEstado').textContent = formatValue(equipo.estado);
                document.getElementById('detailMarcaModelo').textContent = `${formatValue(equipo.marca)} ${formatValue(equipo.modelo)}`;
                document.getElementById('detailSerie').textContent = formatValue(equipo.serie);
                document.getElementById('detailUsuarioPc').textContent = formatValue(equipo.usuario_pc);
                document.getElementById('detailProcesador').textContent = formatValue(equipo.procesador);
                document.getElementById('detailDiscoRam').textContent = `${formatValue(equipo.disco)} / ${formatValue(equipo.ram)}`;
                document.getElementById('detailPantallaExterna').textContent = formatBoolean(equipo.pantalla_externa);
                document.getElementById('detailMonitor').textContent = [equipo.marca_monitor, equipo.modelo_monitor, equipo.serie_monitor].filter(value => value && String(value).trim() !== '').join(' / ') || '—';
                document.getElementById('detailPerifericos').textContent = `Teclado: ${formatBoolean(equipo.teclado)} | Mouse: ${formatBoolean(equipo.mouse)} | Base: ${formatBoolean(equipo.base_notebook)}`;
                document.getElementById('detailOneDrive').textContent = `Funciona: ${formatBoolean(equipo.onedrive_funcionando)} | Respaldo: ${formatBoolean(equipo.respaldo_onedrive)}`;
                document.getElementById('detailReasignado').textContent = formatValue(equipo.equipo_reasignado_a);
                document.getElementById('detailValorEquipo').textContent = formatMoney(equipo.valoracion_equipo, equipo.valoracion_equipo);
                document.getElementById('detailValorMonitor').textContent = formatMoney(equipo.valoracion_monitor, equipo.valoracion_monitor);
                document.getElementById('detailCostoActual').textContent = formatMoney(equipo.valoracion_equipo_actual_numero, equipo.valoracion_equipo_actual);
                document.getElementById('detailMantencion').textContent = formatBoolean(equipo.mantencion_realizada);
                document.getElementById('detailUbicacion').textContent = formatValue(equipo.ubicacion);
                document.getElementById('detailObservaciones').textContent = formatValue(equipo.observaciones);
            });
        }

        if (editFromDetailButton) {
            editFromDetailButton.addEventListener('click', function () {
                if (!currentEquipo) {
                    return;
                }

                pendingEquipoForForm = currentEquipo;
                getModal(detailModal).hide();
            });
        }

        if (canvas) {
            new Chart(canvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(tiposData),
                    datasets: [{
                        data: Object.values(tiposData),
                        backgroundColor: ['#1d4ed8', '#0f766e', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>