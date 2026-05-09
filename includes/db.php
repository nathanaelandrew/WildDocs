<?php
// includes/db.php

function getDB() {
    // Get these from Supabase Project Settings -> Database -> Connection String (PHP)
    $host     = 'db.ynfkvcxdnsihgmneedeu.supabase.co';
    $port     = '5432';
    $dbname   = 'postgres';
    $user     = 'postgres';
    $password = 'CSIT226InformationManagement'; // Use the password you set when creating the project

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

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
    // 1. Initialize ALL keys with 0 so they are never "undefined"
    $stats = [
        'total'    => 0, 
        'pending'  => 0, 
        'paid'     => 0, 
        'approved' => 0, 
        'released' => 0
    ];
    
    try {
        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM requests GROUP BY status");
        $results = $stmt->fetchAll();
        
        foreach ($results as $row) {
            $status = $row['status'];
            $count  = (int)$row['count'];
            
            $stats['total'] += $count;
            
            // Only update if the status from DB matches one of our keys
            if (array_key_exists($status, $stats)) {
                $stats[$status] = $count;
            }
        }
    } catch (PDOException $e) {
        // Handle error or log it
    }

    return $stats;
}

// Fetch the list of requests
// Fetch requests for the Admin Dashboard (with Student details)
function fetchRecentRequests($pdo, $limit = 10) {
    // We JOIN requests with users (for names) and students (for student_number and program)
    $sql = "SELECT 
                r.*, 
                u.first_name, u.last_name, 
                s.student_number, s.program, s.year_level
            FROM requests r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN students s ON u.id = s.user_id
            ORDER BY r.created_at DESC 
            LIMIT :limit";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Fetch requests for a specific student
function fetchStudentRequests($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function getUnviewedCount($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM requests WHERE is_viewed = FALSE");
    return $stmt->fetchColumn();
}