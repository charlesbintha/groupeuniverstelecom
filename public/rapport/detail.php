<?php
// detail.php
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

$pid = (int)($_GET['pid'] ?? 0);
$planId = trim($_GET['planId'] ?? '');
$bucketId = trim($_GET['bucketId'] ?? '');
$token = trim($_GET['token'] ?? '');

if (!$planId) { http_response_code(400); echo "planId requis"; exit; }

$pdo = db();
$proj = $pdo->prepare("SELECT nom_projet, code_projet, ms_group_id FROM projects WHERE id = ?");
$proj->execute([$pid]);
$projRow = $proj->fetch(PDO::FETCH_ASSOC) ?: ['nom_projet'=>'Projet','code_projet'=>'','ms_group_id'=>''];

$tasksUrl = $bucketId
  ? "https://graph.microsoft.com/v1.0/planner/buckets/$bucketId/tasks"
  : "https://graph.microsoft.com/v1.0/planner/plans/$planId/tasks";

$data = gget($tasksUrl);
$tasks = $data['value'] ?? [];

$plan = plan_meta($planId);
$bucket = $bucketId ? bucket_meta($bucketId) : [];
$buckets = plan_buckets($planId);

$items = [];
$now = new DateTimeImmutable();

foreach ($tasks as $t) {
  $det = gget("https://graph.microsoft.com/v1.0/planner/tasks/{$t['id']}/details");
  if (isset($det['_error'])) $det = [];
  $ass = [];
  if (!empty($t['assignments'])) {
    foreach ($t['assignments'] as $uid => $meta) $ass[] = user_name($uid);
  }
  $pct = (int)($t['percentComplete'] ?? 0);
  $due = isset($t['dueDateTime']) ? new DateTimeImmutable($t['dueDateTime']) : null;
  $over = $due && $pct < 100 && $due < $now;

  $items[] = [
    'title' => $t['title'] ?? '',
    'taskId' => $t['id'],
    'bucketId' => (string)($t['bucketId'] ?? ''),
    'assignees' => $ass,
    'created' => $t['createdDateTime'] ?? null,
    'start' => $t['startDateTime'] ?? null,
    'due' => $t['dueDateTime'] ?? null,
    'pct' => $pct,
    'over' => $over,
    'checklist' => is_array(($det['checklist'] ?? null)) ? count($det['checklist']) : 0,
    'hasDesc' => !empty($det['description']),
  ];
}

if (!function_exists('format_date_fr_long')) {
  function format_date_fr_long(?string $s): string {
    if (!$s) return '';
    try { $dt = new DateTimeImmutable($s); } catch (Exception $e) { return ''; }
    if (class_exists('IntlDateFormatter')) {
      $tzName = $dt->getTimezone()->getName();
      if ($tzName === 'Z' || $tzName === '+00:00' || preg_match('~^[+-]\d{2}:?\d{2}$~', $tzName)) { $tzName = 'UTC'; }
      $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::NONE, IntlDateFormatter::NONE, $tzName, IntlDateFormatter::GREGORIAN, 'd MMMM yyyy');
      $out = $fmt->format($dt);
      if (is_string($out) && $out !== '') {
        if (function_exists('mb_strtoupper')) {
          return preg_replace_callback('~^(\d{1,2})\s+(\p{L})(.*)$~u', function($m){ return $m[1].' '.mb_strtoupper($m[2],'UTF-8').$m[3]; }, $out);
        }
        return $out;
      }
    }
    $mois = [1=>'janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    $label = $mois[(int)$dt->format('n')] ?? '';
    if (function_exists('mb_convert_case')) { $label = mb_convert_case($label, MB_CASE_TITLE, 'UTF-8'); } else { $label = ucfirst($label); }
    return sprintf('%d %s %s', (int)$dt->format('j'), $label, $dt->format('Y'));
  }
}
// KPIs
$total = count($items);
$done  = count(array_filter($items, fn($i)=>$i['pct']>=100));
$over  = count(array_filter($items, fn($i)=>$i['over']));

// Export PDF (minimal, sans dependance)
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
  // Si Dompdf est disponible, on produit un PDF HTML stylÃ©
  if (class_exists('Dompdf\\Dompdf')) {
    $esc = fn($s)=>htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $projName = $projRow['nom_projet'] ?: ("Projet #$pid");
    $planTitle = $plan['title'];
    $dateStr = (new DateTimeImmutable('now'))->format('Y-m-d H:i');
    $groupId = (string)($projRow['ms_group_id'] ?? '');
    $members = $groupId ? group_members($groupId) : [];
    if (!$members) {
      // fallback: participants dapres les attributions des taches
      $names = [];
      foreach ($items as $it) { foreach ($it['assignees'] as $nm) { $names[$nm] = true; } }
      foreach (array_keys($names) as $nm) { $members[] = ['displayName'=>$nm, 'mail'=>'']; }
    }
    // Couleurs depuis logo (si GD dispo), sinon palette par defaut
    $accent = '#1d4ed8'; $muted = '#64748b'; $rule = '#c7d2fe';
    $logoPath = __DIR__ . '/GUT.png'; $logoData = '';
    if (is_file($logoPath)) {
      $bin = @file_get_contents($logoPath);
      if ($bin !== false) $logoData = 'data:image/png;base64,' . base64_encode($bin);
      if (function_exists('imagecreatefrompng')) {
        $im = @imagecreatefrompng($logoPath);
        if ($im) {
          $w = imagesx($im); $h = imagesy($im);
          $px = imagecolorat($im, (int)($w/2), (int)($h/2));
          $r = ($px >> 16) & 0xFF; $g = ($px >> 8) & 0xFF; $b = $px & 0xFF;
          $accent = sprintf('#%02x%02x%02x', $r, $g, $b);
          // derive muted/rule
          $muted = sprintf('#%02x%02x%02x', min(255,(int)round($r*0.6+96)), min(255,(int)round($g*0.6+96)), min(255,(int)round($b*0.6+96)));
          $rule  = sprintf('#%02x%02x%02x', min(255,(int)round($r*0.7+120)), min(255,(int)round($g*0.7+120)), min(255,(int)round($b*0.7+120)));
          imagedestroy($im);
        }
      }
    }
    $grouped = [];
    if ($bucketId === '') {
      foreach ($items as $it) { $grouped[(string)($it['bucketId'] ?? '')][] = $it; }
    } else {
      $grouped[(string)$bucketId] = $items;
    }
    $bucketNames = [];
    foreach ($buckets as $b) { $bucketNames[(string)($b['id'] ?? '')] = (string)($b['name'] ?? ''); }

    ob_start();
    ?>
    <!doctype html>
    <html>
    <head>
      <meta charset="utf-8">
      <style>
        @page { margin: 40px 36px; }
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color:#0f172a; font-size:12px; }
        h1 { margin:0 0 4px; font-size:20px; color:<?=$esc($accent)?>; }
        .muted { color:<?=$esc($muted)?>; }
        .rule { height:1px; background:<?=$esc($rule)?>; margin:8px 0 12px; }
        .kpis { margin:8px 0 14px; color:#334155; }
        .kpis b { color:#0f172a; }
        .bucket { margin:18px 0 8px; font-weight:700; color:<?=$esc($accent)?>; }
        table { width:100%; border-collapse: collapse; }
        th, td { padding:8px 10px; border-bottom:1px solid #e2e8f0; text-align:left; vertical-align:top; }
        th { background:#eff6ff; border-top:1px solid #bfdbfe; font-weight:700; font-size:11px; color:#1e40af; }
        td { font-size:11px; }
        .right { text-align:right; }
        .nowrap { white-space:nowrap; }
        .zebra:nth-child(odd) td { background:#f8fafc; }
        .hdrtbl { width:100%; }
        .logo { width:120px; height:auto; }
        .participants { margin:8px 0 12px; }
        .chip { display:inline-block; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:999px; padding:4px 10px; margin:3px; font-size:11px; }
      </style>
    </head>
    <body>
      <table class="hdrtbl" cellspacing="0" cellpadding="0"><tr>
        <td>
          <h1>Rapport Planner - <?=$esc($projName)?></h1>
          <div class="muted">Plan: <b><?=$esc($planTitle)?></b>  G&eacute;n&eacute;r&eacute; le: <?=$esc($dateStr)?></div>
        </td>
        <td style="text-align:right; vertical-align:top;">
          <?php if ($logoData): ?><img class="logo" src="<?=$logoData?>" alt="Logo"><?php endif; ?>
        </td>
      </tr></table>
      <div class="rule"></div>
      <div class="kpis">Total: <b><?=$total?></b> | Termin&eacute;s: <b><?=$done?></b> | En retard: <b><?=$over?></b></div>
      <?php if ($members): ?>
      <div class="participants"><span class="muted">Participants:&nbsp;</span>
        <?php foreach ($members as $m): $dn = $esc($m['displayName'] ?? ''); $em = $esc($m['mail'] ?? ''); ?>
          <span class="chip"><?=$dn?><?php if ($em): ?> &lt;<?=$em?>&gt;<?php endif; ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php foreach ($grouped as $bid => $rows): if (!$rows) continue; $bname = $bucketNames[$bid] ?? ($bid ?: 'Autres'); ?>
        <div class="bucket">Bucket: <?=$esc($bname)?></div>
        <table>
          <thead>
            <tr>
              <th style="width:40%;">T&acirc;che</th>
              <th>Assign&eacute;s</th>
              <th>Statut</th>
              <th class="nowrap">&Eacute;ch&eacute;ance</th>
              <th class="right nowrap">Avancement %</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $it): ?>
              <?php
                $ttl = $esc($it['title'] ?: '(Sans titre)');
                $ass = $esc($it['assignees'] ? implode(', ', $it['assignees']) : '');
                $pct = (int)$it['pct'];
                $stat = ($pct >= 100) ? 'Termin&eacute;' : (($it['over'] ?? false) ? 'En retard' : 'En cours');
                $due = $it['due'] ? format_date_fr_long($it['due']) : '';
              ?>
              <tr class="zebra">
                <td><div style="font-weight:600;"><?=$ttl?></div></td>
                <td><?=$ass?></td>
                <td><?=$stat?></td>
                <td class="nowrap"><?=$esc($due ?: '')?></td>
                <td class="right"><?=(int)$pct?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endforeach; ?>
    </body>
    </html>
    <?php
    $html = ob_get_clean();
    $dompdf = new Dompdf\Dompdf([ 'isRemoteEnabled' => false, 'isHtml5ParserEnabled' => true ]);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $fname = 'rapport_'.preg_replace('~[^a-zA-Z0-9_-]+~','_', $projName).'.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    echo $dompdf->output();
    exit;
  }
  $projName = $projRow['nom_projet'] ?: ("Projet #$pid");
  $planTitle = $plan['title'] ?? $planId;
  $dateStr = (new DateTimeImmutable('now'))->format('Y-m-d H:i');

  // Lignes du rapport (texte), ajout d'un r5sum5
  $lines = [];
  $lines[] = "Rapport Planner - $projName"; // Titre
  $lines[] = "Plan: $planTitle 7  Genere le: $dateStr"; // Sous-titre
  if ($bucketId !== '') $lines[] = "Bucket: ".($bucket['name'] ?? $bucketId);
  $lines[] = "Total: $total | Termin&eacute;es: $done | En retard: $over";
  $lines[] = ""; // espace

  // Grouper par bucket si aucun filtre
  $byBucket = [];
  foreach ($items as $it) { $byBucket[$it['bucketId'] ?? ''][] = $it; }

  $fmtTask = function(array $it) {
    $pct = (int)($it['pct'] ?? 0);
    $ttl = $it['title'] ?: '(Sans titre)';
    $due = $it['due'] ? format_date_fr_long($it['due']) : '';
    $ass = $it['assignees'] ? (implode(', ', $it['assignees'])) : '';
    $extra = [];
    if ($due) $extra[] = "Echeance: $due";
    if ($ass) $extra[] = $ass;
    $extraStr = $extra ? (' ['.implode(' | ', $extra).']') : '';
    return "- [".$pct."%] ".$ttl.$extraStr;
  };

  if ($bucketId === '') {
    foreach ($buckets as $b) {
      $bid = (string)($b['id'] ?? '');
      $bname = (string)($b['name'] ?? $bid);
      $bucketItems = $byBucket[$bid] ?? [];
      if (!count($bucketItems)) continue;
      $lines[] = "Bucket: $bname";
      foreach ($bucketItems as $it) $lines[] = $fmtTask($it);
      $lines[] = "";
    }
  } else {
    $bname = $bucket['name'] ?? $bucketId;
    $lines[] = "Bucket: $bname";
    foreach ($items as $it) $lines[] = $fmtTask($it);
  }

  // Constructeur PDF simple (A4), police Helvetica
  $esc = function(string $s) { return str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], $s); };
  $makePageStream = function(array $pageLines) use ($esc) {
    $yTop = 800; $leading = 16; $i = 0; $chunks = [];
    foreach ($pageLines as $ln) {
      $txt = $esc($ln);
      $x = 50; $yPos = $yTop - ($i * $leading);
      if ($yPos < 60) break;
      if ($i === 0) {
        // Titre: bleu, gras, grand
        $chunks[] = "0.145 0.388 0.922 rg"; // accent color
        $chunks[] = "BT /F2 18 Tf 1 0 0 1 $x $yPos Tm ($txt) Tj ET";
        $chunks[] = "0 0 0 rg"; // reset noir
      } elseif ($i === 1) {
        // Sous-titre: gris
        $chunks[] = "0.39 0.46 0.55 rg";
        $chunks[] = "BT /F1 12 Tf 1 0 0 1 $x $yPos Tm ($txt) Tj ET";
        $chunks[] = "0 0 0 rg";
        // Ligne horizontale sous l'entete
        $yRule = $yPos - 6;
        $chunks[] = "0.85 0.90 0.96 RG 0.5 w 50 $yRule m 545 $yRule l S 0 0 0 RG";
      } else {
        // Buckets en bleu gras, sinon texte normal
        if (substr($ln, 0, 8) === 'Bucket: ') {
          $chunks[] = "0.145 0.388 0.922 rg";
          $chunks[] = "BT /F2 13 Tf 1 0 0 1 $x $yPos Tm ($txt) Tj ET";
          $chunks[] = "0 0 0 rg";
        } else {
          $chunks[] = "BT /F1 11 Tf 1 0 0 1 $x $yPos Tm ($txt) Tj ET";
        }
      }
      $i++;
    }
    $stream = implode("\n", $chunks)."\n";
    $len = strlen($stream);
    return ["<< /Length $len >>\nstream\n$stream\nendstream", $i];
  };

  // Pagination simple
  $yTop = 800; $leading = 14; $minY = 60; $linesPerPage = (int)floor(($yTop - $minY) / $leading) - 1; if ($linesPerPage < 1) $linesPerPage = 1;
  $pagesLines = [];
  $chunk = [];
  foreach ($lines as $ln) { $chunk[] = $ln; if (count($chunk) >= $linesPerPage) { $pagesLines[] = $chunk; $chunk = []; } }
  if ($chunk) $pagesLines[] = $chunk;
  if (!$pagesLines) $pagesLines = [[]];

  // Objets PDF
  $objs = [];
  $objs[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>"; // 1: Helvetica
  $objs[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>"; // 2: Helvetica-Bold
  foreach ($pagesLines as $pg) { [$stream] = $makePageStream($pg); $objs[] = $stream; }
  $pageNos = [];
  $pagesObjNo = 3 + count($pagesLines) + count($pagesLines);
  for ($idx=0;$idx<count($pagesLines);$idx++) {
    $contentNo = 3 + $idx;
    $pageObj = "<< /Type /Page /Parent $pagesObjNo 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 1 0 R /F2 2 0 R >> >> /Contents $contentNo 0 R >>";
    $objs[] = $pageObj;
    $pageNos[] = count($objs);
  }
  $kids = implode(' ', array_map(fn($n)=>"$n 0 R", $pageNos));
  $objs[] = "<< /Type /Pages /Kids [ $kids ] /Count ".count($pageNos)." >>"; // pages node
  $pagesNumber = count($objs);
  $objs[] = "<< /Type /Catalog /Pages $pagesNumber 0 R >>"; // catalog

  // Assembler le PDF
  $out = "%PDF-1.4\n";
  $offsets = [0];
  $pos = strlen($out);
  foreach ($objs as $i => $obj) {
    $n = $i+1;
    $objStr = "$n 0 obj\n$obj\nendobj\n";
    $offsets[] = $pos;
    $out .= $objStr;
    $pos = strlen($out);
  }
  $xrefPos = strlen($out);
  $countAll = count($objs)+1;
  $out .= "xref\n0 $countAll\n";
  $out .= sprintf("%010d %05d f \n", 0, 65535);
  for ($i=1;$i<$countAll;$i++) { $out .= sprintf("%010d %05d n \n", $offsets[$i], 0); }
  $out .= "trailer << /Size $countAll /Root ".count($objs)." 0 R >>\nstartxref\n$xrefPos\n%%EOF";

  $fname = 'rapport_'.preg_replace('~[^a-zA-Z0-9_-]+~','_', $projName).'.pdf';
  header('Content-Type: application/pdf');
  header('Content-Disposition: attachment; filename="'.$fname.'"');
  header('Content-Length: '.strlen($out));
  echo $out;
  exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>D&eacute;tail &mdash; <?=htmlspecialchars($projRow["nom_projet"] ?: "Projet #$pid")?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root { --text:#0f172a; --muted:#64748b; --card:#ffffff; --line:#e2e8f0; --ok:#16a34a; --bad:#dc2626; --accent:#2563eb; --bg:#f8fafc; }
    * { box-sizing: border-box; }
    body { margin:0; padding:28px; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial; color:var(--text); background:var(--bg); }
    a { color: var(--accent); text-decoration:none; }
    h1 { margin:0 0 6px; font-size:24px; letter-spacing:.2px; }
    .muted { color: var(--muted); }

    .hdr { display:flex; gap:12px; align-items:baseline; justify-content:space-between; }
    .pill { display:inline-block; padding:2px 10px; border-radius:999px; background:#f1f5f9; font-size:12px; color:#334155; border:1px solid #e2e8f0; }
    .pill.ok { background:#ecfdf5; color:#065f46; border-color:#a7f3d0; }
    .pill.bad { background:#fef2f2; color:#991b1b; border-color:#fecaca; }
    .pill.info { background:#eff6ff; color:#1e40af; border-color:#bfdbfe; }

    .toolbar { display:flex; gap:8px; }
    .btn { display:inline-block; background: var(--accent); color:#fff; padding:8px 12px; border-radius:10px; font-weight:600; font-size:13px; }

    .kpis { display:flex; gap:10px; margin: 12px 0 16px; flex-wrap:wrap; }
    .k { padding:6px 10px; border-radius:10px; background:#f8fafc; border:1px solid var(--line); font-size:13px; }
    .k.ok { border-color:#bbf7d0; color:var(--ok); }
    .k.bad{ border-color:#fecaca; color:var(--bad); }

    .panel { background:var(--card); border:1px solid var(--line); border-radius:16px; box-shadow: 0 6px 18px rgba(2,6,23,.06); overflow:hidden; }
    table { width:100%; border-collapse: collapse; }
    th, td { padding:12px 14px; border-bottom:1px solid var(--line); vertical-align:top; text-align:left; }
    th { white-space:nowrap; font-weight:600; background:#f8fafc; }
    .right { text-align:right; }
    .nowrap { white-space:nowrap; }

    /* Select de9roulant styl1e9 pour les buckets */
    .select-wrap { position:relative; }
    #bucketId {
      appearance:none; -webkit-appearance:none; -moz-appearance:none;
      padding:8px 34px 8px 10px; border:1px solid var(--line); border-radius:10px;
      background:#fff;
      font-size:13px; color:var(--text);
      box-shadow: 0 1px 2px rgba(2,6,23,.04);
      transition: border-color .2s ease, box-shadow .2s ease;
    }
    #bucketId:focus { outline:none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
    #bucketId::-ms-expand { display:none; }
    .select-wrap::after {
      content:""; pointer-events:none; position:absolute; right:10px; top:50%; transform:translateY(-50%);
      width:10px; height:10px;
      background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><path d="M1 3l4 4 4-4" stroke="%2364748b" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>') no-repeat center center;
    }
  
    /* Route progress bar (top) */
        #route-progress { position:fixed; top:0; left:0; height:3px; width:0; opacity:1; background: linear-gradient(90deg, #2563eb, #22c55e); box-shadow: 0 0 10px rgba(37,99,235,.35); transition: width .18s ease; z-index: 10000; pointer-events:none; }
    @media (prefers-reduced-motion: reduce) { #route-progress { transition: none; } }</style>
</head>
<body>
  <div id='route-progress' aria-hidden='true' style='width:30%;opacity:1'></div>

  <div class="hdr">
    <div>
      <h1><?=htmlspecialchars($projRow['nom_projet'] ?: "Projet #$pid")?></h1>
      <div class="muted">
        Code: <span class="pill"><?=htmlspecialchars($projRow['code_projet'] ?: '—')?></span> 
        Plan: <span class="pill"><?=htmlspecialchars($plan['title'] ?? $planId)?></span> 
        Bucket: <span class="pill"><?=htmlspecialchars($bucket['name'] ?? ($bucketId ?: '—'))?></span>
      </div>
    </div>
    <div class="toolbar">
      <form method="get" action="detail.php" style="display:flex; gap:8px; align-items:center;">
        <input type="hidden" name="pid" value="<?=$pid?>">
        <input type="hidden" name="planId" value="<?=htmlspecialchars($planId)?>">
        <input type="hidden" name="token" value="<?=htmlspecialchars($token)?>">
        <label for="bucketId" class="muted">Bucket</label>
        <div class="select-wrap">
        <select name="bucketId" id="bucketId" onchange="this.form.submit()">
          <option value="" <?= $bucketId === '' ? 'selected' : '' ?>>Tous</option>
          <?php foreach ($buckets as $b): ?>
            <?php $bid = (string)($b['id'] ?? ''); $bname = (string)($b['name'] ?? $bid); ?>
            <option value="<?=htmlspecialchars($bid)?>" <?= $bucketId === $bid ? 'selected' : '' ?>><?=htmlspecialchars($bname)?></option>
          <?php endforeach; ?>
        </select>
        </div>
        <noscript><button type="submit" class="btn">Filtrer</button></noscript>
      </form>
      <a class="btn" target ="_blank" href="detail.php?pid=<?=urlencode((string)$pid)?>&planId=<?=urlencode($planId)?>&bucketId=<?=urlencode($bucketId)?>&token=<?=urlencode($token)?>&export=pdf">G&eacute;n&eacute;rer PDF</a>
      <a class="btn" href="/projects">Retour aux projets</a>
    </div>
  </div>

  <div class="kpis">
    <span class="k">Total: <b><?=$total?></b></span>
    <span class="k ok">Termin&eacute;es: <b><?=$done?></b></span>
    <span class="k bad">En retard: <b><?=$over?></b></span>
  </div>

  <?php if ($bucketId === ''): ?>
    <?php
      $byBucket = [];
      foreach ($items as $it) { $byBucket[$it['bucketId'] ?? ''][] = $it; }
    ?>
    <?php foreach ($buckets as $b): ?>
      <?php $bid = (string)($b['id'] ?? ''); $bname = (string)($b['name'] ?? $bid); $bucketItems = $byBucket[$bid] ?? []; if (!count($bucketItems)) continue; ?>
      <h2 style="margin:16px 0 8px; font-size:18px;">Bucket : <span class="pill"><?=htmlspecialchars($bname)?></span></h2>
      <div class="panel" style="overflow:auto;">
        <table>
          <thead>
            <tr>
              <th style="width:40%;">T&acirc;che</th>
              <th>Assign&eacute;s</th>
              <th>Statut</th>
              <th class="nowrap">&Eacute;ch&eacute;ance</th>
              <th class="right nowrap">%</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bucketItems as $it): ?>
              <tr>
                <td>
                  <div style="font-weight:600;">&nbsp;<?=htmlspecialchars($it['title'] ?: '(Sans titre)')?></div>
                </td>
                <td><?= $it['assignees'] ? htmlspecialchars(implode(', ', $it['assignees'])) : '' ?></td>
                <td>
                  <?php if ($it['pct'] >= 100): ?>
                    <span class="pill ok">Termin&eacute;</span>
                  <?php elseif ($it['over']): ?>
                    <span class="pill bad">En retard</span>
                  <?php else: ?>
                    <span class="pill info">En cours</span>
                  <?php endif; ?>
                </td>
                <td class="nowrap">
                  <?php if ($it['due']): ?>
                    <?php $d = format_date_fr_long($it['due']); $style = $it['over'] ? 'color:var(--bad);font-weight:600;' : ''; ?>
                    <span style="<?=$style?>"><?=$d?></span>
                  <?php else: ?><?php endif; ?>
                </td>
                <td class="right"><?=$it['pct']?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
  <div class="panel" style="overflow:auto;">
    <table>
      <thead>
        <tr>
          <th style="width:40%;">T&acirc;che</th>
          <th>Assign&eacute;s</th>
          <th>Statut</th>
          <th class="nowrap">&Eacute;ch&eacute;ance</th>
          <th class="right nowrap">Avancement %</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td>
              <div style="font-weight:600;">&nbsp;<?=htmlspecialchars($it['title'] ?: '(Sans titre)')?></div>
            </td>
            <td><?= $it['assignees'] ? htmlspecialchars(implode(', ', $it['assignees'])) : '' ?></td>
            <td>
              <?php if ($it['pct'] >= 100): ?>
                <span class="pill ok">Termin&eacute;</span>
              <?php elseif ($it['over']): ?>
                <span class="pill bad">En retard</span>
              <?php else: ?>
                <span class="pill info">En cours</span>
              <?php endif; ?>
            </td>
            <td class="nowrap">
              <?php if ($it['due']): ?>
                <?php
                  $d = format_date_fr_long($it['due']);
                  $style = $it['over'] ? 'color:var(--bad);font-weight:600;' : '';
                ?>
                <span style="<?=$style?>"><?=$d?></span>
              <?php else: ?><?php endif; ?>
            </td>
            <td class="right"><?=$it['pct']?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
  <script>
    (function () {
      var bar = document.getElementById('route-progress');
      var timer = null, width = (function(){ var w = 0; try { w = parseFloat(bar && bar.style.width) || 0; } catch(e){} return w; })();
      function start() {
        if (!bar) return;
        clearInterval(timer);
        if (width < 1) { width = 0; bar.style.width = '0%';
        bar.style.opacity = '1'; }
        timer = setInterval(function () {
          width += Math.max(2, (90 - width) * 0.10);
          if (width > 90) width = 90;
          bar.style.width = width + '%';
        }, 120);
      }
      function finish() {
        if (!bar) return;
        clearInterval(timer);
        bar.style.width = '100%';
        
        
      }
      start();
      window.addEventListener('load', finish);
    })();
  </script>
</body>
</html>























