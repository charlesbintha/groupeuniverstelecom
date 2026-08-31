<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer votre mot de passe — Portail Projets PMO</title>
    <link rel="icon" type="image/png" href="{{ asset('fav.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --blue: #0094d8;
            --blue-600: #007bb3;
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
            background: transparent;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            inset: -12px;
            background: url("{{ asset('background.png') }}") center / cover no-repeat;
            filter: blur(6px) brightness(.9);
            z-index: -2;
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background: linear-gradient(rgba(0,0,0,.20), rgba(0,0,0,.20));
            z-index: -1;
        }

        .card {
            width: 92%;
            max-width: 480px;
            background: rgba(255,255,255,.86);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.55);
            border-radius: 14px;
            box-shadow: var(--shadow);
            padding: 28px;
        }

        .logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            margin-bottom: 8px;
        }

        .logo {
            width: 152px;
            height: 72px;
            object-fit: contain;
        }

        h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: .2px;
            text-align: center;
        }

        .subtitle {
            margin-top: 0;
            color: #334155;
            font-weight: 600;
            font-size: 12px;
        }

        .info-box {
            margin: 16px 0;
            padding: 14px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            font-size: 14px;
            color: #1e40af;
        }

        .info-box strong {
            display: block;
            margin-bottom: 6px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-top: 14px;
            color: var(--text);
            font-size: 14px;
        }

        input[type="password"] {
            width: 100%;
            margin-top: 6px;
            padding: 12px;
            border: 1px solid #d9dee8;
            border-radius: 12px;
            background: #fcfdff;
            outline: none;
            transition: border .15s, box-shadow .15s;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
        }

        input:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(0,148,216,.15);
        }

        .hint {
            margin-top: 6px;
            font-size: 12px;
            color: var(--muted);
        }

        .btn {
            width: 100%;
            margin-top: 20px;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid var(--blue);
            background: var(--blue);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 6px 14px rgba(0,148,216,.25);
            transition: background .2s;
            font-family: 'Montserrat', sans-serif;
            font-size: 15px;
        }

        .btn:hover {
            background: var(--blue-600);
        }

        .err {
            margin-top: 10px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #7f1d1d;
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
        }

        footer {
            position: fixed;
            bottom: 12px;
            left: 0;
            right: 0;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo-wrap">
            <img class="logo" src="{{ asset('GUT.png') }}" alt="Logo GUT">
            <h1>Créer votre mot de passe</h1>
            <div class="subtitle">GUT • PMO</div>
        </div>

        <div class="info-box">
            <strong>Bienvenue {{ $user->name }}</strong>
            Vous allez créer le mot de passe pour votre compte :<br>
            <strong>{{ $user->email }}</strong>
        </div>

        @if ($errors->any())
            <div class="err">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.setup.store', $token) }}" novalidate>
            @csrf

            <label>
                Nouveau mot de passe
                <input type="password"
                       name="password"
                       required
                       autocomplete="new-password"
                       placeholder="Minimum 8 caractères">
            </label>
            <div class="hint">Au moins 8 caractères recommandés</div>

            <label>
                Confirmer le mot de passe
                <input type="password"
                       name="password_confirmation"
                       required
                       autocomplete="new-password"
                       placeholder="Confirmer le mot de passe">
            </label>

            <button class="btn" type="submit">Créer mon mot de passe</button>
        </form>
    </div>

    <footer>© {{ date('Y') }} Groupe Univers Télécom — PMO</footer>
</body>
</html>
