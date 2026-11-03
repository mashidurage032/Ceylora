<?php
/**
 * Ceylora OTP System Status Checker
 * Access: http://localhost/ceylora/otp-system-status.php
 * 
 * This page checks if all components are configured correctly
 */

require_once 'config/db.php';

$status = [
    'database' => false,
    'users_table' => false,
    'otp_table' => false,
    'email_config' => false,
    'email_functions' => false,
    'log_directory' => false,
    'email_log' => false,
    'emails_txt' => false,
];

$details = [];

// Check Database Connection
try {
    $test = $conn->query("SELECT 1");
    $status['database'] = true;
    $details['database'] = '✅ Connected to MySQL';
} catch (Exception $e) {
    $details['database'] = '❌ Database connection failed: ' . $e->getMessage();
}

// Check Users Table
try {
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() > 0) {
        $columns = $conn->query("SHOW COLUMNS FROM users")->fetchAll();
        $hasIsVerified = array_filter($columns, fn($col) => $col['Field'] === 'is_verified');
        if ($hasIsVerified) {
            $status['users_table'] = true;
            $userCount = $conn->query("SELECT COUNT(*) as cnt FROM users")->fetch()['cnt'];
            $details['users_table'] = "✅ Users table exists with is_verified column ($userCount users)";
        } else {
            $details['users_table'] = '⚠️ Users table missing is_verified column';
        }
    } else {
        $details['users_table'] = '❌ Users table not found (run db.sql)';
    }
} catch (Exception $e) {
    $details['users_table'] = '❌ Error checking users table: ' . $e->getMessage();
}

// Check OTP Verifications Table
try {
    $result = $conn->query("SHOW TABLES LIKE 'otp_verifications'");
    if ($result->rowCount() > 0) {
        $status['otp_table'] = true;
        $otpCount = $conn->query("SELECT COUNT(*) as cnt FROM otp_verifications")->fetch()['cnt'];
        $details['otp_table'] = "✅ OTP verifications table exists ($otpCount records)";
    } else {
        $details['otp_table'] = '❌ OTP verifications table not found (run db.sql)';
    }
} catch (Exception $e) {
    $details['otp_table'] = '❌ Error checking OTP table: ' . $e->getMessage();
}

// Check Email Config
try {
    $config = require 'config/mail.php';
    if (isset($config['host']) && isset($config['username']) && isset($config['password'])) {
        if (strpos($config['username'], 'your_email') === false && strpos($config['password'], 'your_app_password') === false) {
            $status['email_config'] = true;
            $details['email_config'] = "✅ SMTP configured for: {$config['host']}:{$config['port']}";
        } else {
            $details['email_config'] = '⚠️ SMTP has placeholder values (not configured yet)';
        }
    } else {
        $details['email_config'] = '❌ SMTP configuration incomplete';
    }
} catch (Exception $e) {
    $details['email_config'] = '❌ Error loading mail config: ' . $e->getMessage();
}

// Check Email Functions
if (file_exists('includes/email.php')) {
    $status['email_functions'] = true;
    $details['email_functions'] = '✅ Email functions file exists';
} else {
    $details['email_functions'] = '❌ includes/email.php not found';
}

// Check Log Directory
$logDir = __DIR__ . '/logs';
if (is_dir($logDir)) {
    $status['log_directory'] = true;
    $details['log_directory'] = '✅ Logs directory exists';
} else {
    $details['log_directory'] = '⚠️ Logs directory not created yet (will be created on first email send)';
}

// Check Email Log
if (file_exists($logDir . '/email.log')) {
    $logSize = filesize($logDir . '/email.log');
    $status['email_log'] = true;
    $details['email_log'] = "✅ Email log exists (" . ($logSize / 1024) . " KB)";
} else {
    $details['email_log'] = '⚠️ Email log not created yet (created on first email send)';
}

// Check Emails.txt
if (file_exists('emails.txt')) {
    $txtSize = filesize('emails.txt');
    $status['emails_txt'] = true;
    $lines = count(file('emails.txt'));
    $details['emails_txt'] = "✅ emails.txt exists (" . ($txtSize / 1024) . " KB, $lines lines)";
} else {
    $details['emails_txt'] = '⚠️ emails.txt not created yet (created on first email send)';
}

// Calculate overall status
$allPassed = array_filter($status);
$passPercentage = round((count($allPassed) / count($status)) * 100);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ceylora OTP System Status</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #80181e, #4e0b0e);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .status-bar {
            background: linear-gradient(90deg, #e8e8e8, #e8e8e8);
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        .status-fill {
            background: linear-gradient(90deg, #27ae60, #229954);
            height: 100%;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
            transition: width 0.3s ease;
        }
        .status-text {
            text-align: center;
            margin-bottom: 30px;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .checks {
            display: grid;
            gap: 15px;
        }
        .check {
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #bbb;
            background: #f9f9f9;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .check.pass {
            border-left-color: #27ae60;
            background: #f0fdf4;
        }
        .check.warn {
            border-left-color: #f59e0b;
            background: #fffbf0;
        }
        .check.fail {
            border-left-color: #dc2626;
            background: #fef2f2;
        }
        .check-icon {
            font-size: 24px;
            min-width: 30px;
        }
        .check-text {
            flex: 1;
        }
        .check-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .check-detail {
            font-size: 14px;
            color: #666;
        }
        .actions {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #80181e;
            color: white;
        }
        .btn-primary:hover {
            background: #4e0b0e;
        }
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        .btn-secondary:hover {
            background: #d0d0d0;
        }
        .next-steps {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 6px;
            margin-top: 30px;
        }
        .next-steps h3 {
            margin-top: 0;
            color: #1e40af;
        }
        .next-steps ol {
            margin: 0;
            padding-left: 20px;
        }
        .next-steps li {
            margin: 10px 0;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏛️ Ceylora OTP System Status</h1>
            <p>Email Verification Configuration Check</p>
        </div>
        
        <div class="content">
            <!-- Status Bar -->
            <div class="status-bar">
                <div class="status-fill" style="width: <?= $passPercentage ?>%">
                    <?= $passPercentage ?>%
                </div>
            </div>
            
            <div class="status-text">
                <?php if ($passPercentage === 100): ?>
                    ✅ All systems operational!
                <?php elseif ($passPercentage >= 75): ?>
                    ✅ Mostly ready - minor setup needed
                <?php elseif ($passPercentage >= 50): ?>
                    ⚠️ Partially configured - action needed
                <?php else: ?>
                    ❌ Setup incomplete - follow next steps
                <?php endif; ?>
            </div>
            
            <!-- Status Checks -->
            <div class="checks">
                <?php foreach ($status as $key => $value): ?>
                    <?php
                        $class = $value ? 'pass' : ($details[$key][0] === '⚠️' ? 'warn' : 'fail');
                        $icon = $value ? '✅' : ($details[$key][0] === '⚠️' ? '⚠️' : '❌');
                    ?>
                    <div class="check <?= $class ?>">
                        <div class="check-icon"><?= $icon ?></div>
                        <div class="check-text">
                            <div class="check-label"><?= ucwords(str_replace('_', ' ', $key)) ?></div>
                            <div class="check-detail"><?= $details[$key] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Next Steps -->
            <div class="next-steps">
                <h3>📋 Next Steps</h3>
                <?php if (strpos($details['email_config'], 'placeholder') !== false || strpos($details['email_config'], 'not configured') !== false): ?>
                    <ol>
                        <li><strong>Configure SMTP:</strong> Edit <code>config/mail.php</code> with your email provider credentials</li>
                        <li><strong>Choose Provider:</strong> Gmail (easiest), Mailtrap (dev), SendGrid (production)</li>
                        <li><strong>Test:</strong> Visit <a href="register.php">register.php</a> to test registration with OTP</li>
                        <li><strong>Check Logs:</strong> After registration, check <code>/emails.txt</code> for OTP code</li>
                    </ol>
                <?php elseif ($passPercentage < 100): ?>
                    <ol>
                        <li><strong>Run Database:</strong> Import <code>db.sql</code> if not done</li>
                        <li><strong>Test:</strong> Visit <a href="register.php">register.php</a> to test</li>
                    </ol>
                <?php else: ?>
                    <ol>
                        <li><strong>Test Registration:</strong> <a href="register.php">Click here to register a test user</a></li>
                        <li><strong>Check Email:</strong> Look for OTP in your inbox</li>
                        <li><strong>Monitor Logs:</strong> Check <code>/emails.txt</code> and <code>/logs/</code></li>
                    </ol>
                <?php endif; ?>
            </div>
            
            <!-- Actions -->
            <div class="actions">
                <a href="SMTP_SETUP_GUIDE.md" class="btn btn-primary">📖 Setup Guide</a>
                <a href="QUICK_REFERENCE.md" class="btn btn-primary">🔍 Quick Reference</a>
                <a href="register.php" class="btn btn-primary">📝 Test Registration</a>
                <a href="javascript:location.reload()" class="btn btn-secondary">🔄 Refresh</a>
            </div>
        </div>
    </div>
</body>
</html>
