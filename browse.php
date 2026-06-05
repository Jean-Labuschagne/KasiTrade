<?php
$pageTitle = 'Browse';
require_once 'includes/header.php';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Filters
$category = filter_var($_GET['category'] ?? '', FILTER_VALIDATE_INT);
$min_price = filter_var($_GET['min_price'] ?? '', FILTER_VALIDATE_FLOAT);
$max_price = filter_var($_GET['max_price'] ?? '', FILTER_VALIDATE_FLOAT);
$township = clean($_GET['township'] ?? '');
$search = clean($_GET['search'] ?? '');
$condition = clean($_GET['condition'] ?? '');

// Build query
$params = [];
$where = ["l.status = 'active'"];

if ($category) {
    $where[] = "l.category_id = ?";
    $params[] = $category;
}
if ($min_price !== false) {
    $where[] = "l.price >= ?";
    $params[] = $min_price;
}
if ($max_price !== false) {
    $where[] = "l.price <= ?";
    $params[] = $max_price;
}
if ($township) {
    $where[] = "u.township = ?";
    $params[] = $township;
}
if ($condition) {
    $where[] = "l.condition_status = ?";
    $params[] = $condition;
}
if ($search) {
    $where[] = "(l.title LIKE ? OR l.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(' AND ', $where);

// Fetch listings
$stmt = $pdo->prepare("SELECT l.*, u.username, u.township, u.id_verified, c.category_name,
                       (SELECT AVG(rating) FROM reviews WHERE reviewee_id = l.seller_id) as seller_rating
                       FROM listings l
                       JOIN users u ON l.seller_id = u.user_id
                       JOIN categories c ON l.category_id = c.category_id
                       WHERE $where_clause
                       ORDER BY l.created_at DESC
                       LIMIT ? OFFSET ?");
$stmt->execute(array_merge($params, [$per_page, $offset]));
$listings = $stmt->fetchAll();

// Count total
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM listings l 
                             JOIN users u ON l.seller_id = u.user_id
                             WHERE $where_clause");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Fetch categories and townships for filters
$cat_stmt = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL");
$all_categories = $cat_stmt->fetchAll();

$town_stmt = $pdo->query("SELECT DISTINCT township FROM users WHERE township IS NOT NULL ORDER BY township");
$all_townships = $town_stmt->fetchAll();
?>

<div class="container py-4">
    <h4 class="mb-3"><i class="bi bi-search"></i> Browse Listings</h4>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-2">
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($all_categories as $c): ?>
                        <option value="<?= $c['category_id'] ?>" <?= $category == $c['category_id'] ? 'selected' : '' ?>>
                            <?= clean($c['category_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="township" class="form-select">
                        <option value="">Any Location</option>
                        <?php foreach ($all_townships as $t): ?>
                        <option value="<?= clean($t['township']) ?>" <?= $township === $t['township'] ? 'selected' : '' ?>>
                            <?= clean($t['township']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="condition" class="form-select">
                        <option value="">Any Condition</option>
                        <option value="new" <?= $condition === 'new' ? 'selected' : '' ?>>New</option>
                        <option value="used" <?= $condition === 'used' ? 'selected' : '' ?>>Used</option>
                        <option value="refurbished" <?= $condition === 'refurbished' ? 'selected' : '' ?>>Refurbished</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <input type="number" name="min_price" class="form-control" placeholder="Min R" value="<?= $min_price ?>">
                        <input type="number" name="max_price" class="form-control" placeholder="Max R" value="<?= $max_price ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-kasitrade w-100"><i class="bi bi-funnel"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results count -->
    <p class="text-muted"><?= $total ?> listing(s) found</p>

    <!-- Listings Grid -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3" id="listingsGrid">
        <?php foreach ($listings as $item): ?>
        <div class="col">
            <div class="card h-100 listing-card shadow-sm">
                <div class="position-relative">
                    <img src="<?= $item['image_paths'] ? json_decode($item['image_paths'])[0] : 'assets/images/no-image.jpg' ?>" 
                         class="card-img-top" alt="<?= clean($item['title']) ?>" loading="lazy"
                         style="height: 180px; object-fit: cover;" onerror="this.src='assets/images/no-image.jpg'">
                    <span class="badge bg-<?= $item['condition_status'] === 'new' ? 'success' : 'warning' ?> position-absolute top-0 end-0 m-2">
                        <?= ucfirst($item['condition_status']) ?>
                    </span>
                    <?php if ($item['id_verified']): ?>
                    <span class="badge bg-primary position-absolute top-0 start-0 m-2">
                        <i class="bi bi-check-circle-fill"></i> Verified
                    </span>
                    <?php endif; ?>
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
                        <i class="bi bi-eye"></i> View Details
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($listings)): ?>
    <div class="text-center py-5">
        <i class="bi bi-inbox display-1 text-muted"></i>
        <p class="text-muted mt-3">No listings found. Try different filters.</p>
    </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>