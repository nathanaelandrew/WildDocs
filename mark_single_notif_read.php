<?php
// mark_single_notif_read.php
session_start();
require_once 'includes/db.php';
header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$notifId = $data['id'] ?? null;

if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin' && $notifId) {
    try {
        $pdo = getDB();
        // Update ONLY this specific request to viewed
        $stmt = $pdo->prepare("UPDATE requests SET is_viewed = TRUE WHERE id = ?");
        $stmt->execute([$notifId]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or missing ID']);
}