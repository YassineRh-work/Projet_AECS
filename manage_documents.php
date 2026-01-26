<?php
/**
 * Page de gestion des fichiers uploadés
 * Affiche la liste et permet la suppression
 */
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$uploadsDir = 'uploads/';
$files = [];

if (is_dir($uploadsDir)) {
    $allFiles = scandir($uploadsDir);
    foreach ($allFiles as $file) {
        if ($file !== '.' && $file !== '..' && $file !== 'index.php' && $file !== '.htaccess') {
            $filepath = $uploadsDir . $file;
            $files[] = [
                'name' => $file,
                'size' => filesize($filepath),
                'modified' => filemtime($filepath),
                'type' => pathinfo($file, PATHINFO_EXTENSION)
            ];
        }
    }
}

// Trier par date de modification (plus récent d'abord)
usort($files, function($a, $b) {
    return $b['modified'] - $a['modified'];
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Documents - AECS Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 20px;
        }
        
        h1 {
            color: #333;
            font-size: 28px;
        }
        
        .user-info {
            color: #666;
            font-size: 14px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 14px;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .stat-card .value {
            font-size: 28px;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        thead {
            background: #f5f5f5;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .file-icon {
            display: inline-block;
            margin-right: 8px;
        }
        
        .file-name {
            color: #2196F3;
            text-decoration: none;
            font-weight: 500;
        }
        
        .file-name:hover {
            text-decoration: underline;
        }
        
        .file-size {
            color: #666;
            font-size: 13px;
        }
        
        .file-date {
            color: #999;
            font-size: 13px;
            white-space: nowrap;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }
        
        .btn-download {
            background: #4CAF50;
            color: white;
        }
        
        .btn-download:hover {
            background: #45a049;
        }
        
        .btn-delete {
            background: #f44336;
            color: white;
        }
        
        .btn-delete:hover {
            background: #da190b;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state p {
            font-size: 16px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #2196F3;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📂 Gestion des Documents</h1>
            <div class="user-info">
                <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?>
                <br>
                <a href="dashboard.php" class="back-link">← Retour au Dashboard</a>
            </div>
        </header>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total fichiers</h3>
                <div class="value"><?php echo count($files); ?></div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h3>Espace utilisé</h3>
                <div class="value">
                    <?php 
                    $totalSize = array_sum(array_column($files, 'size'));
                    echo number_format($totalSize / 1024 / 1024, 2) . ' MB';
                    ?>
                </div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h3>Stockage max</h3>
                <div class="value">10 MB</div>
            </div>
        </div>
        
        <?php if (count($files) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nom du fichier</th>
                        <th>Taille</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $file): ?>
                        <tr>
                            <td>
                                <span class="file-icon">
                                    <?php
                                    $ext = strtolower($file['type']);
                                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                        echo '🖼️';
                                    } elseif ($ext === 'pdf') {
                                        echo '📄';
                                    } elseif (in_array($ext, ['doc', 'docx'])) {
                                        echo '📝';
                                    } else {
                                        echo '📎';
                                    }
                                    ?>
                                </span>
                                <a href="download.php?file=<?php echo urlencode($file['name']); ?>" 
                                   class="file-name" target="_blank">
                                    <?php echo htmlspecialchars($file['name']); ?>
                                </a>
                            </td>
                            <td>
                                <span class="file-size">
                                    <?php echo number_format($file['size'] / 1024, 2) . ' KB'; ?>
                                </span>
                            </td>
                            <td>
                                <span style="background: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                    <?php echo strtoupper($file['type']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="file-date">
                                    <?php echo date('d/m/Y H:i', $file['modified']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="download.php?file=<?php echo urlencode($file['name']); ?>" 
                                       class="btn btn-download" target="_blank">⬇️ Télécharger</a>
                                    <button class="btn btn-delete" 
                                            onclick="deleteFile('<?php echo htmlspecialchars($file['name'], ENT_QUOTES); ?>')">
                                        🗑️ Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p>Aucun fichier uploadé pour le moment</p>
                <p style="font-size: 14px; margin-top: 10px; color: #bbb;">Les documents seront affichés ici après upload</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function deleteFile(filename) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?\n\n' + filename)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('filename', filename);
            
            fetch('delete_file.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Fichier supprimé avec succès');
                    location.reload();
                } else {
                    alert('Erreur : ' + data.message);
                }
            })
            .catch(err => {
                alert('Erreur lors de la suppression : ' + err);
            });
        }
    </script>
</body>
</html>
