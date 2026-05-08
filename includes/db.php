<?php
// includes/db.php configuration

function getDB() {
    $host = 'localhost';
    $db   = 'wilddocs';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    try {
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

function fetchAllRequests($pdo) {
    // Fetches the most recent 10 requests for the dashboard
    $stmt = $pdo->query("SELECT * FROM requests ORDER BY created_at DESC LIMIT 10");
    return $stmt->fetchAll();
}

function getDashboardStats($pdo) {
    $stats = ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0];
    
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM requests GROUP BY status");
    $results = $stmt->fetchAll();
    
    foreach ($results as $row) {
        $stats['total'] += $row['count'];
        $stats[$row['status']] = $row['count'];
    }
    return $stats;
}