<?php
require_once 'includes/init.php';

$token = $_GET['token'] ?? '';
$error = '';
$validToken = false;

if (empty($token)) {
    $error = 'Invalid reset link';
} else {
    // Validate token
    $stmt = $mysqli->prepare("SELECT userID, username, reset_token_expiry FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($userID, $username, $expiry);

    if ($stmt->fetch()) {
        // Check if token is expired
        if (strtotime($expiry) < time()) {
            $error = 'This reset link has expired. Please request a new one.';
        } else {
            $validToken = true;
        }
    } else {
        $error = 'Invalid or expired reset link';
    }
    $stmt->close();
}

// Set page variables
$pageTitle = 'Reset Password - Carbon Type';
$bodyClass = 'reset-password-body';
?>
<?php include 'includes/html-head.php'; ?>
    <style>
        .reset-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .reset-box {
            background: rgba(20, 20, 20, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            max-width: 250px;
            width: 100%;
        }
        .reset-box h1 {
            color: #fff;
            font-size: 20px;
            margin-bottom: 10px;
            text-align: center;
        }
        .reset-box p {
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
            margin-bottom: 30px;
            font-size: 12px;
        }
        .error-box {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(255, 0, 0, 0.3);
            color: #ff6b6b;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }
        .success-box {
            background: rgba(0, 255, 0, 0.1);
            border: 1px solid rgba(0, 255, 0, 0.3);
            color: #0f0;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #0ff;
            text-decoration: none;
            font-size: 14px;
        }
    </style>

    <div class="reset-container">
        <div class="reset-box">
            <h1>Reset Password</h1>

            <?php if (!empty($error)): ?>
                <div class="error-box">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <div class="back-link">
                    <a href="/">Back to Home</a>
                </div>
            <?php elseif ($validToken): ?>
                <p>Enter your new password below</p>

                <form id="resetPasswordForm" class="login-form">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" id="newPassword" name="password" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirm_password" required minlength="6">
                    </div>

                    <div id="resetError" class="error-message" style="display: none; margin-bottom: 15px;"></div>
                    <div id="resetSuccess" class="success-message" style="display: none; margin-bottom: 15px;"></div>

                    <button type="submit" class="btn-login">Reset Password</button>
                </form>

                <div class="back-link">
                    <a href="/">Back to Home</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const form = document.getElementById('resetPasswordForm');
        const resetError = document.getElementById('resetError');
        const resetSuccess = document.getElementById('resetSuccess');

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const password = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;

                // Hide previous messages
                resetError.style.display = 'none';
                resetSuccess.style.display = 'none';

                // Validate passwords match
                if (password !== confirmPassword) {
                    resetError.textContent = 'Passwords do not match';
                    resetError.style.display = 'block';
                    return;
                }

                // Validate password length
                if (password.length < 6) {
                    resetError.textContent = 'Password must be at least 6 characters';
                    resetError.style.display = 'block';
                    return;
                }

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Resetting...';
                submitBtn.disabled = true;

                // Send request
                const formData = new FormData(form);

                fetch('/reset-password-handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resetSuccess.textContent = data.message;
                        resetSuccess.style.display = 'block';
                        form.style.display = 'none';

                        // Redirect to home after 2 seconds
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 2000);
                    } else {
                        resetError.textContent = data.message;
                        resetError.style.display = 'block';
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Reset error:', error);
                    resetError.textContent = 'An error occurred. Please try again.';
                    resetError.style.display = 'block';
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
            });
        }
    </script>

<?php include 'includes/html-close.php'; ?>
