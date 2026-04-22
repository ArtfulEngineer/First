<?php
include "auth.php";
include "db_conn.php";

// ── Stats queries ────────────────────────────────────────────────────────────
$total      = (int) $conn->query("SELECT COUNT(*) c FROM inquiries")->fetch_assoc()['c'];
$new_today  = (int) $conn->query("SELECT COUNT(*) c FROM inquiries WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'];
$this_month = (int) $conn->query("SELECT COUNT(*) c FROM inquiries WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())")->fetch_assoc()['c'];

// Countries breakdown
$res = $conn->query("SELECT country, COUNT(*) c FROM inquiries GROUP BY country ORDER BY c DESC LIMIT 10");
$countries = []; $country_counts = [];
while ($r = $res->fetch_assoc()) {
    $countries[]      = htmlspecialchars($r['country']);
    $country_counts[] = (int) $r['c'];
}

// Monthly trend (last 6 months)
$trend_res = $conn->query("
    SELECT DATE_FORMAT(created_at, '%b %Y') AS month_label,
           COUNT(*) AS cnt
    FROM inquiries
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY MIN(created_at)
");
$trend_labels = []; $trend_data = [];
while ($r = $trend_res->fetch_assoc()) {
    $trend_labels[] = $r['month_label'];
    $trend_data[]   = (int) $r['cnt'];
}

// Recent inquiries
$recent = $conn->query("SELECT name, email, country, created_at FROM inquiries ORDER BY created_at DESC LIMIT 8");

$admin_name = htmlspecialchars($_SESSION['admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — AI Solutions</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:         #0a0a0f;
      --surface:    #111118;
      --surface-2:  #16161f;
      --border:     rgba(255,255,255,0.07);
      --accent:     #00e5ff;
      --accent-dim: rgba(0,229,255,0.1);
      --accent-glow:rgba(0,229,255,0.2);
      --green:      #00e0a0;
      --green-dim:  rgba(0,224,160,0.1);
      --amber:      #ffb84d;
      --amber-dim:  rgba(255,184,77,0.1);
      --text:       #e8e8f0;
      --muted:      #6b6b80;
      --sidebar-w:  240px;
      --radius:     10px;
    }

    html, body { height: 100%; background: var(--bg); color: var(--text); font-family: 'DM Sans', sans-serif; }

    /* Grid bg */
    body::before {
      content: ''; position: fixed; inset: 0;
      background-image:
        linear-gradient(rgba(0,229,255,0.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,229,255,0.02) 1px, transparent 1px);
      background-size: 50px 50px;
      pointer-events: none; z-index: 0;
    }

    /* ── LAYOUT ── */
    .shell { display: flex; min-height: 100vh; position: relative; z-index: 1; }

    /* ── SIDEBAR ── */
    .sidebar {
      width: var(--sidebar-w);
      flex-shrink: 0;
      background: var(--surface);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      padding: 28px 0;
      position: fixed;
      top: 0; left: 0; bottom: 0;
      z-index: 50;
    }

    .sidebar-logo {
      display: flex; align-items: center; gap: 10px;
      padding: 0 24px 28px;
      border-bottom: 1px solid var(--border);
      margin-bottom: 20px;
    }
    .logo-icon {
      width: 34px; height: 34px; border-radius: 8px;
      background: var(--accent-dim);
      border: 1px solid rgba(0,229,255,0.25);
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem;
    }
    .logo-text {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.25rem; letter-spacing: 0.08em; color: var(--accent);
    }

    .nav-label {
      font-size: 0.68rem; font-weight: 600;
      letter-spacing: 0.12em; text-transform: uppercase;
      color: var(--muted); padding: 0 24px; margin-bottom: 6px;
    }

    .nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 24px; font-size: 0.875rem; font-weight: 500;
      color: var(--muted); text-decoration: none;
      transition: all 0.15s ease; margin: 0 8px; border-radius: 8px;
    }
    .nav-item:hover  { color: var(--text); background: rgba(255,255,255,0.04); }
    .nav-item.active { color: var(--accent); background: var(--accent-dim); }
    .nav-item svg { flex-shrink: 0; }

    .sidebar-footer {
      margin-top: auto;
      padding: 20px 16px 0;
      border-top: 1px solid var(--border);
    }
    .user-card {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 12px; border-radius: var(--radius);
      background: var(--surface-2);
    }
    .user-avatar {
      width: 32px; height: 32px; border-radius: 8px;
      background: var(--accent-dim);
      border: 1px solid rgba(0,229,255,0.2);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.85rem; font-weight: 600; color: var(--accent);
      flex-shrink: 0;
    }
    .user-name  { font-size: 0.82rem; font-weight: 600; }
    .user-role  { font-size: 0.72rem; color: var(--muted); }
    .logout-btn {
      margin-left: auto;
      display: flex; align-items: center; justify-content: center;
      width: 28px; height: 28px; border-radius: 6px;
      background: rgba(255,77,106,0.1);
      border: 1px solid rgba(255,77,106,0.2);
      color: #ff4d6a; text-decoration: none;
      transition: all 0.15s; flex-shrink: 0;
    }
    .logout-btn:hover { background: rgba(255,77,106,0.2); }

    /* ── MAIN ── */
    .main {
      margin-left: var(--sidebar-w);
      flex: 1;
      padding: 36px 40px;
      min-height: 100vh;
      animation: fadeIn 0.5s ease both;
    }

    /* ── TOPBAR ── */
    .topbar {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 36px;
    }
    .topbar h1 {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 2.4rem; letter-spacing: 0.04em;
    }
    .topbar-meta { font-size: 0.82rem; color: var(--muted); margin-top: 2px; }

    .live-badge {
      display: inline-flex; align-items: center; gap: 7px;
      padding: 7px 14px; border-radius: 100px;
      background: var(--green-dim);
      border: 1px solid rgba(0,224,160,0.25);
      font-size: 0.78rem; font-weight: 500; color: var(--green);
    }
    .live-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--green); animation: pulse 2s infinite; }

    /* ── STAT CARDS ── */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 32px;
    }

    .stat-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 24px 26px;
      position: relative; overflow: hidden;
      transition: border-color 0.2s, transform 0.2s;
      animation: fadeUp 0.5s ease both;
    }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.12s; }
    .stat-card:nth-child(3) { animation-delay: 0.19s; }
    .stat-card:hover { border-color: rgba(255,255,255,0.14); transform: translateY(-2px); }

    .stat-card::after {
      content: ''; position: absolute;
      top: -40px; right: -40px;
      width: 120px; height: 120px;
      border-radius: 50%;
      pointer-events: none;
    }
    .stat-card.blue::after  { background: radial-gradient(circle, var(--accent-glow), transparent 70%); }
    .stat-card.green::after { background: radial-gradient(circle, rgba(0,224,160,0.18), transparent 70%); }
    .stat-card.amber::after { background: radial-gradient(circle, rgba(255,184,77,0.18), transparent 70%); }

    .stat-label {
      font-size: 0.75rem; font-weight: 600;
      letter-spacing: 0.1em; text-transform: uppercase;
      color: var(--muted); margin-bottom: 12px;
    }
    .stat-value {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 3rem; letter-spacing: 0.02em; line-height: 1;
    }
    .stat-card.blue  .stat-value { color: var(--accent); }
    .stat-card.green .stat-value { color: var(--green); }
    .stat-card.amber .stat-value { color: var(--amber); }
    .stat-sub { font-size: 0.78rem; color: var(--muted); margin-top: 6px; }

    /* ── CHARTS GRID ── */
    .charts-grid {
      display: grid;
      grid-template-columns: 1.4fr 1fr;
      gap: 20px;
      margin-bottom: 28px;
    }

    .panel {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 24px 26px;
      animation: fadeUp 0.5s 0.25s ease both;
    }

    .panel-header {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 22px;
    }
    .panel-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.3rem; letter-spacing: 0.05em;
    }
    .panel-badge {
      font-size: 0.72rem; font-weight: 500; color: var(--muted);
      padding: 4px 10px; border-radius: 100px;
      background: var(--surface-2); border: 1px solid var(--border);
    }

    .chart-wrap { position: relative; height: 220px; }

    /* ── TABLE ── */
    .table-panel {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 14px;
      overflow: hidden;
      animation: fadeUp 0.5s 0.35s ease both;
      margin-bottom: 0;
    }

    .table-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 20px 26px;
      border-bottom: 1px solid var(--border);
    }
    .table-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.3rem; letter-spacing: 0.05em;
    }
    .view-all {
      font-size: 0.8rem; color: var(--accent);
      text-decoration: none; font-weight: 500;
      transition: opacity 0.15s;
    }
    .view-all:hover { opacity: 0.7; }

    table { width: 100%; border-collapse: collapse; }
    thead th {
      padding: 11px 20px;
      font-size: 0.7rem; font-weight: 600;
      letter-spacing: 0.1em; text-transform: uppercase;
      color: var(--muted); text-align: left;
      background: var(--surface-2);
      border-bottom: 1px solid var(--border);
    }
    tbody tr { border-bottom: 1px solid var(--border); transition: background 0.15s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: rgba(255,255,255,0.02); }
    tbody td { padding: 14px 20px; font-size: 0.875rem; }

    .td-name { font-weight: 500; }
    .td-email { color: var(--muted); font-size: 0.82rem; }
    .country-tag {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 3px 10px; border-radius: 100px;
      background: var(--accent-dim);
      border: 1px solid rgba(0,229,255,0.15);
      font-size: 0.75rem; color: var(--accent); font-weight: 500;
    }
    .td-date { color: var(--muted); font-size: 0.8rem; }

    /* ── ANIMATIONS ── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
      from { opacity: 0; } to { opacity: 1; }
    }
    @keyframes pulse {
      0%,100% { opacity: 1; } 50% { opacity: 0.35; }
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 1024px) {
      .charts-grid { grid-template-columns: 1fr; }
      .stats-grid  { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 768px) {
      .sidebar { transform: translateX(-100%); }
      .main { margin-left: 0; padding: 24px 18px; }
      .stats-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
<div class="shell">

  <!-- ── SIDEBAR ─────────────────────────────────────────────── -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon">⚡</div>
      <span class="logo-text">AI·Solutions</span>
    </div>

    <p class="nav-label">Menu</p>
    <a href="dashboard.php" class="nav-item active">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Dashboard
    </a>
    <a href="inquiries.php" class="nav-item">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      Inquiries
    </a>
    <a href="settings.php" class="nav-item">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
      Settings
    </a>

    <div class="sidebar-footer">
      <div class="user-card">
        <div class="user-avatar"><?= strtoupper(substr($admin_name, 0, 1)) ?></div>
        <div>
          <div class="user-name"><?= $admin_name ?></div>
          <div class="user-role">Administrator</div>
        </div>
        <a href="logout.php" class="logout-btn" title="Logout">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
      </div>
    </div>
  </aside>

  <!-- ── MAIN ─────────────────────────────────────────────────── -->
  <main class="main">

    <!-- Topbar -->
    <div class="topbar">
      <div>
        <h1>Dashboard</h1>
        <p class="topbar-meta">Welcome back, <?= $admin_name ?> · <?= date('l, F j Y') ?></p>
      </div>
      <div class="live-badge">
        <span class="live-dot"></span> Live Data
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid">
      <div class="stat-card blue">
        <p class="stat-label">Total Inquiries</p>
        <p class="stat-value"><?= number_format($total) ?></p>
        <p class="stat-sub">All time submissions</p>
      </div>
      <div class="stat-card green">
        <p class="stat-label">New Today</p>
        <p class="stat-value"><?= number_format($new_today) ?></p>
        <p class="stat-sub">Since midnight</p>
      </div>
      <div class="stat-card amber">
        <p class="stat-label">This Month</p>
        <p class="stat-value"><?= number_format($this_month) ?></p>
        <p class="stat-sub"><?= date('F Y') ?></p>
      </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
      <div class="panel">
        <div class="panel-header">
          <h2 class="panel-title">Monthly Trend</h2>
          <span class="panel-badge">Last 6 months</span>
        </div>
        <div class="chart-wrap"><canvas id="lineChart"></canvas></div>
      </div>
      <div class="panel">
        <div class="panel-header">
          <h2 class="panel-title">By Country</h2>
          <span class="panel-badge">Top 10</span>
        </div>
        <div class="chart-wrap"><canvas id="pieChart"></canvas></div>
      </div>
    </div>

    <!-- Recent Inquiries Table -->
    <div class="table-panel">
      <div class="table-header">
        <h2 class="table-title">Recent Inquiries</h2>
        <a href="inquiries.php" class="view-all">View all →</a>
      </div>
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Country</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $recent->fetch_assoc()): ?>
          <tr>
            <td class="td-name"><?= htmlspecialchars($row['name']) ?></td>
            <td class="td-email"><?= htmlspecialchars($row['email']) ?></td>
            <td><span class="country-tag"><?= htmlspecialchars($row['country']) ?></span></td>
            <td class="td-date"><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

  </main>
</div><!-- /.shell -->

<script>
const ACCENT = '#00e5ff';
const GREEN  = '#00e0a0';
const AMBER  = '#ffb84d';
const MUTED  = '#6b6b80';
const BORDER = 'rgba(255,255,255,0.07)';

Chart.defaults.color          = MUTED;
Chart.defaults.borderColor    = BORDER;
Chart.defaults.font.family    = "'DM Sans', sans-serif";
Chart.defaults.plugins.legend.labels.boxWidth = 10;
Chart.defaults.plugins.legend.labels.padding  = 16;

// Palette for pie slices
const palette = [
  '#00e5ff','#00e0a0','#ffb84d','#ff6b9d','#a78bfa',
  '#34d399','#f87171','#60a5fa','#fbbf24','#c084fc'
];

// ── Line / trend chart ──────────────────────────────────────────────────────
const lineCtx = document.getElementById('lineChart').getContext('2d');
const gradient = lineCtx.createLinearGradient(0, 0, 0, 220);
gradient.addColorStop(0,   'rgba(0,229,255,0.18)');
gradient.addColorStop(1,   'rgba(0,229,255,0)');

new Chart(lineCtx, {
  type: 'line',
  data: {
    labels: <?= json_encode($trend_labels) ?>,
    datasets: [{
      label: 'Inquiries',
      data:  <?= json_encode($trend_data) ?>,
      borderColor: ACCENT,
      backgroundColor: gradient,
      borderWidth: 2.5,
      pointBackgroundColor: ACCENT,
      pointRadius: 4,
      pointHoverRadius: 6,
      tension: 0.4,
      fill: true,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: BORDER } },
      y: { grid: { color: BORDER }, beginAtZero: true, ticks: { precision: 0 } }
    }
  }
});

// ── Pie / doughnut chart ────────────────────────────────────────────────────
new Chart(document.getElementById('pieChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($countries) ?>,
    datasets: [{
      data: <?= json_encode($country_counts) ?>,
      backgroundColor: palette,
      borderColor: '#111118',
      borderWidth: 3,
      hoverOffset: 8,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    cutout: '62%',
    plugins: {
      legend: {
        position: 'bottom',
        labels: { color: MUTED, font: { size: 11 } }
      }
    }
  }
});
</script>
</body>
</html>