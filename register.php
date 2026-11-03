<?php include 'includes/header.php';
require_once 'includes/email.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name        = trim($_POST['full_name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $address          = trim($_POST['address'] ?? '');
    $city             = trim($_POST['city'] ?? '');
    $country          = trim($_POST['country'] ?? 'Sri Lanka');

    if (empty($full_name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email already registered
        $stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existingUser = $stmt->fetch();

        if ($existingUser && $existingUser['is_verified'] == 1) {
            $error = "Email already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            try {
                if ($existingUser) {
                    // Update existing unverified user details
                    $stmt = $conn->prepare("
                        UPDATE users 
                        SET full_name = ?, phone = ?, password = ?, address = ?, city = ?, country = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$full_name, $phone, $hashedPassword, $address, $city, $country, $existingUser['id']]);
                } else {
                    // Insert new unverified user
                    $stmt = $conn->prepare("
                        INSERT INTO users (full_name, email, phone, password, address, city, country, is_verified) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 0)
                    ");
                    $stmt->execute([$full_name, $email, $phone, $hashedPassword, $address, $city, $country]);
                }

                // Generate OTP
                $otp = generateOTP(6);

                // Save OTP to DB
                if (saveOTP($conn, $email, $otp)) {
                    // Send OTP email
                    if (sendOtpEmail($email, $otp, $full_name)) {
                        // Store pending email in session
                        $_SESSION['pending_email'] = $email;

                        // Redirect to OTP verification page
                        header('Location: verify-register.php');
                        exit;
                    } else {
                        // If running on localhost (development), allow flow to continue
                        $isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false;
                        if ($isLocal) {
                            // store pending email and a flag so verify page can show the OTP for dev testing
                            $_SESSION['pending_email'] = $email;
                            $_SESSION['dev_show_otp'] = true;
                            unset($_SESSION['smtp_error']);
                            header('Location: verify-register.php');
                            exit;
                        }

                        $error = "Failed to send OTP email.";
                        if (!empty($_SESSION['smtp_error'])) {
                            $error .= " (" . $_SESSION['smtp_error'] . ")";
                            unset($_SESSION['smtp_error']);
                        } else {
                            $error .= " Please check your SMTP configuration in config/mail.php.";
                        }
                    }
                } else {
                    $error = "Could not generate OTP. Please try again.";
                }
            } catch (PDOException $e) {
                $error = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="auth-page">
    <div class="auth-card">
        <h2>Create Account</h2>
        <p class="subtitle">Join Ceylora and explore authentic Sri Lankan treasures</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" required value="<?= h($_POST['full_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?= h($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" required>
                </div>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" value="<?= h($_POST['address'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" value="<?= h($_POST['city'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" value="<?= h($_POST['country'] ?? 'Sri Lanka') ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:10px;">
                Send Verification Code
            </button>
        </form>
        <div class="auth-footer">
            Already have an account? <a href="login.php"><strong>Login here</strong></a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
