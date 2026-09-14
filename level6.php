<?php
require_once 'session_helper.php';
$msg = '';
$error = '';

// Set the initial Double Submit Cookie if not set (Legitimate flow)
if (!isset($_COOKIE['csrf_cookie'])) {
    setcookie('csrf_cookie', 'alice_double_submit_cookie_99a8b1', time() + 3600, '/', '', false, false); // HttpOnly is false so it can be read/manipulated
    $_COOKIE['csrf_cookie'] = 'alice_double_submit_cookie_99a8b1';
}

// VULNERABLE: Stateless Double Submit Cookie comparison.
// The server verifies that the submitted form parameter equals the cookie.
// It does not check if the cookie value belongs to an active, validated session.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_email' && isset($_POST['email'])) {
        $csrf_cookie = isset($_COOKIE['csrf_cookie']) ? $_COOKIE['csrf_cookie'] : '';
        $csrf_param = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
        
        if (!empty($csrf_cookie) && !empty($csrf_param) && $csrf_cookie === $csrf_param) {
            $_SESSION['email'] = $_POST['email'];
            $msg = "Success: Email successfully updated to " . htmlspecialchars($_POST['email']);
            if ($csrf_param === 'attacker_forced_cookie') {
                $msg .= " (Exploited! You successfully forced both the cookie and parameter to match!)";
            }
        } else {
            $error = "Access Denied: Double Submit Cookie mismatch! Cookie: '" . htmlspecialchars($csrf_cookie) . "' | Form Parameter: '" . htmlspecialchars($csrf_param) . "'";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 6: High | CSRF Lab</title>
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
        <h1>Level 6: High — Double Submit Cookie Flaw</h1>

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
            <div>CSRF Cookie (Value): <span class="status-val" style="color: #cbd5e1;"><?php echo htmlspecialchars(isset($_COOKIE['csrf_cookie']) ? $_COOKIE['csrf_cookie'] : 'None'); ?></span></div>
        </div>

        <p>This endpoint implements the **Double Submit Cookie** pattern. The backend checks if the <code>csrf_token</code> POST parameter matches the <code>csrf_cookie</code> cookie. However, because this verification is stateless (no session check for the cookie value), the server simply asks: "Are they equal?"</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
$csrf_cookie = $_COOKIE['csrf_cookie'];
$csrf_param = $_POST['csrf_token'];

// VULNERABLE: No validation checking if the cookie value is legitimate/tied to session
if ($csrf_cookie === $csrf_param) {
    // Process update
}
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Bypass the check. If an attacker can set a cookie on the victim's browser (e.g., via a subdomain or by using JavaScript), they can set both the cookie AND the form parameter to the same value (e.g. <code>attacker_forced_cookie</code>) to satisfy the equation.</p>
        <p><em>To test this locally: run <code>document.cookie="csrf_cookie=attacker_forced_cookie; path=/"</code> in your browser console, then submit your forged CSRF POST form with <code>csrf_token</code> value set to <code>attacker_forced_cookie</code>!</em></p>

        <form action="level6.php" method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <input type="hidden" name="action" value="update_email">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(isset($_COOKIE['csrf_cookie']) ? $_COOKIE['csrf_cookie'] : ''); ?>">
            <label for="email">Update Email (Legitimate Flow):</label><br>
            <input type="email" id="email" name="email" value="alice@securesite.local" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Update Email" class="btn">
        </form>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
