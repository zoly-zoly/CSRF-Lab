<?php
require_once 'session_helper.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛡️ CSRF Masterclass Lab Suite (10 Levels)</title>
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --accent-low: #10b981;
            --accent-medium: #f59e0b;
            --accent-high: #ef4444;
            --accent-critical: #8b5cf6;
            --text-color: #f1f5f9;
            --text-muted: #94a3b8;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1 {
            font-size: 2.6rem;
            margin-bottom: 5px;
            text-align: center;
            letter-spacing: -0.5px;
        }
        p.subtitle {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin-bottom: 25px;
            text-align: center;
            max-width: 700px;
            line-height: 1.6;
        }
        .status-panel {
            background-color: #1e293b;
            border-radius: 10px;
            padding: 15px 30px;
            display: flex;
            gap: 40px;
            margin-bottom: 40px;
            border: 1px solid #334155;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
        }
        .status-item {
            display: flex;
            flex-direction: column;
        }
        .status-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        .status-value {
            font-weight: bold;
            font-size: 1.1rem;
            color: #38bdf8;
        }
        .reset-btn {
            background-color: #ef4444;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.85rem;
            transition: background-color 0.2s;
        }
        .reset-btn:hover {
            background-color: #dc2626;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            max-width: 1300px;
            width: 100%;
        }
        .card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            transition: transform 0.2s, box-shadow 0.2s;
            border-top: 5px solid;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3);
        }
        .card.low { border-color: var(--accent-low); }
        .card.medium { border-color: var(--accent-medium); }
        .card.high { border-color: var(--accent-high); }
        .card.critical { border-color: var(--accent-critical); }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 0.75rem;
            font-weight: bold;
            border-radius: 20px;
            margin-bottom: 15px;
            align-self: flex-start;
            text-transform: uppercase;
        }
        .card.low .badge { background-color: rgba(16, 185, 129, 0.15); color: var(--accent-low); }
        .card.medium .badge { background-color: rgba(245, 158, 11, 0.15); color: var(--accent-medium); }
        .card.high .badge { background-color: rgba(239, 68, 68, 0.15); color: var(--accent-high); }
        .card.critical .badge { background-color: rgba(139, 92, 246, 0.15); color: var(--accent-critical); }

        h2 {
            margin: 0 0 10px 0;
            font-size: 1.3rem;
            line-height: 1.3;
        }
        .desc {
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 20px;
            flex-grow: 1;
        }
        .btn {
            display: inline-block;
            background-color: #3b82f6;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: background-color 0.2s;
            font-size: 0.9rem;
        }
        .card.low .btn { background-color: var(--accent-low); }
        .card.low .btn:hover { background-color: #059669; }
        .card.medium .btn { background-color: var(--accent-medium); }
        .card.medium .btn:hover { background-color: #d97706; }
        .card.high .btn { background-color: var(--accent-high); }
        .card.high .btn:hover { background-color: #dc2626; }
        .card.critical .btn { background-color: var(--accent-critical); }
        .card.critical .btn:hover { background-color: #7c3aed; }
        
        .footer {
            margin-top: 60px;
            color: var(--text-muted);
            font-size: 0.9rem;
            text-align: center;
        }
    </style>
</head>
<body>

    <h1>🛡️ CSRF Masterclass Lab Suite</h1>
    <p class="subtitle">Welcome to the ultimate 10-level CSRF arena. Perform state-changing actions, bypass advanced referer and token whitelists, handle Double Submit Cookies, defeat SameSite restrictions, and execute critical account takeover chains.</p>

    <!-- Simulated Logged-In User Status -->
    <div class="status-panel">
        <div class="status-item">
            <span class="status-label">Victim User Session</span>
            <span class="status-value"><?php echo htmlspecialchars($_SESSION['user']); ?></span>
        </div>
        <div class="status-item">
            <span class="status-label">Victim Profile Email</span>
            <span class="status-value"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
        </div>
        <div class="status-item">
            <span class="status-label">Victim Bank Balance</span>
            <span class="status-value">$<?php echo number_format($_SESSION['balance'], 2); ?></span>
        </div>
        <a href="index.php?reset_session=1" class="reset-btn">🔄 Reset Lab Session</a>
    </div>

    <div class="grid">
        <!-- LEVEL 1 -->
        <div class="card low">
            <div>
                <span class="badge">Level 1: Low</span>
                <h2>GET Method State Change</h2>
                <p class="desc">A standard GET request performs sensitive profile updates on the server. Change the victim's email using simple HTML request elements.</p>
            </div>
            <a href="level1.php" class="btn">Enter Lab</a>
        </div>

        <!-- LEVEL 2 -->
        <div class="card low">
            <div>
                <span class="badge">Level 2: Low</span>
                <h2>POST Method State Change</h2>
                <p class="desc">State change relies on POST. However, there is no anti-CSRF token protection. Bypass this using an external auto-submitting form.</p>
            </div>
            <a href="level2.php" class="btn">Enter Lab</a>
        </div>

        <!-- LEVEL 3 -->
        <div class="card medium">
            <div>
                <span class="badge">Level 3: Medium</span>
                <h2>Weak Referer Validation</h2>
                <p class="desc">The server checks the HTTP Referer header to verify the origin, but uses a weak validation function. Bypass the check by crafting a clever payload.</p>
            </div>
            <a href="level3.php" class="btn">Enter Lab</a>
        </div>

        <!-- LEVEL 4 -->
        <div class="card medium">
            <div>
                <span class="badge">Level 4: Medium</span>
                <h2>Token Omission Logic Flaw</h2>
                <p class="desc">An anti-CSRF token check is active. However, the logic contains a critical flaw: it validates the token only if the parameter is supplied.</p>
            </div>
            <a href="level4.php" class="btn">Enter Lab</a>
        </div>

        <!-- LEVEL 5 -->
        <div class="card high">
            <div>
                <span class="badge">Level 5: High</span>
                <h2>Detached Session Token</h2>
                <p class="desc">The backend strictly validates the CSRF token parameter, but forgets to verify if the supplied token belongs to the *current user's* session.</p>
            </div>
            <a href="level5.php" class="btn">Enter Lab</a>
        </div>

        <!-- LEVEL 6 -->
        <div class="card high">
            <div>
                <span class="badge">Level 6: High</span>
                <h2>Double Submit Cookie Flaw</h2>
                <p class="desc">Anti-CSRF validation relies on matching a form parameter with a cookie. Exploit this logic since the server doesn't link the cookie to an active session.</p>
            </div>
            <a href="level6.php" class="btn">Enter Lab</a>
        </div>

        <!-- LEVEL 7 -->
        <div class="card high">
            <div>
                <span class="badge">Level 7: High</span>
                <h2>JSON Payload CSRF</h2>
                <p class="desc">The server processes action parameters in JSON format. Bypass standard cross-origin browser protection by exploiting weak Content-Type checks on the backend.</p>
            </div>
            <a href="level7.php" class="btn">Enter Lab</a>
        </div>

        <!-- LEVEL 8 -->
        <div class="card high">
            <div>
                <span class="badge">Level 8: High</span>
                <h2>SameSite Lax loose-method Bypass</h2>
                <p class="desc">Session cookies are set with modern <code>SameSite=Lax</code> rules. However, the backend action accepts both GET and POST requests. Abuse the loose method handling.</p>
            </div>
            <a href="level8.php" class="btn">Enter Lab</a>
        </div>

        <!-- LEVEL 9 -->
        <div class="card critical">
            <div>
                <span class="badge">Level 9: Critical</span>
                <h2>SameSite Bypass via Open Redirect</h2>
                <p class="desc">Cookies are protected with <code>SameSite=Lax</code>. Chain a CSRF request through an Open Redirect vulnerability on the target site to trigger action.</p>
            </div>
            <a href="level9.php" class="btn">Enter Lab</a>
        </div>

        <!-- LEVEL 10 -->
        <div class="card critical">
            <div>
                <span class="badge">Level 10: Critical</span>
                <h2>CSRF to Full Account Takeover</h2>
                <p class="desc">The final boss. Chain CSRF to overwrite the email address, and utilize a mock password reset trigger to complete a silent account takeover.</p>
            </div>
            <a href="level10.php" class="btn">Enter Lab</a>
        </div>
    </div>

    <div class="footer">
        <p>Created by <strong>Zoly</strong> for Security Mastery. Run locally with <code>php -S localhost:8080</code> or your local server configuration.</p>
    </div>

</body>
</html>
