<?php
// mark_notifs_read.php
session_start();
require_once 'includes/db.php';
header('Content-Type: application/json');

if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
    try {
        $pdo = getDB();
        // Mark all requests as viewed
        $stmt = $pdo->prepare("UPDATE requests SET is_viewed = TRUE WHERE is_viewed = FALSE");
        $stmt->execute();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}