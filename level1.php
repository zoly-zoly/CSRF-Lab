<?php
require_once 'session_helper.php';
$msg = '';
$error = '';

// VULNERABLE: Handles GET requests for state-changing operations.
if (isset($_GET['action']) && $_GET['action'] === 'update_email') {
    if (isset($_GET['email']) && !empty($_GET['email'])) {
        $_SESSION['email'] = $_GET['email'];
        $msg = "Success: Email successfully updated to " . htmlspecialchars($_GET['email']);
    } else {
        $error = "Error: Email parameter is missing.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 1: Low | CSRF Lab</title>
    <style>
        body { font-family: sans-serif; background-color: #0f172a; color: #f1f5f9; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        .box { background: #1e293b; padding: 30px; border-radius: 12px; max-width: 600px; width: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border-top: 5px solid #10b981; }
        h1 { color: #10b981; margin-top: 0; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; color: #f43f5e; font-family: monospace; }
        pre { background: #0f172a; padding: 15px; border-radius: 6px; overflow-x: auto; border: 1px solid #334155; }
        .btn { display: inline-block; background-color: #10b981; color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; border: none; cursor: pointer; }
        .btn:hover { background-color: #059669; }
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
        <h1>Level 1: Low — GET Method State Change</h1>

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

        <p>The backend updates the victim's profile email using a simple <code>GET</code> request, which is a critical design flaw. Since GET requests are meant only to retrieve data and not alter state, they can be easily forged using simple HTML tags like <code>&lt;img&gt;</code> or <code>&lt;link&gt;</code>.</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
if ($_GET['action'] === 'update_email') {
    $_SESSION['email'] = $_GET['email'];
}
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Forge a GET request on behalf of the victim. If you host an external page containing <code>&lt;img src="http://localhost:8080/csrf-lab/level1.php?action=update_email&email=attacker@evil.local"&gt;</code>, the victim's email changes instantly without user interaction when they visit your page!</p>

        <form action="level1.php" method="GET" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
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
