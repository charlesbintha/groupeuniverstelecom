<?php
// index.php
require __DIR__ . '/lib_graph.php';

$authData = verify_laravel_token();
if (!$authData) {
    http_response_code(403);
    echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès refusé</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f5f7fb; color: #0f172a; }
        .error-box { text-align: center; padding: 2rem; background: white; border-radius: 16px; box-shadow: 0 10px 30px rgba(2,8,23,.06); max-width: 500px; }
        h1 { color: #ef4444; margin: 0 0 1rem; }
        p { margin: 0 0 1.5rem; color: #64748b; }
        a { display: inline-block; padding: 12px 24px; background: #0094d8; color: white; text-decoration: none; border-radius: 8px; font-weight: 500; }
        a:hover { background: #007bb3; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>🔒 Accès refusé</h1>
        <p>Vous devez accéder au rapport via l\'application principale.</p>
        <a href="/projects">Retour aux projets</a>
    </div>
</body>
</html>';
    exit;
}

$pdo = db();
// Tu peux filtrer/ordonner ici selon tes besoins
$q = $pdo->query("SELECT id, nom_projet, code_projet, ms_group_id, ms_plan_id, ms_bucket_id
                  FROM projects
                  WHERE ms_plan_id IS NOT NULL AND ms_plan_id <> ''");
$rows = $q->fetchAll(PDO::FETCH_ASSOC);

$cards = [];
$now = new DateTimeImmutable();

foreach ($rows as $r) {
  $pid = (int)$r['id'];
  $proj = $r['nom_projet'] ?: ($r['code_projet'] ?: "Projet #$pid");
  $planId = trim($r['ms_plan_id'] ?? '');
  $bucketId = trim($r['ms_bucket_id'] ?? '');
  if (!$planId) continue;

  $tasksUrl = $bucketId
    ? "https://graph.microsoft.com/v1.0/planner/buckets/$bucketId/tasks"
    : "https://graph.microsoftonline.com/v1.0/planner/plans/$planId/tasks"; // typo volontaire ? non corrige :
  $tasksUrl = $bucketId
    ? "https://graph.microsoft.com/v1.0/planner/buckets/$bucketId/tasks"
    : "https://graph.microsoft.com/v1.0/planner/plans/$planId/tasks";

  $data = gget($tasksUrl);
  $tasks = $data['value'] ?? [];

  $total = count($tasks);
  $done  = 0; $over = 0;
  foreach ($tasks as $t) {
    $pct = (int)($t['percentComplete'] ?? 0);
    if ($pct >= 100) $done++;
    $due = isset($t['dueDateTime']) ? new DateTimeImmutable($t['dueDateTime']) : null;
    if ($due && $pct < 100 && $due < $now) $over++;
  }

  $plan = plan_meta($planId);
  $bucket = $bucketId ? bucket_meta($bucketId) : [];

  $cards[] = [
    'projectId' => $pid,
    'project' => $proj,
    'projectCode' => $r['code_projet'] ?? '',
    'planId' => $planId,
    'planTitle' => $plan['title'] ?? $planId,
    'bucketId' => $bucketId ?: '',
    'bucketName' => $bucket['name'] ?? ($bucketId ?: 'â€”'),
    'total' => $total,
    'done' => $done,
    'over' => $over,
  ];
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Planner aperçu</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root { --text:#0f172a; --muted:#64748b; --card:#ffffff; --line:#e2e8f0; --ok:#16a34a; --bad:#dc2626; --accent:#2563eb; --bg:#f8fafc; }
    * { box-sizing: border-box; }
    body { margin:0; padding:28px; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial; color:var(--text); background:var(--bg); }
    h1 { margin:0 0 6px; font-size:24px; letter-spacing:.2px; }
    .subtle { color: var(--muted); }

    .grid { display:grid; grid-template-columns: repeat(auto-fill,minmax(300px,1fr)); gap:18px; margin-top:18px; align-items: stretch; }
    .card-link { display:block; height:100%; color:inherit; text-decoration:none; }
    .card { display:flex; flex-direction:column; height:100%; min-height: 190px; background: var(--card); border:1px solid var(--line); border-radius:16px; padding:16px; box-shadow: 0 6px 18px rgba(2,6,23,.06); transition: transform .15s ease, box-shadow .15s ease, border-color .2s ease; }
    .card:hover, .card-link:focus .card { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(2,6,23,.12); border-color:#cbd5e1; }
    .title { font-weight:700; margin-bottom:6px; font-size:16px; }
    .row { display:flex; gap:12px; justify-content: space-between; align-items: center; }
    .badge { display:inline-block; padding:2px 10px; border-radius:999px; background:#f1f5f9; font-size:12px; color:#334155; border:1px solid #e2e8f0; }
    .muted { color: var(--muted); font-size:12px; }
    .footer { margin-top:auto; }
    .kpis { display:flex; gap:8px; margin:10px 0; flex-wrap:wrap; }
    .k { padding:6px 10px; border-radius:10px; background:#f8fafc; border:1px solid var(--line); font-size:12px; }
    .k.ok { border-color:#bbf7d0; color:var(--ok); }
    .k.bad{ border-color:#fecaca; color:var(--bad); }
    .progress { height:8px; background:#eef2f7; border-radius:999px; overflow:hidden; border:1px solid #e2e8f0; }
    .bar { height:100%; background: linear-gradient(90deg,#22c55e,#16a34a); }
    .bar.bad { background: linear-gradient(90deg,#f97316,#dc2626); }

    /* Route progress bar (top) */
        #route-progress { position:fixed; top:0; left:0; height:3px; width:0; opacity:1; background: linear-gradient(90deg, #2563eb, #22c55e); box-shadow: 0 0 10px rgba(37,99,235,.35); transition: width .18s ease; z-index: 10000; pointer-events:none; }
    @media (prefers-reduced-motion: reduce) { #route-progress { transition: none; } }  </style>
  </head>
<body>
  <div id='route-progress' aria-hidden='true'></div>
  <h1>Planner Aperçu</h1>
  <div class="subtle">Cliquez sur une carte pour ouvrir le détail.</div>

  <div class="grid">
    <?php foreach ($cards as $c): ?>
      <?php $pctDone = ($c['total'] ?? 0) ? (int)round($c['done'] * 100 / $c['total']) : 0; $hasOver = ($c['over'] ?? 0) > 0; ?>
      <a class="card-link" href="detail.php?pid=<?=urlencode((string)$c['projectId'])?>&planId=<?=urlencode($c['planId'])?>&bucketId=<?=urlencode($c['bucketId'])?>">
        <article class="card">
          <div>
            <div class="title"><?=htmlspecialchars($c['project'])?></div>
            <?php if (!empty($c['projectCode'])): ?>
              <div class="muted">Code projet : <span class="badge"><?=htmlspecialchars($c['projectCode'])?></span></div>
            <?php endif; ?>
            <div class="row" style="margin-top:10px;">
              <div>
                <div class="muted">Bucket</div>
                <div class="badge"><?=htmlspecialchars($c['bucketName'])?></div>
              </div>
            </div>
          </div>

          <div class="footer">
            <div class="kpis">
              <span class="k">Total: <b><?=$c['total']?></b></span>
              <span class="k ok">Terminées: <b><?=$c['done']?></b></span>
              <span class="k <?=$hasOver ? 'bad' : ''?>">En retard: <b><?=$c['over']?></b></span>
            </div>
            <div class="progress" aria-hidden="true">
              <div class="bar <?=$hasOver ? 'bad' : ''?>" style="width: <?=$pctDone?>%"></div>
            </div>
            <div class="muted" style="margin-top:6px;">Avancement: <b><?=$pctDone?>%</b></div>
          </div>
        </article>
      </a>
    <?php endforeach; ?>
  </div>
  <script>
    (function () {
      var bar = document.getElementById('route-progress');
      var timer = null, width = 0;
      function start() {
        if (!bar) return;
        clearInterval(timer);
        width = 0;
        bar.style.width = '0%';
        bar.style.opacity = '1';
        timer = setInterval(function () {
          width += Math.max(3, (90 - width) * 0.12);
          if (width > 90) width = 90;
          bar.style.width = width + '%';
        }, 480);
      }
      function finish() {
        if (!bar) return;
        clearInterval(timer);
        bar.style.width = '100%';
        
      }
      var links = document.querySelectorAll('a.card-link');
      links.forEach(function (a) {
        a.addEventListener('click', function (e) {
          if (e.defaultPrevented) return;
          if (e.button !== 0) return;
          if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
          try {
            var url = new URL(a.getAttribute('href') || '', window.location.href);
            if (!/detail\.php$/i.test(url.pathname)) return;
          } catch (_) { return; }
          start();
        }, { capture: true });
      });
      document.addEventListener('submit', function () { start(); }, true);
      window.addEventListener('beforeunload', function () { finish(); });
    })();
  </script>
</body>
</html>





