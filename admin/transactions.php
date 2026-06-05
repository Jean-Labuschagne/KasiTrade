<?php
require_once '../config/functions.php';

if (!hasRole('admin')) {
    redirect('../index.php', 'Access denied', 'danger');
}

$pageTitle = 'Transactions';

$transactions = $pdo->query("SELECT t.*, 
                            buyer.username as buyer_name, seller.username as seller_name,
                            l.title as listing_title, p.name as pickup_name
                            FROM transactions t
                            JOIN users buyer ON t.buyer_id = buyer.user_id
                            JOIN users seller ON t.seller_id = seller.user_id
                            JOIN listings l ON t.listing_id = l.listing_id
                            LEFT JOIN pickup_points p ON t.pickup_point_id = p.pickup_point_id
                            ORDER BY t.created_at DESC")->fetchAll();

$total_revenue = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE escrow_status = 'released'")->fetch()['total'];
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
                    <li class="nav-item"><a class="nav-link active" href="transactions.php"><i class="bi bi-currency-exchange"></i> Transactions</a></li>
                    <li class="nav-item"><a class="nav-link" href="disputes.php"><i class="bi bi-exclamation-triangle"></i> Disputes</a></li>
                    <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-flag"></i> Reports</a></li>
                    <li class="nav-item mt-3"><a class="nav-link text-warning" href="../index.php"><i class="bi bi-arrow-left"></i> Back to Site</a></li>
                </ul>
            </div>
        </nav>

        <main class="admin-main p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="bi bi-currency-exchange"></i> Transactions</h4>
                <div class="card bg-kasitrade text-white px-3 py-2">
                    <strong>Total Revenue: <?= formatPrice($total_revenue) ?></strong>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Listing</th>
                                    <th>Buyer</th>
                                    <th>Seller</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Pickup</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $t): ?>
                                <tr>
                                    <td><?= $t['transaction_id'] ?></td>
                                    <td><?= clean($t['listing_title']) ?></td>
                                    <td><?= clean($t['buyer_name']) ?></td>
                                    <td><?= clean($t['seller_name']) ?></td>
                                    <td class="text-kasitrade"><?= formatPrice($t['amount']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $t['escrow_status'] === 'released' ? 'success' : ($t['escrow_status'] === 'pending' ? 'warning' : ($t['escrow_status'] === 'disputed' ? 'danger' : 'info')) ?>">
                                            <?= $t['escrow_status'] ?>
                                        </span>
                                    </td>
                                    <td><?= $t['payment_method'] ?></td>
                                    <td><?= clean($t['pickup_name'] ?? 'N/A') ?></td>
                                    <td><?= timeAgo($t['created_at']) ?></td>
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