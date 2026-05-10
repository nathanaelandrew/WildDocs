<?php
// mark_student_notifs_read_all.php
session_start();
require_once 'includes/db.php';
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE requests SET student_is_viewed = TRUE WHERE user_id = ? AND student_is_viewed = FALSE");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}