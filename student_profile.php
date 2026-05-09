<?php
// user_profile.php — WildDocuments User Profile
session_start();

// PREVIEW MODE: Prevents redirect if testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 999;
    $_SESSION['user_name'] = "Juan dela Cruz";
}

// Mock Data (Fetched from DB in real scenario)
$user = [
    'first_name' => 'Juan',
    'last_name'  => 'dela Cruz',
    'student_id' => '2021-00123',
    'program'    => 'BS Computer Science',
    'email'      => 'juan.delacruz@university.edu.ph',
    'phone'      => '0912 345 6789',
    'join_date'  => 'Oct 2023'
];

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logic for updating would go here
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — WildDocuments</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Profile Specific Styles */
        .profile-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            padding: 32px;
            margin-bottom: 24px;
        }
        .profile-header-flex {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
        }
        .avatar-circle {
            width: 80px; height: 80px;
            background: var(--pink-bg);
            color: var(--crimson);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; font-weight: 700;
            border: 3px solid var(--white);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .profile-title h2 { margin: 0; font-size: 1.4rem; color: var(--text-dark); }
        .profile-title p { margin: 4px 0 0; color: var(--text-muted); font-size: 0.9rem; }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .read-only-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            border-radius: 8px;
            color: #64748b;
            font-size: 0.95rem;
            cursor: not-allowed;
        }
        .section-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        
        .alert-success {
            background: #f0fdf4;
            border-left: 4px solid #16a34a;
            color: #166534;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>

<?php 
if (file_exists('includes/student_navbar.php')) include 'includes/student_navbar.php'; 
?>

<div class="app-layout">
    <?php if (file_exists('includes/student_sidebar.php')) include 'includes/student_sidebar.php'; ?>

    <main style="padding: 40px 0 64px; flex: 1; background: var(--bg-light); overflow-x: hidden;">
        <div class="container">
            
            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="alert-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Profile settings updated successfully.</span>
                </div>
            <?php endif; ?>

            <div class="profile-card">
                <div class="profile-header-flex">
                    <div class="avatar-circle">
                        <?= substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1) ?>
                    </div>
                    <div class="profile-title">
                        <h2>Personal Information</h2>
                        <p>Manage your basic account details and contact information.</p>
                    </div>
                </div>

                <form method="POST" action="user_profile.php">
                    <div class="info-grid">
                        <div class="form-group">
                            <label class="section-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="section-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>">
                        </div>
                    </div>

                    <div class="info-grid">
                        <div class="form-group">
                            <label class="section-label">University Email</label>
                            <div class="read-only-box"><?= $user['email'] ?></div>
                            <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 6px;">Email is managed by the registrar and cannot be changed.</p>
                        </div>
                        <div class="form-group">
                            <label class="section-label">Contact Number</label>
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                        </div>
                    </div>

                    <div style="margin-top: 12px; border-top: 1px solid #f1f5f9; padding-top: 24px;">
                        <button type="submit" class="btn btn-primary">Save Profile Changes</button>
                    </div>
                </form>
            </div>

            <div class="profile-card">
                <h2 style="font-size: 1.2rem; margin-bottom: 20px;">Academic Details</h2>
                <div class="info-grid">
                    <div>
                        <span class="section-label">Student ID</span>
                        <div class="read-only-box"><?= $user['student_id'] ?></div>
                    </div>
                    <div>
                        <span class="section-label">Degree Program</span>
                        <div class="read-only-box"><?= $user['program'] ?></div>
                    </div>
                    <div>
                        <span class="section-label">Account Status</span>
                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 8px;">
                            <span style="width: 10px; height: 10px; background: #16a34a; border-radius: 50%;"></span>
                            <span style="font-weight: 600; color: #16a34a; font-size: 0.9rem;">Verified Student</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="profile-card" style="border: 1px solid #fee2e2;">
                <h2 style="font-size: 1.1rem; color: #b91c1c; margin-bottom: 10px;">Security</h2>
                <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 20px;">Need to change your password? Protect your account with a strong password.</p>
                <a href="#" class="btn btn-outline" style="border-color: #fca5a5; color: #b91c1c;">Update Password</a>
            </div>

        </div>
    </main>
</div>

<?php if (file_exists('includes/footer.php')) include 'includes/footer.php'; ?>

</body>
</html>