<?php
// lib_graph.php — utilitaires DB et Microsoft Graph
require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Dotenv\Dotenv;

if (session_status() === PHP_SESSION_NONE) session_start();

// Charger .env tôt
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

function envv($keys, $default = '') {
  $keys = (array)$keys;
  foreach ($keys as $k) {
    if (isset($_ENV[$k]) && $_ENV[$k] !== '') return $_ENV[$k];
    $v = getenv($k);
    if ($v !== false && $v !== '') return $v;
  }
  return $default;
}

function verify_laravel_token(): array|false {
  $token = $_GET['token'] ?? null;
  if (!$token) {
    error_log('Token verification: No token provided');
    return false;
  }

  if (!preg_match('/^report_access_\d+_\d+_\d+$/', $token)) {
    error_log('Token verification: Invalid token format: ' . $token);
    return false;
  }

  try {
    $pdo = db();

    $cachePrefix = 'portail-pmo-gut-cache-';
    $fullKey = $cachePrefix . $token;

    $stmt = $pdo->prepare("SELECT value, expiration FROM cache WHERE `key` = ?");
    $stmt->execute([$fullKey]);
    $cached = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cached) {
      error_log('Token verification: Token not found in cache. Searched for: ' . $fullKey);
      return false;
    }

    if ($cached['expiration'] < time()) {
      error_log('Token verification: Token expired. Expiration: ' . $cached['expiration'] . ', Current: ' . time());
      return false;
    }

    $rawValue = $cached['value'];

    $data = @unserialize($rawValue);

    if ($data === false && $rawValue !== 'b:0;') {
      error_log('Token verification: Failed to unserialize data. Raw value length: ' . strlen($rawValue));
      return false;
    }

    if (!is_array($data) || !isset($data['user_id'], $data['project_id'], $data['ip'])) {
      error_log('Token verification: Invalid data structure. Data: ' . print_r($data, true));
      return false;
    }

    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if ($data['ip'] !== $clientIp) {
      error_log('Token verification: IP mismatch. Expected: ' . $data['ip'] . ', Got: ' . $clientIp);
      return false;
    }

    error_log('Token verification: SUCCESS for user_id=' . $data['user_id'] . ', project_id=' . $data['project_id']);
    return $data;
  } catch (Exception $e) {
    error_log('Token verification exception: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
    return false;
  }
}

function db() : PDO {
  static $pdo = null;
  if ($pdo) return $pdo;
  $dsn = envv('DB_DSN');
  if (!$dsn) {
    $host = envv('DB_HOST', '127.0.0.1');
    $name = envv('DB_NAME', '');
    $port = envv('DB_PORT');
    $charset = envv('DB_CHARSET', 'utf8mb4');
    $dsn = "mysql:host={$host}" . ($port ? ";port={$port}" : '') . ";dbname={$name};charset={$charset}";
  }
  $user = envv('DB_USER');
  $pass = envv('DB_PASS');
  $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
  return $pdo;
}

function http() : Client {
  $verify = true;
  $ca = envv('CURL_CA');
  if ($ca && file_exists($ca)) $verify = $ca;
  $timeout = (float)envv('HTTP_TIMEOUT', '30');
  $connect = (float)envv('HTTP_CONNECT_TIMEOUT', '5');
  return new Client([
    'http_errors' => false,
    'timeout' => $timeout,
    'connect_timeout' => $connect,
    'verify' => $verify,
    'headers' => [ 'Accept' => 'application/json' ],
  ]);
}

function graph_token() : string {
  if (!empty($_SESSION['graph_token']) && ($_SESSION['graph_token_exp'] ?? 0) > time()+60) {
    return $_SESSION['graph_token'];
  }
  $http = http();
  $tenant = envv(['GRAPH_TENANT_ID','TENANT_ID']);
  $client = envv(['GRAPH_CLIENT_ID','CLIENT_ID']);
  $secret = envv(['GRAPH_CLIENT_SECRET','CLIENT_SECRET']);
  $scope = envv('GRAPH_SCOPE', 'https://graph.microsoft.com/.default');
  $r = $http->post("https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token", [
    'form_params' => [
      'client_id' => $client,
      'client_secret' => $secret,
      'grant_type' => 'client_credentials',
      'scope' => $scope,
    ]
  ]);
  $code = $r->getStatusCode();
  if ($code < 200 || $code >= 300) {
    throw new RuntimeException("Auth Graph échouée ($code): " . $r->getBody());
  }
  $body = json_decode($r->getBody(), true);
  $_SESSION['graph_token'] = $body['access_token'];
  $_SESSION['graph_token_exp'] = time() + (int)($body['expires_in'] ?? 3600);
  return $_SESSION['graph_token'];
}

function gget(string $url) : array {
  try {
    $http = http();
    $token = graph_token();
    $resp = $http->get($url, ['headers' => ['Authorization' => "Bearer $token"]]);
    $code = $resp->getStatusCode();
    if ($code >= 200 && $code < 300) return json_decode($resp->getBody(), true) ?? [];
    return ['_error' => $code, '_body' => (string)$resp->getBody()];
  } catch (Throwable $e) {
    return ['_error' => 'exception', '_body' => $e->getMessage()];
  }
}

// Méta (petit cache process)
function plan_meta(string $planId) : array {
  static $cache = [];
  if (isset($cache[$planId])) return $cache[$planId];
  $d = gget("https://graph.microsoft.com/v1.0/planner/plans/$planId");
  return $cache[$planId] = $d;
}

function bucket_meta(string $bucketId) : array {
  static $cache = [];
  if (isset($cache[$bucketId])) return $cache[$bucketId];
  $d = gget("https://graph.microsoft.com/v1.0/planner/buckets/$bucketId");
  return $cache[$bucketId] = $d;
}

function plan_buckets(string $planId) : array {
  static $cache = [];
  if (isset($cache[$planId])) return $cache[$planId];
  $d = gget("https://graph.microsoft.com/v1.0/planner/plans/$planId/buckets");
  $list = is_array($d['value'] ?? null) ? $d['value'] : [];
  return $cache[$planId] = $list;
}

function group_members(string $groupId) : array {
  static $cache = [];
  if (!$groupId) return [];
  if (isset($cache[$groupId])) return $cache[$groupId];
  $d = gget("https://graph.microsoft.com/v1.0/groups/$groupId/members?$select=displayName,mail,id");
  return $cache[$groupId] = (is_array($d['value'] ?? null) ? $d['value'] : []);
}

function user_name(string $userId) : string {
  static $cache = [];
  if (!$userId) return '';
  if (isset($cache[$userId])) return $cache[$userId];
  $d = gget("https://graph.microsoft.com/v1.0/users/$userId");
  return $cache[$userId] = ($d['displayName'] ?? $userId);
}
