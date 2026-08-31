<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Accès refusé — Portail PMO GUT</title>
    <link rel="icon" type="image/png" href="{{ asset('fav.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --blue: #0094d8;
            --blue-600: #007bb3;
            --orange: #f07d00;
            --text: #0f172a;
            --muted: #64748b;
            --shadow: 0 12px 32px rgba(2,8,23,.08);
        }

        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }

        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #f7f9ff 0%, #eef2ff 100%);
            position: relative;
        }

        .container {
            text-align: center;
            max-width: 600px;
            padding: 40px 20px;
        }

        .error-code {
            font-size: 120px;
            font-weight: 800;
            color: var(--orange);
            line-height: 1;
            margin: 0;
        }

        h1 {
            font-size: 32px;
            font-weight: 700;
            margin: 20px 0;
            color: var(--text);
        }

        p {
            font-size: 18px;
            color: var(--muted);
            margin: 20px 0;
            line-height: 1.6;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 10px;
            border: 1px solid var(--blue);
            background: var(--blue);
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 6px 14px rgba(0,148,216,.25);
            transition: background .2s;
            margin: 10px;
        }

        .btn:hover {
            background: var(--blue-600);
        }

        .btn.sec {
            background: #fff;
            color: var(--blue);
            box-shadow: none;
        }

        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <div class="error-code">403</div>
        <h1>Accès refusé</h1>
        <p>
            Vous n'avez pas les permissions nécessaires pour accéder à cette page.
        </p>
        <p>
            Cette section est réservée aux administrateurs du système.
        </p>
        <div style="margin-top: 40px;">
            <a href="{{ route('projects.index') }}" class="btn">← Retour à la liste des projets</a>
            @if(!auth()->check())
                <a href="{{ route('login') }}" class="btn sec">Se connecter</a>
            @else
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn sec">Déconnexion</button>
                </form>
            @endif
        </div>
    </div>

    <footer>
        © {{ date('Y') }} Groupe Univers Télécom — PMO
    </footer>
</body>
</html>
