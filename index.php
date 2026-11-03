<?php include 'includes/header.php'; ?>

<?php
// Get featured products
$stmt = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.featured = 1 LIMIT 8");
$featuredProducts = $stmt->fetchAll();

// Get categories
$stmt = $conn->query("SELECT * FROM categories LIMIT 8");
$categories = $stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-ornament">✦ ✦ ✦</div>
            <h1>Welcome to Ceylora</h1>
            <p class="tagline">Authentic Sri Lankan Treasures</p>
            <p>Discover the finest handicrafts, spices, Ceylon tea, gems, and traditional souvenirs from the Pearl of the Indian Ocean.</p>
            <div style="display:flex; gap:15px; justify-content:center;">
                <a href="products.php" class="btn btn-primary btn-lg">Shop Now</a>
                <a href="outlets.php" class="btn btn-outline btn-lg">Find Outlets</a>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section style="background: var(--white);">
    <div class="container">
        <div class="section-header">
            <span class="ornament">✦ ✦ ✦</span>
            <h2>Explore Categories</h2>
            <p>Browse our collection of authentic Sri Lankan products</p>
        </div>
        <div class="categories-grid">
            <?php
            $icons = ['🎨', '🍵', '🌿', '🎁', '💎', '🧵', '🌺', '🥥'];
            foreach ($categories as $i => $cat):
            ?>
            <a href="products.php?category=<?= $cat['id'] ?>" class="category-card">
                <div class="icon"><?= $icons[$i] ?? '📦' ?></div>
                <h3><?= h($cat['name']) ?></h3>
                <p><?= h(substr($cat['description'], 0, 60)) ?>...</p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section>
    <div class="container">
        <div class="section-header">
            <span class="ornament">✦ ✦ ✦</span>
            <h2>Featured Products</h2>
            <p>Hand-picked treasures from across Sri Lanka</p>
        </div>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <span class="product-badge">Featured</span>
                    <?php if ($product['image'] && file_exists('uploads/products/' . $product['image'])): ?>
                        <img src="uploads/products/<?= h($product['image']) ?>" alt="<?= h($product['name']) ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/400x300/e5f5e0/00441b?text=<?= urlencode($product['name']) ?>" alt="<?= h($product['name']) ?>">
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="category-tag"><?= h($product['category_name']) ?></span>
                    <h3><a href="product_detail.php?id=<?= $product['id'] ?>"><?= h($product['name']) ?></a></h3>
                    <div class="price"><?= formatPrice($product['price']) ?></div>
                    <div class="product-actions">
                        <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn btn-secondary btn-sm">View Details</a>
                        <a href="add_to_cart.php?product_id=<?= $product['id'] ?>" class="btn btn-primary btn-sm">Add to Cart</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center; margin-top:40px;">
            <a href="products.php" class="btn btn-secondary btn-lg">View All Products →</a>
        </div>
    </div>
</section>

<!-- Why Ceylora Section -->
<section style="background: var(--white);">
    <div class="container">
        <div class="section-header">
            <span class="ornament">✦ ✦ ✦</span>
            <h2>Why Choose Ceylora?</h2>
            <p>We bring the authentic Sri Lankan experience to you</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="icon">🏆</div>
                <h3>100% Authentic</h3>
                <p>All products are sourced directly from local Sri Lankan artisans and producers.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🌍</div>
                <h3>Worldwide Shipping</h3>
                <p>We deliver authentic Sri Lankan products to your doorstep globally.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🔒</div>
                <h3>Secure Payment</h3>
                <p>Shop with confidence using our secure payment gateway.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>