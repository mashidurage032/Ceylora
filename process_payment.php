<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get cart items
$stmt = $conn->prepare("
    SELECT c.*, p.name, p.price, p.stock 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

// Validate stock
foreach ($cartItems as $item) {
    if ($item['quantity'] > $item['stock']) {
        $_SESSION['error'] = "Sorry, '{$item['name']}' only has {$item['stock']} in stock.";
        header('Location: cart.php');
        exit;
    }
}

// Collect form data
$fullName = trim($_POST['full_name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$shippingAddress = trim($_POST['shipping_address']);
$shippingCity = trim($_POST['shipping_city']);
$shippingCountry = trim($_POST['shipping_country']);
$paymentMethod = $_POST['payment_method'];
$notes = trim($_POST['notes'] ?? '');
$totalAmount = (float)$_POST['total_amount'];

// Simulate payment processing
// In production, this would integrate with Stripe, PayPal, or another gateway
$transactionId = 'TXN_' . strtoupper(uniqid()) . '_' . time();
$paymentStatus = 'paid'; // Simulated successful payment

// For card payment - validate card details (simulated)
if ($paymentMethod === 'card') {
    $cardNumber = $_POST['card_number'] ?? '';
    $cardName = $_POST['card_name'] ?? '';
    $expiryDate = $_POST['expiry_date'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    
    // Basic validation (in production, card processing would happen via payment gateway API)
    if (empty($cardNumber) || empty($cardName) || empty($expiryDate) || empty($cvv)) {
        $_SESSION['error'] = "Please fill in all card details.";
        header('Location: checkout.php');
        exit;
    }
}

try {
    $conn->beginTransaction();

    // Create order
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, total_amount, shipping_address, shipping_city, shipping_country, phone, payment_method, payment_status, transaction_id, order_status, notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)
    ");
    $stmt->execute([$userId, $totalAmount, $shippingAddress, $shippingCity, $shippingCountry, $phone, $paymentMethod, $paymentStatus, $transactionId, $notes]);
    $orderId = $conn->lastInsertId();

    // Create order items and update stock
    foreach ($cartItems as $item) {
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);

        // Update stock
        $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }

    // Clear cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);

    $conn->commit();

    // Send email notification (simulated)
    $userStmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $userEmail = $userStmt->fetchColumn();
    
    // In production: use PHPMailer or similar
    $subject = "Ceylora - Order Confirmation #$orderId";
    $message = "Dear $fullName,\n\nThank you for your order!\n\nOrder ID: #$orderId\nTransaction ID: $transactionId\nTotal: $" . number_format($totalAmount, 2) . "\n\nWe will process your order shortly.\n\nBest regards,\nCeylora Team";
    $headers = "From: orders@ceylora.com";
    @mail($userEmail, $subject, $message, $headers);

    header("Location: order_confirmation.php?order_id=$orderId");
    exit;

} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = "Payment processing failed. Please try again.";
    header('Location: checkout.php');
    exit;
}
?>