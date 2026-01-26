<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Réponse immédiate au preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = '127.0.0.1';
$port = 3306;
$db   = 'aecs_dashboard';
$user = 'root';
$pass = 'Aecs17Villa';
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
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'DB connection failed',
        'error'   => $e->getMessage()
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

/**
 * GET : renvoie toutes les données
 */
if ($method === 'GET') {
    try {
        $activities = $pdo->query("SELECT * FROM activites ORDER BY date, heure_debut")->fetchAll();
        $coord      = $pdo->query("SELECT * FROM coord_activites ORDER BY date")->fetchAll();

        echo json_encode([
            'status'          => 'success',
            'activities'      => $activities,
            'coordActivities' => $coord
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Query failed',
            'error'   => $e->getMessage()
        ]);
    }
    exit;
}

/**
 * POST : remplace le contenu des tables par les données envoyées
 */
if ($method === 'POST') {
    $inputRaw = file_get_contents('php://input');
    $input    = json_decode($inputRaw, true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Invalid JSON',
            'raw'     => $inputRaw
        ]);
        exit;
    }

    $activities      = $input['activities']      ?? [];
    $coordActivities = $input['coordActivities'] ?? [];

    try {
        // Vider les tables
        $pdo->exec("TRUNCATE TABLE activites");
        $pdo->exec("TRUNCATE TABLE coord_activites");

        // ========= ACTIVITES (onglet Équipe opérationnelle) =========
        $stmtA = $pdo->prepare("
            INSERT INTO activites
            (projet, mois, date, periode, objectif, type_atelier, responsable,
             lieu, heure_debut, heure_fin, duree, participants, commentaire, statut)
            VALUES
            (:projet, :mois, :date, :periode, :objectif, :type_atelier, :responsable,
             :lieu, :heure_debut, :heure_fin, :duree, :participants, :commentaire, :statut)
        ");

        foreach ($activities as $a) {
            $stmtA->execute([
                ':projet'       => $a['projet']      ?? null,
                ':mois'         => $a['mois']        ?? null,
                ':date'         => $a['date']        ?? null,
                ':periode'      => $a['periode']     ?? null,
                ':objectif'     => $a['objectif']    ?? null,
                ':type_atelier' => ($a['typeAtelier'] ?? $a['type'] ?? null),
                ':responsable'  => $a['responsable'] ?? null,
                ':lieu'         => $a['lieu']        ?? null,
                ':heure_debut'  => $a['heureDebut']  ?? null,
                ':heure_fin'    => $a['heureFin']    ?? null,
                ':duree'        => $a['duree']       ?? null,
                ':participants' => $a['participants']?? null,
                ':commentaire'  => $a['commentaire'] ?? null,
                ':statut'       => $a['statut']      ?? 'Prévu',
            ]);
        }

        // ========= COORD_ACTIVITES (onglet Coordinateur) =========
        // Assure‑toi que la table a bien ces colonnes :
        // projet, mois, date, responsable, activite, public_cible,
        // partenaire, periode, type_atelier, lieu, duree_prep, statut, commentaires
        $stmtC = $pdo->prepare("
            INSERT INTO coord_activites
            (projet, mois, date, responsable, activite, public_cible,
             partenaire, periode, type_atelier, lieu, duree_prep, statut, commentaires)
            VALUES
            (:projet, :mois, :date, :responsable, :activite, :public_cible,
             :partenaire, :periode, :type_atelier, :lieu, :duree_prep, :statut, :commentaires)
        ");

        foreach ($coordActivities as $c) {
            $stmtC->execute([
                ':projet'       => $c['projet']      ?? null,
                ':mois'         => $c['mois']        ?? null,
                ':date'         => $c['date']        ?? null,
                ':responsable'  => $c['responsable'] ?? null,
                ':activite'     => $c['activite']    ?? null,
                ':public_cible' => $c['public']      ?? null,
                ':partenaire'   => $c['partenaire']  ?? null,
                ':periode'      => $c['periode']     ?? null,
                ':type_atelier' => $c['typeAtelier'] ?? null,
                ':lieu'         => $c['lieu']        ?? null,
                ':duree_prep'   => $c['dureePrep']   ?? null,
                ':statut'       => $c['statut']      ?? null,
                ':commentaires' => $c['commentaires']?? null,
            ]);
        }

        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Save failed',
            'error'   => $e->getMessage()
        ]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
