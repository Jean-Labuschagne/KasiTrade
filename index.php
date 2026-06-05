<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

// Fetch featured listings
$stmt = $pdo->query("SELECT l.*, u.username, u.township, c.category_name,
                     (SELECT AVG(rating) FROM reviews WHERE reviewee_id = l.seller_id) as seller_rating
                     FROM listings l
                     JOIN users u ON l.seller_id = u.user_id
                     JOIN categories c ON l.category_id = c.category_id
                     WHERE l.status = 'active'
                     ORDER BY l.created_at DESC
                     LIMIT 8");
$featured = $stmt->fetchAll();

// Fetch categories
$stmt = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL LIMIT 6");
$categories = $stmt->fetchAll();
?>

<!-- Hero Section -->
<div class="hero-section bg-kasitrade text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-5 fw-bold">Buy & Sell in Your Township</h1>
                <p class="lead">South Africa's trusted marketplace for local trade. Low data, secure payments, community pickup.</p>
                <div class="d-flex gap-2">
                    <a href="browse.php" class="btn btn-light btn-lg"><i class="bi bi-search"></i> Browse Now</a>
                    <a href="create-listing.php" class="btn btn-outline-light btn-lg"><i class="bi bi-plus-circle"></i> Start Selling</a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="text-center">
                    <i class="bi bi-shop display-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Categories -->
<div class="container py-4">
    <h5 class="mb-3"><i class="bi bi-grid"></i> Popular Categories</h5>
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
        <?php foreach ($categories as $cat): ?>
        <div class="col">
            <a href="browse.php?category=<?= $cat['category_id'] ?>" class="text-decoration-none">
                <div class="card h-100 text-center p-3 category-card">
                    <i class="bi bi-box-seam display-4 text-kasitrade"></i>
                    <h6 class="mt-2 mb-0"><?= clean($cat['category_name']) ?></h6>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Featured Listings -->
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5><i class="bi bi-star-fill text-warning"></i> Featured Listings</h5>
        <a href="browse.php" class="text-decoration-none">View All <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
        <?php foreach ($featured as $item): ?>
        <div class="col">
            <div class="card h-100 listing-card shadow-sm">
                <div class="position-relative">
                    <img src="<?= $item['image_paths'] ? json_decode($item['image_paths'])[0] : 'assets/images/no-image.jpg' ?>" 
                         class="card-img-top" alt="<?= clean($item['title']) ?>" loading="lazy"
                         style="height: 180px; object-fit: cover;" onerror="this.src='assets/images/no-image.jpg'">
                    <span class="badge bg-<?= $item['condition_status'] === 'new' ? 'success' : 'warning' ?> position-absolute top-0 end-0 m-2">
                        <?= ucfirst($item['condition_status']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <h6 class="card-title text-truncate"><?= clean($item['title']) ?></h6>
                    <p class="card-text">
                        <span class="h5 text-kasitrade"><?= formatPrice($item['price']) ?></span>
                    </p>
                    <p class="card-text small text-muted">
                        <i class="bi bi-geo-alt"></i> <?= clean($item['township']) ?>
                        <span class="mx-1">|</span>
                        <i class="bi bi-star-fill text-warning"></i> <?= number_format($item['seller_rating'] ?? 0, 1) ?>
                    </p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="listing.php?id=<?= $item['listing_id'] ?>" class="btn btn-kasitrade btn-sm w-100">
                        <i class="bi bi-eye"></i> View
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- How It Works -->
<div class="bg-light py-5">
    <div class="container">
        <h4 class="text-center mb-4">How KasiTrade Works</h4>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <div class="col text-center">
                <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
                    <i class="bi bi-camera display-5 text-kasitrade"></i>
                </div>
                <h5>1. Snap & List</h5>
                <p class="text-muted">Take photos, set price, and post in under 2 minutes.</p>
            </div>
            <div class="col text-center">
                <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
                    <i class="bi bi-chat-dots display-5 text-kasitrade"></i>
                </div>
                <h5>2. Chat & Deal</h5>
                <p class="text-muted">Message buyers, negotiate price, arrange pickup.</p>
            </div>
            <div class="col text-center">
                <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
                    <i class="bi bi-shield-check display-5 text-kasitrade"></i>
                </div>
                <h5>3. Safe Exchange</h5>
                <p class="text-muted">Pay via PayShap, meet at verified pickup point, scan QR.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>