<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$conn = getDbConnection();
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_flight'])) {
        $airlineName = trim($_POST['airline_name'] ?? '');
        $originCity = trim($_POST['origin_city'] ?? '');
        $destinationCity = trim($_POST['destination_city'] ?? '');
        $basePrice = (float) ($_POST['base_price'] ?? 0);

        if ($airlineName !== '' && $originCity !== '' && $destinationCity !== '' && $originCity !== $destinationCity && $basePrice > 0) {
            $stmt = mysqli_prepare($conn, "INSERT INTO flights (airline_name, origin_city, destination_city, base_price, status) VALUES (?, ?, ?, ?, 'active')");
            mysqli_stmt_bind_param($stmt, "sssd", $airlineName, $originCity, $destinationCity, $basePrice);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $message = 'Flight added successfully.';
        } else {
            $message = 'Please enter valid flight details before adding a flight.';
            $messageType = 'danger';
        }
    }

    if (isset($_POST['delete_flight'])) {
        $flightId = (int) ($_POST['flight_id'] ?? 0);
        if ($flightId > 0) {
            $stmt = mysqli_prepare($conn, "DELETE FROM flights WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $flightId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $message = 'Flight removed successfully.';
        } else {
            $message = 'Unable to remove the selected flight.';
            $messageType = 'danger';
        }
    }

    if (isset($_POST['change_password'])) {
        $currentPassword = trim($_POST['current_password'] ?? '');
        $newPassword = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');
        $adminId = (int) $_SESSION['admin_id'];

        $stmt = mysqli_prepare($conn, "SELECT password FROM admins WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $adminId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $adminRow = $result ? mysqli_fetch_assoc($result) : null;

        if (!$adminRow || $currentPassword !== $adminRow['password']) {
            $message = 'Current password is incorrect.';
            $messageType = 'danger';
        } elseif ($newPassword === '' || strlen($newPassword) < 4) {
            $message = 'New password must be at least 4 characters long.';
            $messageType = 'danger';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'New password and confirm password do not match.';
            $messageType = 'danger';
        } else {
            mysqli_stmt_close($stmt);
            if ($result) {
                mysqli_free_result($result);
            }

            $updateStmt = mysqli_prepare($conn, "UPDATE admins SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($updateStmt, "si", $newPassword, $adminId);
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);
            $message = 'Admin password updated successfully.';
        }

        if (isset($stmt) && $stmt) {
            mysqli_stmt_close($stmt);
        }
        if ($result) {
            mysqli_free_result($result);
        }
    }
}

$stats = [
    'totalBookings' => 0,
    'totalRevenue' => 0,
    'totalUsers' => 0,
    'activeFlights' => 0
];

$statsResult = mysqli_query(
    $conn,
    "SELECT
        (SELECT COUNT(*) FROM booking) AS total_bookings,
        (SELECT COALESCE(SUM(price), 0) FROM booking) AS total_revenue,
        (SELECT COUNT(*) FROM users) AS total_users,
        (SELECT COUNT(*) FROM flights WHERE status = 'active') AS active_flights"
);

if ($statsResult) {
    $row = mysqli_fetch_assoc($statsResult);
    $stats['totalBookings'] = (int) ($row['total_bookings'] ?? 0);
    $stats['totalRevenue'] = (float) ($row['total_revenue'] ?? 0);
    $stats['totalUsers'] = (int) ($row['total_users'] ?? 0);
    $stats['activeFlights'] = (int) ($row['active_flights'] ?? 0);
    mysqli_free_result($statsResult);
}

$recentBookings = mysqli_query($conn, "SELECT name, email, airline, origin_city, destination_city, travel_date, price FROM booking ORDER BY id DESC LIMIT 6");
$flightsResult = mysqli_query($conn, "SELECT id, airline_name, origin_city, destination_city, base_price, status FROM flights ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --navy: #0f172a;
            --blue: #2563eb;
            --sky: #0ea5e9;
            --slate: #64748b;
            --panel: #ffffff;
            --bg: #eef4ff;
        }
        body {
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, 0.22), transparent 26%),
                linear-gradient(180deg, #f8fbff, #eaf1ff);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            color: var(--navy);
        }
        .sidebar {
            background: linear-gradient(180deg, var(--navy), #1e3a8a);
            color: #fff;
            border-radius: 24px;
            padding: 28px;
            min-height: calc(100vh - 48px);
            position: sticky;
            top: 24px;
        }
        .stat-card, .panel-card {
            background: var(--panel);
            border: 0;
            border-radius: 24px;
            box-shadow: 0 18px 40px rgba(37, 99, 235, 0.10);
        }
        .stat-card {
            padding: 24px;
            height: 100%;
        }
        .stat-label {
            color: var(--slate);
            font-size: 0.92rem;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-top: 8px;
        }
        .badge-soft {
            background: rgba(37, 99, 235, 0.12);
            color: var(--blue);
            border-radius: 999px;
            padding: 6px 12px;
            font-weight: 600;
        }
        .table thead th {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="sidebar">
                    <h3 class="mb-2">SkyHigh Admin</h3>
                    <p class="text-white-50 mb-4">Control flights, monitor revenue, and review recent bookings.</p>
                    <div class="mb-4">
                        <div class="small text-white-50">Signed in as</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin', ENT_QUOTES); ?></div>
                        <div class="small text-white-50"><?php echo htmlspecialchars($_SESSION['admin_email'] ?? '', ENT_QUOTES); ?></div>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="#overview" class="btn btn-light">Dashboard Overview</a>
                        <a href="#manage-flights" class="btn btn-outline-light">Manage Flights</a>
                        <a href="index.php" class="btn btn-outline-light">Open Website</a>
                        <a href="admin_logout.php" class="btn btn-danger">Logout</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4" id="overview">
                    <div>
                        <h1 class="mb-1">Dashboard Overview</h1>
                        <p class="text-muted mb-0">A quick snapshot of bookings, users, flights, and income.</p>
                    </div>
                    <span class="badge-soft">Live Admin View</span>
                </div>

                <?php if ($message !== '') { ?>
                    <div class="alert alert-<?php echo $messageType; ?> mb-4"><?php echo htmlspecialchars($message, ENT_QUOTES); ?></div>
                <?php } ?>

                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card">
                            <div class="stat-label">Total Booked Flights</div>
                            <div class="stat-value"><?php echo $stats['totalBookings']; ?></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card">
                            <div class="stat-label">Total Revenue</div>
                            <div class="stat-value">INR <?php echo number_format($stats['totalRevenue'], 0); ?></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card">
                            <div class="stat-label">Registered Users</div>
                            <div class="stat-value"><?php echo $stats['totalUsers']; ?></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card">
                            <div class="stat-label">Active Flights</div>
                            <div class="stat-value"><?php echo $stats['activeFlights']; ?></div>
                        </div>
                    </div>
                </div>

                <div class="panel-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="mb-0">Recent Bookings</h3>
                        <span class="text-muted">Latest 6 reservations</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Passenger</th>
                                    <th>Email</th>
                                    <th>Airline</th>
                                    <th>Route</th>
                                    <th>Date</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recentBookings && mysqli_num_rows($recentBookings) > 0) { ?>
                                    <?php while ($booking = mysqli_fetch_assoc($recentBookings)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['name'], ENT_QUOTES); ?></td>
                                            <td><?php echo htmlspecialchars($booking['email'], ENT_QUOTES); ?></td>
                                            <td><?php echo htmlspecialchars($booking['airline'], ENT_QUOTES); ?></td>
                                            <td><?php echo htmlspecialchars($booking['origin_city'] . ' -> ' . $booking['destination_city'], ENT_QUOTES); ?></td>
                                            <td><?php echo htmlspecialchars($booking['travel_date'] ?? 'Not set', ENT_QUOTES); ?></td>
                                            <td>INR <?php echo number_format((float) ($booking['price'] ?? 0), 2); ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No bookings yet.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row g-4" id="manage-flights">
                    <div class="col-xl-5">
                        <div class="panel-card p-4">
                            <h3 class="mb-3">Add Flight</h3>
                            <form method="POST" action="admin_dashboard.php">
                                <div class="mb-3">
                                    <label class="form-label">Airline Name</label>
                                    <input type="text" class="form-control" name="airline_name" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Origin City</label>
                                        <input type="text" class="form-control" name="origin_city" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Destination City</label>
                                        <input type="text" class="form-control" name="destination_city" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Base Price</label>
                                    <input type="number" class="form-control" name="base_price" min="1" step="0.01" required>
                                </div>
                                <button type="submit" name="add_flight" class="btn btn-primary w-100">Add Flight</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-xl-7">
                        <div class="panel-card p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="mb-0">Change Admin Password</h3>
                                <span class="text-muted">Update your admin login here</span>
                            </div>
                            <form method="POST" action="admin_dashboard.php">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="new_password" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                </div>
                                <button type="submit" name="change_password" class="btn btn-dark">Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-xl-7">
                        <div class="panel-card p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="mb-0">Flight Inventory</h3>
                                <span class="text-muted">Remove flights you no longer want to offer</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Airline</th>
                                            <th>Route</th>
                                            <th>Base Price</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($flightsResult && mysqli_num_rows($flightsResult) > 0) { ?>
                                            <?php while ($flight = mysqli_fetch_assoc($flightsResult)) { ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($flight['airline_name'], ENT_QUOTES); ?></td>
                                                    <td><?php echo htmlspecialchars($flight['origin_city'] . ' -> ' . $flight['destination_city'], ENT_QUOTES); ?></td>
                                                    <td>INR <?php echo number_format((float) $flight['base_price'], 2); ?></td>
                                                    <td class="text-end">
                                                        <form method="POST" action="admin_dashboard.php" onsubmit="return confirm('Remove this flight from the booking form?');">
                                                            <input type="hidden" name="flight_id" value="<?php echo (int) $flight['id']; ?>">
                                                            <button type="submit" name="delete_flight" class="btn btn-outline-danger btn-sm">Remove</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">No flights configured yet.</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
if ($recentBookings) {
    mysqli_free_result($recentBookings);
}
if ($flightsResult) {
    mysqli_free_result($flightsResult);
}
mysqli_close($conn);
?>
