<?php
require_once '../config/functions.php';

// RBAC check - only admin/moderator
if (!hasRole('admin') && !hasRole('moderator')) {
    redirect('../index.php', 'Access denied', 'danger');
}

$pageTitle = 'Admin Dashboard';

// Fetch statistics
$stats = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM users WHERE role_id = 3) as buyers,
    (SELECT COUNT(*) FROM users WHERE role_id = 2) as sellers,
    (SELECT COUNT(*) FROM listings WHERE status = 'active') as active_listings,
    (SELECT COUNT(*) FROM listings WHERE status = 'pending') as pending_listings,
    (SELECT COUNT(*) FROM transactions WHERE escrow_status = 'released') as completed_transactions,
    (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE escrow_status = 'released') as total_revenue,
    (SELECT COUNT(*) FROM disputes WHERE status = 'open') as open_disputes,
    (SELECT COUNT(*) FROM reports WHERE status = 'pending') as pending_reports
")->fetch();

// Recent users
$recent_users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent listings
$recent_listings = $pdo->query("SELECT l.*, u.username FROM listings l JOIN users u ON l.seller_id = u.user_id ORDER BY l.created_at DESC LIMIT 5")->fetchAll();

// Recent reports
$recent_reports = $pdo->query("SELECT r.*, u.username as reporter FROM reports r JOIN users u ON r.reporter_id = u.user_id ORDER BY r.created_at DESC LIMIT 5")->fetchAll();
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
    <link rel="stylesheet" href="../assets/css/bootstrap-custom.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <nav class="admin-sidebar bg-dark text-white">
            <div class="p-3">
                <h5 class="mb-4"><i class="bi bi-speedometer2"></i> KasiTrade Admin</h5>
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php"><i class="bi bi-people"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="listings.php"><i class="bi bi-box"></i> Listings</a></li>
                    <li class="nav-item"><a class="nav-link" href="transactions.php"><i class="bi bi-currency-exchange"></i> Transactions</a></li>
                    <li class="nav-item"><a class="nav-link" href="disputes.php"><i class="bi bi-exclamation-triangle"></i> Disputes</a></li>
                    <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-flag"></i> Reports</a></li>
                    <li class="nav-item"><a class="nav-link" href="pickup-points.php"><i class="bi bi-geo-alt"></i> Pickup Points</a></li>
                    <li class="nav-item mt-3"><a class="nav-link text-warning" href="../index.php"><i class="bi bi-arrow-left"></i> Back to Site</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="admin-main p-4">
            <h4 class="mb-4">Dashboard Overview</h4>

            <!-- Stats Cards -->
            <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
                <div class="col">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-title">Buyers</h6>
                            <h3><?= $stats['buyers'] ?></h3>
                            <i class="bi bi-people display-4 opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title">Sellers</h6>
                            <h3><?= $stats['sellers'] ?></h3>
                            <i class="bi bi-shop display-4 opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title">Active Listings</h6>
                            <h3><?= $stats['active_listings'] ?></h3>
                            <i class="bi bi-box display-4 opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h6 class="card-title">Pending</h6>
                            <h3><?= $stats['pending_listings'] ?></h3>
                            <i class="bi bi-hourglass display-4 opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card bg-kasitrade text-white">
                        <div class="card-body">
                            <h6 class="card-title">Revenue</h6>
                            <h3>R<?= number_format($stats['total_revenue'], 0) ?></h3>
                            <i class="bi bi-cash-stack display-4 opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <h6 class="card-title">Completed</h6>
                            <h3><?= $stats['completed_transactions'] ?></h3>
                            <i class="bi bi-check-circle display-4 opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h6 class="card-title">Disputes</h6>
                            <h3><?= $stats['open_disputes'] ?></h3>
                            <i class="bi bi-exclamation-triangle display-4 opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <h6 class="card-title">Reports</h6>
                            <h3><?= $stats['pending_reports'] ?></h3>
                            <i class="bi bi-flag display-4 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-people"></i> Recent Users</div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recent_users as $u): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?= clean($u['username']) ?> <small class="text-muted">(<?= clean($u['township']) ?>)</small></span>
                                    <span class="badge bg-<?= $u['role_id'] == 1 ? 'danger' : ($u['role_id'] == 2 ? 'success' : 'primary') ?>">
                                        <?= $u['role_id'] == 1 ? 'Admin' : ($u['role_id'] == 2 ? 'Seller' : 'Buyer') ?>
                                    </span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-box"></i> Recent Listings</div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recent_listings as $l): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-truncate" style="max-width: 70%;"><?= clean($l['title']) ?></span>
                                    <span class="badge bg-<?= $l['status'] === 'active' ? 'success' : 'warning' ?>"><?= $l['status'] ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-flag"></i> Recent Reports</div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recent_reports as $r): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="text-truncate" style="max-width: 70%;"><?= clean($r['report_type']) ?></span>
                                    <span class="badge bg-<?= $r['status'] === 'pending' ? 'warning' : 'info' ?>"><?= $r['status'] ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>