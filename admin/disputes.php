<?php
require_once '../config/functions.php';

if (!hasRole('admin') && !hasRole('moderator')) {
    redirect('../index.php', 'Access denied', 'danger');
}

$pageTitle = 'Handle Disputes';

// Handle dispute resolution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRF($_POST['csrf_token'] ?? '')) {
    $dispute_id = filter_var($_POST['dispute_id'], FILTER_VALIDATE_INT);
    $status = clean($_POST['status']);
    $resolution = clean($_POST['resolution']);

    $pdo->prepare("UPDATE disputes SET status = ?, resolution = ?, admin_id = ?, resolved_at = NOW() WHERE dispute_id = ?")
        ->execute([$status, $resolution, $_SESSION['user_id'], $dispute_id]);

    // If refunded, update transaction
    if ($status === 'refunded') {
        $pdo->prepare("UPDATE transactions SET escrow_status = 'refunded' WHERE transaction_id = (SELECT transaction_id FROM disputes WHERE dispute_id = ?)")
            ->execute([$dispute_id]);
    }

    redirect('disputes.php', 'Dispute resolved', 'success');
}

$disputes = $pdo->query("SELECT d.*, 
                         t.amount, t.buyer_id, t.seller_id, t.listing_id,
                         buyer.username as buyer_name, seller.username as seller_name,
                         l.title as listing_title
                         FROM disputes d
                         JOIN transactions t ON d.transaction_id = t.transaction_id
                         JOIN users buyer ON t.buyer_id = buyer.user_id
                         JOIN users seller ON t.seller_id = seller.user_id
                         JOIN listings l ON t.listing_id = l.listing_id
                         ORDER BY d.created_at DESC")->fetchAll();
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
                    <li class="nav-item"><a class="nav-link" href="listings.php"><i class="bi bi-box"></i> Listings</a></li>
                    <li class="nav-item"><a class="nav-link" href="transactions.php"><i class="bi bi-currency-exchange"></i> Transactions</a></li>
                    <li class="nav-item"><a class="nav-link active" href="disputes.php"><i class="bi bi-exclamation-triangle"></i> Disputes</a></li>
                    <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-flag"></i> Reports</a></li>
                    <li class="nav-item mt-3"><a class="nav-link text-warning" href="../index.php"><i class="bi bi-arrow-left"></i> Back to Site</a></li>
                </ul>
            </div>
        </nav>

        <main class="admin-main p-4">
            <h4 class="mb-4"><i class="bi bi-exclamation-triangle"></i> Handle Disputes</h4>

            <div class="row">
                <?php foreach ($disputes as $d): ?>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <span>Dispute #<?= $d['dispute_id'] ?></span>
                            <span class="badge bg-<?= $d['status'] === 'open' ? 'danger' : ($d['status'] === 'mediating' ? 'warning' : 'success') ?>"><?= $d['status'] ?></span>
                        </div>
                        <div class="card-body">
                            <p><strong>Type:</strong> <?= clean($d['dispute_type']) ?></p>
                            <p><strong>Listing:</strong> <?= clean($d['listing_title']) ?></p>
                            <p><strong>Amount:</strong> <?= formatPrice($d['amount']) ?></p>
                            <p><strong>Buyer:</strong> <?= clean($d['buyer_name']) ?></p>
                            <p><strong>Seller:</strong> <?= clean($d['seller_name']) ?></p>
                            <p><strong>Description:</strong> <?= clean($d['description']) ?></p>

                            <?php if ($d['resolution']): ?>
                            <div class="alert alert-info">
                                <strong>Resolution:</strong> <?= clean($d['resolution']) ?>
                            </div>
                            <?php endif; ?>

                            <?php if ($d['status'] !== 'resolved' && $d['status'] !== 'refunded'): ?>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                                <input type="hidden" name="dispute_id" value="<?= $d['dispute_id'] ?>">
                                <div class="mb-2">
                                    <select name="status" class="form-select">
                                        <option value="open" <?= $d['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                                        <option value="mediating">Mediating</option>
                                        <option value="resolved_buyer">Resolved (Buyer)</option>
                                        <option value="resolved_seller">Resolved (Seller)</option>
                                        <option value="refunded">Refunded</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <textarea name="resolution" class="form-control" rows="2" placeholder="Resolution notes..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-kasitrade w-100">Resolve Dispute</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>