<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — Portail Projets PMO</title>
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

        /* Full screen blurred background */
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
            max-width: 420px;
            background: rgba(255,255,255,.86);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.55);
            border-radius: 14px;
            box-shadow: var(--shadow);
            padding: 22px;
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
        }

        .subtitle {
            margin-top: 0;
            color: #334155;
            font-weight: 600;
            font-size: 12px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-top: 12px;
            color: var(--text);
        }

        input[type="email"],
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
        }

        input:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(0,148,216,.15);
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            color: #374151;
            font-size: 14px;
        }

        .btn {
            width: 100%;
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid var(--blue);
            background: var(--blue);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 6px 14px rgba(0,148,216,.25);
            transition: background .2s;
            font-family: 'Montserrat', sans-serif;
        }

        .btn:hover {
            background: var(--blue-600);
        }

        .err {
            margin-top: 10px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #7f1d1d;
            padding: 10px;
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
            <h1>Portail Projets — Connexion</h1>
            <div class="subtitle">GUT • PMO</div>
        </div>

        @if (session('success'))
            <div style="margin-top: 10px; background: #ecfdf5; border: 1px solid #bbf7d0; color: #166534; padding: 12px; border-radius: 10px; font-size: 14px;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="err">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <label>
                Email
                <input type="email"
                       name="email"
                       required
                       autocomplete="username"
                       value="{{ old('email', $rememberEmail ?? '') }}"
                       placeholder="votre@email">
            </label>

            <label>
                Mot de passe
                <input type="password"
                       name="password"
                       required
                       autocomplete="current-password"
                       placeholder="••••••••">
            </label>

            <label class="remember">
                <input type="checkbox"
                       name="remember_me"
                       {{ ($rememberChecked ?? false) ? 'checked' : '' }}>
                Se rappeler de moi
            </label>

            <button class="btn" type="submit">Se connecter</button>
        </form>
    </div>

    <footer>© {{ date('Y') }} Groupe Univers Télécom — PMO</footer>
</body>
</html>
