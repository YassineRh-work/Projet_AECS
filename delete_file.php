<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$filename = $_POST['filename'] ?? null;
if (!$filename) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No filename provided']);
    exit;
}

// Valider le nom de fichier
if (strpos($filename, '/') !== false || strpos($filename, '\\') !== false || strpos($filename, '..') !== false) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid filename']);
    exit;
}

$filepath = 'uploads/' . $filename;

if (!file_exists($filepath)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'File not found']);
    exit;
}

// Supprimer le fichier
if (unlink($filepath)) {
    echo json_encode(['status' => 'success', 'message' => 'File deleted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete file']);
}
