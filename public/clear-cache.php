<?php
/**
 * Script de nettoyage des caches
 * À supprimer après utilisation pour raisons de sécurité
 */

// Vérification basique de sécurité
$secret = 'GUT2025'; // Changez cette valeur
if (!isset($_GET['secret']) || $_GET['secret'] !== $secret) {
    die('Accès refusé');
}

echo "<h1>🧹 Nettoyage des Caches</h1>";
echo "<pre>";

// Chemin vers artisan
$artisan = __DIR__ . '/../artisan';

// 1. Cache Laravel
echo "1️⃣ Vidage cache Laravel...\n";
exec('php ' . $artisan . ' cache:clear 2>&1', $output1);
echo implode("\n", $output1) . "\n\n";

// 2. Config cache
echo "2️⃣ Vidage config cache...\n";
exec('php ' . $artisan . ' config:clear 2>&1', $output2);
echo implode("\n", $output2) . "\n\n";

// 3. Route cache
echo "3️⃣ Vidage route cache...\n";
exec('php ' . $artisan . ' route:clear 2>&1', $output3);
echo implode("\n", $output3) . "\n\n";

// 4. View cache
echo "4️⃣ Vidage view cache...\n";
exec('php ' . $artisan . ' view:clear 2>&1', $output4);
echo implode("\n", $output4) . "\n\n";

// 5. Optimize clear
echo "5️⃣ Optimize clear (opcache)...\n";
exec('php ' . $artisan . ' optimize:clear 2>&1', $output5);
echo implode("\n", $output5) . "\n\n";

// 6. Opcache reset (si disponible)
echo "6️⃣ Reset opcache PHP...\n";
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ Opcache vidé\n";
} else {
    echo "⚠️ Opcache non disponible\n";
}

echo "\n";
echo "========================================\n";
echo "✅ NETTOYAGE TERMINÉ\n";
echo "========================================\n";
echo "\n";
echo "⚠️ IMPORTANT: Supprimez ce fichier maintenant pour raisons de sécurité!\n";

echo "</pre>";
?>
