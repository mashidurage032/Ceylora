<?php include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$orderId = (int)($_GET['order_id'] ?? 0);

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll();
?>

<section>
    <div class="container">
        <div class="order-success">
            <div class="success-icon">✅</div>
            <h1>Order Placed Successfully!</h1>
            <div class="order-id">Order #<?= $orderId ?></div>
            <p style="color:var(--text-medium); margin-bottom:10px;">Transaction ID: <?= h($order['transaction_id']) ?></p>
            <p style="color:var(--text-medium); margin-bottom:30px;">Thank you for shopping at Ceylora! A confirmation email has been sent to your email address.</p>
            
            <!-- Order Details -->
            <div style="background:var(--white); padding:30px; border-radius:var(--radius-lg); box-shadow:var(--shadow); text-align:left; max-width:600px; margin:0 auto;">
                <h3 style="margin-bottom:20px;">Order Details</h3>
                
                <?php foreach ($orderItems as $item): ?>
                <div style="display:flex; gap:12px; padding:12px 0; border-bottom:1px solid var(--border);">
                    <div style="flex:1;">
                        <strong><?= h($item['name']) ?></strong>
                        <br><small style="color:var(--text-light);">Qty: <?= $item['quantity'] ?> × <?= formatPrice($item['price']) ?></small>
                    </div>
                    <div style="font-weight:600; color:var(--primary);">
                        <?= formatPrice($item['price'] * $item['quantity']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div style="display:flex; justify-content:space-between; padding:15px 0; font-weight:700; font-size:1.2rem; color:var(--primary); border-top:2px solid var(--primary); margin-top:10px;">
                    <span>Total Paid</span>
                    <span><?= formatPrice($order['total_amount']) ?></span>
                </div>
                
                <div style="margin-top:15px; padding:15px; background:var(--secondary); border-radius:var(--radius);">
                    <p style="margin:3px 0;"><strong>Payment Method:</strong> <?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></p>
                    <p style="margin:3px 0;"><strong>Status:</strong> <span class="status-badge status-pending"><?= $order['order_status'] ?></span></p>
                    <p style="margin:3px 0;"><strong>Shipping to:</strong> <?= h($order['shipping_address']) ?>, <?= h($order['shipping_city']) ?></p>
                </div>
            </div>

            <div style="margin-top:30px; display:flex; gap:15px; justify-content:center;">
                <a href="my_orders.php" class="btn btn-secondary">View My Orders</a>
                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>