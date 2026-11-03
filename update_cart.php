<?php
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartId = (int)$_POST['cart_id'];
    $quantity = max(1, (int)$_POST['quantity']);
    
    // Fetch product details for price calculation
    $stmt = $conn->prepare("
        SELECT p.price 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.id = ? AND c.user_id = ?
    ");
    $stmt->execute([$cartId, $_SESSION['user_id']]);
    $product = $stmt->fetch();
    
    if ($product) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cartId, $_SESSION['user_id']]);
        
        // Check if AJAX request
        $isAjax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        
        if ($isAjax) {
            // Get updated cart items to calculate new totals
            $stmt = $conn->prepare("
                SELECT c.quantity, p.price 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $cartItems = $stmt->fetchAll();
            
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $shipping = $subtotal > 100 ? 0 : 10;
            $total = $subtotal + $shipping;
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'item_total' => formatPrice($product['price'] * $quantity),
                'subtotal' => formatPrice($subtotal),
                'shipping' => $shipping == 0 ? 'FREE' : formatPrice($shipping),
                'total' => formatPrice($total),
                'has_shipping' => $shipping > 0
            ]);
            exit;
        }
    }
}

header('Location: cart.php?updated=1');
exit;
?>