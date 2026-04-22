<?php
include "auth.php";
include "db_conn.php";
include "csrf.php";

$message = '';
$error = '';
$admin = Admin::getCurrentAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyToken($_POST['token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'All fields are required.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } else {
            // Validate current password
            if (!$admin->verifyPassword($current_password)) {
                $error = 'Current password is incorrect.';
            } else {
                // Validate new password strength
                $validator = new PasswordValidator($new_password);
                if (!$validator->validate()) {
                    $error = 'Password does not meet requirements: ' . $validator->getErrorsString();
                } else {
                    // Update password
                    if ($admin->changePassword($new_password)) {
                        $message = 'Password updated successfully! ✓';
                        CSRF::rotateToken();
                    } else {
                        $error = 'Failed to update password. Please try again.';
                    }
                }
            }
        }
    }
}

$token = generateToken();
$adminInfo = $admin ? $admin->getInfo() : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings — AI Solutions</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Instrument+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --bg:          #07080d;
        --surface:     #0d0f18;
        --surface2:    #12141f;
        --border:      rgba(255,255,255,0.06);
        --border-mid:  rgba(255,255,255,0.1);
        --accent:      #c8f65b;
        --accent-dim:  rgba(200,246,91,0.1);
        --red:         #f65b6e;
        --red-dim:     rgba(246,91,110,0.1);
        --text:        #eef0f6;
        --muted:       rgba(238,240,246,0.38);
        --radius:      12px;
        --radius-sm:   8px;
    }

    html { color-scheme: dark; }
    body {
        font-family: 'Instrument Sans', sans-serif;
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
        line-height: 1.6;
        overflow-x: hidden;
    }

    body::after {
        content: '';
        position: fixed; inset: 0;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
        pointer-events: none; z-index: 0; opacity: .55;
    }

    .topbar {
        position: sticky; top: 0; z-index: 100;
        background: rgba(7,8,13,0.88);
        backdrop-filter: blur(18px);
        border-bottom: 1px solid var(--border);
        padding: 0 36px;
        height: 58px;
        display: flex; align-items: center; justify-content: space-between;
    }

    .topbar-brand {
        display: flex; align-items: center; gap: 10px;
        font-family: 'Syne', sans-serif;
        font-weight: 800; font-size: .82rem;
        letter-spacing: .14em; text-transform: uppercase;
        color: var(--accent);
    }

    .brand-pip {
        width: 8px; height: 8px; border-radius: 50%;
        background: var(--accent);
        box-shadow: 0 0 10px var(--accent);
        animation: pip 2.6s ease-in-out infinite;
    }

    @keyframes pip { 0%,100%{box-shadow:0 0 7px var(--accent)} 50%{box-shadow:0 0 20px var(--accent)} }

    .topbar-meta { font-size: .75rem; color: var(--muted); }
    .topbar-meta span { color: var(--text); font-weight: 500; }

    .wrap {
        position: relative; z-index: 1;
        max-width: 680px;
        margin: 0 auto;
        padding: 40px 36px 80px;
    }

    .page-header {
        display: flex; align-items: flex-end; justify-content: space-between;
        margin-bottom: 32px;
        gap: 20px;
    }

    .page-title-group { display: flex; flex-direction: column; gap: 4px; }
    .page-eyebrow {
        font-size: .68rem; font-weight: 500;
        letter-spacing: .22em; text-transform: uppercase;
        color: var(--accent);
    }

    .page-title {
        font-family: 'Syne', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -.03em;
    }

    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 28px;
        margin-bottom: 20px;
    }

    .card-title {
        font-family: 'Syne', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 18px;
    }

    label {
        display: block;
        font-size: .85rem;
        font-weight: 500;
        margin-bottom: 7px;
        color: var(--text);
    }

    input[type="password"],
    input[type="text"],
    input[type="email"] {
        width: 100%;
        padding: 11px 14px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        color: var(--text);
        font-family: 'Instrument Sans', sans-serif;
        font-size: .9rem;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }

    input::placeholder { color: var(--muted); }

    input:focus {
        border-color: rgba(200,246,91,.4);
        box-shadow: 0 0 0 3px rgba(200,246,91,.07);
    }

    .password-strength {
        margin-top: 8px;
        display: flex;
        gap: 4px;
    }

    .strength-bar {
        height: 4px;
        flex: 1;
        border-radius: 2px;
        background: var(--border);
        transition: background .2s;
    }

    .strength-bar.active { background: var(--accent); }

    .strength-text {
        font-size: .75rem;
        color: var(--muted);
        margin-top: 6px;
    }

    .btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 11px 24px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border-mid);
        background: var(--surface);
        color: var(--text);
        font-family: 'Instrument Sans', sans-serif;
        font-size: .88rem; font-weight: 600;
        cursor: pointer; text-decoration: none;
        transition: all .2s;
        white-space: nowrap;
    }

    .btn:hover { background: var(--surface2); border-color: rgba(255,255,255,.16); transform: translateY(-1px); }

    .btn.primary {
        background: var(--accent); border-color: var(--accent);
        color: #0a0a0f;
    }

    .btn.primary:hover { box-shadow: 0 4px 18px rgba(200,246,91,.28); }

    .btn.danger {
        background: var(--red-dim); border-color: rgba(246,91,110,.3);
        color: var(--red);
    }

    .btn.danger:hover { background: rgba(246,91,110,.15); }

    .message {
        padding: 13px 16px;
        border-radius: var(--radius-sm);
        margin-bottom: 18px;
        font-size: .9rem;
        animation: slideDown .3s ease;
    }

    .message.success {
        background: rgba(200,246,91,.1);
        border: 1px solid rgba(200,246,91,.2);
        color: var(--accent);
    }

    .message.error {
        background: rgba(246,91,110,.1);
        border: 1px solid rgba(246,91,110,.2);
        color: var(--red);
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .admin-info {
        background: var(--surface2);
        padding: 16px;
        border-radius: var(--radius-sm);
        margin-bottom: 20px;
        border-left: 3px solid var(--accent);
    }

    .admin-name {
        font-weight: 600;
        color: var(--text);
    }

    .admin-email {
        font-size: .85rem;
        color: var(--muted);
        margin-top: 4px;
    }

    .actions {
        display: flex; gap: 10px; margin-top: 20px;
    }

    .back-link {
        display: inline-flex; align-items: center; gap: 6px;
        color: var(--muted);
        text-decoration: none;
        font-size: .9rem;
        transition: color .2s;
    }

    .back-link:hover { color: var(--accent); }

    @media (max-width: 600px) {
        .wrap { padding: 24px 18px 60px; }
        .page-title { font-size: 1.5rem; }
        .card { padding: 20px; }
        .actions { flex-direction: column; }
        .btn { width: 100%; justify-content: center; }
    }
  </style>
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">
        <div class="brand-pip"></div>
        AI Solutions
    </div>
    <div class="topbar-meta">
        Admin Settings &nbsp;·&nbsp; <span><?php echo date('D, d M Y'); ?></span>
    </div>
</header>

<div class="wrap">
    <div class="page-header">
        <div class="page-title-group">
            <p class="page-eyebrow">Account</p>
            <h1 class="page-title">Settings</h1>
        </div>
        <a href="dashboard.php" class="back-link">← Back</a>
    </div>

    <?php if ($message): ?>
    <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($adminInfo): ?>
    <div class="card">
        <div class="card-title">Profile Information</div>
        <div class="admin-info">
            <div class="admin-name">👤 <?php echo htmlspecialchars($adminInfo['username']); ?></div>
            <?php if (!empty($adminInfo['created_at'])): ?>
            <div class="admin-email">Member since <?php echo date('M d, Y', strtotime($adminInfo['created_at'])); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-title">Change Password</div>
        <form method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" placeholder="Enter your current password" required>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required oninput="updateStrength()">
                <div class="password-strength" id="strength">
                    <div class="strength-bar"></div>
                    <div class="strength-bar"></div>
                    <div class="strength-bar"></div>
                    <div class="strength-bar"></div>
                    <div class="strength-bar"></div>
                </div>
                <div class="strength-text">
                    <span id="strength-text">Password strength: —</span>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
            </div>

            <div class="actions">
                <button type="submit" class="btn primary">Update Password</button>
                <a href="dashboard.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function getPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[!@#$%^&*()_+\-=\[\]{};:'"",.<>?\/]/.test(password)) strength++;
    return Math.min(strength, 5);
}

function updateStrength() {
    const password = document.getElementById('new_password').value;
    const strength = getPasswordStrength(password);
    const bars = document.querySelectorAll('.strength-bar');
    const text = document.getElementById('strength-text');
    const labels = ['—', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];

    bars.forEach((bar, index) => {
        if (index < strength) {
            bar.classList.add('active');
        } else {
            bar.classList.remove('active');
        }
    });

    text.textContent = 'Password strength: ' + labels[strength];
}

document.getElementById('new_password').addEventListener('change', function() {
    const confirm = document.getElementById('confirm_password');
    if (this.value !== confirm.value) {
        confirm.style.borderColor = 'var(--red)';
    } else {
        confirm.style.borderColor = '';
    }
});

document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('new_password');
    if (password.value !== this.value) {
        this.style.borderColor = 'var(--red)';
    } else {
        this.style.borderColor = '';
    }
});
</script>

</body>
</html>