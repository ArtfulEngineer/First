<?php
session_start();
include "db_conn.php";

// Redirect if already logged in
if (isset($_SESSION['admin'])) {
    header("Location: dashboard.php");
    exit();
}

// ── Brute-force protection ──────────────────────────────────────────────────
const MAX_ATTEMPTS  = 5;
const LOCKOUT_TIME  = 15 * 60; // 15 minutes in seconds

if (!isset($_SESSION['login_attempts']))  $_SESSION['login_attempts']  = 0;
if (!isset($_SESSION['last_attempt_time'])) $_SESSION['last_attempt_time'] = 0;

$locked_out  = false;
$remaining   = 0;
$error       = '';
$success     = false;

// Check lockout
if ($_SESSION['login_attempts'] >= MAX_ATTEMPTS) {
    $elapsed   = time() - $_SESSION['last_attempt_time'];
    $remaining = LOCKOUT_TIME - $elapsed;
    if ($remaining > 0) {
        $locked_out = true;
    } else {
        // Lockout expired — reset
        $_SESSION['login_attempts']  = 0;
        $_SESSION['last_attempt_time'] = 0;
    }
}

// ── Handle login POST ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login']) && !$locked_out) {

    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Please fill in all fields.';
        } else {
            $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $row    = $result->fetch_assoc();
            $stmt->close();

            if ($row && password_verify($password, $row['password'])) {
                // Successful login — reset counters
                $_SESSION['login_attempts']  = 0;
                $_SESSION['last_attempt_time'] = 0;
                session_regenerate_id(true);
                $_SESSION['admin']      = $row['username'];
                $_SESSION['login_time'] = time();
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
                $attempts_left = MAX_ATTEMPTS - $_SESSION['login_attempts'];

                if ($attempts_left <= 0) {
                    $locked_out = true;
                    $remaining  = LOCKOUT_TIME;
                    $error = 'Too many failed attempts. Account locked for 15 minutes.';
                } else {
                    $error = "Invalid credentials. {$attempts_left} attempt(s) remaining.";
                }
            }
        }
    }
}

// ── Generate CSRF token ─────────────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Lockout countdown (minutes:seconds)
$lockout_display = '';
if ($locked_out && $remaining > 0) {
    $mins = floor($remaining / 60);
    $secs = $remaining % 60;
    $lockout_display = sprintf('%d:%02d', $mins, $secs);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — AI Solutions</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:          #0a0a0f;
      --surface:     #111118;
      --surface-2:   #16161f;
      --border:      rgba(255,255,255,0.07);
      --border-hover:rgba(255,255,255,0.15);
      --accent:      #00e5ff;
      --accent-dim:  rgba(0,229,255,0.1);
      --accent-glow: rgba(0,229,255,0.25);
      --danger:      #ff4d6a;
      --danger-dim:  rgba(255,77,106,0.12);
      --text:        #e8e8f0;
      --muted:       #6b6b80;
      --radius:      10px;
    }

    html, body {
      height: 100%;
      background: var(--bg);
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
    }

    /* Grid + glow bg */
    body::before {
      content: '';
      position: fixed; inset: 0;
      background-image:
        linear-gradient(rgba(0,229,255,0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,229,255,0.025) 1px, transparent 1px);
      background-size: 60px 60px;
      pointer-events: none;
    }
    body::after {
      content: '';
      position: fixed;
      bottom: -300px; left: 50%;
      transform: translateX(-50%);
      width: 800px; height: 800px;
      background: radial-gradient(circle, rgba(0,229,255,0.06) 0%, transparent 65%);
      pointer-events: none;
    }

    /* ── Layout ── */
    .page {
      position: relative;
      z-index: 1;
      min-height: 100vh;
      display: grid;
      grid-template-columns: 1fr 480px 1fr;
      grid-template-rows: 1fr auto 1fr;
      align-items: center;
    }

    /* ── Back link ── */
    .back-link {
      position: fixed;
      top: 24px; left: 28px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 0.82rem;
      font-weight: 500;
      color: var(--muted);
      text-decoration: none;
      transition: color 0.2s;
      z-index: 10;
    }
    .back-link:hover { color: var(--text); }
    .back-link svg { transition: transform 0.2s; }
    .back-link:hover svg { transform: translateX(-3px); }

    /* ── Card ── */
    .card {
      grid-column: 2;
      grid-row: 2;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 48px 44px 44px;
      animation: fadeUp 0.6s ease both;
      box-shadow: 0 40px 80px rgba(0,0,0,0.5);
    }

    /* ── Brand ── */
    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 36px;
    }
    .brand-icon {
      width: 40px; height: 40px;
      border-radius: 10px;
      background: var(--accent-dim);
      border: 1px solid rgba(0,229,255,0.25);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.1rem;
    }
    .brand-name {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.4rem;
      letter-spacing: 0.08em;
      color: var(--accent);
    }

    /* ── Headings ── */
    .card h1 {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 2.6rem;
      letter-spacing: 0.04em;
      line-height: 1;
      margin-bottom: 6px;
    }
    .card .subtitle {
      font-size: 0.88rem;
      color: var(--muted);
      margin-bottom: 32px;
    }

    /* ── Divider ── */
    .divider {
      height: 1px;
      background: var(--border);
      margin-bottom: 32px;
    }

    /* ── Form ── */
    .field {
      margin-bottom: 20px;
    }
    label {
      display: block;
      font-size: 0.78rem;
      font-weight: 500;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 8px;
    }
    .input-wrap {
      position: relative;
    }
    .input-icon {
      position: absolute;
      left: 14px; top: 50%;
      transform: translateY(-50%);
      color: var(--muted);
      pointer-events: none;
      display: flex;
    }
    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 13px 14px 13px 42px;
      background: var(--surface-2);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.95rem;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    input:focus {
      border-color: rgba(0,229,255,0.4);
      box-shadow: 0 0 0 3px var(--accent-dim);
    }
    input::placeholder { color: var(--muted); }
    input:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }

    /* Toggle password */
    .toggle-pw {
      position: absolute;
      right: 14px; top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--muted);
      cursor: pointer;
      padding: 2px;
      display: flex;
      transition: color 0.2s;
    }
    .toggle-pw:hover { color: var(--text); }

    /* ── Alert ── */
    .alert {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 13px 16px;
      border-radius: var(--radius);
      font-size: 0.875rem;
      margin-bottom: 24px;
      animation: shake 0.4s ease;
    }
    .alert-error {
      background: var(--danger-dim);
      border: 1px solid rgba(255,77,106,0.25);
      color: #ff8096;
    }
    .alert-lock {
      background: rgba(255,160,0,0.1);
      border: 1px solid rgba(255,160,0,0.25);
      color: #ffb84d;
    }

    /* ── Submit button ── */
    .btn-submit {
      width: 100%;
      padding: 14px;
      margin-top: 8px;
      background: var(--accent);
      color: #0a0a0f;
      border: none;
      border-radius: var(--radius);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      box-shadow: 0 0 20px var(--accent-glow);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    .btn-submit:hover:not(:disabled) {
      background: #33eaff;
      box-shadow: 0 0 30px rgba(0,229,255,0.5);
      transform: translateY(-1px);
    }
    .btn-submit:disabled {
      opacity: 0.4;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    /* ── Footer note ── */
    .card-footer {
      margin-top: 28px;
      padding-top: 20px;
      border-top: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .card-footer p {
      font-size: 0.78rem;
      color: var(--muted);
    }
    .attempts-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 4px 10px;
      border-radius: 100px;
      background: var(--surface-2);
      border: 1px solid var(--border);
      font-size: 0.75rem;
      color: var(--muted);
    }
    .attempts-badge.warn { color: #ffb84d; border-color: rgba(255,160,0,0.25); }
    .attempts-badge.danger { color: var(--danger); border-color: rgba(255,77,106,0.25); }

    /* ── Countdown ── */
    #countdown { font-weight: 600; }

    /* ── Animations ── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(28px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes shake {
      0%,100% { transform: translateX(0); }
      20%      { transform: translateX(-6px); }
      40%      { transform: translateX(6px); }
      60%      { transform: translateX(-4px); }
      80%      { transform: translateX(4px); }
    }

    /* ── Responsive ── */
    @media (max-width: 560px) {
      .page { grid-template-columns: 1fr; padding: 24px 16px; }
      .card { grid-column: 1; padding: 36px 24px 28px; }
    }
  </style>
</head>
<body>

  <a href="index.php" class="back-link">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M19 12H5M5 12l7-7M5 12l7 7"/>
    </svg>
    Back to Home
  </a>

  <div class="page">
    <div class="card">

      <!-- Brand -->
      <div class="brand">
        <div class="brand-icon">⚡</div>
        <span class="brand-name">AI·Solutions</span>
      </div>

      <h1>Admin Login</h1>
      <p class="subtitle">Restricted area — authorised personnel only.</p>
      <div class="divider"></div>

      <!-- Alerts -->
      <?php if ($locked_out): ?>
        <div class="alert alert-lock">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <span>Account temporarily locked. Try again in <strong><span id="countdown"><?= htmlspecialchars($lockout_display) ?></span></strong>.</span>
        </div>
      <?php elseif ($error): ?>
        <div class="alert alert-error">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12" y2="16.5" stroke-width="2.5"/></svg>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <!-- Form -->
      <form method="POST" autocomplete="off" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div class="field">
          <label for="username">Username</label>
          <div class="input-wrap">
            <span class="input-icon">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <input
              type="text"
              id="username"
              name="username"
              placeholder="Enter your username"
              value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
              <?= $locked_out ? 'disabled' : '' ?>
              required
              autocomplete="username"
            >
          </div>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap">
            <span class="input-icon">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Enter your password"
              <?= $locked_out ? 'disabled' : '' ?>
              required
              autocomplete="current-password"
            >
            <button type="button" class="toggle-pw" onclick="togglePassword()" aria-label="Toggle password visibility">
              <svg id="eye-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>

        <button type="submit" name="login" class="btn-submit" <?= $locked_out ? 'disabled' : '' ?>>
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
          Sign In
        </button>
      </form>

      <!-- Footer -->
      <div class="card-footer">
        <p>Secure session · Protected route</p>
        <?php
          $attempts = $_SESSION['login_attempts'];
          $badgeClass = $attempts >= 4 ? 'danger' : ($attempts >= 2 ? 'warn' : '');
        ?>
        <span class="attempts-badge <?= $badgeClass ?>">
          <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <?= MAX_ATTEMPTS - $attempts ?> / <?= MAX_ATTEMPTS ?> attempts left
        </span>
      </div>

    </div><!-- /.card -->
  </div><!-- /.page -->

  <script>
    // ── Password toggle ──────────────────────────────────────────────────────
    function togglePassword() {
      const input   = document.getElementById('password');
      const icon    = document.getElementById('eye-icon');
      const visible = input.type === 'text';
      input.type = visible ? 'password' : 'text';
      icon.innerHTML = visible
        ? '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'
        : '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
    }

    // ── Lockout countdown ────────────────────────────────────────────────────
    <?php if ($locked_out && $remaining > 0): ?>
    (function() {
      let remaining = <?= (int)$remaining ?>;
      const el = document.getElementById('countdown');
      if (!el) return;
      const tick = () => {
        if (remaining <= 0) { location.reload(); return; }
        const m = Math.floor(remaining / 60);
        const s = remaining % 60;
        el.textContent = m + ':' + String(s).padStart(2, '0');
        remaining--;
        setTimeout(tick, 1000);
      };
      tick();
    })();
    <?php endif; ?>

    // ── Auto-focus first empty field ────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
      const u = document.getElementById('username');
      const p = document.getElementById('password');
      if (u && !u.disabled) {
        u.value ? p?.focus() : u.focus();
      }
    });
  </script>
</body>
</html>