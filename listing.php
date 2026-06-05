<?php
require_once 'config/functions.php';

$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
if (!$id) {
    redirect('browse.php', 'Listing not found', 'warning');
}

$stmt = $pdo->prepare("SELECT l.*, u.username, u.first_name, u.last_name, u.phone_number, 
                        u.township, u.id_verified, u.user_id as seller_id, c.category_name,
                        (SELECT AVG(rating) FROM reviews WHERE reviewee_id = l.seller_id) as seller_rating,
                        (SELECT COUNT(*) FROM reviews WHERE reviewee_id = l.seller_id) as review_count
                        FROM listings l
                        JOIN users u ON l.seller_id = u.user_id
                        JOIN categories c ON l.category_id = c.category_id
                        WHERE l.listing_id = ? AND l.status = 'active'");
$stmt->execute([$id]);
$listing = $stmt->fetch();

if (!$listing) {
    redirect('browse.php', 'Listing not found', 'warning');
}

$pageTitle = clean($listing['title']);
require_once 'includes/header.php';

$images = json_decode($listing['image_paths'] ?? '[]', true);
if (empty($images)) $images = ['assets/images/no-image.jpg'];

// Fetch reviews for seller
$rev_stmt = $pdo->prepare("SELECT r.*, u.username as reviewer_name 
                           FROM reviews r
                           JOIN users u ON r.reviewer_id = u.user_id
                           WHERE r.reviewee_id = ?
                           ORDER BY r.created_at DESC LIMIT 5");
$rev_stmt->execute([$listing['seller_id']]);
$reviews = $rev_stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row">
        <!-- Image Gallery -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <img src="<?= $images[0] ?>" class="card-img-top" alt="<?= clean($listing['title']) ?>" 
                     style="max-height: 400px; object-fit: contain;" onerror="this.src='assets/images/no-image.jpg'">
                <?php if (count($images) > 1): ?>
                <div class="card-body">
                    <div class="row g-2">
                        <?php foreach (array_slice($images, 1) as $img): ?>
                        <div class="col-3">
                            <img src="<?= $img ?>" class="img-thumbnail" style="height: 80px; object-fit: cover; cursor: pointer;"
                                 onclick="document.querySelector('.card-img-top').src=this.src">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Details -->
        <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="badge bg-<?= $listing['condition_status'] === 'new' ? 'success' : 'warning' ?> mb-2">
                        <?= ucfirst($listing['condition_status']) ?>
                    </span>
                    <h3><?= clean($listing['title']) ?></h3>
                </div>
                <h2 class="text-kasitrade"><?= formatPrice($listing['price']) ?></h2>
            </div>

            <p class="text-muted">
                <i class="bi bi-folder"></i> <?= clean($listing['category_name']) ?>
                <span class="mx-2">|</span>
                <i class="bi bi-eye"></i> <?= $listing['view_count'] ?> views
            </p>

            <hr>

            <h6>Description</h6>
            <p><?= nl2br(clean($listing['description'])) ?></p>

            <hr>

            <!-- Seller Info -->
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-kasitrade text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 50px; height: 50px;">
                            <i class="bi bi-person fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">
                                <?= clean($listing['first_name'] . ' ' . $listing['last_name']) ?>
                                <?php if ($listing['id_verified']): ?>
                                <i class="bi bi-check-circle-fill text-primary" title="Verified"></i>
                                <?php endif; ?>
                            </h6>
                            <p class="mb-0 small text-muted">
                                <i class="bi bi-geo-alt"></i> <?= clean($listing['township']) ?>
                                <span class="mx-1">|</span>
                                <i class="bi bi-star-fill text-warning"></i> 
                                <?= number_format($listing['seller_rating'] ?? 0, 1) ?> 
                                (<?= $listing['review_count'] ?> reviews)
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2 mt-3">
                <?php if (isLoggedIn() && $_SESSION['user_id'] != $listing['seller_id']): ?>
                <a href="messages.php?to=<?= $listing['seller_id'] ?>&listing=<?= $listing['listing_id'] ?>" 
                   class="btn btn-kasitrade btn-lg">
                    <i class="bi bi-chat-dots"></i> Message Seller
                </a>
                <button class="btn btn-outline-kasitrade btn-lg" onclick="addToCart(<?= $listing['listing_id'] ?>)">
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
                <?php elseif (!isLoggedIn()): ?>
                <a href="auth/login.php" class="btn btn-kasitrade btn-lg">Login to Contact Seller</a>
                <?php endif; ?>
            </div>

            <!-- Reviews -->
            <?php if (!empty($reviews)): ?>
            <hr>
            <h6>Recent Reviews for Seller</h6>
            <?php foreach ($reviews as $review): ?>
            <div class="card mb-2">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between">
                        <span class="small"><strong><?= clean($review['reviewer_name']) ?></strong></span>
                        <span class="text-warning">
                            <?php for ($i = 0; $i < $review['rating']; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                        </span>
                    </div>
                    <p class="small mb-0"><?= clean($review['comment']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
// Update view count
$pdo->prepare("UPDATE listings SET view_count = view_count + 1 WHERE listing_id = ?")
    ->execute([$id]);
require_once 'includes/footer.php'; 
?>