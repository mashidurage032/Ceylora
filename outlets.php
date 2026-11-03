<?php include 'includes/header.php';

// Get all outlets
$stmt = $conn->query("SELECT * FROM outlets ORDER BY city");
$outlets = $stmt->fetchAll();
?>

<div class="breadcrumb">
    <div class="container">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li>Our Outlets</li>
        </ul>
    </div>
</div>

<!-- Hero -->
<div class="about-hero" style="padding:60px 0;">
    <div class="container">
        <h1 style="color:var(--white); font-size:2.5rem;">🏪 Our Outlets</h1>
        <p style="color:rgba(255,255,255,0.9); font-size:1.1rem;">Visit us at any of our locations across Sri Lanka</p>
    </div>
</div>

<section>
    <div class="container">
        <!-- Map of all outlets -->
        <div style="margin-bottom:40px;">
            <h2 style="margin-bottom:15px;">📍 Find Us on the Map</h2>
            <div id="map"></div>
        </div>

        <div class="outlets-grid">
            <?php foreach ($outlets as $outlet): ?>
            <div class="outlet-card">
                <div class="outlet-card-image">
                    🏪
                </div>
                <div class="outlet-card-body">
                    <h3><?= h($outlet['name']) ?></h3>
                    <div class="info-row">
                        <span class="icon">📍</span>
                        <span><?= h($outlet['address']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="icon">🏙️</span>
                        <span><?= h($outlet['city']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="icon">📞</span>
                        <span><?= h($outlet['phone']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="icon">✉️</span>
                        <span><?= h($outlet['email']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="icon">🕐</span>
                        <span><?= h($outlet['opening_hours']) ?></span>
                    </div>
                    <?php if ($outlet['latitude'] && $outlet['longitude']): ?>
                    <a href="https://www.google.com/maps?q=<?= $outlet['latitude'] ?>,<?= $outlet['longitude'] ?>" target="_blank" class="btn btn-sm btn-primary" style="margin-top:15px;">
                        🗺️ Get Directions
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var outlets = <?= json_encode($outlets) ?>;
        initOutletMap(outlets);
    });
</script>

<?php include 'includes/footer.php'; ?>