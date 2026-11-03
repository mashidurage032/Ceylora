<?php include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Get cart items
$stmt = $conn->prepare("
    SELECT c.*, p.name, p.price, p.image, p.stock 
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

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $subtotal > 100 ? 0 : 10;
$total = $subtotal + $shipping;
?>

<div class="breadcrumb">
    <div class="container">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li>Checkout</li>
        </ul>
    </div>
</div>

<section>
    <div class="container">
        <div class="section-header" style="margin-bottom:30px;">
            <h2>💳 Checkout</h2>
            <p>Complete your order with secure payment</p>
        </div>

        <form id="checkout-form" action="process_payment.php" method="POST">
            <div class="checkout-grid">
                <!-- Shipping & Payment Details -->
                <div>
                    <!-- Shipping Information -->
                    <div class="checkout-form" style="margin-bottom:25px;">
                        <h2>📦 Shipping Information</h2>
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="full_name" required value="<?= h($user['full_name']) ?>">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" required value="<?= h($user['email']) ?>">
                            </div>
                            <div class="form-group">
                                <label>Phone *</label>
                                <input type="text" name="phone" required value="<?= h($user['phone'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Shipping Address *</label>
                            <textarea name="shipping_address" rows="3" required><?= h($user['address'] ?? '') ?></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>City *</label>
                                <input type="text" name="shipping_city" required value="<?= h($user['city'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Country *</label>
                                <input type="text" name="shipping_country" required value="<?= h($user['country'] ?? 'Sri Lanka') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Order Notes (Optional)</label>
                            <textarea name="notes" rows="2" placeholder="Any special instructions..."></textarea>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-form">
                        <h2>💳 Payment Method</h2>
                        <div class="payment-methods">
                            <label class="payment-method selected">
                                <input type="radio" name="payment_method" value="card" checked>
                                <span>💳</span>
                                <div>
                                    <strong>Credit / Debit Card</strong>
                                    <br><small style="color:var(--text-light);">Visa, MasterCard, Amex</small>
                                </div>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="paypal">
                                <span>🅿️</span>
                                <div>
                                    <strong>PayPal</strong>
                                    <br><small style="color:var(--text-light);">Pay with your PayPal account</small>
                                </div>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="bank_transfer">
                                <span>🏦</span>
                                <div>
                                    <strong>Bank Transfer</strong>
                                    <br><small style="color:var(--text-light);">Direct bank transfer</small>
                                </div>
                            </label>
                        </div>

                        <!-- Card Details -->
                        <div id="card-details" class="card-details">
                            <h4>Card Details</h4>
                            <div class="card-icons" style="margin-bottom:15px;">
                                <span style="font-size:2rem;">💳</span>
                                <span style="background:#1a1f71; color:white; padding:5px 10px; border-radius:4px; font-weight:bold; font-size:0.8rem;">VISA</span>
                                <span style="background:#eb001b; color:white; padding:5px 10px; border-radius:4px; font-weight:bold; font-size:0.8rem;">MC</span>
                            </div>
                            <div class="form-group">
                                <label>Card Number *</label>
                                <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            <div class="form-group">
                                <label>Cardholder Name *</label>
                                <input type="text" id="card_name" name="card_name" placeholder="John Doe">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Expiry Date *</label>
                                    <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" maxlength="5">
                                </div>
                                <div class="form-group">
                                    <label>CVV *</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="3">
                                </div>
                            </div>
                        </div>
                        
                        <div style="margin-top:15px; padding:15px; background:var(--secondary); border-radius:var(--radius); text-align:center;">
                            <p style="color:var(--primary); font-size:0.9rem;">🔒 Your payment information is encrypted and secure</p>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div>
                    <div class="payment-section">
                        <h2 style="font-size:1.5rem;">📋 Order Summary</h2>
                        <div style="margin-top:20px;">
                            <?php foreach ($cartItems as $item): ?>
                            <div style="display:flex; gap:12px; padding:12px 0; border-bottom:1px solid var(--border);">
                                <?php if ($item['image'] && file_exists('uploads/products/' . $item['image'])): ?>
                                    <img src="uploads/products/<?= h($item['image']) ?>" style="width:60px; height:60px; object-fit:cover; border-radius:8px;">
                                <?php else: ?>
                                    <div style="width:60px; height:60px; background:var(--secondary); border-radius:8px; display:flex; align-items:center; justify-content:center;">📦</div>
                                <?php endif; ?>
                                <div style="flex:1;">
                                    <strong style="font-size:0.9rem;"><?= h($item['name']) ?></strong>
                                    <br><small style="color:var(--text-light);">Qty: <?= $item['quantity'] ?></small>
                                </div>
                                <div style="font-weight:600; color:var(--primary);">
                                    <?= formatPrice($item['price'] * $item['quantity']) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div style="margin-top:20px;">
                            <div class="summary-row" style="display:flex; justify-content:space-between; padding:8px 0;">
                                <span>Subtotal</span>
                                <span><?= formatPrice($subtotal) ?></span>
                            </div>
                            <div class="summary-row" style="display:flex; justify-content:space-between; padding:8px 0;">
                                <span>Shipping</span>
                                <span><?= $shipping == 0 ? 'FREE' : formatPrice($shipping) ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between; padding:15px 0; font-weight:700; font-size:1.3rem; color:var(--primary); border-top:2px solid var(--primary); margin-top:10px;">
                                <span>Total</span>
                                <span><?= formatPrice($total) ?></span>
                            </div>
                        </div>

                        <input type="hidden" name="total_amount" value="<?= $total ?>">
                        <button type="submit" class="btn btn-primary btn-lg" style="width:100%; margin-top:20px; font-size:1.1rem;">
                            🔒 Pay <?= formatPrice($total) ?>
                        </button>
                        
                        <a href="cart.php" style="display:block; text-align:center; margin-top:15px; color:var(--text-light);">← Back to Cart</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>