<?php
require_once 'session_helper.php';
$msg = '';
$error = '';

// VULNERABLE: State-changing operations handled loosely via $_REQUEST (GET or POST)
// Allows a SameSite=Lax session cookie bypass since Lax cookies are sent on top-level GET navigations.
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$to = isset($_REQUEST['to']) ? $_REQUEST['to'] : '';
$amount = isset($_REQUEST['amount']) ? floatval($_REQUEST['amount']) : 0.00;

if ($action === 'transfer') {
    if (!empty($to) && $amount > 0) {
        if ($_SESSION['balance'] >= $amount) {
            $_SESSION['balance'] -= $amount;
            $msg = "Success: Successfully transferred $" . number_format($amount, 2) . " to " . htmlspecialchars($to);
            
            // Check if GET was used to flag the bypass
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $msg .= " (Exploited! You bypassed SameSite=Lax POST restrictions using a GET request!)";
            }
        } else {
            $error = "Error: Insufficient funds in your account.";
        }
    } else {
        $error = "Error: Invalid recipient or transfer amount.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 8: High | CSRF Lab</title>
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
        <h1>Level 8: High — SameSite Lax loose-method Bypass</h1>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?php echo $msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Active Session Status -->
        <div class="status-box">
            <p style="margin: 0 0 5px 0; font-size: 0.85rem; color: #94a3b8; text-transform: uppercase;">Current Bank Account Status (Alice)</p>
            <div>User: <span class="status-val"><?php echo htmlspecialchars($_SESSION['user']); ?></span></div>
            <div>Balance: <span class="status-val">$<?php echo number_format($_SESSION['balance'], 2); ?></span></div>
        </div>

        <p>The system's session cookies are configured with <code>SameSite=Lax</code>, which prevents cookies from being attached to cross-site <code>POST</code> requests. However, the state-changing fund transfer endpoint is loosely coded using <code>$_REQUEST</code>, meaning **it accepts parameters via both GET and POST**.</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
// VULNERABLE: Uses $_REQUEST instead of restricting to $_POST
$action = $_REQUEST['action'];
$to = $_REQUEST['to'];
$amount = $_REQUEST['amount'];

if ($action === 'transfer') {
    $_SESSION['balance'] -= $amount;
}
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Bypass the SameSite=Lax protection. Since browsers *do* attach Lax cookies during top-level GET navigations (such as clicking a standard <code>&lt;a href&gt;</code> link or setting <code>window.location.href</code>), you can initiate a CSRF attack simply by forcing the victim to navigate via a GET request:</p>
        <pre><code>&lt;a href="http://localhost:8080/csrf-lab/level8.php?action=transfer&to=attacker@evil.local&amount=250"&gt;
    Click here to see a funny cat video!
&lt;/a&gt;</code></pre>

        <form action="level8.php" method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <input type="hidden" name="action" value="transfer">
            <label for="to">Recipient Email:</label><br>
            <input type="email" id="to" name="to" value="bob@securesite.local" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br><br>
            
            <label for="amount">Amount ($):</label><br>
            <input type="number" id="amount" name="amount" value="50.00" step="0.01" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            
            <input type="submit" value="Transfer Funds" class="btn">
        </form>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
