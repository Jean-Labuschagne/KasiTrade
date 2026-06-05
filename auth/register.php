<?php
require_once '../config/functions.php';

if (isLoggedIn()) {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $username = clean($_POST['username']);
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];
        $first_name = clean($_POST['first_name']);
        $last_name = clean($_POST['last_name']);
        $phone = clean($_POST['phone']);
        $sa_id = clean($_POST['sa_id']);
        $township = clean($_POST['township']);
        $role = $_POST['role'] === 'seller' ? 2 : 3;

        // Validation
        if (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address';
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);

            if ($stmt->fetch()) {
                $error = 'Email or username already exists';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users 
                    (username, email, password_hash, first_name, last_name, 
                     phone_number, sa_id_number, township, role_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

                if ($stmt->execute([$username, $email, $hash, $first_name, $last_name, 
                                   $phone, $sa_id, $township, $role])) {
                    $success = 'Registration successful! Please login.';
                } else {
                    $error = 'Registration failed. Try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - KasiTrade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h3 class="text-kasitrade">KasiTrade</h3>
                            <p class="text-muted">Create your account</p>
                        </div>

                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= generateCSRF() ?>">

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
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" 
                                       placeholder="0821234567" pattern="0[6-8][0-9]{8}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">SA ID Number (optional)</label>
                                <input type="text" name="sa_id" class="form-control" 
                                       placeholder="13 digits" maxlength="13">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Township</label>
                                <select name="township" class="form-select" required>
                                    <option value="">Select your township...</option>
                                    <option value="Soweto">Soweto</option>
                                    <option value="Alexandra">Alexandra</option>
                                    <option value="Khayelitsha">Khayelitsha</option>
                                    <option value="Tembisa">Tembisa</option>
                                    <option value="Mamelodi">Mamelodi</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">I want to</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="role" id="buyer" value="buyer" checked>
                                    <label class="btn btn-outline-kasitrade" for="buyer">Buy</label>
                                    <input type="radio" class="btn-check" name="role" id="seller" value="seller">
                                    <label class="btn btn-outline-kasitrade" for="seller">Sell</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" 
                                       required minlength="8">
                                <div class="form-text">Min 8 characters</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-kasitrade w-100 mb-3">Register</button>

                            <p class="text-center mb-0">Already have an account? 
                                <a href="login.php">Login</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/validation.js"></script>
</body>
</html>