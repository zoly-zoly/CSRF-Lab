<?php
require_once 'session_helper.php';
$msg = '';
$error = '';
$stage = 1;

// Handles both the email update (CSRF step 1) and password reset request (Takeover step 2)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_email') {
            // CSRF step: Update email without token validation (vulnerable!)
            if (isset($_POST['email']) && !empty($_POST['email'])) {
                $_SESSION['email'] = $_POST['email'];
                $msg = "Success: Email successfully updated to " . htmlspecialchars($_POST['email']);
            }
        } else if ($_POST['action'] === 'request_reset') {
            // Takeover step: Request a password reset
            $current_email = $_SESSION['email'];
            $stage = 2;
            
            // Check if account has been hijacked
            if (strpos($current_email, 'evil.local') !== false || $current_email !== 'alice@securesite.local') {
                $msg = "🔥 CRITICAL: Password reset link successfully sent to '" . htmlspecialchars($current_email) . "'. ACCOUNT TAKEOVER COMPLETE!";
            } else {
                $msg = "Legitimate: Password reset link safely sent to your original email 'alice@securesite.local'.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 10: Critical | CSRF Lab</title>
    <style>
        body { font-family: sans-serif; background-color: #0f172a; color: #f1f5f9; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        .box { background: #1e293b; padding: 30px; border-radius: 12px; max-width: 650px; width: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border-top: 5px solid #8b5cf6; }
        h1 { color: #8b5cf6; margin-top: 0; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; color: #f43f5e; font-family: monospace; }
        pre { background: #0f172a; padding: 15px; border-radius: 6px; overflow-x: auto; border: 1px solid #334155; }
        .btn { display: inline-block; background-color: #8b5cf6; color: white; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; border: none; cursor: pointer; }
        .btn:hover { background-color: #7c3aed; }
        a.back { color: #94a3b8; text-decoration: none; display: inline-block; margin-top: 20px; }
        a.back:hover { color: #f1f5f9; }
        .status-box { background: #0f172a; padding: 15px; border-radius: 6px; border: 1px solid #334155; margin-bottom: 20px; }
        .status-val { font-weight: bold; color: #38bdf8; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }
        .alert-success { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid #10b981; }
        .alert-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid #ef4444; }
        .alert-critical { background: rgba(139, 92, 246, 0.15); color: #a78bfa; border: 1px solid #8b5cf6; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 0.9; } 50% { opacity: 1; } 100% { opacity: 0.9; } }
    </style>
</head>
<body>
    <div class="box">
        <h1>Level 10: Critical — CSRF to Account Takeover</h1>

        <?php if (!empty($msg)): ?>
            <div class="alert <?php echo ($stage === 2 && strpos($_SESSION['email'], 'evil.local') !== false) ? 'alert-critical' : 'alert-success'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <!-- Active Session Status -->
        <div class="status-box">
            <p style="margin: 0 0 5px 0; font-size: 0.85rem; color: #94a3b8; text-transform: uppercase;">Current System Status (Alice's Session)</p>
            <div>User: <span class="status-val"><?php echo htmlspecialchars($_SESSION['user']); ?></span></div>
            <div>Registered Email: <span class="status-val"><?php echo htmlspecialchars($_SESSION['email']); ?></span></div>
        </div>

        <p>This level illustrates how a seemingly low or medium-impact CSRF flaw can be chained to achieve critical impact: **Complete Account Takeover.** </p>
        <p>The profile email update form is vulnerable to CSRF. When an attacker updates the email address of the victim, they can then invoke the "Request Password Reset" utility. The backend fetches the current email of the session and sends the reset link directly to the newly updated attacker email address!</p>
        
        <h3>The Chain Lifecycle:</h3>
        <pre><code>1. Trigger CSRF Form to update Alice's email to 'attacker@evil.local'
2. Trigger POST to request_reset endpoint
3. System routes the password reset token directly to 'attacker@evil.local'
4. Attacker clicks token, changes password, and fully hijacks account!</code></pre>

        <h3>Your Goal:</h3>
        <p>Perform the full takeover chain. Update Alice's email to <code>attacker@evil.local</code> (or any other domain) via CSRF, then request a password reset below and observe where the system sends the reset keys!</p>

        <!-- Form 1: Vulnerable email update (Simulated local endpoint) -->
        <form action="level10.php" method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <input type="hidden" name="action" value="update_email">
            <label for="email">Update Email (Legitimate Profile Flow):</label><br>
            <input type="email" id="email" name="email" value="alice@securesite.local" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Update Email" class="btn" style="background-color: #475569;">
        </form>

        <!-- Form 2: Password reset utility -->
        <form action="level10.php" method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed #334155;">
            <input type="hidden" name="action" value="request_reset">
            <p style="margin: 0 0 10px 0; font-size: 0.9rem; color: #94a3b8;">Forgotten your password? Request a recovery link:</p>
            <input type="submit" value="Request Password Reset Link" class="btn">
        </form>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
