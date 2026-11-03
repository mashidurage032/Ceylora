<?php include 'includes/header.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product details
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

// Get outlet locations for this product
$stmt = $conn->prepare("
    SELECT o.* FROM outlets o 
    INNER JOIN product_outlets po ON o.id = po.outlet_id 
    WHERE po.product_id = ? 
    ORDER BY o.city
");
$stmt->execute([$productId]);
$outlets = $stmt->fetchAll();

// Get related products
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? LIMIT 4");
$stmt->execute([$product['category_id'], $productId]);
$relatedProducts = $stmt->fetchAll();
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="container">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
            <li><?= h($product['name']) ?></li>
        </ul>
    </div>
</div>

<section>
    <div class="container">
        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success">✅ Product added to cart successfully!</div>
        <?php endif; ?>

        <div class="product-detail">
            <!-- Product Image -->
            <div class="product-gallery">
                <?php if ($product['image'] && file_exists('uploads/products/' . $product['image'])): ?>
                    <img src="uploads/products/<?= h($product['image']) ?>" alt="<?= h($product['name']) ?>">
                <?php else: ?>
                    <img src="https://via.placeholder.com/600x500/e5f5e0/00441b?text=<?= urlencode($product['name']) ?>" alt="<?= h($product['name']) ?>">
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="product-details-info">
                <div class="category-breadcrumb"><?= h($product['category_name'] ?? 'General') ?></div>
                <h1><?= h($product['name']) ?></h1>
                <div class="price"><?= formatPrice($product['price']) ?></div>
                
                <div class="description">
                    <?= nl2br(h($product['description'])) ?>
                </div>

                <?php if ($product['stock'] > 0): ?>
                    <div class="stock-info in-stock">
                        ✅ In Stock (<?= $product['stock'] ?> available)
                    </div>
                <?php else: ?>
                    <div class="stock-info out-of-stock">
                        ❌ Out of Stock
                    </div>
                <?php endif; ?>

                <?php if ($product['stock'] > 0): ?>
                <form action="add_to_cart.php" method="POST" class="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="redirect" value="product_detail.php?id=<?= $product['id'] ?>&added=1">
                    <div class="quantity-selector">
                        <label>Quantity:</label>
                        <button type="button" class="btn btn-sm btn-secondary qty-minus">−</button>
                        <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="qty-input">
                        <button type="button" class="btn btn-sm btn-secondary qty-plus">+</button>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">🛒 Add to Cart</button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Outlet Locations for This Product -->
        <?php if (!empty($outlets)): ?>
        <div class="outlet-section">
            <h2>📍 Available at These Ceylora Outlets</h2>
            <p style="color: var(--text-medium); margin-bottom: 20px;">Visit any of our outlets to see this product in person</p>
            
            <div class="outlets-list">
                <?php foreach ($outlets as $outlet): ?>
                <div class="outlet-item">
                    <h4>🏪 <?= h($outlet['name']) ?></h4>
                    <p>📍 <?= h($outlet['address']) ?></p>
                    <p>📞 <?= h($outlet['phone']) ?></p>
                    <p>🕐 <?= h($outlet['opening_hours']) ?></p>
                    <?php if ($outlet['latitude'] && $outlet['longitude']): ?>
                    <a href="https://www.google.com/maps?q=<?= $outlet['latitude'] ?>,<?= $outlet['longitude'] ?>" target="_blank" class="view-map">
                        🗺️ View on Google Maps →
                    </a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Map -->
            <div id="map"></div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var outlets = <?= json_encode($outlets) ?>;
                    initOutletMap(outlets);
                });
            </script>
        </div>
        <?php endif; ?>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div style="margin-top: 60px;">
            <div class="section-header">
                <h2>You May Also Like</h2>
            </div>
            <div class="products-grid">
                <?php foreach ($relatedProducts as $rp): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($rp['image'] && file_exists('uploads/products/' . $rp['image'])): ?>
                            <img src="uploads/products/<?= h($rp['image']) ?>" alt="<?= h($rp['name']) ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x300/e5f5e0/00441b?text=<?= urlencode($rp['name']) ?>" alt="<?= h($rp['name']) ?>">
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <span class="category-tag"><?= h($rp['category_name']) ?></span>
                        <h3><a href="product_detail.php?id=<?= $rp['id'] ?>"><?= h($rp['name']) ?></a></h3>
                        <div class="price"><?= formatPrice($rp['price']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>