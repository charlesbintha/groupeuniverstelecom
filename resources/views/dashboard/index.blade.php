@extends('layouts.app')

@section('title')
    @role('Admin')
        Tableau de Bord - Administration
    @elserole('Manager')
        Tableau de Bord - Manager
    @else
        Tableau de Bord
    @endrole
@endsection

@section('page-title')
    @role('Admin')
        Tableau de Bord - Administration
    @elserole('Manager')
        Tableau de Bord - Manager
    @else
        Mon Tableau de Bord
    @endrole
@endsection

@section('styles')
<style>
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .filter-section label {
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .filter-section select {
        flex: 1;
        max-width: 400px;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-section select:hover {
        border-color: var(--blue);
    }

    .filter-section select:focus {
        outline: none;
        border-color: var(--blue);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        text-align: center;
        border-top: 4px solid var(--blue);
    }

    .stat-card.success {
        border-top-color: var(--success);
    }

    .stat-card.orange {
        border-top-color: var(--orange);
    }

    .stat-card.red {
        border-top-color: #ef4444;
    }

    .stat-card.gray {
        border-top-color: #64748b;
    }

    .stat-card.purple {
        border-top-color: #a855f7;
    }

    .stat-value {
        font-size: 36px;
        font-weight: 700;
        color: #333;
        margin: 10px 0;
    }

    .stat-label {
        font-size: 14px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .charts-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .chart-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .chart-title {
        font-size: 18px;
        font-weight: 700;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f0f0f0;
    }

    .chart-container {
        position: relative;
        height: 300px;
    }

    @media (max-width: 900px) {
        .charts-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="dashboard-container">
    <div class="card" style="margin-bottom: 30px;">
        <div class="card-head">
            <h2 class="title">Tableau de Bord</h2>
            <div class="toolbar">
                <a class="btn sec" href="{{ route('projects.index') }}">📁 Projets</a>
                @can('users.view')
                    <a class="btn sec" href="{{ route('admin.users.index') }}">👥 Utilisateurs</a>
                @endcan
                @role('Admin')
                    <a class="btn sec" href="{{ route('admin.activity.index') }}">📋 Journal d’activité</a>
                @endrole
            </div>
        </div>
    </div>

    @role('Admin')
        <div class="filter-section">
            <label>Filtrer par filiale:</label>
            <select id="filialeFilter" onchange="filterByFiliale()">
                <option value="">-- Toutes les filiales --</option>
                @foreach($filiales as $filiale)
                    <option value="{{ $filiale }}" {{ $filialeFilter == $filiale ? 'selected' : '' }}>
                        {{ $filiale }}
                    </option>
                @endforeach
            </select>
        </div>
    @endrole

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Projets</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>

        <div class="stat-card orange">
            <div class="stat-label">Projets en cours</div>
            <div class="stat-value">{{ $stats['en_cours'] }}</div>
        </div>

        <div class="stat-card success">
            <div class="stat-label">Projets Terminés</div>
            <div class="stat-value">{{ $stats['completed'] }}</div>
        </div>

        <div class="stat-card orange">
            <div class="stat-label">Projets non démarrés</div>
            <div class="stat-value">{{ $stats['non_demarrer'] }}</div>
        </div>

        <div class="stat-card gray">
            <div class="stat-label">Projets suspendus</div>
            <div class="stat-value">{{ $stats['suspendu'] }}</div>
        </div>

        <div class="stat-card purple">
            <div class="stat-label">Projets mis en pause</div>
            <div class="stat-value">{{ $stats['mis_en_pause'] }}</div>
        </div>

        <div class="stat-card red">
            <div class="stat-label">Projets en retard</div>
            <div class="stat-value">{{ $stats['retard'] }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Budget Total</div>
            <div class="stat-value" style="font-size: 24px;">{{ number_format($stats['budget_total'], 0, ',', ' ') }} FCFA</div>
        </div>

        <div class="stat-card orange">
            <div class="stat-label">Budget en cours</div>
            <div class="stat-value" style="font-size: 24px;">{{ number_format($stats['budget_en_cours'], 0, ',', ' ') }} FCFA</div>
        </div>

        <div class="stat-card success">
            <div class="stat-label">Montant Encaissement</div>
            <div class="stat-value" style="font-size: 24px;">{{ number_format($stats['me_total'], 0, ',', ' ') }} FCFA</div>
        </div>

        <div class="stat-card orange">
            <div class="stat-label">Montant Décaissement</div>
             <div class="stat-value" style="font-size: 24px;"> {{ number_format($stats['md_total'], 0, ',', ' ') }} FCFA</div>
        </div>

        <div class="stat-card purple">
            <div class="stat-label">Montant Recouvrement</div>
            <div class="stat-value" style="font-size: 24px;">{{ number_format($stats['montant_recouvrement_total'], 0, ',', ' ') }} FCFA</div>
        </div>

        <div class="stat-card success">
            <div class="stat-label">Montant recouvré</div>
            <div class="stat-value" style="font-size: 24px;">{{ number_format($stats['montant_recouvre_total'], 0, ',', ' ') }} FCFA</div>
        </div>
    </div>

    <div class="charts-row">
        <div class="chart-card">
            <h3 class="chart-title">📊 Projets par Statut</h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3 class="chart-title">📈 Projets par Type</h3>
            <div class="chart-container">
                <canvas id="typeChart"></canvas>
            </div>
        </div>
    </div>

    <div class="charts-row">
        <div class="chart-card">
            <h3 class="chart-title">🎯 Projets par Nature</h3>
            <div class="chart-container">
                <canvas id="natureChart"></canvas>
            </div>
        </div>

        @role('Admin')
            @if($budgetByFiliale)
                <div class="chart-card">
                    <h3 class="chart-title">💰 Budget par Filiale</h3>
                    <div class="chart-container">
                        <canvas id="budgetChart"></canvas>
                    </div>
                </div>
            @endif
        @elserole('Manager')
            @if($projectsByOwner)
                <div class="chart-card">
                    <h3 class="chart-title">👤 Projets par Chef de Projet</h3>
                    <div class="chart-container">
                        <canvas id="ownerChart"></canvas>
                    </div>
                </div>
            @endif
        @endrole
    </div>

    <div class="charts-row">
        <div class="chart-card">
            <h3 class="chart-title">📈 Évolution Temporelle (12 derniers mois)</h3>
            <div class="chart-container">
                <canvas id="evolutionChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h3 class="chart-title">⚡ Vélocité Mensuelle (6 derniers mois)</h3>
            <div class="chart-container">
                <canvas id="velocityChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
    const chartColors = {
        blue: '#3b82f6',
        green: '#10b981',
        yellow: '#fbbf24',
        orange: '#f97316',
        purple: '#a855f7',
        pink: '#ec4899',
        indigo: '#6366f1',
        gray: '#9ca3af',
        red: '#ef4444'
    };

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: {
                        size: 12,
                        family: 'Montserrat'
                    }
                }
            },
            datalabels: {
                color: '#fff',
                font: {
                    weight: 'bold',
                    size: 14
                },
                formatter: (value, context) => {
                    if (value === 0) return '';
                    return value;
                }
            }
        }
    };

    const statusData = {
        labels: ['Planifié', 'En cours', 'Pause', 'Suspendu', 'Mis en pause', 'Retard', 'Terminé'],
        datasets: [{
            data: [
                {{ $byStatus['Planifié'] ?? 0 }},
                {{ $byStatus['En cours'] ?? 0 }},
                {{ $byStatus['Pause'] ?? 0 }},
                {{ $byStatus['Suspendu'] ?? 0 }},
                {{ $byStatus['Mis en pause'] ?? 0 }},
                {{ $byStatus['Retard'] ?? 0 }},
                {{ $byStatus['Terminé'] ?? 0 }}
            ],
            backgroundColor: [
                chartColors.yellow,
                chartColors.blue,
                chartColors.orange,
                chartColors.gray,
                chartColors.purple,
                chartColors.red,
                chartColors.green
            ],
            borderWidth: 0
        }]
    };

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: statusData,
        options: {
            ...chartOptions,
            cutout: '60%'
        }
    });

    const typeData = {
        labels: ['Interne', 'Externe'],
        datasets: [{
            label: 'Nombre de projets',
            data: [
                {{ $byType['Interne'] ?? 0 }},
                {{ $byType['Externe'] ?? 0 }}
            ],
            backgroundColor: [chartColors.blue, chartColors.green],
            borderWidth: 0,
            borderRadius: 8
        }]
    };

    new Chart(document.getElementById('typeChart'), {
        type: 'bar',
        data: typeData,
        options: {
            ...chartOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    const natureData = {
        labels: ['B2B', 'B2C', 'GOV', 'Autres'],
        datasets: [{
            data: [
                {{ $byNature['B2B'] ?? 0 }},
                {{ $byNature['B2C'] ?? 0 }},
                {{ $byNature['GOV'] ?? 0 }},
                {{ $byNature['Autres'] ?? 0 }}
            ],
            backgroundColor: [
                chartColors.purple,
                chartColors.pink,
                chartColors.indigo,
                chartColors.gray
            ],
            borderWidth: 0
        }]
    };

    new Chart(document.getElementById('natureChart'), {
        type: 'pie',
        data: natureData,
        options: chartOptions
    });

    @role('Admin')
        @if($budgetByFiliale)
        const budgetData = {
            labels: {!! json_encode($budgetByFiliale->pluck('filiale_executant')->map(function($filiale) {
                return strlen($filiale) > 25 ? substr($filiale, 0, 25) . '...' : $filiale;
            })) !!},
            datasets: [{
                label: 'Budget (FCFA)',
                data: {!! json_encode($budgetByFiliale->pluck('budget')) !!},
                backgroundColor: chartColors.blue,
                borderWidth: 0,
                borderRadius: 6
            }]
        };

        new Chart(document.getElementById('budgetChart'), {
            type: 'bar',
            data: budgetData,
            options: {
                ...chartOptions,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                            }
                        }
                    }
                },
                plugins: {
                    ...chartOptions.plugins,
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('fr-FR').format(context.parsed.x) + ' FCFA';
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#333',
                        formatter: (value) => {
                            if (value === 0) return '';
                            return new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(value);
                        }
                    }
                }
            }
        });
        @endif
    @endrole

    @role('Manager')
        @if($projectsByOwner)
        const ownerData = {
            labels: {!! json_encode($projectsByOwner->pluck('owner_executant')->map(function($owner) {
                return strlen($owner) > 25 ? substr($owner, 0, 25) . '...' : $owner;
            })) !!},
            datasets: [{
                label: 'Nombre de projets',
                data: {!! json_encode($projectsByOwner->pluck('count')) !!},
                backgroundColor: chartColors.green,
                borderWidth: 0,
                borderRadius: 6
            }]
        };

        new Chart(document.getElementById('ownerChart'), {
            type: 'bar',
            data: ownerData,
            options: {
                ...chartOptions,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        @endif
    @endrole

    const evolutionData = {
        labels: {!! json_encode(array_column($monthlyEvolution, 'month')) !!},
        datasets: [{
            label: 'Projets créés',
            data: {!! json_encode(array_column($monthlyEvolution, 'count')) !!},
            borderColor: chartColors.blue,
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true,
            borderWidth: 3
        }]
    };

    new Chart(document.getElementById('evolutionChart'), {
        type: 'line',
        data: evolutionData,
        options: {
            ...chartOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                ...chartOptions.plugins,
                datalabels: {
                    display: false
                }
            }
        }
    });

    const velocityData = {
        labels: {!! json_encode(array_column($velocity, 'month')) !!},
        datasets: [
            {
                label: 'Créés',
                data: {!! json_encode(array_column($velocity, 'created')) !!},
                backgroundColor: chartColors.blue,
                borderWidth: 0,
                borderRadius: 6
            },
            {
                label: 'Terminés',
                data: {!! json_encode(array_column($velocity, 'completed')) !!},
                backgroundColor: chartColors.green,
                borderWidth: 0,
                borderRadius: 6
            }
        ]
    };

    new Chart(document.getElementById('velocityChart'), {
        type: 'bar',
        data: velocityData,
        options: {
            ...chartOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    @if(auth()->user()->isAdmin())
        function filterByFiliale() {
            const select = document.getElementById('filialeFilter');
            const filiale = select.value;
            const url = new URL(window.location.href);

            if (filiale) {
                url.searchParams.set('filiale', filiale);
            } else {
                url.searchParams.delete('filiale');
            }

            window.location.href = url.toString();
        }
    @endif
</script>
@endsection
