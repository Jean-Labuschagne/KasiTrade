<?php
$pageTitle = 'My Profile';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    redirect('auth/login.php', 'Please login', 'warning');
}

$stmt = $pdo->prepare("SELECT u.*, r.role_name,
                       (SELECT AVG(rating) FROM reviews WHERE reviewee_id = u.user_id) as avg_rating,
                       (SELECT COUNT(*) FROM reviews WHERE reviewee_id = u.user_id) as review_count
                       FROM users u
                       JOIN roles r ON u.role_id = r.role_id
                       WHERE u.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fetch user's listings
$list_stmt = $pdo->prepare("SELECT * FROM listings WHERE seller_id = ? ORDER BY created_at DESC");
$list_stmt->execute([$_SESSION['user_id']]);
$my_listings = $list_stmt->fetchAll();

// Fetch purchase history
$purchase_stmt = $pdo->prepare("SELECT t.*, l.title, l.image_paths, u.username as seller_name
                               FROM transactions t
                               JOIN listings l ON t.listing_id = l.listing_id
                               JOIN users u ON t.seller_id = u.user_id
                               WHERE t.buyer_id = ?
                               ORDER BY t.created_at DESC");
$purchase_stmt->execute([$_SESSION['user_id']]);
$purchases = $purchase_stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row">
        <!-- Profile Card -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <div class="bg-kasitrade text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="bi bi-person display-3"></i>
                    </div>
                    <h5><?= clean($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                    <p class="text-muted mb-1">@<?= clean($user['username']) ?></p>
                    <p class="text-muted mb-1"><i class="bi bi-geo-alt"></i> <?= clean($user['township']) ?></p>
                    <span class="badge bg-<?= $user['role_id'] == 2 ? 'success' : 'primary' ?>"><?= clean($user['role_name']) ?></span>

                    <?php if ($user['avg_rating']): ?>
                    <div class="mt-2">
                        <i class="bi bi-star-fill text-warning"></i> <?= number_format($user['avg_rating'], 1) ?>
                        <span class="text-muted">(<?= $user['review_count'] ?> reviews)</span>
                    </div>
                    <?php endif; ?>

                    <?php if ($user['id_verified']): ?>
                    <div class="mt-2">
                        <span class="badge bg-primary"><i class="bi bi-check-circle-fill"></i> ID Verified</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between">
                        <span><i class="bi bi-box"></i> My Listings</span>
                        <span class="badge bg-kasitrade"><?= count($my_listings) ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span><i class="bi bi-bag"></i> Purchases</span>
                        <span class="badge bg-kasitrade"><?= count($purchases) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#listings"><i class="bi bi-box"></i> My Listings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#purchases"><i class="bi bi-bag"></i> Purchases</a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Listings Tab -->
                <div class="tab-pane fade show active" id="listings">
                    <?php if (hasPermission('post_listings')): ?>
                    <a href="create-listing.php" class="btn btn-kasitrade mb-3"><i class="bi bi-plus-circle"></i> Post New Listing</a>
                    <?php endif; ?>

                    <?php if (empty($my_listings)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-box display-1 text-muted"></i>
                        <p class="text-muted mt-3">No listings yet. Start selling!</p>
                    </div>
                    <?php else: ?>
                    <div class="row row-cols-1 row-cols-sm-2 g-3">
                        <?php foreach ($my_listings as $item): ?>
                        <div class="col">
                            <div class="card h-100">
                                <img src="<?= $item['image_paths'] ? json_decode($item['image_paths'])[0] : 'assets/images/no-image.jpg' ?>" 
                                     class="card-img-top" style="height: 150px; object-fit: cover;" onerror="this.src='assets/images/no-image.jpg'">
                                <div class="card-body">
                                    <h6 class="text-truncate"><?= clean($item['title']) ?></h6>
                                    <p class="text-kasitrade mb-1"><?= formatPrice($item['price']) ?></p>
                                    <span class="badge bg-<?= $item['status'] === 'active' ? 'success' : 'warning' ?>"><?= $item['status'] ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Purchases Tab -->
                <div class="tab-pane fade" id="purchases">
                    <?php if (empty($purchases)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-bag display-1 text-muted"></i>
                        <p class="text-muted mt-3">No purchases yet. Start browsing!</p>
                    </div>
                    <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($purchases as $p): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= clean($p['title']) ?></h6>
                                    <p class="mb-0 small text-muted">
                                        Seller: <?= clean($p['seller_name']) ?> | 
                                        <?= formatPrice($p['amount']) ?> |
                                        <span class="badge bg-<?= $p['escrow_status'] === 'released' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($p['escrow_status']) ?>
                                        </span>
                                    </p>
                                </div>
                                <a href="listing.php?id=<?= $p['listing_id'] ?>" class="btn btn-sm btn-outline-kasitrade">View</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>