<?php
// check_cache_table.php - Verify cache table exists
require __DIR__ . '/lib_graph.php';

echo "<h1>Cache Table Check</h1>";

try {
    $pdo = db();

    // Check if cache table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'cache'");
    $tableExists = $stmt->fetch();

    if (!$tableExists) {
        echo "<p style='color:red;'><strong>❌ Table 'cache' does NOT exist!</strong></p>";
        echo "<p>You need to run: <code>php artisan migrate</code></p>";
    } else {
        echo "<p style='color:green;'><strong>✅ Table 'cache' exists!</strong></p>";

        // Check table structure
        $stmt = $pdo->query("DESCRIBE cache");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>Table Structure:</h2>";
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Count rows
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM cache");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "<h2>Cache Entries:</h2>";
        echo "<p>Total entries: <strong>" . $count['count'] . "</strong></p>";

        if ($count['count'] > 0) {
            $stmt = $pdo->query("SELECT `key`, FROM_UNIXTIME(expiration) as exp_date FROM cache ORDER BY expiration DESC LIMIT 10");
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<h3>Recent entries (last 10):</h3>";
            echo "<table border='1' style='border-collapse:collapse;'>";
            echo "<tr><th>Key</th><th>Expiration</th></tr>";
            foreach ($entries as $entry) {
                echo "<tr><td>" . htmlspecialchars($entry['key']) . "</td><td>" . htmlspecialchars($entry['exp_date']) . "</td></tr>";
            }
            echo "</table>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color:red;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
