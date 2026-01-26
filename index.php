<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil AECS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="logo.svg">
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <header>
        <div class="logo">
            <div class="user-tooltip"><?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom'] . ' (' . $_SESSION['role'] . ')', ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <div class="header-right">
            <nav>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                </ul>
            </nav>
                <div class="user-info">
                    👤🌐
                    <div class="user-tooltip"><?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom'] . ' (' . $_SESSION['role'] . ')', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            <button onclick="window.location.href='logout.php'" class="btn-logout">⏻ Déconnexion</button>
        </div>
    </header>
    
    <main>
        <section class="hero">
            <h2>Bienvenue sur le site de l'AECS</h2>
            <img src="logo.svg" alt="Logo AECS" class="hero-logo">
        </section>
    </main>
    
    <footer>
        <p>&copy; AECS 2025-2026</p>
    </footer>
</body>
</html>