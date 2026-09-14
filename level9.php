<?php
require_once 'session_helper.php';
$msg = '';
$error = '';

// VULNERABLE: Handles GET requests and relies on strict Referer validation.
// This is bypassable by chaining an Open Redirect on the same trusted domain.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $current_host = $_SERVER['HTTP_HOST']; // E.g., localhost:8080
    
    // Parse referer host
    $parsed_referer = parse_url($referer);
    $referer_host = isset($parsed_referer['host']) ? $parsed_referer['host'] : '';
    $referer_port = isset($parsed_referer['port']) ? ':' . $parsed_referer['port'] : '';
    $referer_full_host = $referer_host . $referer_port;
    
    // STRICT CHECK: The Referer MUST match our current host exactly.
    // This blocks standard cross-site CSRF from third-party attacker domains.
    if ($referer_full_host === $current_host) {
        if ($_GET['action'] === 'transfer') {
            $to = isset($_GET['to']) ? $_GET['to'] : '';
            $amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0.00;
            
            if (!empty($to) && $amount > 0 && $_SESSION['balance'] >= $amount) {
                $_SESSION['balance'] -= $amount;
                $msg = "Success: Successfully transferred $" . number_format($amount, 2) . " to " . htmlspecialchars($to);
                
                // Detect if the request was channeled via a local open redirect page
                if (isset($_GET['via_redirect']) && $_GET['via_redirect'] == '1') {
                    $msg .= " (Exploited! You successfully bypassed strict Referer checks by chaining with an Open Redirect!)";
                }
            } else {
                $error = "Error: Invalid transaction parameters or insufficient funds.";
            }
        }
    } else {
        $error = "Access Denied: Strict Referer validation failed! Referer host was '" . htmlspecialchars($referer_full_host) . "', but we require '" . htmlspecialchars($current_host) . "'";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 9: Critical | CSRF Lab</title>
    <style>
        body { font-family: sans-serif; background-color: #0f172a; color: #f1f5f9; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        .box { background: #1e293b; padding: 30px; border-radius: 12px; max-width: 650px; width: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border-top: 5px solid #8b5cf6; }
        h1 { color: #8b5cf6; margin-top: 0; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; color: #f43f5e; font-family: monospace; }
        pre { background: #0f172a; padding: 15px; border-radius: 6px; overflow-x: auto; border: 1px solid #334155; }
        .btn { display: inline-block; background-color: #8b5cf6; color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; border: none; cursor: pointer; }
        .btn:hover { background-color: #7c3aed; }
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
        <h1>Level 9: Critical — SameSite Bypass via Open Redirect</h1>

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

        <p>The developer implements strict <code>Referer</code> host verification, allowing requests only if the Referer header matches our host exactly. However, this domain has an **Open Redirect** vulnerability on one of its pages (like our Level 1 Open Redirect lab!).</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
$referer_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);

// STRICT checking, but easily bypassed if the site contains an Open Redirect
if ($referer_host === $_SERVER['HTTP_HOST']) {
    // Process transfer via GET
}
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Bypass strict Referer host verification by channeling your request. </p>
        <p><strong>The Chain:</strong> If an attacker forces the victim's browser to visit a local open redirect page on <code>localhost:8080</code> (e.g. <code>open-redirect-lab/level1.php</code>), and points that redirect back to this page with the action parameters, the browser will navigate internally from <code>localhost:8080</code> to this page. The resulting Referer header will point to <code>http://localhost:8080/...</code>, completely satisfying the strict check!</p>

        <!-- Simulated open-redirect attacker trigger form -->
        <form action="level9.php" method="GET" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <input type="hidden" name="action" value="transfer">
            <input type="hidden" name="via_redirect" value="1">
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
