<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portail Projets') — PMO GUT</title>

    <link rel="icon" type="image/png" href="{{ asset('fav.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #f5f7fb;
            --card: #ffffff;
            --border: #e6e8ee;
            --text: #0f172a;
            --muted: #64748b;
            --blue: #0094d8;
            --blue-600: #007bb3;
            --orange: #f07d00;
            --danger: #ef4444;
            --success: #10b981;
            --radius: 16px;
            --shadow: 0 10px 30px rgba(2,8,23,.06);
            --maxw: 1100px;
        }

        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }

        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--text);
            background: linear-gradient(180deg, #f7f9ff 0%, #eef2ff 100%);
            min-height: 100vh;
        }

        /* Header */
        .site-header {
            background: linear-gradient(to right, #ffffff 0%, #cfe9f7 35%, #007bb3 100%);
            box-shadow: 0 2px 20px rgba(0, 148, 216, 0.2);
            position: relative;
            overflow: hidden;
        }

        .site-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("{{ asset('GUT.png') }}");
            background-repeat: no-repeat;
            background-position: left 20px center;
            background-size: auto 48px;
            
        }

        .site-header .wrap {
            max-width: var(--maxw);
            margin: 0 auto;
            min-height: 70px;
            padding: 16px 24px;
            display: flex;
            align-items: right;
            justify-content: flex-end;
            position: relative;
            z-index: 1;
            margin-right: 40px;
        }

        /* User Info in Header */
        .user-info {
            display: flex;
            align-items: right;
            gap: 20px;
        }

        .user-details {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-name {
            font-weight: 700;
            font-size: 15px;
            color: #ffffff;
            letter-spacing: 0.3px;
        }

        .user-meta {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            padding-left: 12px;
            border-left: 1px solid rgba(255, 255, 255, 0.3);
        }

        .user-role {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 12px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            backdrop-filter: blur(10px);
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 8px 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-logout svg {
            width: 18px;
            height: 18px;
        }

        /* Main content */
        .page {
            max-width: var(--maxw);
            margin: 24px auto;
            padding: 0 18px 40px;
        }
        
        

        /* Card */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .card-head {
            padding: 18px;
            border-bottom: 1px dashed var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .card-body {
            padding: 18px;
        }

        .title {
            margin: 0;
            font-weight: 800;
            font-size: 22px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid var(--blue);
            background: var(--blue);
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 6px 14px rgba(0,148,216,.25);
            transition: background .2s;
        }

        .btn:hover {
            background: var(--blue-600);
        }

        .btn.sec {
            background: #fff;
            color: var(--blue);
        }

        .btn.orange {
            border-color: var(--orange);
            background: var(--orange);
        }

        .btn.danger {
            border-color: var(--danger);
            background: var(--danger);
        }

        .btn.success {
            border-color: var(--success);
            background: var(--success);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Toolbar */
        .toolbar {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-warning {
            background: #fef3c7;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .alert-info {
            background: #dbeafe;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        /* Forms */
        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text);
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d9dee8;
            border-radius: 10px;
            background: #fcfdff;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            outline: none;
            transition: border .15s, box-shadow .15s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(0,148,216,.15);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        th, td {
            text-align: left;
            padding: 10px 12px;
        }

        th {
            font-size: 12px;
            letter-spacing: .5px;
            color: #4b5563;
            text-transform: uppercase;
            font-weight: 700;
        }

        tr.row {
            background: #fff;
            border: 1px solid var(--border);
        }

        tr.row td:first-child,
        tr.row th:first-child {
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        tr.row td:last-child,
        tr.row th:last-child {
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        /* Pills/Badges */
        .pill {
            display: inline-block;
            font-size: 12px;
            padding: 4px 12px;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            font-weight: 600;
        }

        .pill.green {
            border-color: #bbf7d0;
            background: #ecfdf5;
            color: #166534;
        }

        .pill.blue {
            border-color: #bfdbfe;
            background: #dbeafe;
            color: #1e40af;
        }

        .pill.yellow {
            border-color: #fde68a;
            background: #fef3c7;
            color: #92400e;
        }

        .pill.orange {
            border-color: #fed7aa;
            background: #ffedd5;
            color: #9a3412;
        }

        .pill.red {
            border-color: #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        .pill.gray {
            border-color: #e5e7eb;
            background: #f9fafb;
            color: #6b7280;
        }

        .pill.blue-outline {
            border: 1.5px solid #3b82f6;
            background: transparent;
            color: #3b82f6;
            font-weight: 600;
        }

        .pill.purple-outline {
            border: 1.5px solid #a855f7;
            background: transparent;
            color: #a855f7;
            font-weight: 600;
        }

        /* Resource Details - Inline Style */
        .resource-details-inline {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .resource-item-inline {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #f9fafb;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .resource-label-inline {
            font-size: 0.85rem;
            color: #6b7280;
            font-weight: 500;
        }

        /* Utilities */
        .text-muted {
            color: var(--muted);
        }

        .text-center {
            text-align: center;
        }

        .mt-1 { margin-top: 8px; }
        .mt-2 { margin-top: 16px; }
        .mt-3 { margin-top: 24px; }
        .mb-1 { margin-bottom: 8px; }
        .mb-2 { margin-bottom: 16px; }
        .mb-3 { margin-bottom: 24px; }

        /* Documents List - Modern Design */
        .documents-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 15px;
        }

        .document-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .document-item:hover {
            border-color: #d1d5db;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .doc-content {
            flex: 1;
            min-width: 0;
        }

        .doc-title {
            font-weight: 600;
            color: #111827;
            font-size: 0.95rem;
            margin-bottom: 6px;
        }

        .doc-details {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            font-size: 0.85rem;
            color: #6b7280;
        }

        .doc-filename {
            color: #374151;
        }

        .doc-separator {
            color: #d1d5db;
        }

        .doc-size,
        .doc-date {
            color: #9ca3af;
        }

        .btn-download-modern {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-download-modern:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.25);
        }

        .btn-download-modern:active {
            transform: translateY(0);
        }

        /* Notes Content - Modern Design */
        .notes-content {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-left: 3px solid #3b82f6;
            padding: 16px 20px;
            border-radius: 6px;
            color: #374151;
            line-height: 1.7;
            font-size: 0.95rem;
            text-align: left;
        }

        /* Existing Docs Info (edit view) - Modern Design */
        .existing-docs-info {
            background: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-left: 3px solid #3b82f6;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 16px;
        }

        .existing-docs-info strong {
            color: #1e40af;
            font-weight: 600;
        }

        .existing-docs-info ul {
            margin: 10px 0 4px 0;
            padding: 0;
            list-style: none;
        }

        .existing-docs-info li {
            color: #374151;
            margin: 6px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 12px;
            background: white;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .existing-docs-info li:hover {
            background: #f8fafc;
        }

        .existing-docs-info .doc-details {
            flex: 1;
            font-size: 0.9rem;
        }

        .existing-docs-info small {
            color: #6b7280;
            font-size: 0.85rem;
        }

        .btn-delete-doc {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            cursor: pointer;
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .btn-delete-doc:hover {
            background: #fecaca;
            border-color: #fca5a5;
            transform: translateY(-1px);
        }

        .btn-delete-doc:active {
            transform: translateY(0);
        }

        /* Footer */
        footer {
            text-align: center;
            color: var(--muted);
            font-size: 12px;
            padding: 20px;
            margin-top: 40px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .site-header .wrap {
                padding: 12px 16px;
                flex-direction: column;
                gap: 12px;
            }

            .user-info {
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }

            .user-details {
                justify-content: center;
                flex-wrap: wrap;
                gap: 8px;
            }

            .user-name {
                font-size: 14px;
            }

            .user-meta {
                font-size: 12px;
                padding-left: 10px;
                border-left: 1px solid rgba(255, 255, 255, 0.3);
            }

            .user-role {
                font-size: 10px;
                padding: 3px 10px;
            }

            .btn-logout {
                width: 100%;
                justify-content: center;
            }

            .logout-text {
                display: inline;
            }

            .page {
                padding: 0 12px 30px;
            }

            .card-head {
                flex-direction: column;
                align-items: stretch;
            }

            .toolbar {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @yield('styles')
    </style>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="wrap">
            @auth
                <div class="user-info">
                    <div class="user-details">
                        <span class="user-name">{{ Auth::user()->employee->nom ?? Auth::user()->name }}</span>
                        @if(Auth::user()->getFiliale())
                            <span class="user-meta">{{ Auth::user()->getFiliale() }}</span>
                        @endif
                        @if(Auth::user()->getRoleNames()->isNotEmpty())
                            <span class="user-role pill blue">{{ Auth::user()->getRoleNames()->first() }}</span>
                        @endif
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-logout">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span class="logout-text">Déconnexion</span>
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <!-- Main Content -->
    <main class="page">
        @if (session('success'))
            <div class="alert alert-success">
                {!! session('success') !!}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                {!! session('error') !!}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <strong>Erreur(s):</strong>
                <ul style="margin: 8px 0 0 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        © {{ date('Y') }} Groupe Univers Télécom — PMO
    </footer>

    @yield('scripts')
</body>
</html>
