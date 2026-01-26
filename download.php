<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

$filename = $_GET['file'] ?? null;
if (!$filename) {
    http_response_code(404);
    echo "File not found";
    exit;
}

// Valider le nom de fichier (éviter les path traversal)
if (strpos($filename, '/') !== false || strpos($filename, '\\') !== false || strpos($filename, '..') !== false) {
    http_response_code(403);
    echo "Invalid filename";
    exit;
}

$filepath = 'uploads/' . $filename;

if (!file_exists($filepath)) {
    http_response_code(404);
    echo "File not found";
    exit;
}

// Déterminer le type MIME
$ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
$mimeTypes = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

$mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';

// Envoyer le fichier
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filepath));
header('Content-Disposition: inline; filename="' . basename($filepath) . '"');

readfile($filepath);
