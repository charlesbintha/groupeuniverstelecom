<?php
// debug_token.php - Temporary debug page
require __DIR__ . '/lib_graph.php';

$token = $_GET['token'] ?? 'NO TOKEN';

echo "<h1>Debug Token Verification</h1>";
echo "<p><strong>Token:</strong> " . htmlspecialchars($token) . "</p>";

if (!$token || $token === 'NO TOKEN') {
    echo "<p style='color:red;'>No token provided in URL</p>";
    exit;
}

try {
    $pdo = db();

    $cachePrefix = 'portail-pmo-gut-cache-';
    $fullKey = $cachePrefix . $token;

    echo "<p><strong>Cache Key (with prefix):</strong> " . htmlspecialchars($fullKey) . "</p>";

    $stmt = $pdo->prepare("SELECT * FROM cache WHERE `key` = ?");
    $stmt->execute([$fullKey]);
    $cached = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h2>Cache Query Result:</h2>";
    if (!$cached) {
        echo "<p style='color:red;'>Token NOT FOUND in cache table</p>";

        // Check if any tokens exist
        $stmt2 = $pdo->query("SELECT `key`, expiration FROM cache WHERE `key` LIKE 'report_access_%' ORDER BY expiration DESC LIMIT 5");
        $allTokens = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        echo "<h3>Recent tokens in cache (last 5):</h3>";
        if (empty($allTokens)) {
            echo "<p>No tokens found at all!</p>";
        } else {
            echo "<table border='1' style='border-collapse:collapse;'>";
            echo "<tr><th>Key</th><th>Expiration</th><th>Status</th></tr>";
            foreach ($allTokens as $t) {
                $status = $t['expiration'] >= time() ? 'Valid' : 'Expired';
                echo "<tr><td>" . htmlspecialchars($t['key']) . "</td><td>" . date('Y-m-d H:i:s', $t['expiration']) . "</td><td>" . $status . "</td></tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color:green;'>Token FOUND in cache</p>";

        echo "<h3>Cache Entry Details:</h3>";
        echo "<ul>";
        echo "<li><strong>Key:</strong> " . htmlspecialchars($cached['key']) . "</li>";
        echo "<li><strong>Expiration:</strong> " . date('Y-m-d H:i:s', $cached['expiration']) . " (" . $cached['expiration'] . ")</li>";
        echo "<li><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . " (" . time() . ")</li>";

        $isExpired = $cached['expiration'] < time();
        echo "<li><strong>Status:</strong> <span style='color:" . ($isExpired ? 'red' : 'green') . ";'>" . ($isExpired ? 'EXPIRED' : 'VALID') . "</span></li>";
        echo "</ul>";

        echo "<h3>Unserialized Data:</h3>";
        $data = @unserialize($cached['value']);

        if ($data === false) {
            echo "<p style='color:red;'>Failed to unserialize!</p>";
            echo "<p><strong>Raw value (first 200 chars):</strong><br><code>" . htmlspecialchars(substr($cached['value'], 0, 200)) . "</code></p>";
        } else {
            echo "<pre>" . print_r($data, true) . "</pre>";

            if (is_array($data)) {
                echo "<h3>Data Validation:</h3>";
                echo "<ul>";
                echo "<li>Has user_id: " . (isset($data['user_id']) ? '✅ ' . $data['user_id'] : '❌') . "</li>";
                echo "<li>Has project_id: " . (isset($data['project_id']) ? '✅ ' . $data['project_id'] : '❌') . "</li>";
                echo "<li>Has ip: " . (isset($data['ip']) ? '✅ ' . $data['ip'] : '❌') . "</li>";

                $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                echo "<li>Client IP: " . $clientIp . "</li>";

                if (isset($data['ip'])) {
                    $ipMatch = $data['ip'] === $clientIp;
                    echo "<li>IP Match: " . ($ipMatch ? '✅ YES' : '❌ NO (Expected: ' . $data['ip'] . ')') . "</li>";
                }
                echo "</ul>";
            }
        }
    }

} catch (Exception $e) {
    echo "<p style='color:red;'><strong>Exception:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<h2>Environment Info:</h2>";
echo "<ul>";
echo "<li>PHP Version: " . PHP_VERSION . "</li>";
echo "<li>Client IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "</li>";
echo "<li>User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . "</li>";
echo "</ul>";
