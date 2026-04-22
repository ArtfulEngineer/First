<?php include "csrf.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Contact — AI Solutions</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=Instrument+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --bg: #05050a;
        --surface: #0e0e18;
        --border: rgba(255,255,255,0.07);
        --accent: #c8f65b;
        --accent-dim: rgba(200,246,91,0.12);
        --accent-glow: rgba(200,246,91,0.25);
        --text: #f0f0f0;
        --muted: rgba(240,240,240,0.4);
        --input-bg: rgba(255,255,255,0.04);
        --input-focus: rgba(200,246,91,0.06);
    }

    html { scroll-behavior: smooth; }

    body {
        font-family: 'Instrument Sans', sans-serif;
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
        display: flex;
        align-items: stretch;
        overflow-x: hidden;
    }

    /* ── noise grain overlay ── */
    body::before {
        content: '';
        position: fixed;
        inset: 0;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
        pointer-events: none;
        z-index: 0;
        opacity: 0.6;
    }

    /* ── ambient glow blobs ── */
    .glow-blob {
        position: fixed;
        border-radius: 50%;
        filter: blur(120px);
        pointer-events: none;
        z-index: 0;
        animation: drift 18s ease-in-out infinite alternate;
    }
    .glow-blob:nth-child(1) { width: 500px; height: 500px; background: rgba(200,246,91,0.07); top: -10%; left: -10%; animation-delay: 0s; }
    .glow-blob:nth-child(2) { width: 400px; height: 400px; background: rgba(100,120,255,0.06); bottom: -10%; right: -5%; animation-delay: -6s; }
    .glow-blob:nth-child(3) { width: 300px; height: 300px; background: rgba(200,246,91,0.04); top: 50%; left: 40%; animation-delay: -12s; }

    @keyframes drift {
        from { transform: translate(0, 0) scale(1); }
        to   { transform: translate(30px, 20px) scale(1.08); }
    }

    /* ── layout ── */
    .page-wrap {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: 1fr 1fr;
        width: 100%;
        min-height: 100vh;
    }

    /* ── left panel ── */
    .left-panel {
        padding: 60px 56px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border-right: 1px solid var(--border);
        background: linear-gradient(160deg, rgba(200,246,91,0.03) 0%, transparent 60%);
        animation: fadeUp 0.8s ease both;
    }

    .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 60px; }
    .brand-dot {
        width: 9px; height: 9px;
        background: var(--accent);
        border-radius: 50%;
        box-shadow: 0 0 12px var(--accent);
        animation: pulse 2.5s ease-in-out infinite;
    }
    @keyframes pulse {
        0%,100% { box-shadow: 0 0 8px var(--accent); }
        50%      { box-shadow: 0 0 22px var(--accent); }
    }
    .brand-name {
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        font-size: 1rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--accent);
    }

    .left-hero { flex: 1; display: flex; flex-direction: column; justify-content: center; }

    .eyebrow {
        font-size: 0.72rem;
        font-weight: 500;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 20px;
        opacity: 0;
        animation: fadeUp 0.7s 0.3s ease both;
    }

    .hero-title {
        font-family: 'Syne', sans-serif;
        font-size: clamp(3rem, 4.5vw, 5.2rem);
        font-weight: 800;
        line-height: 0.95;
        letter-spacing: -0.03em;
        margin-bottom: 28px;
        opacity: 0;
        animation: fadeUp 0.7s 0.45s ease both;
    }
    .hero-title span {
        color: var(--accent);
        display: block;
    }

    .hero-desc {
        font-size: 0.97rem;
        line-height: 1.75;
        color: var(--muted);
        max-width: 340px;
        opacity: 0;
        animation: fadeUp 0.7s 0.6s ease both;
    }

    .info-pills {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-top: 52px;
        opacity: 0;
        animation: fadeUp 0.7s 0.75s ease both;
    }
    .pill {
        display: flex;
        align-items: center;
        gap: 14px;
        font-size: 0.85rem;
        color: var(--muted);
        transition: color 0.3s;
    }
    .pill:hover { color: var(--text); }
    .pill-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        background: var(--accent-dim);
        border: 1px solid rgba(200,246,91,0.2);
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
        transition: background 0.3s, box-shadow 0.3s;
    }
    .pill:hover .pill-icon {
        background: rgba(200,246,91,0.2);
        box-shadow: 0 0 14px rgba(200,246,91,0.15);
    }

    .left-footer {
        margin-top: 48px;
        padding-top: 28px;
        border-top: 1px solid var(--border);
        font-size: 0.75rem;
        color: var(--muted);
        letter-spacing: 0.04em;
        opacity: 0;
        animation: fadeUp 0.7s 0.9s ease both;
    }

    /* ── right panel (form) ── */
    .right-panel {
        padding: 60px 56px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        overflow-y: auto;
        opacity: 0;
        animation: fadeUp 0.8s 0.2s ease both;
    }

    .form-header {
        margin-bottom: 36px;
    }
    .form-header h2 {
        font-family: 'Syne', sans-serif;
        font-size: 1.6rem;
        font-weight: 700;
        margin-bottom: 6px;
        letter-spacing: -0.02em;
    }
    .form-header p {
        font-size: 0.85rem;
        color: var(--muted);
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .form-group { display: flex; flex-direction: column; gap: 7px; }
    .form-group.full { grid-column: 1 / -1; }

    label {
        font-size: 0.72rem;
        font-weight: 500;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--muted);
        transition: color 0.3s;
    }
    .form-group:focus-within label { color: var(--accent); }

    .input-wrap {
        position: relative;
    }
    .input-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        color: var(--muted);
        pointer-events: none;
        transition: color 0.3s;
    }
    .textarea-wrap .input-icon { top: 16px; transform: none; }
    .form-group:focus-within .input-icon { color: var(--accent); }

    input, textarea {
        width: 100%;
        padding: 13px 14px 13px 40px;
        background: var(--input-bg);
        border: 1px solid var(--border);
        border-radius: 10px;
        color: var(--text);
        font-family: 'Instrument Sans', sans-serif;
        font-size: 0.9rem;
        transition: background 0.25s, border-color 0.25s, box-shadow 0.25s;
        outline: none;
        -webkit-appearance: none;
        appearance: none;
    }
    input::placeholder, textarea::placeholder { color: rgba(240,240,240,0.22); }
    input:focus, textarea:focus {
        background: var(--input-focus);
        border-color: rgba(200,246,91,0.45);
        box-shadow: 0 0 0 3px rgba(200,246,91,0.08);
    }
    textarea {
        resize: vertical;
        min-height: 110px;
        padding-top: 13px;
        line-height: 1.6;
    }

    /* character counter */
    .char-count {
        font-size: 0.68rem;
        color: var(--muted);
        text-align: right;
        margin-top: 4px;
        transition: color 0.3s;
    }
    .char-count.warn { color: #f6a05b; }

    /* submit button */
    .submit-row {
        margin-top: 24px;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    button[type="submit"] {
        flex: 1;
        padding: 15px 28px;
        background: var(--accent);
        border: none;
        border-radius: 10px;
        color: #0a0a0f;
        font-family: 'Syne', sans-serif;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
    }
    button[type="submit"]::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,0.3) 50%, transparent 70%);
        transform: translateX(-100%);
        transition: transform 0.5s;
    }
    button[type="submit"]:hover::before { transform: translateX(100%); }
    button[type="submit"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(200,246,91,0.3);
    }
    button[type="submit"]:active { transform: translateY(0); }
    button[type="submit"]:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }

    .btn-text, .btn-loading { transition: opacity 0.2s; }
    .btn-loading { display: none; }

    .privacy-note {
        font-size: 0.72rem;
        color: var(--muted);
        line-height: 1.5;
        max-width: 160px;
    }

    /* toast message */
    #toast {
        position: fixed;
        bottom: 32px;
        left: 50%;
        transform: translateX(-50%) translateY(20px);
        padding: 14px 24px;
        border-radius: 12px;
        font-size: 0.88rem;
        font-weight: 500;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.35s, transform 0.35s;
        z-index: 9999;
        white-space: nowrap;
        backdrop-filter: blur(12px);
    }
    #toast.show {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
        pointer-events: auto;
    }
    #toast.success {
        background: rgba(200,246,91,0.15);
        border: 1px solid rgba(200,246,91,0.4);
        color: var(--accent);
    }
    #toast.error {
        background: rgba(246,91,91,0.12);
        border: 1px solid rgba(246,91,91,0.35);
        color: #f65b5b;
    }

    /* step progress dots */
    .progress-dots {
        display: flex;
        gap: 6px;
        margin-bottom: 28px;
    }
    .dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: var(--border);
        transition: background 0.4s, transform 0.4s;
    }
    .dot.active {
        background: var(--accent);
        transform: scale(1.3);
        box-shadow: 0 0 8px var(--accent);
    }
    .dot.done { background: rgba(200,246,91,0.4); }

    /* animations */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes shake {
        0%,100% { transform: translateX(0); }
        20%,60%  { transform: translateX(-6px); }
        40%,80%  { transform: translateX(6px); }
    }
    .shake { animation: shake 0.4s ease; }

    /* ── responsive ── */
    @media (max-width: 820px) {
        .page-wrap { grid-template-columns: 1fr; }
        .left-panel {
            border-right: none;
            border-bottom: 1px solid var(--border);
            padding: 40px 28px 32px;
        }
        .hero-title { font-size: clamp(2.6rem, 10vw, 3.6rem); }
        .right-panel { padding: 36px 28px 60px; }
        .form-grid { grid-template-columns: 1fr; }
        .form-group.full { grid-column: 1; }
        .info-pills { flex-direction: row; flex-wrap: wrap; }
    }
</style>
</head>
<body>

<div class="glow-blob"></div>
<div class="glow-blob"></div>
<div class="glow-blob"></div>

<div id="toast"></div>

<div class="page-wrap">

    <!-- LEFT PANEL -->
    <div class="left-panel">
        <div class="brand">
            <span class="brand-dot"></span>
            <span class="brand-name">AI Solutions</span>
        </div>

        <div class="left-hero">
            <p class="eyebrow">Get in Touch</p>
            <h1 class="hero-title">
                Let's Build
                <span>Something</span>
                Great.
            </h1>
            <p class="hero-desc">Tell us about your project and we'll get back to you within one business day with a tailored proposal.</p>

            <div class="info-pills">
                <div class="pill">
                    <div class="pill-icon">⚡</div>
                    <span>Response within 24 hours</span>
                </div>
                <div class="pill">
                    <div class="pill-icon">🔒</div>
                    <span>Your data is fully secure</span>
                </div>
                <div class="pill">
                    <div class="pill-icon">🌍</div>
                    <span>Global clients, local care</span>
                </div>
            </div>
        </div>

        <div class="left-footer">
            &copy; <?php echo date('Y'); ?> AI Solutions &nbsp;·&nbsp; All rights reserved
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
        <div class="form-header">
            <div class="progress-dots" id="progressDots">
                <div class="dot active" id="d1"></div>
                <div class="dot" id="d2"></div>
                <div class="dot" id="d3"></div>
                <div class="dot" id="d4"></div>
                <div class="dot" id="d5"></div>
                <div class="dot" id="d6"></div>
                <div class="dot" id="d7"></div>
            </div>
            <h2>Send a Message</h2>
            <p>Fill in the details below — every field helps us understand you better.</p>
        </div>

        <form id="contactForm" novalidate>
            <input type="hidden" name="token" value="<?php echo generateToken(); ?>">

            <div class="form-grid">

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <div class="input-wrap">
                        <span class="input-icon">✦</span>
                        <input type="text" id="name" name="name" placeholder="Jane Doe" required autocomplete="name">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <span class="input-icon">@</span>
                        <input type="email" id="email" name="email" placeholder="jane@company.com" required autocomplete="email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <div class="input-wrap">
                        <span class="input-icon">#</span>
                        <input type="tel" id="phone" name="phone" placeholder="+1 (555) 000-0000" required autocomplete="tel">
                    </div>
                </div>

                <div class="form-group">
                    <label for="company">Company</label>
                    <div class="input-wrap">
                        <span class="input-icon">◈</span>
                        <input type="text" id="company" name="company" placeholder="Acme Corp" required autocomplete="organization">
                    </div>
                </div>

                <div class="form-group">
                    <label for="country">Country</label>
                    <div class="input-wrap">
                        <span class="input-icon">◉</span>
                        <input type="text" id="country" name="country" placeholder="United States" required autocomplete="country-name">
                    </div>
                </div>

                <div class="form-group">
                    <label for="job_title">Job Title</label>
                    <div class="input-wrap">
                        <span class="input-icon">▲</span>
                        <input type="text" id="job_title" name="job_title" placeholder="Head of Engineering" required autocomplete="organization-title">
                    </div>
                </div>

                <div class="form-group full">
                    <label for="job_details">Project Details</label>
                    <div class="input-wrap textarea-wrap">
                        <span class="input-icon">✎</span>
                        <textarea id="job_details" name="job_details" placeholder="Describe your project, goals, timeline, and any specific requirements…" required maxlength="800"></textarea>
                    </div>
                    <div class="char-count" id="charCount">0 / 800</div>
                </div>

            </div>

            <div class="submit-row">
                <button type="submit" id="submitBtn">
                    <span class="btn-text">Send Message →</span>
                    <span class="btn-loading">Sending…</span>
                </button>
                <p class="privacy-note">We never share your data with third parties.</p>
            </div>
        </form>
    </div>

</div>

<script>
$(function () {

    /* ── progress dot tracker ── */
    const fields = ['name','email','phone','company','country','job_title','job_details'];
    function updateDots() {
        fields.forEach((id, i) => {
            const val = $('#'+id).val().trim();
            const dot = $('#d'+(i+1));
            dot.removeClass('active done');
            if (val) dot.addClass('done');
        });
        const focused = fields.findIndex(id => document.activeElement && document.activeElement.id === id);
        if (focused >= 0) $('#d'+(focused+1)).removeClass('done').addClass('active');
    }
    fields.forEach(id => {
        $('#'+id).on('input focus', updateDots);
    });

    /* ── char counter ── */
    $('#job_details').on('input', function () {
        const len = $(this).val().length;
        const el = $('#charCount');
        el.text(len + ' / 800');
        el.toggleClass('warn', len > 700);
    });

    /* ── toast helper ── */
    function showToast(msg, type) {
        const t = $('#toast');
        t.removeClass('success error show').text(msg);
        setTimeout(() => t.addClass(type + ' show'), 10);
        setTimeout(() => t.removeClass('show'), 4200);
    }

    /* ── submit ── */
    $('#contactForm').on('submit', function (e) {
        e.preventDefault();

        // basic client-side validation with shake
        let valid = true;
        fields.forEach(id => {
            const input = $('#'+id);
            if (!input.val().trim()) {
                input.addClass('shake');
                setTimeout(() => input.removeClass('shake'), 400);
                valid = false;
            }
        });
        if (!valid) { showToast('Please fill in all fields.', 'error'); return; }

        const btn = $('#submitBtn');
        btn.prop('disabled', true);
        btn.find('.btn-text').hide();
        btn.find('.btn-loading').show();

        $.ajax({
            url: 'contact_process.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                btn.prop('disabled', false);
                btn.find('.btn-text').show();
                btn.find('.btn-loading').hide();
                if (res.success) {
                    showToast('✓ Message sent! We\'ll be in touch soon.', 'success');
                    $('#contactForm')[0].reset();
                    $('#charCount').text('0 / 800');
                    fields.forEach(id => $('#d'+(fields.indexOf(id)+1)).removeClass('done active'));
                    $('#d1').addClass('active');
                } else {
                    showToast(res.message || 'Something went wrong. Try again.', 'error');
                }
            },
            error: function () {
                btn.prop('disabled', false);
                btn.find('.btn-text').show();
                btn.find('.btn-loading').hide();
                showToast('Network error — please try again.', 'error');
            }
        });
    });

});
</script>
</body>
</html>