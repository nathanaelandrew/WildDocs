<?php
// update_status.php
session_start();
require_once 'includes/db.php';

// 1. Security Check: Only allow logged-in admins
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// 2. Get the data from the POST request
$data = json_decode(file_get_contents('php://input'), true);
$requestId = $data['requestId'] ?? null;
$newStatus = $data['status'] ?? null;

if ($requestId && $newStatus) {
    try {
        $pdo = getDB();
        
        // 3. Update the database
        // Find this line in update_status.php and change it to:
        $stmt = $pdo->prepare("UPDATE requests SET status = ?, is_viewed = TRUE WHERE id = ?");
        $result = $stmt->execute([$newStatus, $requestId]);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Update failed']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
}