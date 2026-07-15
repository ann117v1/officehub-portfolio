<!DOCTYPE html>
<html lang="es" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — OfficeHub</title>
    <style>
        :root {
            --bg-base: #0d1117;
            --text-1: #e6edf3;
            --text-2: #8b949e;
            --accent: #58a6ff;
        }

        [data-theme="light"] {
            --bg-base: #ffffff;
            --text-1: #1f2328;
            --text-2: #636c76;
            --accent: #0969da;
        }

        body {
            background: var(--bg-base);
            color: var(--text-1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
        }
    </style>
    <script>
        (function() {
            const t = localStorage.getItem('hh_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>

<body>
    <div style="text-align:center;">
        <div style="font-size:72px;font-weight:700;color:var(--text-2);line-height:1;">404</div>
        <p style="color:var(--text-2);font-size:15px;margin-top:12px;">Página no encontrada.</p>
        <a href="<?= (defined('APP_BASE') ? APP_BASE : '') . '/' ?>"
            style="display:inline-block;margin-top:20px;color:var(--accent);font-size:13px;text-decoration:none;">
            ← Volver al inicio
        </a>
    </div>
</body>

</html>