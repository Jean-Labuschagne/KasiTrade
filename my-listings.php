<?php
$pageTitle = 'My Listings';
require_once 'includes/header.php';

if (!hasPermission('manage_own_listings')) {
    redirect('index.php', 'Access denied', 'warning');
}

// Handle delete
if (isset($_POST['delete_id']) && verifyCSRF($_POST['csrf_token'] ?? '')) {
    $stmt = $pdo->prepare("UPDATE listings SET status = 'deleted' WHERE listing_id = ? AND seller_id = ?");
    $stmt->execute([filter_var($_POST['delete_id'], FILTER_VALIDATE_INT), $_SESSION['user_id']]);
    redirect('my-listings.php', 'Listing deleted', 'success');
}

$stmt = $pdo->prepare("SELECT l.*, c.category_name,
                       (SELECT COUNT(*) FROM transactions WHERE listing_id = l.listing_id) as sale_count
                       FROM listings l
                       JOIN categories c ON l.category_id = c.category_id
                       WHERE l.seller_id = ? AND l.status != 'deleted'
                       ORDER BY l.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$listings = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-box"></i> My Listings</h4>
        <a href="create-listing.php" class="btn btn-kasitrade"><i class="bi bi-plus-circle"></i> New Listing</a>
    </div>

    <?php if (empty($listings)): ?>
    <div class="text-center py-5">
        <i class="bi bi-box display-1 text-muted"></i>
        <p class="text-muted mt-3">No listings yet. Start selling!</p>
        <a href="create-listing.php" class="btn btn-kasitrade">Post First Item</a>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Sales</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listings as $item): ?>
                <tr>
                    <td>
                        <img src="<?= $item['image_paths'] ? json_decode($item['image_paths'])[0] : 'assets/images/no-image.jpg' ?>" 
                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;" 
                             onerror="this.src='assets/images/no-image.jpg'">
                    </td>
                    <td><?= clean($item['title']) ?></td>
                    <td class="text-kasitrade"><?= formatPrice($item['price']) ?></td>
                    <td><span class="badge bg-<?= $item['status'] === 'active' ? 'success' : ($item['status'] === 'pending' ? 'warning' : 'secondary') ?>"><?= $item['status'] ?></span></td>
                    <td><?= $item['sale_count'] ?></td>
                    <td>
                        <a href="listing.php?id=<?= $item['listing_id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this listing?')">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                            <input type="hidden" name="delete_id" value="<?= $item['listing_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>