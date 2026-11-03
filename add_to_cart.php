<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=products.php');
    exit;
}

$userId = $_SESSION['user_id'];
$productId = 0;
$quantity = 1;
$redirect = 'cart.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)$_POST['product_id'];
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    $redirect = $_POST['redirect'] ?? 'cart.php';
} elseif (isset($_GET['product_id'])) {
    $productId = (int)$_GET['product_id'];
}

if ($productId > 0) {
    // Check if product exists and has stock
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND stock > 0");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if ($product) {
        // Check if already in cart
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$newQty, $existing['id']]);
        } else {
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $productId, $quantity]);
        }
    }
}

header('Location: ' . $redirect);
exit;
?>