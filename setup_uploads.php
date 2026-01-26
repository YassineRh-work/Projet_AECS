<?php
/**
 * Script d'initialisation du dossier uploads
 * À exécuter une seule fois pour créer le dossier et les protections
 */

$uploadsDir = __DIR__ . '/uploads';

echo "=== Vérification du système de gestion des documents ===\n\n";

// Créer le dossier s'il n'existe pas
if (!is_dir($uploadsDir)) {
    if (mkdir($uploadsDir, 0755, true)) {
        echo "✓ Dossier 'uploads' créé avec succès\n";
    } else {
        echo "✗ Erreur : impossible de créer le dossier 'uploads'\n";
        exit(1);
    }
} else {
    echo "✓ Dossier 'uploads' existe déjà\n";
}

// Vérifier les permissions
$perms = fileperms($uploadsDir);
$permsOctal = substr(sprintf('%o', $perms), -3);
echo "  Permissions : $permsOctal\n";

if (is_writable($uploadsDir)) {
    echo "✓ Dossier 'uploads' est accessible en écriture\n";
} else {
    echo "⚠ ATTENTION : Dossier 'uploads' n'est pas accessible en écriture\n";
    echo "  Essayez : chmod -R 755 uploads/\n";
}

// Créer un fichier .htaccess pour sécuriser le dossier
$htaccessFile = $uploadsDir . '/.htaccess';
$htaccessContent = <<<'HTACCESS'
# Protection du dossier uploads
<FilesMatch "\.(php|phtml|php3|php4|php5|phps)$">
    Deny from all
</FilesMatch>

# Autoriser l'accès direct aux fichiers
<FilesMatch "\.(pdf|jpg|jpeg|png|gif|webp|doc|docx)$">
    Allow from all
</FilesMatch>
HTACCESS;

if (file_exists($htaccessFile)) {
    echo "✓ Fichier '.htaccess' existe déjà\n";
} else {
    if (file_put_contents($htaccessFile, $htaccessContent)) {
        echo "✓ Fichier '.htaccess' créé (protection des scripts PHP)\n";
    } else {
        echo "⚠ Impossible de créer '.htaccess' (non critique)\n";
    }
}

// Créer un index.php vide pour prévenir le listing
$indexFile = $uploadsDir . '/index.php';
if (!file_exists($indexFile)) {
    if (file_put_contents($indexFile, "<?php // Protection du dossier\n")) {
        echo "✓ Fichier 'index.php' créé (protection contre le listing)\n";
    }
}

// Vérifier les fichiers PHP requis
echo "\n=== Vérification des fichiers PHP ===\n";
$requiredFiles = [
    'upload.php' => 'Endpoint d\'upload des fichiers',
    'download.php' => 'Endpoint de téléchargement des fichiers',
    'dashboard_api.php' => 'API du dashboard'
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✓ $file ($description)\n";
    } else {
        echo "✗ $file ($description) - MANQUANT\n";
    }
}

// Vérifier la base de données
echo "\n=== Vérification de la base de données ===\n";
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=aecs_dashboard;charset=utf8mb4', 'root', 'Aecs17Villa');
    
    // Vérifier la colonne piece_jointe
    $stmt = $pdo->query("SHOW COLUMNS FROM coord_activites LIKE 'piece_jointe'");
    if ($stmt->fetch()) {
        echo "✓ Colonne 'piece_jointe' existe dans 'coord_activites'\n";
    } else {
        echo "✗ Colonne 'piece_jointe' manquante\n";
        echo "  Exécutez : ALTER TABLE coord_activites ADD COLUMN piece_jointe varchar(500);\n";
    }
} catch (Exception $e) {
    echo "✗ Erreur de connexion BD : " . $e->getMessage() . "\n";
}

echo "\n=== Configuration terminée ===\n";
echo "Les documents seront stockés dans le dossier 'uploads/'\n";
echo "Taille maximale : 10 MB par fichier\n";
echo "Formats acceptés : PDF, JPG, PNG, GIF, WEBP, DOC, DOCX\n";
