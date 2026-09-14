<?php
require_once 'session_helper.php';
$msg = '';
$error = '';

// VULNERABLE: Loose Referer validation check.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $current_host = $_SERVER['HTTP_HOST']; // E.g., localhost:8080
    
    // Developer checks if the current host string is present ANYWHERE in the Referer header.
    // Also, if the Referer is completely missing, the developer permits it to avoid blocking legitimate users behind proxies.
    if (empty($referer) || strpos($referer, $current_host) !== false) {
        if (isset($_POST['action']) && $_POST['action'] === 'update_email' && isset($_POST['email'])) {
            $_SESSION['email'] = $_POST['email'];
            $msg = "Success: Email successfully updated to " . htmlspecialchars($_POST['email']);
        }
    } else {
        $error = "Access Denied: Referer validation failed! Your Referer was: '" . htmlspecialchars($referer) . "'. It must contain '" . htmlspecialchars($current_host) . "'";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 3: Medium | CSRF Lab</title>
    <style>
        body { font-family: sans-serif; background-color: #0f172a; color: #f1f5f9; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        .box { background: #1e293b; padding: 30px; border-radius: 12px; max-width: 600px; width: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border-top: 5px solid #f59e0b; }
        h1 { color: #f59e0b; margin-top: 0; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; color: #f43f5e; font-family: monospace; }
        pre { background: #0f172a; padding: 15px; border-radius: 6px; overflow-x: auto; border: 1px solid #334155; }
        .btn { display: inline-block; background-color: #f59e0b; color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; border: none; cursor: pointer; }
        .btn:hover { background-color: #d97706; }
        a.back { color: #94a3b8; text-decoration: none; display: inline-block; margin-top: 20px; }
        a.back:hover { color: #f1f5f9; }
        .status-box { background: #0f172a; padding: 15px; border-radius: 6px; border: 1px solid #334155; margin-bottom: 20px; }
        .status-val { font-weight: bold; color: #38bdf8; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }
        .alert-success { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid #10b981; }
        .alert-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid #ef4444; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Level 3: Medium — Weak Referer Validation</h1>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?php echo $msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Active Session Status -->
        <div class="status-box">
            <p style="margin: 0 0 5px 0; font-size: 0.85rem; color: #94a3b8; text-transform: uppercase;">Current Victim Profile Status</p>
            <div>User: <span class="status-val"><?php echo htmlspecialchars($_SESSION['user']); ?></span></div>
            <div>Email: <span class="status-val"><?php echo htmlspecialchars($_SESSION['email']); ?></span></div>
        </div>

        <p>The developer added a validation check to verify that the HTTP <code>Referer</code> header contains the application's hostname. However, using <code>strpos()</code> on the whole Referer string is weak, and allowing empty referers creates a major bypass.</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
$referer = $_SERVER['HTTP_REFERER'];
$allowed_host = $_SERVER['HTTP_HOST']; // E.g. localhost:8080

// VULNERABLE logic: Checks for partial matches and permits empty referers
if (empty($referer) || strpos($referer, $allowed_host) !== false) {
    // Execute state change
}
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Bypass the Referer check. There are two distinct bypass techniques:</p>
        <ol>
            <li><strong>Query/Path Injection:</strong> Host your exploit on an external domain, but include the allowed host in the path or query string: <br><code>http://evil.com/exploit.html?localhost:8080</code>. The referer will contain the allowed host, passing the <code>strpos</code> check!</li>
            <li><strong>Referer Stripping:</strong> Instruct the browser to strip the Referer header completely by adding a meta tag to your attacker exploit: <br><code>&lt;meta name="referrer" content="no-referrer"&gt;</code>.</li>
        </ol>

        <form action="level3.php" method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <input type="hidden" name="action" value="update_email">
            <label for="email">Update Email (Legitimate Flow):</label><br>
            <input type="email" id="email" name="email" value="alice@securesite.local" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Update Email" class="btn">
        </form>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
