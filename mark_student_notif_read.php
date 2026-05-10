<?php
// mark_student_notif_read.php
session_start();
require_once 'includes/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$notifId = $data['id'] ?? null;

if (isset($_SESSION['user_id']) && $notifId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE requests SET student_is_viewed = TRUE WHERE id = ? AND user_id = ?");
    $stmt->execute([$notifId, $_SESSION['user_id']]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}