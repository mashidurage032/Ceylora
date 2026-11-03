<?php include 'includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($message)) {
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        $success = "Thank you for your message! We'll get back to you shortly.";
    }
}
?>

<div class="breadcrumb">
    <div class="container">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li>Contact Us</li>
        </ul>
    </div>
</div>

<section>
    <div class="container">
        <div class="section-header">
            <span class="ornament">✦ ✦ ✦</span>
            <h2>Contact Us</h2>
            <p>We'd love to hear from you</p>
        </div>

        <div class="contact-grid">
            <div class="contact-form">
                <h2 style="font-size:1.5rem; margin-bottom:20px;">Send us a Message</h2>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= h($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= h($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Your Name *</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject">
                    </div>
                    <div class="form-group">
                        <label>Message *</label>
                        <textarea name="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message →</button>
                </form>
            </div>

            <div class="contact-info-cards">
                <div class="contact-info-card">
                    <h3>📍 Main Office</h3>
                    <p>No. 45, York Street<br>Colombo 01, Sri Lanka</p>
                </div>
                <div class="contact-info-card">
                    <h3>📞 Phone</h3>
                    <p>+94 11 234 5678<br>+94 77 123 4567</p>
                </div>
                <div class="contact-info-card">
                    <h3>✉️ Email</h3>
                    <p>info@ceylora.com<br>support@ceylora.com</p>
                </div>
                <div class="contact-info-card">
                    <h3>🕐 Business Hours</h3>
                    <p>Monday - Saturday<br>9:00 AM - 8:00 PM</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>