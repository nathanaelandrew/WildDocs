<?php
// update_status.php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($id && $status) {
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
        $success = $stmt->execute([$status, $id]);
        echo json_encode(['success' => $success]);
        exit;
    }
}