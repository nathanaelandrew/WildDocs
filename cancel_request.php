<?php
// cancel_request.php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

// 1. Auth Check: Only logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$requestId = $data['id'] ?? null;
$userId = $_SESSION['user_id'];

if ($requestId) {
    try {
        $pdo = getDB();

        // 2. Security Check: Ensure this request belongs to the logged-in student AND is still "pending"
        $stmt = $pdo->prepare("SELECT id FROM requests WHERE id = ? AND user_id = ? AND status = 'pending'");
        $stmt->execute([$requestId, $userId]);
        $request = $stmt->fetch();

        if ($request) {
            // 3. Delete from the database
            $delete = $pdo->prepare("DELETE FROM requests WHERE id = ?");
            $delete->execute([$requestId]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cannot cancel. Request is already processed or not found.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
}