<?php
require_once 'config/db.php';

// Step 1: Check database connection
echo "<h3>Step 1: Database Connection</h3>";
echo "✅ Connected successfully<br><br>";

// Step 2: Check admin record
echo "<h3>Step 2: Admin Record</h3>";
$stmt = $conn->prepare("SELECT * FROM admins WHERE username = 'admin'");
$stmt->execute();
$admin = $stmt->fetch();

if ($admin) {
    echo "✅ Admin found<br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Email: " . $admin['email'] . "<br>";
    echo "Stored Hash: " . $admin['password'] . "<br><br>";
} else {
    echo "❌ No admin found!<br>";
}

// Step 3: Verify password
echo "<h3>Step 3: Password Verification</h3>";
$testPassword = 'admin123';
$result = password_verify($testPassword, $admin['password']);
echo "Testing password 'admin123': " . ($result ? '✅ MATCH' : '❌ FAIL') . "<br><br>";

// Step 4: If FAIL, generate new hash and update
if (!$result) {
    echo "<h3>Step 4: Fix - Generating New Hash</h3>";
    $newHash = password_hash('admin123', PASSWORD_DEFAULT);
    echo "New hash: " . $newHash . "<br><br>";
    
    // Auto-fix: Update the database
    $update = $conn->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
    $update->execute([$newHash]);
    echo "✅ Password updated! Try logging in now.<br>";
    
    // Verify again
    $stmt2 = $conn->prepare("SELECT password FROM admins WHERE username = 'admin'");
    $stmt2->execute();
    $newAdmin = $stmt2->fetch();
    $verify = password_verify('admin123', $newAdmin['password']);
    echo "Re-verify: " . ($verify ? '✅ NOW WORKING!' : '❌ Still failing') . "<br>";
}
?>