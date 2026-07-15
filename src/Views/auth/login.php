<?php
$mode = $mode ?? 'login';
$error = $error ?? null;
$success = $success ?? null;
$token = $token ?? null;
$tokenValid = $tokenValid ?? false;
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $mode === 'forgot' ? 'Recuperar contrasena' : ($mode === 'reset' ? 'Crear nueva contrasena' : 'Iniciar sesion') ?> - OfficeHub</title>
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <style>
        :root {
            --bg-base: #0d1117;
            --bg-surface: #161b22;
            --bg-input: #0d1117;
            --border: #21262d;
            --border-2: #30363d;
            --text-1: #e6edf3;
            --text-2: #8b949e;
            --text-3: #484f58;
            --accent: #58a6ff;
            --accent-bg: #1f6feb;
            --accent-brd: #388bfd;
            --red: #f85149;
            --red-bg: #3a1a1a;
            --green: #0f110f;
            --green-bg: #1a3a1a;
            --nav-bg: #1c2128;
            --nav-border: #2a2f35;
            --star-bg-start: #1b2735;
            --star-bg-end: #090a0f;
            --star-color-1: rgba(255, 255, 255, 0.9);
            --star-color-2: rgba(155, 201, 255, 0.72);
            --star-color-3: rgba(88, 166, 255, 0.56);
            --star-glow: rgba(88, 166, 255, 0.18);
            --star-haze: rgba(0, 255, 170, 0.045);
        }

        [data-theme="light"] {
            --bg-base: #f8fafc;
            --bg-surface: #ffffff;
            --bg-input: #ffffff;
            --border: #d0d7de;
            --border-2: #d0d7de;
            --text-1: #1f2328;
            --text-2: #636c76;
            --text-3: #9198a1;
            --accent: #0969da;
            --accent-bg: #0969da;
            --accent-brd: #0969da;
            --red: #d1242f;
            --red-bg: #ffebe9;
            --green: #1a7f37;
            --green-bg: #dafbe1;
            --nav-bg: #e2e8f0;
            --nav-border: #cbd5e1;
            --star-bg-start: #cfe5ff;
            --star-bg-end: #f1f8ff;
            --star-color-1: rgba(9, 105, 218, 0.58);
            --star-color-2: rgba(31, 111, 235, 0.42);
            --star-color-3: rgba(88, 166, 255, 0.36);
            --star-glow: rgba(9, 105, 218, 0.22);
            --star-haze: rgba(88, 166, 255, 0.16);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--bg-base);
            color: var(--text-1);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: background-color 0.2s, color 0.2s;
            overflow-x: hidden;
        }

        /* Fondo estrellado global */
        .bg-grid {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
            background:
                radial-gradient(circle at 50% 115%, var(--star-glow) 0%, transparent 38%),
                radial-gradient(circle at 80% 15%, var(--star-haze) 0%, transparent 30%),
                radial-gradient(ellipse at bottom, var(--star-bg-start) 0%, var(--star-bg-end) 72%);
            background-size: cover, cover, cover;
        }

        .bg-grid::before,
        .bg-grid::after {
            content: "";
            position: absolute;
            inset: 0;
            opacity: 0.9;
            will-change: background-position;
        }

        .bg-grid::before {
            background-image:
                radial-gradient(circle, var(--star-color-1) 0 1px, transparent 1.5px),
                radial-gradient(circle, var(--star-color-2) 0 1px, transparent 1.5px),
                radial-gradient(circle, var(--star-color-3) 0 1px, transparent 1.5px);
            background-size: 140px 140px, 210px 210px, 310px 310px;
            background-position: 16px 22px, 90px 125px, 180px 48px;
            animation: starDriftFine 28s linear infinite;
        }

        .bg-grid::after {
            background-image:
                radial-gradient(circle, var(--star-color-1) 0 1.4px, transparent 2px),
                radial-gradient(circle, var(--star-color-2) 0 1.8px, transparent 2.6px);
            background-size: 260px 260px, 420px 420px;
            background-position: 44px 72px, 190px 12px;
            filter: drop-shadow(0 0 5px var(--star-glow));
            opacity: 0.72;
            animation: starDriftBright 48s linear infinite;
        }

        @keyframes starDriftFine {
            from {
                background-position: 16px 22px, 90px 125px, 180px 48px;
            }

            to {
                background-position: 16px -118px, 90px -85px, 180px -262px;
            }
        }

        @keyframes starDriftBright {
            from {
                background-position: 44px 72px, 190px 12px;
            }

            to {
                background-position: 44px -188px, 190px -408px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .bg-grid::before,
            .bg-grid::after {
                animation: none;
            }
        }

        /* Navbar minimo */
        .navbar-wrap {
            padding: 12px 24px;
            display: flex;
            justify-content: center;
            position: relative;
            z-index: 10;
        }

        .navbar {
            background: var(--nav-bg);
            border: 1px solid var(--nav-border);
            border-radius: 40px;
            padding: 5px 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .nav-logo-icon {
            width: 26px;
            height: 26px;
            background: var(--bg-surface);
            border: 1px solid var(--border-2);
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-logo-text {
            color: var(--text-1);
            font-size: 14px;
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        .nav-theme {
            background: var(--bg-surface);
            border: 1px solid var(--nav-border);
            color: var(--text-2);
            font-size: 13px;
            padding: 4px 10px;
            border-radius: 20px;
            cursor: pointer;
            transition: color 0.15s;
        }

        .nav-theme:hover {
            color: var(--text-1);
        }

        /* Contenido centrado */
        .login-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            z-index: 1;
        }

        .login-box {
            width: 100%;
            max-width: 340px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .login-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .login-title-dot {
            width: 10px;
            height: 10px;
            background: var(--accent);
            border-radius: 50%;
        }

        .login-title-text {
            color: var(--text-1);
            font-size: 22px;
            font-weight: 600;
        }

        .login-subtitle {
            color: var(--text-2);
            font-size: 13px;
        }

        .login-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 24px;
        }

        .flash-error {
            background: var(--red-bg);
            border: 1px solid var(--red);
            color: var(--red);
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 13px;
        }

        .flash-success {
            background: var(--green-bg);
            border: 1px solid var(--green);
            color: var(--green);
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 13px;
            line-height: 1.45;
        }

        .field {
            margin-bottom: 14px;
        }

        .field label {
            display: block;
            font-size: 13px;
            color: var(--text-1);
            font-weight: 500;
            margin-bottom: 6px;
        }

        .field input {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border-2);
            color: var(--text-1);
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13px;
            outline: none;
            transition: border-color 0.15s;
        }

        .field input:focus {
            border-color: var(--accent-brd);
        }

        .password-field {
            position: relative;
        }

        .password-field input {
            padding-right: 42px;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            width: 28px;
            height: 28px;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 0;
            color: var(--text-2);
            cursor: pointer;
        }

        .password-toggle:hover {
            color: var(--text-1);
        }

        .auth-help {
            color: var(--text-2);
            font-size: 13px;
            line-height: 1.55;
            margin-bottom: 18px;
        }

        .auth-links {
            display: flex;
            justify-content: center;
            margin-top: 16px;
        }

        .auth-link {
            color: var(--accent);
            font-size: 13px;
            text-decoration: none;
        }

        .auth-link:hover {
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            background: var(--accent-bg);
            border: 1px solid var(--accent-brd);
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            padding: 9px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 6px;
        }

        .login-note {
            text-align: center;
            font-size: 12px;
            color: var(--text-3);
            margin-top: 16px;
        }
    </style>
    <script>
        (function() {
            const saved = localStorage.getItem('hh_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', saved);
        })();
    </script>
</head>

<body>

    <div class="bg-grid"></div>

    <!-- Navbar minimo -->
    <div class="navbar-wrap">
        <nav class="navbar">
            <a href="<?= base('login') ?>" class="nav-logo">
                <div class="nav-logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                    </svg>
                </div>
                <span class="nav-logo-text">OfficeHub</span>
            </a>
            <button class="nav-theme" onclick="toggleTheme()" id="theme-btn" title="Cambiar tema">
                <span id="theme-icon">&#9728;</span>
            </button>
        </nav>
    </div>

    <!-- Login centrado -->
    <div class="login-wrap">
        <div class="login-box">

            <div class="login-header">
                <div class="login-title">
                    <span class="login-title-dot"></span>
                    <span class="login-title-text">OfficeHub</span>
                </div>
                <p class="login-subtitle">
                    <?php if ($mode === 'forgot'): ?>
                        Te enviaremos un enlace seguro por correo
                    <?php elseif ($mode === 'reset'): ?>
                        Elegi una contrasena nueva para tu cuenta
                    <?php else: ?>
                        Plataforma interna de repositorios
                    <?php endif; ?>
                </p>
            </div>

            <div class="login-card">
                <?php if ($error ?? null): ?>
                    <div class="flash-error"><?= e($error) ?></div>
                <?php endif; ?>

                <?php if ($success ?? null): ?>
                    <div class="flash-success"><?= e($success) ?></div>
                <?php endif; ?>

                <?php if ($mode === 'forgot'): ?>
                    <p class="auth-help">
                        Ingresa tu correo institucional asociado a tu usuario. Si la cuenta esta activa, recibiras un enlace valido durante 30 minutos.
                    </p>
                    <form method="POST" action="<?= base('olvide-mi-contrasena') ?>">
                        <?= csrf_field() ?>
                        <div class="field">
                            <label for="reset-email">Correo electronico</label>
                            <input id="reset-email" type="email" name="email" required autocomplete="email" placeholder="tu.usuario@example.test">
                        </div>
                        <button type="submit" class="btn-submit">Enviar enlace</button>
                    </form>
                    <div class="auth-links">
                        <a class="auth-link" href="<?= base('login') ?>">Volver al inicio de sesion</a>
                    </div>
                <?php elseif ($mode === 'reset'): ?>
                    <?php if ($tokenValid ?? false): ?>
                        <form method="POST" action="<?= base('restablecer-contrasena') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
                            <div class="field">
                                <label for="new-password">Nueva contrasena</label>
                                <div class="password-field">
                                    <input id="new-password" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Minimo 8 caracteres">
                                    <button type="button" class="password-toggle" data-password-toggle="new-password" aria-label="Mostrar contrasena" title="Mostrar contrasena">
                                        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="field">
                                <label for="password-confirmation">Repetir contrasena</label>
                                <div class="password-field">
                                    <input id="password-confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Repeti la contrasena">
                                    <button type="button" class="password-toggle" data-password-toggle="password-confirmation" aria-label="Mostrar contrasena" title="Mostrar contrasena">
                                        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn-submit">Guardar nueva contrasena</button>
                        </form>
                    <?php else: ?>
                        <?php if (!($error ?? null)): ?>
                            <div class="flash-error">El enlace es invalido, ya fue utilizado o vencio.</div>
                        <?php endif; ?>
                        <div class="auth-links">
                            <a class="auth-link" href="<?= base('olvide-mi-contrasena') ?>">Solicitar un enlace nuevo</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <form method="POST" action="<?= base('login') ?>">
                        <?= csrf_field() ?>
                        <div class="field">
                            <label for="login-username">Usuario</label>
                            <input id="login-username" type="text" name="username" required autocomplete="username" placeholder="tu.usuario">
                        </div>
                        <div class="field">
                            <label for="login-password">Contrasena</label>
                            <div class="password-field">
                                <input id="login-password" type="password" name="password" required autocomplete="current-password" placeholder="********">
                                <button type="button" class="password-toggle" data-password-toggle="login-password" aria-label="Mostrar contrasena" title="Mostrar contrasena">
                                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn-submit">Entrar</button>
                    </form>
                    <div class="auth-links">
                        <a class="auth-link" href="<?= base('olvide-mi-contrasena') ?>">Olvidaste tu contrasena?</a>
                    </div>
                <?php endif; ?>
            </div>

            <p class="login-note">Solo para uso interno del Dpto. de Sistemas</p>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('hh_theme', next);
            document.getElementById('theme-icon').innerHTML = next === 'dark' ? '&#9728;' : '&#9790;';
        }
        document.addEventListener('DOMContentLoaded', () => {
            const theme = localStorage.getItem('hh_theme') || 'dark';
            document.getElementById('theme-icon').innerHTML = theme === 'dark' ? '&#9728;' : '&#9790;';

            document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.dataset.passwordToggle);
                    const showPassword = input.type === 'password';

                    input.type = showPassword ? 'text' : 'password';
                    button.setAttribute('aria-label', showPassword ? 'Ocultar contrasena' : 'Mostrar contrasena');
                    button.setAttribute('title', showPassword ? 'Ocultar contrasena' : 'Mostrar contrasena');
                });
            });
        });
    </script>

</body>

</html>