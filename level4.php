<?php
require_once 'session_helper.php';
$msg = '';
$error = '';

// Generate CSRF token for the session if not already set
if (!isset($_SESSION['csrf_token_lvl4'])) {
    $_SESSION['csrf_token_lvl4'] = bin2hex(random_bytes(16));
}

// VULNERABLE: Checks token validation ONLY IF the parameter is set.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_email' && isset($_POST['email'])) {
        
        // Logical check flaw
        if (isset($_POST['csrf_token'])) {
            if ($_POST['csrf_token'] === $_SESSION['csrf_token_lvl4']) {
                $_SESSION['email'] = $_POST['email'];
                $msg = "Success: Email successfully updated to " . htmlspecialchars($_POST['email']);
            } else {
                $error = "Access Denied: Invalid anti-CSRF token!";
            }
        } else {
            // VULNERABLE: If token parameter is completely missing, it bypasses validation!
            $_SESSION['email'] = $_POST['email'];
            $msg = "Success: Email successfully updated to " . htmlspecialchars($_POST['email']) . " (Bypassed Token Check!)";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 4: Medium | CSRF Lab</title>
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
        <h1>Level 4: Medium — Token Omission Logic Flaw</h1>

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

        <p>The developer implements a cryptographic anti-CSRF token verification. However, the logical parser code is flawed: **the token is only validated if it is present in the POST parameter.** If the parameter is missing, the code skips the check entirely.</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // VULNERABLE: Logic error!
    if (isset($_POST['csrf_token'])) {
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("Invalid Token!");
        }
    }
    // If 'csrf_token' is not sent, execution falls through!
    $_SESSION['email'] = $_POST['email'];
}
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Bypass the token validation check. Craft a POST payload containing only the <code>action</code> and <code>email</code> parameters, while completely omitting the <code>csrf_token</code> parameter from your form submit.</p>

        <form action="level4.php" method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <input type="hidden" name="action" value="update_email">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token_lvl4']; ?>">
            <label for="email">Update Email (Legitimate Flow):</label><br>
            <input type="email" id="email" name="email" value="alice@securesite.local" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Update Email" class="btn">
        </form>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
