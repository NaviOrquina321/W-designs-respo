<?php
// admin-login.php - Dedicated Administrator Login Portal
require_once __DIR__ . '/api/config.php';

// If already logged in as admin, redirect directly to admin.php
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin.php');
    exit;
}

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errorMsg = 'Invalid session CSRF token. Please refresh and try again.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $errorMsg = 'Please enter email and password.';
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $now = time();
            $cutoff = $now - 900; // 15 min lock

            $pdo->exec("CREATE TABLE IF NOT EXISTS `failed_login_attempts` (
              `id` INTEGER PRIMARY KEY AUTOINCREMENT,
              `email` VARCHAR(100) NOT NULL,
              `ip_address` VARCHAR(45) NOT NULL,
              `attempt_time` INT NOT NULL
            );");

            // Check rate limit
            $stLock = $pdo->prepare("SELECT COUNT(*) as cnt FROM failed_login_attempts WHERE email = ? AND ip_address = ? AND attempt_time > ?");
            $stLock->execute([$email, $ip, $cutoff]);
            if ($stLock->fetch()['cnt'] >= 5) {
                $errorMsg = 'Too many failed login attempts. Please try again in 15 minutes.';
            } else {
                $stmt = $pdo->prepare("SELECT user_id, email, password_hash, role, name FROM users WHERE LOWER(email) = ? AND role = 'admin'");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                $dummyHash = '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1e8c9gS/p.U.pGZ40U45LqU3q1r5nGO';

                if (!$user) {
                    password_verify($password, $dummyHash); // timing attack protection
                    $stFail = $pdo->prepare("INSERT INTO failed_login_attempts (email, ip_address, attempt_time) VALUES (?, ?, ?)");
                    $stFail->execute([$email, $ip, $now]);
                    $errorMsg = 'Invalid administrator credentials.';
                } elseif (!password_verify($password, $user['password_hash'])) {
                    $stFail = $pdo->prepare("INSERT INTO failed_login_attempts (email, ip_address, attempt_time) VALUES (?, ?, ?)");
                    $stFail->execute([$email, $ip, $now]);
                    $errorMsg = 'Invalid administrator credentials.';
                } else {
                    // Clear failed attempts
                    $stClr = $pdo->prepare("DELETE FROM failed_login_attempts WHERE email = ? AND ip_address = ?");
                    $stClr->execute([$email, $ip]);

                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = 'admin';
                    $_SESSION['name'] = $user['name'];

                    header('Location: admin.php');
                    exit;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TutorLink Admin Login</title>
  <link rel="icon" type="image/png" href="assets/favicon.png">
  <link rel="stylesheet" href="styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body.admin-login-body {
      background: var(--oxford-navy, #0b132a);
      color: #ffffff;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .admin-login-card {
      background: #ffffff;
      color: var(--ink, #0b132a);
      padding: 40px;
      border-radius: 8px;
      max-width: 420px;
      width: 90%;
      box-shadow: 0 20px 50px rgba(0,0,0,0.3);
      border: 1px solid var(--warm-gold, #c5a059);
    }
    .admin-login-head {
      text-align: center;
      margin-bottom: 28px;
    }
    .admin-login-head h2 {
      font-family: 'Cinzel', serif;
      font-size: 1.8rem;
      color: var(--oxford-navy, #0b132a);
      margin-bottom: 6px;
    }
    .admin-login-head p {
      color: var(--muted, #64748b);
      font-size: 0.9rem;
    }
    .alert-error {
      background: #fee2e2;
      color: #991b1b;
      padding: 10px 14px;
      border-radius: 4px;
      font-size: 0.88rem;
      margin-bottom: 20px;
      border: 1px solid #fca5a5;
    }
  </style>
</head>
<body class="admin-login-body">

  <div class="admin-login-card">
    <div class="admin-login-head">
      <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 12px;">
        <img src="assets/logo.png" alt="TutorLink Logo" style="height: 42px; width: 42px; object-fit: contain; border-radius: 50%;" onerror="this.style.display='none'">
        <h2>TutorLink Admin</h2>
      </div>
      <p>System Administrator Authentication</p>
    </div>

    <?php if (!empty($errorMsg)): ?>
      <div class="alert-error"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <form method="POST" action="admin-login.php">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <div class="form-group">
        <label>Admin Email</label>
        <input type="email" name="email" class="form-input" required placeholder="admin@tutorlink.ph" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-input" required placeholder="••••••••">
      </div>

      <button type="submit" class="btn btn-primary full-width margin-top">Log In to Admin Portal</button>
    </form>
  </div>

</body>
</html>
