<?php
include "auth.php";
include "db_conn.php";

// Handle search & filters
$search  = $_GET['search']  ?? '';
$sort    = in_array($_GET['sort'] ?? '', ['created_at','name','company','country']) ? $_GET['sort'] : 'created_at';
$dir     = ($_GET['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
$page    = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$offset  = ($page - 1) * $perPage;

// Count total for pagination
$countStmt = $conn->prepare("SELECT COUNT(*) FROM inquiries WHERE name LIKE ? OR email LIKE ? OR company LIKE ?");
$st = "%$search%";
$countStmt->bind_param("sss", $st, $st, $st);
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_row()[0];
$totalPages = ceil($totalRows / $perPage);

// Main query
$query = "SELECT * FROM inquiries WHERE name LIKE ? OR email LIKE ? OR company LIKE ? ORDER BY $sort $dir LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sssii", $st, $st, $st, $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);

// Stats
$statsQ = $conn->query("SELECT COUNT(*) as total, COUNT(DISTINCT company) as companies, COUNT(DISTINCT country) as countries, DATE(MAX(created_at)) as latest FROM inquiries");
$stats = $statsQ->fetch_assoc();

/**
 * Summary of sortUrl
 * @param mixed $col
 * @return string
 */
function sortUrl($col) {
    $params = $_GET;
    $params['sort'] = $col;
    $params['dir']  = (($_GET['sort'] ?? '') === $col && ($_GET['dir'] ?? 'DESC') === 'DESC') ? 'ASC' : 'DESC';
    $params['page'] = 1;
    return '?' . http_build_query($params);
}
/**
 * Summary of pageUrl
 * @param mixed $p
 * @return string
 */
function pageUrl($p) {
    $params = $_GET;
    $params['page'] = $p;
    return '?' . http_build_query($params);
}
/**
 * Summary of esc
 * @param mixed $v
 * @return string
 */
function esc($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inquiries — AI Solutions</title>
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
    --accent-glow: rgba(200,246,91,0.22);
    --red:         #f65b6e;
    --red-dim:     rgba(246,91,110,0.1);
    --blue:        #5b9ef6;
    --blue-dim:    rgba(91,158,246,0.1);
    --gold:        #f6c55b;
    --gold-dim:    rgba(246,197,91,0.1);
    --text:        #eef0f6;
    --muted:       rgba(238,240,246,0.38);
    --muted2:      rgba(238,240,246,0.18);
    --radius:      12px;
    --radius-sm:   8px;
}

html { color-scheme: dark; }

body {
    font-family: 'Instrument Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    line-height: 1.5;
    overflow-x: hidden;
}

/* grain */
body::after {
    content: '';
    position: fixed; inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
    pointer-events: none; z-index: 0; opacity: .55;
}

/* ── TOPBAR ── */
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

.topbar-meta {
    font-size: .75rem; color: var(--muted);
    display: flex; align-items: center; gap: 6px;
}
.topbar-meta span { color: var(--text); font-weight: 500; }

/* ── WRAPPER ── */
.wrap {
    position: relative; z-index: 1;
    max-width: 1360px;
    margin: 0 auto;
    padding: 36px 36px 80px;
}

/* ── PAGE HEADER ── */
.page-header {
    display: flex; align-items: flex-end; justify-content: space-between;
    margin-bottom: 32px;
    gap: 20px; flex-wrap: wrap;
    opacity: 0; animation: fadeUp .65s .05s ease both;
}
.page-title-group { display: flex; flex-direction: column; gap: 4px; }
.page-eyebrow {
    font-size: .68rem; font-weight: 500;
    letter-spacing: .22em; text-transform: uppercase;
    color: var(--accent);
}
.page-title {
    font-family: 'Syne', sans-serif;
    font-size: clamp(2rem, 3.5vw, 3rem);
    font-weight: 800;
    line-height: 1;
    letter-spacing: -.03em;
}

/* ── STAT CARDS ── */
.stat-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 28px;
    opacity: 0; animation: fadeUp .65s .15s ease both;
}
.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px 22px;
    display: flex; flex-direction: column; gap: 8px;
    position: relative; overflow: hidden;
    transition: border-color .25s, transform .25s;
}
.stat-card::before {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(135deg, var(--card-glow, transparent) 0%, transparent 60%);
    opacity: 0; transition: opacity .3s;
}
.stat-card:hover { border-color: var(--border-mid); transform: translateY(-2px); }
.stat-card:hover::before { opacity: 1; }
.stat-card.green  { --card-glow: rgba(200,246,91,0.06); }
.stat-card.red    { --card-glow: rgba(246,91,110,0.06); }
.stat-card.blue   { --card-glow: rgba(91,158,246,0.06); }
.stat-card.gold   { --card-glow: rgba(246,197,91,0.06); }

.stat-label {
    font-size: .68rem; font-weight: 500;
    letter-spacing: .14em; text-transform: uppercase;
    color: var(--muted);
}
.stat-value {
    font-family: 'Syne', sans-serif;
    font-size: 2.4rem; font-weight: 800;
    letter-spacing: -.04em; line-height: 1;
}
.stat-card.green .stat-value { color: var(--accent); }
.stat-card.red   .stat-value { color: var(--red); }
.stat-card.blue  .stat-value { color: var(--blue); }
.stat-card.gold  .stat-value { color: var(--gold); }

.stat-sub { font-size: .75rem; color: var(--muted); }

/* ── TOOLBAR ── */
.toolbar {
    display: flex; align-items: center; justify-content: space-between;
    gap: 14px; flex-wrap: wrap;
    margin-bottom: 20px;
    opacity: 0; animation: fadeUp .65s .25s ease both;
}
.search-wrap {
    position: relative; flex: 1; min-width: 240px; max-width: 420px;
}
.search-wrap svg {
    position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
    color: var(--muted); pointer-events: none;
}
.search-wrap input {
    width: 100%;
    padding: 10px 14px 10px 42px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    color: var(--text);
    font-family: 'Instrument Sans', sans-serif;
    font-size: .88rem;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}
.search-wrap input::placeholder { color: var(--muted2); }
.search-wrap input:focus {
    border-color: rgba(200,246,91,.4);
    box-shadow: 0 0 0 3px rgba(200,246,91,.07);
}

.toolbar-right { display: flex; align-items: center; gap: 10px; }

.btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 16px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-mid);
    background: var(--surface);
    color: var(--text);
    font-family: 'Instrument Sans', sans-serif;
    font-size: .82rem; font-weight: 500;
    cursor: pointer; text-decoration: none;
    transition: background .2s, border-color .2s, transform .15s;
    white-space: nowrap;
}
.btn:hover { background: var(--surface2); border-color: rgba(255,255,255,.16); transform: translateY(-1px); }
.btn.accent {
    background: var(--accent); border-color: var(--accent);
    color: #0a0a0f; font-weight: 600;
}
.btn.accent:hover { box-shadow: 0 4px 18px rgba(200,246,91,.28); }

.result-count { font-size: .78rem; color: var(--muted); white-space: nowrap; }
.result-count strong { color: var(--text); }

/* ── TABLE CARD ── */
.table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    opacity: 0; animation: fadeUp .65s .35s ease both;
}

.table-scroll { overflow-x: auto; }

table {
    width: 100%;
    border-collapse: collapse;
    font-size: .84rem;
}
thead tr {
    border-bottom: 1px solid var(--border-mid);
    background: var(--surface2);
}
th {
    padding: 12px 16px;
    text-align: left;
    font-size: .67rem;
    font-weight: 600;
    letter-spacing: .13em;
    text-transform: uppercase;
    color: var(--muted);
    white-space: nowrap;
    user-select: none;
}
th a {
    color: inherit; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
    transition: color .2s;
}
th a:hover { color: var(--text); }
th.active-sort a { color: var(--accent); }
.sort-icon { font-size: .7rem; opacity: .6; }

tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background .18s;
    cursor: default;
}
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: rgba(255,255,255,.025); }

td {
    padding: 13px 16px;
    vertical-align: middle;
    color: var(--text);
}
td.muted { color: var(--muted); font-size: .8rem; }

/* Avatar cell */
.avatar-cell { display: flex; align-items: center; gap: 11px; }
.avatar {
    width: 34px; height: 34px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Syne', sans-serif;
    font-size: .8rem; font-weight: 700;
    flex-shrink: 0;
    letter-spacing: 0;
}
.name-primary { font-weight: 500; font-size: .88rem; }
.name-email { font-size: .74rem; color: var(--muted); margin-top: 1px; }

/* Badge */
.badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 9px;
    border-radius: 100px;
    font-size: .7rem; font-weight: 500;
    white-space: nowrap;
}

/* Details expand */
.details-btn {
    background: none; border: 1px solid var(--border);
    color: var(--muted); padding: 4px 10px;
    border-radius: 6px; font-size: .74rem;
    cursor: pointer; font-family: inherit;
    transition: border-color .2s, color .2s;
    white-space: nowrap;
}
.details-btn:hover { border-color: var(--accent); color: var(--accent); }

.details-row td {
    background: var(--surface2);
    padding: 0;
    border-bottom: 1px solid var(--border-mid);
}
.details-inner {
    padding: 18px 20px 18px 66px;
    font-size: .84rem;
    color: var(--muted);
    line-height: 1.7;
    display: none;
    border-left: 2px solid var(--accent);
    margin: 0 16px;
}
.details-inner.open { display: block; }

/* Date */
.date-cell { white-space: nowrap; }
.date-main { font-size: .82rem; }
.date-time { font-size: .72rem; color: var(--muted); }

/* empty state */
.empty-state {
    text-align: center;
    padding: 72px 20px;
}
.empty-icon { font-size: 3rem; margin-bottom: 16px; opacity: .4; }
.empty-title { font-family: 'Syne', sans-serif; font-size: 1.3rem; font-weight: 700; margin-bottom: 8px; }
.empty-desc { color: var(--muted); font-size: .88rem; }

/* ── PAGINATION ── */
.pagination {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px;
    border-top: 1px solid var(--border);
    gap: 12px; flex-wrap: wrap;
    opacity: 0; animation: fadeUp .65s .45s ease both;
}
.page-info { font-size: .78rem; color: var(--muted); }
.page-buttons { display: flex; gap: 6px; }
.page-btn {
    min-width: 34px; height: 34px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    background: var(--surface2);
    color: var(--muted);
    font-size: .8rem; font-family: 'Syne', sans-serif;
    text-decoration: none;
    transition: all .18s;
    padding: 0 8px;
}
.page-btn:hover:not(.disabled):not(.current) {
    border-color: rgba(200,246,91,.4); color: var(--accent);
}
.page-btn.current {
    background: var(--accent-dim); border-color: rgba(200,246,91,.45);
    color: var(--accent); font-weight: 700;
}
.page-btn.disabled { opacity: .3; pointer-events: none; }

/* ── ANIMATIONS ── */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
    .wrap { padding: 24px 18px 60px; }
    .stat-row { grid-template-columns: 1fr 1fr; }
    .topbar { padding: 0 18px; }
    .page-title { font-size: 2rem; }
}
@media (max-width: 580px) {
    .stat-row { grid-template-columns: 1fr 1fr; gap: 10px; }
    .stat-value { font-size: 1.8rem; }
    .toolbar { flex-direction: column; align-items: stretch; }
    .toolbar-right { justify-content: space-between; }
    .search-wrap { max-width: 100%; }
}
</style>
</head>
<body>

<!-- TOPBAR -->
<header class="topbar">
    <div class="topbar-brand">
        <div class="brand-pip"></div>
        AI Solutions
    </div>
    <div class="topbar-meta">
        Admin Dashboard &nbsp;·&nbsp; <span><?php echo date('D, d M Y'); ?></span>
    </div>
</header>

<div class="wrap">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="page-title-group">
            <p class="page-eyebrow">Admin Panel</p>
            <h1 class="page-title">Inquiries</h1>
        </div>
        <a href="export.php?<?php echo esc(http_build_query($_GET)); ?>" class="btn accent">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export CSV
        </a>
    </div>

    <!-- STAT CARDS -->
    <div class="stat-row">
        <div class="stat-card green">
            <div class="stat-label">Total Inquiries</div>
            <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
            <div class="stat-sub">All time submissions</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Companies</div>
            <div class="stat-value"><?php echo number_format($stats['companies']); ?></div>
            <div class="stat-sub">Unique organisations</div>
        </div>
        <div class="stat-card gold">
            <div class="stat-label">Countries</div>
            <div class="stat-value"><?php echo number_format($stats['countries']); ?></div>
            <div class="stat-sub">Global reach</div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">Latest Entry</div>
            <div class="stat-value" style="font-size:1.3rem;padding-top:6px">
                <?php echo $stats['latest'] ? date('d M', strtotime($stats['latest'])) : '—'; ?>
            </div>
            <div class="stat-sub"><?php echo $stats['latest'] ? date('Y', strtotime($stats['latest'])) : 'No entries yet'; ?></div>
        </div>
    </div>

    <!-- TOOLBAR -->
    <div class="toolbar">
        <form method="GET" style="display:flex;align-items:center;gap:10px;flex:1;flex-wrap:wrap;">
            <?php foreach(['sort','dir','page'] as $k): ?>
                <?php if(isset($_GET[$k])): ?>
                    <input type="hidden" name="<?php echo $k; ?>" value="<?php echo esc($_GET[$k]); ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            <div class="search-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" placeholder="Search name, email or company…" value="<?php echo esc($search); ?>" autocomplete="off">
            </div>
            <button type="submit" class="btn accent" style="height:38px;">Search</button>
            <?php if($search): ?>
            <a href="?" class="btn" style="height:38px;">Clear</a>
            <?php endif; ?>
        </form>
        <div class="toolbar-right">
            <span class="result-count">
                <strong><?php echo number_format($totalRows); ?></strong> result<?php echo $totalRows !== 1 ? 's' : ''; ?>
            </span>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <div class="table-scroll">
        <?php if(empty($rows)): ?>
            <div class="empty-state">
                <div class="empty-icon">◎</div>
                <div class="empty-title">No inquiries found</div>
                <div class="empty-desc">Try a different search term or clear the filter.</div>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th><?php $col='name'; ?><a href="<?php echo sortUrl($col); ?>" class="<?php echo $sort===$col?'active-sort':''; ?>">
                        Contact <?php echo $sort===$col ? ($dir==='DESC'?'↓':'↑') : ''; ?>
                    </a></th>
                    <th>Phone</th>
                    <th><?php $col='company'; ?><a href="<?php echo sortUrl($col); ?>" class="<?php echo $sort===$col?'active-sort':''; ?>">
                        Company <?php echo $sort===$col ? ($dir==='DESC'?'↓':'↑') : ''; ?>
                    </a></th>
                    <th><?php $col='country'; ?><a href="<?php echo sortUrl($col); ?>" class="<?php echo $sort===$col?'active-sort':''; ?>">
                        Country <?php echo $sort===$col ? ($dir==='DESC'?'↓':'↑') : ''; ?>
                    </a></th>
                    <th>Job Title</th>
                    <th>Details</th>
                    <th><?php $col='created_at'; ?><a href="<?php echo sortUrl($col); ?>" class="<?php echo $sort===$col?'active-sort':''; ?>">
                        Date <?php echo $sort===$col ? ($dir==='DESC'?'↓':'↑') : ''; ?>
                    </a></th>
                </tr>
            </thead>
            <tbody>
            <?php
            $colors = ['#c8f65b','#5b9ef6','#f65b6e','#f6c55b','#a78bfa','#f6825b','#5bf6d4'];
            foreach($rows as $i => $row):
                $initials = strtoupper(substr($row['name'],0,1) . (strpos($row['name'],' ')!==false ? substr($row['name'], strpos($row['name'],' ')+1, 1) : ''));
                $color = $colors[$i % count($colors)];
                $bg    = $color . '1a';
                $dt    = $row['created_at'] ? new DateTime($row['created_at']) : null;
                $rowId = 'row-' . $i;
            ?>
            <tr>
                <td>
                    <div class="avatar-cell">
                        <div class="avatar" style="background:<?php echo $bg; ?>;color:<?php echo $color; ?>;"><?php echo esc($initials); ?></div>
                        <div>
                            <div class="name-primary"><?php echo esc($row['name']); ?></div>
                            <div class="name-email"><?php echo esc($row['email']); ?></div>
                        </div>
                    </div>
                </td>
                <td class="muted"><?php echo esc($row['phone']); ?></td>
                <td><?php echo esc($row['company']); ?></td>
                <td>
                    <span class="badge" style="background:var(--blue-dim);color:var(--blue);">
                        <?php echo esc($row['country']); ?>
                    </span>
                </td>
                <td>
                    <span class="badge" style="background:rgba(167,139,250,0.1);color:#a78bfa;">
                        <?php echo esc($row['job_title']); ?>
                    </span>
                </td>
                <td>
                    <?php if(!empty($row['job_details'])): ?>
                    <button class="details-btn" onclick="toggleDetail('<?php echo $rowId; ?>', this)">View ↓</button>
                    <?php else: ?>
                    <span class="muted">—</span>
                    <?php endif; ?>
                </td>
                <td class="date-cell">
                    <?php if($dt): ?>
                    <div class="date-main"><?php echo $dt->format('d M Y'); ?></div>
                    <div class="date-time"><?php echo $dt->format('H:i'); ?></div>
                    <?php else: ?>—<?php endif; ?>
                </td>
            </tr>
            <?php if(!empty($row['job_details'])): ?>
            <tr class="details-row">
                <td colspan="7">
                    <div class="details-inner" id="<?php echo $rowId; ?>">
                        <?php echo nl2br(esc($row['job_details'])); ?>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        </div>

        <!-- PAGINATION -->
        <?php if($totalPages > 1 || $totalRows > 0): ?>
        <div class="pagination">
            <div class="page-info">
                Page <strong><?php echo $page; ?></strong> of <strong><?php echo max(1, $totalPages); ?></strong>
                &nbsp;·&nbsp; <?php echo number_format($offset+1); ?>–<?php echo number_format(min($offset+$perPage, $totalRows)); ?> of <?php echo number_format($totalRows); ?>
            </div>
            <div class="page-buttons">
                <a href="<?php echo pageUrl(1); ?>" class="page-btn <?php echo $page<=1?'disabled':''; ?>">«</a>
                <a href="<?php echo pageUrl($page-1); ?>" class="page-btn <?php echo $page<=1?'disabled':''; ?>">‹</a>
                <?php
                $start = max(1, $page - 2);
                $end   = min($totalPages, $page + 2);
                for($p = $start; $p <= $end; $p++):
                ?>
                <a href="<?php echo pageUrl($p); ?>" class="page-btn <?php echo $p===$page?'current':''; ?>"><?php echo $p; ?></a>
                <?php endfor; ?>
                <a href="<?php echo pageUrl($page+1); ?>" class="page-btn <?php echo $page>=$totalPages?'disabled':''; ?>">›</a>
                <a href="<?php echo pageUrl($totalPages); ?>" class="page-btn <?php echo $page>=$totalPages?'disabled':''; ?>">»</a>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
function toggleDetail(id, btn) {
    const el = document.getElementById(id);
    const open = el.classList.toggle('open');
    btn.textContent = open ? 'Hide ↑' : 'View ↓';
    btn.style.color = open ? 'var(--accent)' : '';
    btn.style.borderColor = open ? 'var(--accent)' : '';
}
</script>

</body>
</html>