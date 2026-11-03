<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$cartId = (int)($_GET['id'] ?? 0);

if ($cartId > 0) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cartId, $_SESSION['user_id']]);
}

header('Location: cart.php?removed=1');
exit;
?>