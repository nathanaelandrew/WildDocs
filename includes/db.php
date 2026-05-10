<?php
// includes/db.php

function getDB() {
    // 1. Connection Details
    // We use the 'aws-1...' address because it has better DNS stability than the '.co' address
    $host     = 'aws-1-ap-northeast-1.pooler.supabase.com'; 
    $port     = '6543'; // Port 6543 is less likely to be blocked by Wi-Fi firewalls
    $dbname   = 'postgres';
    $user     = 'postgres.ynfkvcxdnsihgmneedeu'; 
    $password = 'CSIT226InformationManagement'; 

    /**
     * 2. THE UNIVERSAL DSN
     * - sslmode=require: Required by Supabase
     * - sslcert='': Fixes "Permission Denied" and "/var/root" errors on Mac
     */
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;sslcert=''";

    try {
        // Backup to tell the Mac driver to stop looking for root certificates
        putenv("PGSSLCERT=''");

        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT         => false,
            PDO::ATTR_TIMEOUT            => 10
        ]);
        return $pdo;
    } catch (PDOException $e) {
        $error = $e->getMessage();

        // If you still see "could not translate host name", it is 100% your internet
        if (str_contains($error, "could not translate host name") || str_contains($error, "nodename")) {
            die("DNS ERROR: Your computer cannot find Supabase. <br><br><b>Fix:</b> Connect your computer to a <b>Mobile Hotspot</b> and refresh.");
        }

        die("DATABASE CONNECTION FAILED: " . $error);
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
    $sql = "SELECT 
                r.*, 
                u.first_name, u.last_name, 
                s.student_number, s.program, s.year_level,
                dt.name as document_name  -- <--- WE GET THE NAME FROM HERE NOW
            FROM requests r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN students s ON u.id = s.user_id
            JOIN document_types dt ON r.document_type_id = dt.id -- <--- THE JOIN
            ORDER BY r.created_at DESC 
            LIMIT :limit";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Fetch requests for a specific student
// includes/db.php

function fetchStudentRequests($pdo, $user_id) {
    // We JOIN the requests table with the document_types table 
    // to get the 'name' and call it 'document_name'
    $sql = "SELECT 
                r.*, 
                dt.name as document_name 
            FROM requests r
            JOIN document_types dt ON r.document_type_id = dt.id
            WHERE r.user_id = ? 
            ORDER BY r.created_at DESC";
            
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Return empty array if query fails
        return [];
    }
}

function getUnviewedCount($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM requests WHERE is_viewed = FALSE");
    return $stmt->fetchColumn();
}