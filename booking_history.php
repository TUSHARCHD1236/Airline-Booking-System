<?php
session_start();

if (!isset($_SESSION['user'])) {
    echo "
    <html>
    <head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
    <script>
    Swal.fire({
        icon: 'warning',
        title: 'Login Required',
        text: 'Please login first'
    }).then(() => {
        window.location.href = 'index.php';
    });
    </script>
    </body>
    </html>
    ";
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "airline_system");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$email = $_SESSION['user'];
$name = $_SESSION['name'] ?? 'User';

$stmt = mysqli_prepare($conn, "SELECT name, phone, email, class_type, airline, origin_city, destination_city, travel_date, travel_time, seat_no, passenger_count, price FROM booking WHERE email = ? ORDER BY id DESC");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">SkyHigh</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($name, ENT_QUOTES); ?></span>
                <a href="index.php#book-flight" class="btn btn-outline-light btn-sm me-2">Book Flight</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Your Booking History</h2>
                <p class="text-muted mb-0">Showing reservations for <?php echo htmlspecialchars($email, ENT_QUOTES); ?></p>
            </div>
            <a href="index.php#book-flight" class="btn btn-primary">Book New Flight</a>
        </div>

        <?php if ($result && mysqli_num_rows($result) > 0) { ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped bg-white shadow-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>Passenger</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Airline</th>
                            <th>Class</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Passengers</th>
                            <th>Seat</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?></td>
                                <td><?php echo htmlspecialchars($row['phone'], ENT_QUOTES); ?></td>
                                <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?></td>
                                <td><?php echo htmlspecialchars($row['airline'], ENT_QUOTES); ?></td>
                                <td><?php echo htmlspecialchars($row['class_type'], ENT_QUOTES); ?></td>
                                <td><?php echo htmlspecialchars($row['origin_city'], ENT_QUOTES); ?></td>
                                <td><?php echo htmlspecialchars($row['destination_city'], ENT_QUOTES); ?></td>
                                <td><?php echo htmlspecialchars($row['travel_date'] ?? 'Not set', ENT_QUOTES); ?></td>
                                <td><?php echo htmlspecialchars($row['travel_time'] ?? 'Not set', ENT_QUOTES); ?></td>
                                <td><?php echo htmlspecialchars((string) ($row['passenger_count'] ?? '1'), ENT_QUOTES); ?></td>
                                <td><?php echo htmlspecialchars($row['seat_no'] ?? 'Not set', ENT_QUOTES); ?></td>
                                <td><?php echo $row['price'] !== null ? 'INR ' . number_format((float) $row['price'], 2) : 'Not set'; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="alert alert-info shadow-sm">
                No bookings found for your account yet. Book your first flight from the home page.
            </div>
        <?php } ?>
    </div>
</body>
</html>
<?php
if ($result) {
    mysqli_free_result($result);
}
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
