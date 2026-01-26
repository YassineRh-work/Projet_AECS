<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Session check
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Répondre immédiatement aux requêtes OPTIONS (preflight)
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

if ($method === 'GET') {
    try {
        $activities = $pdo->query("SELECT * FROM activites ORDER BY date, heure_debut")->fetchAll();
        $coord = $pdo->query("SELECT * FROM coord_activites ORDER BY date")->fetchAll();

        echo json_encode([
            'status' => 'success',
            'activities' => $activities,
            'coordActivities' => $coord
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Query failed',
            'error'   => $e->getMessage()
        ]);
    }
    exit;
}

if ($method === 'POST') {
    $inputRaw = file_get_contents('php://input');
    $input = json_decode($inputRaw, true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid JSON',
            'raw'     => $inputRaw
        ]);
        exit;
    }

    $activities      = $input['activities']      ?? [];
    $coordActivities = $input['coordActivities'] ?? [];

    try {
        // Vérification rapide : on refuse si au moins une coordActivity n’a pas de typeAtelier
        foreach ($coordActivities as $c) {
            if (!isset($c['typeAtelier']) || $c['typeAtelier'] === null) {
                throw new Exception("Champ typeAtelier manquant pour une coord_activite");
            }
        }

        // On commence une transaction pour ne pas laisser la base vide en cas d’erreur
        $pdo->beginTransaction();

        $pdo->exec("TRUNCATE TABLE activites");
        $pdo->exec("TRUNCATE TABLE coord_activites");

        $stmtA = $pdo->prepare("
            INSERT INTO activites
            (projet,pole, mois, date, periode, objectif, type_atelier, responsable,
             lieu, heure_debut, heure_fin, duree, participants, commentaire, statut)
            VALUES
            (:projet, :pole, :mois, :date, :periode, :objectif, :type_atelier, :responsable,
             :lieu, :heure_debut, :heure_fin, :duree, :participants, :commentaire, :statut)
        ");

        foreach ($activities as $a) {
            $stmtA->execute([
                ':projet'       => $a['projet']       ?? null,
                ':pole'         => $a['pole']         ?? null,
                ':mois'         => $a['mois']         ?? null,
                ':date'         => $a['date']         ?? null,
                ':periode'      => $a['periode']      ?? null,
                ':objectif'     => $a['objectif']     ?? null,
                ':type_atelier' => ($a['type'] ?? $a['typeAtelier'] ?? 'Non précisé'),
                ':responsable'  => $a['responsable']  ?? null,
                ':lieu'         => $a['lieu']         ?? null,
                ':heure_debut'  => $a['heureDebut']   ?? null,
                ':heure_fin'    => $a['heureFin']     ?? null,
                ':duree'        => $a['duree']        ?? null,
                ':participants' => $a['participants'] ?? null,
                ':commentaire'  => $a['commentaire']  ?? null,
                ':statut'       => $a['statut']       ?? 'Prévu',
            ]);
        }

        $stmtC = $pdo->prepare("
            INSERT INTO coord_activites
            (projet, mois, date, date_fin, responsable, activite, public_cible,
             partenaire, type_atelier, lieu, duree_prep, statut, commentaires, pole,
             description_action_partenaire, description_action_projet, piece_jointe)
            VALUES
            (:projet, :mois, :date, :date_fin, :responsable, :activite, :public_cible,
             :partenaire, :type_atelier, :lieu, :duree_prep, :statut, :commentaires, :pole,
             :description_action_partenaire, :description_action_projet, :piece_jointe)
        ");

        foreach ($coordActivities as $c) {
            $stmtC->execute([
                ':projet'       => $c['projet']       ?? null,
                ':mois'         => $c['mois']         ?? null,
                ':date'         => $c['date']         ?? null,
                ':date_fin'     => $c['dateFin']      ?? null,
                ':responsable'  => $c['responsable']  ?? null,
                ':activite'     => $c['activite']     ?? null,
                ':public_cible' => $c['public']       ?? null,
                ':partenaire'   => $c['partenaire']   ?? null,
                ':type_atelier' => $c['typeAtelier']  ?? 'Non précisé',
                ':lieu'         => $c['lieu']         ?? null,
                ':duree_prep'   => $c['dureePrep']    ?? null,
                ':statut'       => $c['statut']       ?? null,
                ':commentaires' => $c['commentaires'] ?? null,
                ':pole'         => $c['pole']         ?? null,
                ':description_action_partenaire' => $c['actPart'] ?? null,
                ':description_action_projet'     => $c['actProjet'] ?? null,
                ':piece_jointe' => $c['pieceJointe']  ?? null,
            ]);
        }

        $pdo->commit();

        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Save failed',
            'error'   => $e->getMessage()
        ]);
    }
    exit;
}



http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
