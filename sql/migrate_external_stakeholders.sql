-- ============================================================
-- Script de migration des parties prenantes externes
-- À exécuter AVANT la migration Laravel 2026_02_13_000002
-- ============================================================

-- Étape 1: Sauvegarder les données actuelles (optionnel mais recommandé)
-- CREATE TABLE project_external_stakeholders_backup AS
-- SELECT * FROM project_external_stakeholders;

-- Étape 2: Concaténer prenom + nom dans la colonne prenom
-- Cette colonne sera renommée en 'nom_complet' par la migration Laravel
UPDATE project_external_stakeholders
SET prenom = TRIM(CONCAT(COALESCE(prenom, ''), ' ', COALESCE(nom, '')))
WHERE prenom IS NOT NULL OR nom IS NOT NULL;

-- Étape 3: Mettre la colonne nom à NULL (elle deviendra 'organisation')
-- Les utilisateurs devront renseigner l'organisation manuellement après migration
UPDATE project_external_stakeholders
SET nom = NULL;

-- ============================================================
-- Après ce script, lancez la migration Laravel:
-- php artisan migrate
-- ============================================================
