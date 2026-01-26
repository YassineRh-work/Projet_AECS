<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// Configuration
$uploadDir = 'uploads/';
$maxFileSize = 10 * 1024 * 1024; // 10MB
$allowedMimes = [
    'application/pdf' => 'pdf',
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'
];

// Créer le dossier s'il n'existe pas
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Vérifier si un fichier est envoyé
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No file provided']);
    exit;
}

$file = $_FILES['file'];

// Validation
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Upload error: ' . $file['error']]);
    exit;
}

if ($file['size'] > $maxFileSize) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'File too large (max 10MB)']);
    exit;
}

// Vérifier le type MIME
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, array_keys($allowedMimes))) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'File type not allowed. Allowed: PDF, JPG, PNG, GIF, WEBP, DOC, DOCX']);
    exit;
}

// Générer un nom de fichier unique et sécurisé
$originalName = pathinfo($file['name'], PATHINFO_FILENAME);
$extension = $allowedMimes[$mimeType];
$filename = uniqid('doc_' . $_SESSION['user_id'] . '_') . '.' . $extension;
$filepath = $uploadDir . $filename;

// Sauvegarder le fichier
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save file']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'filename' => $filename,
    'originalName' => $originalName,
    'filepath' => $filepath
]);
