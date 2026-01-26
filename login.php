<?php
session_start();

// Afficher les erreurs en phase de dev (à désactiver en prod)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Connexion à la base (mêmes paramètres que ton script API)
    $host    = '127.0.0.1';
    $port    = 3306;
    $db      = 'aecs_dashboard';
    $user    = 'root';
    $pass    = 'Aecs17Villa';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (Exception $e) {
        // En prod, loguer l'erreur plutôt que l'afficher
        $error = "Erreur de connexion à la base de données.";
    }

    if (!isset($error)) {
        // Adapter le nom de la table/colonnes à ton schéma réel
        $stmt = $pdo->prepare("SELECT id, username, password, role,pole FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $userRow = $stmt->fetch();

        // Si les mots de passe sont en clair (à éviter en prod)
        if ($userRow && $userRow['password'] === $password) {
            $_SESSION['user_id']   = $userRow['id'];
            $_SESSION['username']  = $userRow['username'];
            // Extraire prénom et nom du username (format prenom_nom)
            $parts = explode('_', $userRow['username']);
            $_SESSION['prenom']    = $parts[0] ?? '';
            $_SESSION['nom']       = $parts[1] ?? '';
            $_SESSION['role']      = $userRow['role'];
            $_SESSION['pole']       = $userRow['pole'];


            header('Location: index.php'); // ou dashboard.php
            exit;
        } else {
            $error = "Identifiant ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion AECS</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <h1 class="login-title">Connexion AECS</h1>

        <?php if (!empty($error)) : ?>
            <p style="color:red;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="username">Identifiant</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-login">Se connecter</button>
        </form>
    </div>
</body>
</html>
