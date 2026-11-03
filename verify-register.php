<?php
require_once 'config/db.php';
require_once 'includes/email.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If no pending email, send back to register
if (empty($_SESSION['pending_email'])) {
    header('Location: register.php');
    exit;
}

$email = $_SESSION['pending_email'];

// Fetch user details from DB
$stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    unset($_SESSION['pending_email']);
    header('Location: register.php');
    exit;
}

$error   = '';
$success = '';

// Mask email for display: e.g. jo***@gmail.com
function maskEmail($email) {
    [$local, $domain] = explode('@', $email);
    $masked = substr($local, 0, 2) . str_repeat('*', max(2, strlen($local) - 2));
    return $masked . '@' . $domain;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'verify';

    // ── Resend OTP ──────────────────────────────────────────────
    if ($action === 'resend') {
        $otp = generateOTP(6);
        if (saveOTP($conn, $email, $otp) && sendOtpEmail($email, $otp, $user['full_name'])) {
            $success = "A new OTP has been sent to " . maskEmail($email) . ".";
        } else {
            $error = "Failed to resend OTP. Please try again.";
        }
    }

    // ── Verify OTP ──────────────────────────────────────────────
    if ($action === 'verify') {
        $otp_input = trim($_POST['otp'] ?? '');

        if (strlen($otp_input) !== 6 || !ctype_digit($otp_input)) {
            $error = "Please enter a valid 6-digit OTP.";
        } elseif (!verifyOTP($conn, $email, $otp_input)) {
            $error = "Invalid or expired OTP. Please try again or resend.";
        } else {
            try {
                // Update user to verified
                $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
                $stmt->execute([$user['id']]);

                // Clear pending session data
                unset($_SESSION['pending_email']);

                // Log user in automatically
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $email;

                header('Location: index.php');
                exit;

            } catch (PDOException $e) {
                $error = "Verification failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ceylora - Verify Email</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .otp-input {
            letter-spacing: 8px;
            font-size: 24px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="auth-page">
    <div class="auth-card">
        <h2><i class="fas fa-envelope-open-text" style="color:var(--primary);"></i> Verify Your Email</h2>
        <p class="subtitle">
            We sent a 6-digit verification code to<br>
            <strong><?= h(maskEmail($email)) ?></strong>.<br>
            The code expires in <strong>15 minutes</strong>.
        </p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php endif; ?>

        <!-- Verify Form -->
        <form method="POST">
            <input type="hidden" name="action" value="verify">
            <div class="form-group">
                <label>Enter OTP Code</label>
                <input
                    class="otp-input"
                    type="text"
                    name="otp"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    placeholder="000000"
                    required
                    autofocus
                >
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:10px;">
                <i class="fas fa-check-circle"></i> Verify & Create Account
            </button>
        </form>

        <!-- Resend OTP Form -->
        <form method="POST" style="margin-top: 12px;">
            <input type="hidden" name="action" value="resend">
            <button type="submit" class="btn" style="width:100%; background:#f0f0f0; color:#333;">
                <i class="fas fa-redo"></i> Resend OTP
            </button>
        </form>

        <div class="auth-footer" style="margin-top: 16px;">
            <a href="register.php">← Go back to Registration</a>
        </div>
    </div>
</div>

</body>
</html>
