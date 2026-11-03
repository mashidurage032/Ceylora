<?php include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=cart.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get cart items
$stmt = $conn->prepare("
    SELECT c.*, p.name, p.price, p.image, p.stock, cat.name as category_name 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    LEFT JOIN categories cat ON p.category_id = cat.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

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
            <li>Shopping Cart</li>
        </ul>
    </div>
</div>

<section>
    <div class="container">
        <div class="section-header" style="margin-bottom:30px;">
            <h2>🛒 Shopping Cart</h2>
        </div>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">Cart updated successfully!</div>
        <?php endif; ?>
        <?php if (isset($_GET['removed'])): ?>
            <div class="alert alert-info">Item removed from cart.</div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="empty-state">
                <div class="icon">🛒</div>
                <h3>Your Cart is Empty</h3>
                <p>Looks like you haven't added any items yet.</p>
                <a href="products.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                    <tr class="cart-item-row" data-cart-id="<?= $item['id'] ?>">
                        <td>
                            <div class="cart-product">
                                <?php if ($item['image'] && file_exists('uploads/products/' . $item['image'])): ?>
                                    <img src="uploads/products/<?= h($item['image']) ?>" alt="<?= h($item['name']) ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/80x80/e5f5e0/00441b?text=P" alt="">
                                <?php endif; ?>
                                <div>
                                    <strong><?= h($item['name']) ?></strong>
                                    <br><small style="color:var(--text-light);"><?= h($item['category_name'] ?? '') ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= formatPrice($item['price']) ?></td>
                        <td>
                            <form action="update_cart.php" method="POST" class="cart-update-form" style="display:flex; gap:5px; align-items:center;">
                                <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                <input type="number" name="quantity" class="quantity-input" data-cart-id="<?= $item['id'] ?>" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" style="width:70px;">
                                <button type="submit" class="btn btn-sm btn-secondary update-btn">Update</button>
                            </form>
                        </td>
                        <td><strong class="item-total-display"><?= formatPrice($item['price'] * $item['quantity']) ?></strong></td>
                        <td>
                            <a href="remove_from_cart.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this item?')">✕</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="cart-subtotal"><?= formatPrice($subtotal) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span id="cart-shipping"><?= $shipping == 0 ? 'FREE' : formatPrice($shipping) ?></span>
                </div>
                <div id="free-shipping-note" class="summary-row" style="font-size:0.85rem; color:var(--accent-dark); <?= $shipping == 0 ? 'display:none;' : '' ?>">
                    <span>Free shipping on orders over $100</span>
                </div>
                <div class="summary-total">
                    <span>Total</span>
                    <span id="cart-total"><?= formatPrice($total) ?></span>
                </div>
                <a href="checkout.php" class="btn btn-primary" style="width:100%; margin-top:15px;">Proceed to Checkout →</a>
                <a href="products.php" class="btn btn-secondary" style="width:100%; margin-top:10px;">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
    /* Hide update buttons when JavaScript is active */
    .js-active .update-btn {
        display: none !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add class to body to hide manual update buttons via CSS
    document.body.classList.add('js-active');

    const quantityInputs = document.querySelectorAll('.quantity-input');

    quantityInputs.forEach(input => {
        // Submit AJAX on quantity change
        input.addEventListener('change', function() {
            updateCartItem(this);
        });

        // Handle user typing the quantity
        input.addEventListener('keyup', function() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                updateCartItem(this);
            }, 500);
        });
    });

    function updateCartItem(input) {
        const cartId = input.getAttribute('data-cart-id');
        let quantity = parseInt(input.value);
        const maxStock = parseInt(input.getAttribute('max')) || 999;
        
        if (isNaN(quantity) || quantity < 1) {
            quantity = 1;
            input.value = 1;
        } else if (quantity > maxStock) {
            quantity = maxStock;
            input.value = maxStock;
        }

        const row = input.closest('.cart-item-row');
        const totalDisplay = row.querySelector('.item-total-display');

        const formData = new FormData();
        formData.append('cart_id', cartId);
        formData.append('quantity', quantity);
        formData.append('ajax', '1');

        fetch('update_cart.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update specific item total
                totalDisplay.textContent = data.item_total;
                
                // Update summary card values
                document.getElementById('cart-subtotal').textContent = data.subtotal;
                document.getElementById('cart-shipping').textContent = data.shipping;
                document.getElementById('cart-total').textContent = data.total;

                // Toggle visibility of the shipping note based on whether shipping has cost
                const shippingNote = document.getElementById('free-shipping-note');
                if (data.has_shipping) {
                    shippingNote.style.display = 'flex';
                } else {
                    shippingNote.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('AJAX Error:', error);
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>