<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit();
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $conn = getDbConnection();
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, password FROM admins WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = $result ? mysqli_fetch_assoc($result) : null;

    if ($admin && $password === $admin['password']) {
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header('Location: admin_dashboard.php');
        exit();
    }

    $errorMessage = 'Invalid admin email or password.';

    if ($result) {
        mysqli_free_result($result);
    }
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .admin-card {
            width: 100%;
            max-width: 460px;
            border: 0;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
        }
        .admin-card .top-band {
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            color: #fff;
            padding: 28px;
        }
    </style>
</head>
<body>
    <div class="card admin-card">
        <div class="top-band">
            <h2 class="mb-2">SkyHigh Admin Panel</h2>
            <p class="mb-0">Monitor bookings, revenue, and flights from one dashboard.</p>
        </div>
        <div class="card-body p-4 p-md-5">
            <?php if ($errorMessage !== '') { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></div>
            <?php } ?>
            <form method="POST" action="admin_login.php">
                <div class="mb-3">
                    <label class="form-label">Admin Email</label>
                    <input type="email" class="form-control form-control-lg" name="email" placeholder="admin@skyhigh.com" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control form-control-lg" name="password" placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Login to Dashboard</button>
            </form>
            <div class="mt-4 text-center text-muted">
                Demo admin access is ready for the project presentation.
            </div>
        </div>
    </div>
</body>
</html>
