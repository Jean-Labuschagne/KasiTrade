<?php
require_once '../config/functions.php';

if (!hasRole('admin')) {
    redirect('../index.php', 'Access denied', 'danger');
}

$pageTitle = 'Manage Users';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' && verifyCSRF($_POST['csrf_token'] ?? '')) {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, role_id, township) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            clean($_POST['username']),
            filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL),
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            clean($_POST['first_name']),
            clean($_POST['last_name']),
            filter_var($_POST['role_id'], FILTER_VALIDATE_INT),
            clean($_POST['township'])
        ]);
    } elseif ($action === 'update' && verifyCSRF($_POST['csrf_token'] ?? '')) {
        $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, first_name=?, last_name=?, 
                              role_id=?, township=?, is_active=? WHERE user_id=?");
        $stmt->execute([
            clean($_POST['username']),
            filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL),
            clean($_POST['first_name']),
            clean($_POST['last_name']),
            filter_var($_POST['role_id'], FILTER_VALIDATE_INT),
            clean($_POST['township']),
            isset($_POST['is_active']) ? 1 : 0,
            filter_var($_POST['user_id'], FILTER_VALIDATE_INT)
        ]);
    } elseif ($action === 'delete' && verifyCSRF($_POST['csrf_token'] ?? '')) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([filter_var($_POST['user_id'], FILTER_VALIDATE_INT)]);
    }

    redirect('users.php', 'Operation completed', 'success');
}

// Fetch all users with roles
$users = $pdo->query("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id ORDER BY u.created_at DESC")->fetchAll();
$roles = $pdo->query("SELECT * FROM roles")->fetchAll();
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
                    <li class="nav-item"><a class="nav-link active" href="users.php"><i class="bi bi-people"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="listings.php"><i class="bi bi-box"></i> Listings</a></li>
                    <li class="nav-item"><a class="nav-link" href="transactions.php"><i class="bi bi-currency-exchange"></i> Transactions</a></li>
                    <li class="nav-item"><a class="nav-link" href="disputes.php"><i class="bi bi-exclamation-triangle"></i> Disputes</a></li>
                    <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-flag"></i> Reports</a></li>
                    <li class="nav-item mt-3"><a class="nav-link text-warning" href="../index.php"><i class="bi bi-arrow-left"></i> Back to Site</a></li>
                </ul>
            </div>
        </nav>

        <main class="admin-main p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="bi bi-people"></i> Manage Users</h4>
                <button class="btn btn-kasitrade" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-circle"></i> Add User
                </button>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Township</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= $u['user_id'] ?></td>
                                    <td><?= clean($u['username']) ?></td>
                                    <td><?= clean($u['email']) ?></td>
                                    <td><?= clean($u['first_name'] . ' ' . $u['last_name']) ?></td>
                                    <td><span class="badge bg-<?= $u['role_id'] == 1 ? 'danger' : ($u['role_id'] == 2 ? 'success' : 'primary') ?>"><?= clean($u['role_name']) ?></span></td>
                                    <td><?= clean($u['township']) ?></td>
                                    <td><span class="badge bg-<?= $u['is_active'] ? 'success' : 'secondary' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?= $u['user_id'] ?>)"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                            <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-kasitrade text-white">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">
                        <input type="hidden" name="action" value="create">

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role_id" class="form-select" required>
                                <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['role_id'] ?>"><?= clean($r['role_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Township</label>
                            <input type="text" name="township" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-kasitrade">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>