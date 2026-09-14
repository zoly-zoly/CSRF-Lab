<?php
require_once 'session_helper.php';
$msg = '';
$error = '';

// Generate CSRF token for the victim (Alice)
if (!isset($_SESSION['csrf_token_lvl5_alice'])) {
    $_SESSION['csrf_token_lvl5_alice'] = 'alice_secure_token_5e89a3';
}

// Attacker (Bob) has logged into his own account on his own browser and obtained his own valid token
$attacker_bob_valid_token = 'bob_secure_token_9f31c2';

// VULNERABLE: The server validates that the token is "valid", but fails to verify if it belongs to Alice.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_email' && isset($_POST['email'])) {
        $submitted_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
        
        // Validation check (flawed): Looks up if the token exists in the "active token pool"
        $valid_token_pool = [$_SESSION['csrf_token_lvl5_alice'], $attacker_bob_valid_token];
        
        if (in_array($submitted_token, $valid_token_pool)) {
            $_SESSION['email'] = $_POST['email'];
            $msg = "Success: Email successfully updated to " . htmlspecialchars($_POST['email']);
            if ($submitted_token === $attacker_bob_valid_token) {
                $msg .= " (Exploited! You successfully used Bob's session token to trigger Alice's action!)";
            }
        } else {
            $error = "Access Denied: Invalid anti-CSRF token!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 5: High | CSRF Lab</title>
    <style>
        body { font-family: sans-serif; background-color: #0f172a; color: #f1f5f9; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        .box { background: #1e293b; padding: 30px; border-radius: 12px; max-width: 600px; width: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border-top: 5px solid #ef4444; }
        h1 { color: #ef4444; margin-top: 0; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; color: #f43f5e; font-family: monospace; }
        pre { background: #0f172a; padding: 15px; border-radius: 6px; overflow-x: auto; border: 1px solid #334155; }
        .btn { display: inline-block; background-color: #ef4444; color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; border: none; cursor: pointer; }
        .btn:hover { background-color: #dc2626; }
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
        <h1>Level 5: High — Detached Session Token</h1>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?php echo $msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Active Session Status -->
        <div class="status-box">
            <p style="margin: 0 0 5px 0; font-size: 0.85rem; color: #94a3b8; text-transform: uppercase;">Current Victim Profile Status (Alice's Session)</p>
            <div>User: <span class="status-val"><?php echo htmlspecialchars($_SESSION['user']); ?></span></div>
            <div>Email: <span class="status-val"><?php echo htmlspecialchars($_SESSION['email']); ?></span></div>
        </div>

        <p>This endpoint implements cryptographic tokens and enforces that they must be present. However, the validation engine queries a central pool of "all active/valid session tokens" but fails to check if the supplied token belongs to the **current active user session (Alice).**</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
$submitted_token = $_POST['csrf_token'];

// VULNERABLE: Check against any active token, not Alice's specifically
if (in_array($submitted_token, $global_active_tokens)) {
    // Process update
}
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Bypass the protection by using a **different user's valid token**. </p>
        <p>Log into your attacker account (Bob) and retrieve your own valid token: <code><?php echo $attacker_bob_valid_token; ?></code>. Put Bob's token into your CSRF payload directed at Alice. Since the server recognizes it as a valid active token, it executes the action on Alice's account!</p>

        <form action="level5.php" method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <input type="hidden" name="action" value="update_email">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token_lvl5_alice']; ?>">
            <label for="email">Update Email (Legitimate Flow):</label><br>
            <input type="email" id="email" name="email" value="alice@securesite.local" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Update Email" class="btn">
        </form>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
