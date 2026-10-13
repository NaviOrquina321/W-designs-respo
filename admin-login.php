<?php
// admin-login.php - Dedicated Administrator Login Portal
require_once __DIR__ . '/api/config.php';

// If already logged in as admin, redirect directly to admin.php
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin.php');
    exit;
}

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errorMsg = 'Please enter email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT user_id, email, password_hash, role, name FROM users WHERE LOWER(email) = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = 'admin';
            $_SESSION['name'] = $user['name'];

            header('Location: admin.php');
            exit;
        } else {
            $errorMsg = 'Invalid administrator credentials.';
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body.admin-login-body {
      background: var(--oxford-navy);
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
      color: var(--ink);
      padding: 40px;
      border-radius: 8px;
      max-width: 420px;
      width: 90%;
      box-shadow: 0 20px 50px rgba(0,0,0,0.3);
      border: 1px solid var(--warm-gold);
    }
    .admin-login-head {
      text-align: center;
      margin-bottom: 28px;
    }
    .admin-login-head h2 {
      font-family: 'Cinzel', serif;
      font-size: 1.8rem;
      color: var(--oxford-navy);
      margin-bottom: 6px;
    }
    .admin-login-head p {
      color: var(--muted);
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
        <img src="assets/logo.png" alt="TutorLink Logo" style="height: 42px; width: 42px; object-fit: contain; border-radius: 50%;" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'42\' height=\'42\' viewBox=\'0 0 42 42\'><circle cx=\'21\' cy=\'21\' r=\'20\' fill=\'%230b132a\'/><text x=\'50%\' y=\'55%\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%23c5a059\' font-family=\'sans-serif\' font-size=\'15\' font-weight=\'bold\'>TL</text></svg>';">
        <h2>TutorLink Admin</h2>
      </div>
      <p>System Administrator Authentication</p>
    </div>

    <?php if (!empty($errorMsg)): ?>
      <div class="alert-error"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <form method="POST" action="admin-login.php">
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
