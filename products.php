<?php include 'includes/header.php';

// Get categories for filter
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Build query
$where = "WHERE 1=1";
$params = [];

if (isset($_GET['category']) && $_GET['category'] != '') {
    $where .= " AND p.category_id = ?";
    $params[] = $_GET['category'];
}

if (isset($_GET['search']) && $_GET['search'] != '') {
    $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = '%' . $_GET['search'] . '%';
    $params[] = '%' . $_GET['search'] . '%';
}

// Sorting
$orderBy = "ORDER BY p.created_at DESC";
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_low': $orderBy = "ORDER BY p.price ASC"; break;
        case 'price_high': $orderBy = "ORDER BY p.price DESC"; break;
        case 'name': $orderBy = "ORDER BY p.name ASC"; break;
    }
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Get total count
$countStmt = $conn->prepare("SELECT COUNT(*) FROM products p $where");
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

// Get products
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $where $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="container">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li>Products</li>
        </ul>
    </div>
</div>

<section>
    <div class="container">
        <div class="section-header">
            <span class="ornament">✦ ✦ ✦</span>
            <h2>Our Products</h2>
            <p>Discover authentic Sri Lankan treasures</p>
        </div>

        <!-- Filters -->
        <div style="background: var(--white); padding: 20px 25px; border-radius: var(--radius-lg); box-shadow: var(--shadow); margin-bottom: 30px; display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
            <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; width: 100%;">
                <div style="flex: 2; min-width: 200px;">
                    <input type="text" name="search" placeholder="🔍 Search products..." value="<?= h($_GET['search'] ?? '') ?>" style="width: 100%; padding: 10px 15px; border: 2px solid var(--border); border-radius: var(--radius);">
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <select name="category" style="width: 100%; padding: 10px 15px; border: 2px solid var(--border); border-radius: var(--radius);">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '' ?>><?= h($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <select name="sort" style="width: 100%; padding: 10px 15px; border: 2px solid var(--border); border-radius: var(--radius);">
                        <option value="">Sort By</option>
                        <option value="price_low" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_low') ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_high') ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="name" <?= (isset($_GET['sort']) && $_GET['sort'] == 'name') ? 'selected' : '' ?>>Name: A-Z</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            </form>
        </div>

        <p style="margin-bottom: 20px; color: var(--text-light);">Showing <?= count($products) ?> of <?= $totalProducts ?> products</p>

        <?php if (empty($products)): ?>
            <div class="empty-state">
                <div class="icon">🔍</div>
                <h3>No Products Found</h3>
                <p>Try adjusting your search or filter criteria.</p>
                <a href="products.php" class="btn btn-secondary">View All Products</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['featured']): ?>
                            <span class="product-badge">Featured</span>
                        <?php endif; ?>
                        <?php if ($product['image'] && file_exists('uploads/products/' . $product['image'])): ?>
                            <img src="uploads/products/<?= h($product['image']) ?>" alt="<?= h($product['name']) ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x300/e5f5e0/00441b?text=<?= urlencode($product['name']) ?>" alt="<?= h($product['name']) ?>">
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <span class="category-tag"><?= h($product['category_name'] ?? 'General') ?></span>
                        <h3><a href="product_detail.php?id=<?= $product['id'] ?>"><?= h($product['name']) ?></a></h3>
                        
                        <!-- ✅ LKR Price Display -->
                        <div class="price">Rs. <?= number_format($product['price'], 2) ?></div>
                        
                        <div class="product-actions">
                            <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn btn-secondary btn-sm">Details</a>
                            <a href="add_to_cart.php?product_id=<?= $product['id'] ?>" class="btn btn-primary btn-sm">Add to Cart</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php
                    $queryParams = $_GET;
                    $queryParams['page'] = $i;
                    $queryString = http_build_query($queryParams);
                    ?>
                    <?php if ($i == $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= $queryString ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>