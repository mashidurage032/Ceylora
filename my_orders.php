<?php include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=my_orders.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get all orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();
?>

<div class="breadcrumb">
    <div class="container">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li>My Orders</li>
        </ul>
    </div>
</div>

<section>
    <div class="container">
        <div class="section-header" style="margin-bottom:30px;">
            <h2>📦 My Orders</h2>
            <p>Track your order status</p>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <div class="icon">📦</div>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet.</p>
                <a href="products.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong>#<?= $order['id'] ?></strong></td>
                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td><strong><?= formatPrice($order['total_amount']) ?></strong></td>
                        <td>
                            <span style="color:var(--primary); font-weight:500;">
                                <?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?>
                            </span>
                            <br><small style="color:var(--text-light);"><?= $order['payment_status'] ?></small>
                        </td>
                        <td>
                            <?php
                            $statusClass = 'status-pending';
                            switch(strtolower($order['order_status'])) {
                                case 'in progress': $statusClass = 'status-processing'; break;
                                case 'completed': $statusClass = 'status-completed'; break;
                                case 'cancelled': $statusClass = 'status-cancelled'; break;
                            }
                            ?>
                            <span class="status-badge <?= $statusClass ?>"><?= h($order['order_status']) ?></span>
                        </td>
                        <td>
                            <a href="order_confirmation.php?order_id=<?= $order['id'] ?>" class="btn btn-sm btn-secondary">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>