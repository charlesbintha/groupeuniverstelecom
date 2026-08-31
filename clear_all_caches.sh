#!/bin/bash
# Script pour vider TOUS les caches Laravel + PHP

echo "========================================="
echo "🧹 NETTOYAGE COMPLET DES CACHES"
echo "========================================="
echo ""

# 1. Cache Laravel
echo "1️⃣ Vidage cache Laravel..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo "✅ Cache Laravel vidé"
echo ""

# 2. Cache PHP Opcache (si activé)
echo "2️⃣ Vidage opcache PHP..."
php -r "if(function_exists('opcache_reset')) { opcache_reset(); echo '✅ Opcache vidé'; } else { echo '⚠️ Opcache non activé'; }"
echo ""

# 3. Restart Queue Worker (si running)
echo "3️⃣ Arrêt des workers de queue..."
php artisan queue:restart
echo "✅ Workers redémarrés"
echo ""

echo "========================================="
echo "✅ NETTOYAGE TERMINÉ"
echo "========================================="
echo ""
echo "🔄 Relancez maintenant le worker de queue:"
echo "   php artisan queue:work"
echo ""
