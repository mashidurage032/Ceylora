<?php include 'includes/header.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Check if user is verified
        $isVerified = (isset($user['is_verified']) && $user['is_verified'] == 1);

        if (!$isVerified) {
            // User not verified - send OTP
            require_once 'includes/email.php';
            $otp = generateOTP(6);
            if (saveOTP($conn, $email, $otp)) {
                sendOtpEmail($email, $otp, $user['full_name']);
            }
            $_SESSION['pending_email'] = $email;
            $_SESSION['login_otp_required'] = true;
            header('Location: verify-login.php');
            exit;
        } else {
            // User verified - log in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            
            // Redirect to intended page or home
            $redirect = $_GET['redirect'] ?? 'index.php';
            header('Location: ' . $redirect);
            exit;
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<div class="auth-page">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p class="subtitle">Login to your Ceylora account</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">Registration successful! Please login.</div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:10px;">Login</button>
        </form>
        <div class="auth-footer">
            Don't have an account? <a href="register.php"><strong>Register here</strong></a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>