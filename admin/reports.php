<?php
require_once '../config/functions.php';

if (!hasRole('admin') && !hasRole('moderator')) {
    redirect('../index.php', 'Access denied', 'danger');
}

$pageTitle = 'Manage Reports';

// Handle report actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRF($_POST['csrf_token'] ?? '')) {
    $report_id = filter_var($_POST['report_id'], FILTER_VALIDATE_INT);
    $status = clean($_POST['status']);
    $notes = clean($_POST['admin_notes'] ?? '');

    $pdo->prepare("UPDATE reports SET status = ?, admin_notes = ?, resolved_at = NOW() WHERE report_id = ?")
        ->execute([$status, $notes, $report_id]);
    redirect('reports.php', 'Report updated', 'success');
}

$reports = $pdo->query("SELECT r.*, 
                        reporter.username as reporter_name,
                        reported.username as reported_name,
                        l.title as listing_title
                        FROM reports r
                        LEFT JOIN users reporter ON r.reporter_id = reporter.user_id
                        LEFT JOIN users reported ON r.reported_user_id = reported.user_id
                        LEFT JOIN listings l ON r.listing_id = l.listing_id
                        ORDER BY r.created_at DESC")->fetchAll();
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
                    <li class="nav-item"><a class="nav-link" href="disputes.php"><i class="bi bi-exclamation-triangle"></i> Disputes</a></li>
                    <li class="nav-item"><a class="nav-link active" href="reports.php"><i class="bi bi-flag"></i> Reports</a></li>
                    <li class="nav-item mt-3"><a class="nav-link text-warning" href="../index.php"><i class="bi bi-arrow-left"></i> Back to Site</a></li>
                </ul>
            </div>
        </nav>

        <main class="admin-main p-4">
            <h4 class="mb-4"><i class="bi bi-flag"></i> Manage Reports</h4>

            <div class="row">
                <?php foreach ($reports as $r): ?>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="badge bg-<?= $r['status'] === 'pending' ? 'warning' : ($r['status'] === 'resolved' ? 'success' : 'info') ?>"><?= $r['status'] ?></span>
                            <small class="text-muted"><?= timeAgo($r['created_at']) ?></small>
                        </div>
                        <div class="card-body">
                            <p><strong>Type:</strong> <?= clean($r['report_type']) ?></p>
                            <p><strong>Reporter:</strong> <?= clean($r['reporter_name'] ?? 'Unknown') ?></p>
                            <p><strong>Reported:</strong> <?= clean($r['reported_name'] ?? 'Unknown') ?></p>
                            <?php if ($r['listing_title']): ?>
                            <p><strong>Listing:</strong> <?= clean($r['listing_title']) ?></p>
                            <?php endif; ?>
                            <p><strong>Reason:</strong> <?= clean($r['reason']) ?></p>

                            <?php if ($r['admin_notes']): ?>
                            <p class="text-muted"><strong>Notes:</strong> <?= clean($r['admin_notes']) ?></p>
                            <?php endif; ?>

                            <form method="POST" class="mt-3">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                                <input type="hidden" name="report_id" value="<?= $r['report_id'] ?>">
                                <div class="input-group">
                                    <select name="status" class="form-select">
                                        <option value="pending" <?= $r['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="investigating" <?= $r['status'] === 'investigating' ? 'selected' : '' ?>>Investigating</option>
                                        <option value="resolved" <?= $r['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                        <option value="dismissed" <?= $r['status'] === 'dismissed' ? 'selected' : '' ?>>Dismissed</option>
                                    </select>
                                    <button type="submit" class="btn btn-kasitrade">Update</button>
                                </div>
                            </form>
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