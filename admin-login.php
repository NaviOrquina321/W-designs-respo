<?php
// admin-login.php - Sign-in page for the separate TutorLink Admin Portal
require_once __DIR__ . '/api/config.php';

// Already signed in as admin on this portal: go straight to the dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TutorLink Admin Sign In</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body.admin-login-body { min-height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center; background: var(--paper, #faf9f6); font-family: 'Inter', sans-serif; color: var(--ink, #121212); }
    .admin-login-card { width: 100%; max-width: 400px; margin: 24px; padding: 32px; background: #fff; border: 1px solid var(--line, #d9d6cf); border-radius: 6px; box-sizing: border-box; }
    .admin-login-card h1 { margin: 0 0 4px; font-size: 1.5rem; }
    .admin-login-card .sub { margin: 0 0 24px; color: var(--ink-soft, #666); font-size: 0.92rem; }
    .admin-login-card label { display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 600; }
    .admin-login-card input { width: 100%; box-sizing: border-box; padding: 10px 12px; margin-bottom: 16px; border: 1px solid var(--line, #d9d6cf); border-radius: 4px; font: inherit; }
    .admin-login-card button { width: 100%; padding: 11px; border: 0; border-radius: 4px; background: var(--ink, #121212); color: #fff; font: inherit; font-weight: 600; cursor: pointer; }
    .admin-login-card button:disabled { opacity: 0.6; cursor: wait; }
    #admin-login-error { min-height: 1.2em; margin: 0 0 12px; color: #b42318; font-size: 0.88rem; }
    .admin-login-card .back { display: block; margin-top: 18px; text-align: center; font-size: 0.85rem; color: var(--ink-soft, #666); }
  </style>
</head>
<body class="admin-login-body">
  <main class="admin-login-card">
    <h1>TutorLink Admin</h1>
    <p class="sub">Administrator sign in. This portal is separate from the main site.</p>

    <form id="admin-login-form">
      <label for="admin-email">Email address</label>
      <input type="email" id="admin-email" required autocomplete="username" placeholder="admin@tutorlink.ph">

      <label for="admin-password">Password</label>
      <input type="password" id="admin-password" required autocomplete="current-password" placeholder="••••••••">

      <p id="admin-login-error" role="alert"></p>
      <button type="submit" id="admin-login-btn">Sign in to Admin Portal</button>
    </form>

    <a class="back" href="index.php" target="_blank" rel="noopener">Open the main TutorLink site in a new tab</a>
  </main>

  <script>
    document.getElementById('admin-login-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = document.getElementById('admin-login-btn');
      const err = document.getElementById('admin-login-error');
      err.textContent = '';
      btn.disabled = true;

      try {
        const res = await fetch('api/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-TutorLink-Portal': 'admin' },
          body: JSON.stringify({
            email: document.getElementById('admin-email').value,
            password: document.getElementById('admin-password').value
          })
        });
        const data = await res.json();
        if (!res.ok || data.status !== 'success') {
          err.textContent = data.message || 'Sign in failed.';
          btn.disabled = false;
          return;
        }
        window.location.href = 'admin.php';
      } catch (ex) {
        err.textContent = 'Could not connect to the server.';
        btn.disabled = false;
      }
    });
  </script>
</body>
</html>
