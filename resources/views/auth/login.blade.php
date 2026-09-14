<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - Inventario TI</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg?v=2') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <style>
        :root {
            --bg: #eef6f5;
            --bg-deep: #dff8f5;
            --surface: rgba(255, 255, 255, 0.78);
            --surface-strong: #ffffff;
            --ink: #0f172a;
            --muted: #475569;
            --brand: #0e6f6a;
            --brand-dark: #0b5651;
            --accent: #1fc8bc;
            --border: rgba(15, 23, 42, 0.08);
            --shadow: rgba(15, 23, 42, 0.15);
        }

        body.theme-dark {
            --bg: #020c18;
            --bg-deep: #0b1728;
            --surface: rgba(15, 23, 42, 0.82);
            --surface-strong: rgba(15, 23, 42, 0.95);
            --ink: #edf6ff;
            --muted: #dfeafc;
            --brand: #37d7c9;
            --brand-dark: #5eead4;
            --accent: #7dd3fc;
            --border: rgba(148, 163, 184, 0.22);
            --shadow: rgba(2, 6, 23, 0.42);
            background:
                radial-gradient(circle at top left, rgba(55, 215, 201, 0.14), transparent 28%),
                radial-gradient(circle at bottom right, rgba(125, 211, 252, 0.08), transparent 30%),
                linear-gradient(180deg, #020c18 0%, #0b1728 100%);
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            min-height: 100%;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, var(--bg) 0%, var(--bg-deep) 100%);
            color: var(--ink);
        }

        body {
            position: relative;
            display: grid;
            place-items: center;
            padding: 1.5rem;
            overflow: hidden;
            transition: background 0.35s ease, color 0.35s ease;
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
        }

        body::before {
            background: url('{{ asset('images/circuit-pattern.svg') }}') no-repeat right top / min(900px, 80vw) auto;
            opacity: 0.32;
            z-index: 0;
        }

        body::after {
            background:
                radial-gradient(circle at 18% 18%, rgba(15, 118, 110, 0.18), transparent 20%),
                radial-gradient(circle at 82% 80%, rgba(31, 200, 188, 0.12), transparent 22%);
            z-index: 0;
        }

        .login-shell {
            position: relative;
            z-index: 1;
            width: min(100%, 460px);
            display: grid;
            place-items: center;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(18px);
            opacity: 0.55;
            pointer-events: none;
        }

        .orb-one {
            width: 220px;
            height: 220px;
            background: rgba(31, 200, 188, 0.2);
            top: -60px;
            left: -50px;
        }

        .orb-two {
            width: 280px;
            height: 280px;
            background: rgba(125, 211, 252, 0.18);
            bottom: -100px;
            right: -45px;
        }

        .theme-toggle {
            position: fixed;
            top: 1.1rem;
            right: 1.1rem;
            z-index: 3;
            width: 64px;
            height: 34px;
            border: 0;
            border-radius: 999px;
            background: rgba(148, 163, 184, 0.25);
            box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.3);
            transition: all 0.3s ease;
            overflow: hidden;
            cursor: pointer;
        }

        .theme-toggle .toggle-track {
            position: relative;
            display: block;
            width: 100%;
            height: 100%;
        }

        .theme-toggle .toggle-thumb {
            position: absolute;
            top: 4px;
            left: 4px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.14);
            transition: transform 0.35s ease;
        }

        body.theme-dark .theme-toggle .toggle-thumb {
            transform: translateX(30px);
        }

        .theme-toggle .sun-icon,
        .theme-toggle .moon-icon {
            position: absolute;
            font-size: 0.8rem;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .theme-toggle .sun-icon {
            opacity: 1;
            transform: rotate(0deg);
            color: #f59e0b;
        }

        .theme-toggle .moon-icon {
            opacity: 0;
            transform: rotate(-90deg);
            color: #cbd5e1;
        }

        body.theme-dark .theme-toggle .sun-icon {
            opacity: 0;
            transform: rotate(90deg);
        }

        body.theme-dark .theme-toggle .moon-icon {
            opacity: 1;
            transform: rotate(0deg);
        }

        .login-card {
            position: relative;
            width: 100%;
            max-width: 460px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1.75rem;
            box-shadow: 0 30px 80px var(--shadow);
            backdrop-filter: blur(16px);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #0f172a, #1e293b 52%, #0f766e);
            padding: 2rem 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .login-logo {
            width: min(220px, 62vw);
            height: auto;
            filter: drop-shadow(0 10px 18px rgba(0, 0, 0, 0.24));
        }

        .login-body {
            padding: 1.75rem 2rem 2rem;
        }

        .login-title {
            font-size: clamp(1.45rem, 2vw, 1.8rem);
            font-weight: 800;
            margin: 0 0 .4rem;
            color: var(--ink);
            letter-spacing: -0.03em;
        }

        .login-subtitle {
            color: var(--muted);
            font-size: .92rem;
            margin: 0 0 1.5rem;
            line-height: 1.5;
        }

        .field-group {
            margin-bottom: 1.1rem;
        }

        .field-label {
            display: block;
            font-weight: 600;
            font-size: .82rem;
            color: var(--ink);
            margin-bottom: .45rem;
        }

        .field-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            left: .9rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            opacity: .7;
            pointer-events: none;
            color: var(--muted);
        }

        .field-input {
            width: 100%;
            padding: .8rem .9rem .8rem 2.5rem;
            border-radius: .9rem;
            border: 1px solid rgba(15, 23, 42, 0.12);
            background: rgba(248, 250, 252, 0.9);
            font-size: .95rem;
            color: var(--ink);
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }

        body.theme-dark .field-input {
            background: rgba(15, 23, 42, 0.7);
            border-color: rgba(148, 163, 184, 0.22);
            color: var(--ink);
        }

        .field-input:focus {
            outline: none;
            border-color: var(--accent);
            background: var(--surface-strong);
            box-shadow: 0 0 0 4px rgba(31, 200, 188, 0.18);
        }

        .toggle-password {
            position: absolute;
            right: .5rem;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            cursor: pointer;
            font-size: .9rem;
            padding: .35rem .5rem;
            opacity: .7;
            color: var(--muted);
        }

        .toggle-password:hover { opacity: 1; }

        .field-error {
            color: #dc2626;
            font-size: .8rem;
            margin-top: .4rem;
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .85rem;
            color: var(--muted);
        }

        .remember-label input {
            width: 1rem;
            height: 1rem;
            accent-color: var(--brand);
        }

        .forgot-link {
            font-size: .85rem;
            color: var(--brand);
            text-decoration: none;
            font-weight: 700;
        }

        .forgot-link:hover { text-decoration: underline; }

        .btn-login {
            width: 100%;
            padding: .9rem 1rem;
            border: 0;
            border-radius: .9rem;
            background: linear-gradient(135deg, var(--brand) 0%, var(--accent) 100%);
            color: #fff;
            font-weight: 800;
            font-size: .96rem;
            letter-spacing: .02em;
            cursor: pointer;
            box-shadow: 0 16px 30px rgba(14, 111, 106, 0.25);
            transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 20px 32px rgba(14, 111, 106, 0.3);
            filter: brightness(1.02);
        }

        .btn-login:active { transform: translateY(0); }

        .status-banner {
            background: rgba(24, 184, 174, 0.12);
            border: 1px solid rgba(14, 111, 106, 0.25);
            color: var(--brand-dark);
            font-size: .85rem;
            font-weight: 700;
            padding: .7rem .9rem;
            border-radius: .8rem;
            margin-bottom: 1.1rem;
        }

        .login-footer-note {
            text-align: center;
            margin-top: 1.5rem;
            font-size: .78rem;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <button type="button" class="theme-toggle" id="themeToggle" aria-label="Cambiar tema" aria-pressed="false">
        <span class="toggle-track">
            <span class="toggle-thumb">
                <span class="sun-icon"><i class="bi bi-sun-fill"></i></span>
                <span class="moon-icon"><i class="bi bi-moon-stars-fill"></i></span>
            </span>
        </span>
    </button>

    <div class="login-shell">
        <div class="orb orb-one"></div>
        <div class="orb orb-two"></div>

        <div class="login-card">
            <div class="login-header">
                <img src="{{ asset('images/pc-logo.png') }}" alt="PC Geek" class="login-logo">
            </div>

            <div class="login-body">
                <h1 class="login-title"><i class="bi bi-person-check"></i> Bienvenido de nuevo</h1>
                <p class="login-subtitle">Ingresa tus credenciales para acceder al panel de inventario de equipos.</p>

                @if (session('status'))
                    <div class="status-banner">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="field-group">
                        <label for="email" class="field-label">Correo electrónico</label>
                        <div class="field-wrap">
                            <span class="field-icon"><i class="bi bi-envelope"></i></span>
                            <input id="email" class="field-input" type="email" name="email"
                                value="{{ old('email') }}" required autofocus autocomplete="username"
                                placeholder="tucorreo@pcgeek.cl">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="field-error" />
                    </div>

                    <div class="field-group">
                        <label for="password" class="field-label">Contraseña</label>
                        <div class="field-wrap">
                            <span class="field-icon"><i class="bi bi-lock"></i></span>
                            <input id="password" class="field-input" type="password" name="password"
                                required autocomplete="current-password" placeholder="••••••••"
                                style="padding-right: 3rem;">
                            <button type="button" class="toggle-password" onclick="
                                const pw = document.getElementById('password');
                                pw.type = pw.type === 'password' ? 'text' : 'password';
                                this.querySelector('i').className = pw.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
                            "><i class="bi bi-eye"></i></button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="field-error" />
                    </div>

                    <div class="remember-row">
                        <label class="remember-label">
                            <input id="remember_me" type="checkbox" name="remember">
                            <span>Recordarme</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="forgot-link" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                        @endif
                    </div>

                    <button type="submit" class="btn-login">Iniciar sesión</button>
                </form>

                <p class="login-footer-note">Acceso exclusivo para personal autorizado de PC Geek.</p>
            </div>
        </div>
    </div>

    <script>
        const THEME_KEY = 'inventario-theme';
        const toggleButton = document.getElementById('themeToggle');

        const applyTheme = (isDark) => {
            document.body.classList.toggle('theme-dark', isDark);
            document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
            toggleButton?.setAttribute('aria-pressed', String(isDark));
        };

        const savedTheme = localStorage.getItem(THEME_KEY);
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(savedTheme ? savedTheme === 'dark' : prefersDark);

        toggleButton?.addEventListener('click', () => {
            const next = !document.body.classList.contains('theme-dark');
            localStorage.setItem(THEME_KEY, next ? 'dark' : 'light');
            applyTheme(next);
        });
    </script>
</body>
</html>
