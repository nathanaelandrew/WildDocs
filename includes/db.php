<?php
// includes/db.php

function getDB() {
    // Get these from Supabase Project Settings -> Database -> Connection String (PHP)
    $host     = 'db.your-project-id.supabase.co';
    $port     = '5432';
    $dbname   = 'postgres';
    $user     = 'postgres';
    $password = 'YOUR_SUPABASE_PASSWORD'; // Use the password you set when creating the project

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";

    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Fetch stats for the 4 top cards
function getDashboardStats($pdo) {
    $stats = ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0];
    
    // In Postgres, we use GROUP BY to count statuses
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM requests GROUP BY status");
    $results = $stmt->fetchAll();
    
    foreach ($results as $row) {
        $stats['total'] += (int)$row['count'];
        $stats[$row['status']] = (int)$row['count'];
    }
    return $stats;
}

// Fetch the list of requests
function fetchRecentRequests($pdo, $limit = 10) {
    $stmt = $pdo->prepare("SELECT * FROM requests ORDER BY created_at DESC LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}