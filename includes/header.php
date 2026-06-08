<?php
require_once __DIR__ . '/../config/functions.php';

function assetVersion($relativePath) {
    $fullPath = __DIR__ . '/../' . ltrim($relativePath, '/');
    return file_exists($fullPath) ? filemtime($fullPath) : time();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'KasiTrade' ?> - Township Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= assetVersion('assets/css/style.css') ?>">
    <link rel="stylesheet" href="assets/css/responsive.css?v=<?= assetVersion('assets/css/responsive.css') ?>">
    <link rel="stylesheet" href="assets/css/bootstrap-custom.css?v=<?= assetVersion('assets/css/bootstrap-custom.css') ?>">
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-kasitrade sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shop"></i> KasiTrade
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="browse.php"><i class="bi bi-search"></i> Browse</a></li>
                    <li class="nav-item"><a class="nav-link" href="categories.php"><i class="bi bi-grid"></i> Categories</a></li>
                    <?php if (hasPermission('post_listings')): ?>
                    <li class="nav-item"><a class="nav-link" href="create-listing.php"><i class="bi bi-plus-circle"></i> Sell</a></li>
                    <?php endif; ?>
                    <?php if (hasRole('admin')): ?>
                    <li class="nav-item"><a class="nav-link" href="admin/dashboard.php"><i class="bi bi-speedometer2"></i> Admin</a></li>
                    <?php endif; ?>
                </ul>
                <form class="d-flex me-2" action="browse.php" method="GET">
                    <div class="input-group">
                        <input class="form-control form-control-sm" type="search" name="search" 
                               placeholder="Search..." value="<?= clean($_GET['search'] ?? '') ?>">
                        <button class="btn btn-light btn-sm" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                <?php if (isLoggedIn()): ?>
                <a href="cart.php" class="btn btn-outline-light btn-sm me-2 position-relative" aria-label="View cart">
                    <i class="bi bi-cart3"></i>
                    <span class="cart-count badge rounded-pill bg-warning text-dark position-absolute top-0 start-100 translate-middle d-none">0</span>
                </a>
                <?php endif; ?>
                <?php if (isLoggedIn()): ?>
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= clean($_SESSION['username']) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="messages.php"><i class="bi bi-chat"></i> Messages</a></li>
                        <?php if (hasPermission('view_purchase_history')): ?>
                        <li><a class="dropdown-item" href="purchases.php"><i class="bi bi-bag"></i> Purchases</a></li>
                        <?php endif; ?>
                        <?php if (hasPermission('manage_own_listings')): ?>
                        <li><a class="dropdown-item" href="my-listings.php"><i class="bi bi-box"></i> My Listings</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
                <?php else: ?>
                <a href="auth/login.php" class="btn btn-outline-light btn-sm">Login</a>
                <a href="auth/register.php" class="btn btn-light btn-sm ms-2">Join</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main id="main-content" class="pb-5">
    <?= flash() ?>