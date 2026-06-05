<?php
require_once '../config/functions.php';

if (!hasRole('admin') && !hasRole('moderator')) {
    redirect('../index.php', 'Access denied', 'danger');
}

$pageTitle = 'Moderate Listings';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRF($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $listing_id = filter_var($_POST['listing_id'], FILTER_VALIDATE_INT);

    if ($action === 'approve') {
        $pdo->prepare("UPDATE listings SET status = 'active' WHERE listing_id = ?")->execute([$listing_id]);
    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE listings SET status = 'suspended' WHERE listing_id = ?")->execute([$listing_id]);
    } elseif ($action === 'delete') {
        $pdo->prepare("UPDATE listings SET status = 'deleted' WHERE listing_id = ?")->execute([$listing_id]);
    }
    redirect('listings.php', 'Listing updated', 'success');
}

// Fetch all listings with filters
$status_filter = clean($_GET['status'] ?? '');
$params = [];
$where = "WHERE l.status != 'deleted'";
if ($status_filter) {
    $where .= " AND l.status = ?";
    $params[] = $status_filter;
}

$listings = $pdo->prepare("SELECT l.*, u.username, u.township, c.category_name 
                          FROM listings l
                          JOIN users u ON l.seller_id = u.user_id
                          JOIN categories c ON l.category_id = c.category_id
                          $where
                          ORDER BY l.created_at DESC");
$listings->execute($params);
$listings = $listings->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - KasiTrade Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-layout">
        <nav class="admin-sidebar bg-dark text-white">
            <div class="p-3">
                <h5 class="mb-4"><i class="bi bi-speedometer2"></i> KasiTrade Admin</h5>
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php"><i class="bi bi-people"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link active" href="listings.php"><i class="bi bi-box"></i> Listings</a></li>
                    <li class="nav-item"><a class="nav-link" href="transactions.php"><i class="bi bi-currency-exchange"></i> Transactions</a></li>
                    <li class="nav-item"><a class="nav-link" href="disputes.php"><i class="bi bi-exclamation-triangle"></i> Disputes</a></li>
                    <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-flag"></i> Reports</a></li>
                    <li class="nav-item mt-3"><a class="nav-link text-warning" href="../index.php"><i class="bi bi-arrow-left"></i> Back to Site</a></li>
                </ul>
            </div>
        </nav>

        <main class="admin-main p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="bi bi-box"></i> Moderate Listings</h4>
                <div class="btn-group">
                    <a href="?status=" class="btn btn-outline-secondary <?= !$status_filter ? 'active' : '' ?>">All</a>
                    <a href="?status=pending" class="btn btn-outline-warning <?= $status_filter === 'pending' ? 'active' : '' ?>">Pending</a>
                    <a href="?status=active" class="btn btn-outline-success <?= $status_filter === 'active' ? 'active' : '' ?>">Active</a>
                    <a href="?status=suspended" class="btn btn-outline-danger <?= $status_filter === 'suspended' ? 'active' : '' ?>">Suspended</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Seller</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listings as $l): ?>
                                <tr>
                                    <td><?= $l['listing_id'] ?></td>
                                    <td>
                                        <img src="<?= $l['image_paths'] ? json_decode($l['image_paths'])[0] : '../assets/images/no-image.jpg' ?>" 
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                                             onerror="this.src='../assets/images/no-image.jpg'">
                                    </td>
                                    <td><?= clean($l['title']) ?></td>
                                    <td><?= clean($l['username']) ?> <small class="text-muted">(<?= clean($l['township']) ?>)</small></td>
                                    <td><?= formatPrice($l['price']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $l['status'] === 'active' ? 'success' : ($l['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                            <?= $l['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= timeAgo($l['created_at']) ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                                            <input type="hidden" name="listing_id" value="<?= $l['listing_id'] ?>">
                                            <?php if ($l['status'] === 'pending'): ?>
                                            <button type="submit" name="action" value="approve" class="btn btn-sm btn-success"><i class="bi bi-check"></i></button>
                                            <button type="submit" name="action" value="reject" class="btn btn-sm btn-warning"><i class="bi bi-x"></i></button>
                                            <?php endif; ?>
                                            <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>