<?php
$pageTitle = 'Sell Item';
require_once 'includes/header.php';

if (!hasPermission('post_listings')) {
    redirect('auth/login.php', 'Please login to sell items', 'warning');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $title = clean($_POST['title']);
        $description = clean($_POST['description']);
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $category_id = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);
        $condition = $_POST['condition'] ?? 'new';

        if (strlen($title) < 5) {
            $error = 'Title must be at least 5 characters';
        } elseif ($price <= 0) {
            $error = 'Price must be greater than 0';
        } elseif (!$category_id) {
            $error = 'Please select a category';
        } else {
            // Handle image upload
            $image_paths = [];
            if (!empty($_FILES['images']['tmp_name'][0])) {
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                        $filename = 'uploads/listings/' . uniqid() . '_' . time() . '.' . $ext;

                        if (move_uploaded_file($tmp_name, $filename)) {
                            $image_paths[] = $filename;
                        }
                    }
                }
            }

            $stmt = $pdo->prepare("INSERT INTO listings 
                (seller_id, title, description, price, category_id, condition_status, image_paths, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");

            if ($stmt->execute([
                $_SESSION['user_id'], $title, $description, $price, 
                $category_id, $condition, json_encode($image_paths)
            ])) {
                $success = 'Listing posted successfully! Pending admin approval.';
            } else {
                $error = 'Failed to post listing.';
            }
        }
    }
}

// Fetch categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
$categories = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-kasitrade text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Post New Listing</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

                        <div class="mb-3">
                            <label class="form-label">Item Title *</label>
                            <input type="text" name="title" class="form-control" required 
                                   maxlength="200" placeholder="e.g., Samsung Galaxy A14 - Like New">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category...</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['category_id'] ?>"><?= clean($cat['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price (R) *</label>
                            <div class="input-group">
                                <span class="input-group-text">R</span>
                                <input type="number" name="price" class="form-control" required 
                                       min="1" step="0.01" placeholder="0.00">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Condition *</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="condition" id="new" value="new" checked>
                                <label class="btn btn-outline-kasitrade" for="new">New</label>
                                <input type="radio" class="btn-check" name="condition" id="used" value="used">
                                <label class="btn btn-outline-kasitrade" for="used">Used</label>
                                <input type="radio" class="btn-check" name="condition" id="refurbished" value="refurbished">
                                <label class="btn btn-outline-kasitrade" for="refurbished">Refurbished</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-control" rows="4" required 
                                      maxlength="2000" placeholder="Describe your item..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Photos (max 5)</label>
                            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                            <div class="form-text">First image will be the main photo</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-kasitrade btn-lg">
                                <i class="bi bi-upload"></i> Post Listing
                            </button>
                            <a href="my-listings.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>