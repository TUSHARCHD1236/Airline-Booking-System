<?php
session_start();
require_once 'db.php';

function calculateTotalPrice($basePrice, $classType, $seatNumber, $passengerCount) {
    $classPrices = [
        'Basic' => 5000,
        'silver' => 7500,
        'Gold' => 10000,
        'Premium' => 14000
    ];

    $seatPrices = [
        'A' => 1200,
        'B' => 900,
        'C' => 600,
        'D' => 300
    ];

    $seatGroup = strtoupper(substr($seatNumber, 0, 1));

    $pricePerPassenger = $basePrice + ($classPrices[$classType] ?? 5000) + ($seatPrices[$seatGroup] ?? 0);
    return $pricePerPassenger * max(1, $passengerCount);
}

if (!isset($_SESSION['user'])) {
    echo "
    <html>
    <head>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>

    <script>
    Swal.fire({
        icon: 'warning',
        title: 'Login Required!',
        text: 'Please login first!',
    }).then(() => {
        window.location.href = 'index.php';
    });
    </script>

    </body>
    </html>
    ";
    exit();
}

$conn = getDbConnection();

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = $_SESSION['user'];
$class = $_POST['tic'] ?? 'Basic';
$airline = trim($_POST['airline'] ?? '');
$arr = trim($_POST['arr'] ?? '');
$des = trim($_POST['des'] ?? '');
$travelDate = $_POST['travel_date'] ?? '';
$travelTime = $_POST['travel_time'] ?? '';
$seatNo = strtoupper(trim($_POST['seat_no'] ?? ''));
$passengerCount = (int) ($_POST['passenger_count'] ?? 1);
$flightId = (int) ($_POST['flight_id'] ?? 0);

if (isset($_SESSION['name']) && trim($_SESSION['name']) !== '') {
    $name = $_SESSION['name'];
}

$basePrice = 0;
$flightStmt = mysqli_prepare($conn, "SELECT airline_name, origin_city, destination_city, base_price FROM flights WHERE id = ? AND status = 'active'");
if ($flightStmt) {
    mysqli_stmt_bind_param($flightStmt, "i", $flightId);
    mysqli_stmt_execute($flightStmt);
    $flightResult = mysqli_stmt_get_result($flightStmt);
    $selectedFlight = $flightResult ? mysqli_fetch_assoc($flightResult) : null;

    if ($selectedFlight) {
        $airline = $selectedFlight['airline_name'];
        $arr = $selectedFlight['origin_city'];
        $des = $selectedFlight['destination_city'];
        $basePrice = (float) $selectedFlight['base_price'];
    }

    if ($flightResult) {
        mysqli_free_result($flightResult);
    }
    mysqli_stmt_close($flightStmt);
}

$price = calculateTotalPrice($basePrice, $class, $seatNo, $passengerCount);
$result = false;

if (
    $name !== '' &&
    $phone !== '' &&
    $email !== '' &&
    $airline !== '' &&
    $arr !== '' &&
    $des !== '' &&
    $travelDate !== '' &&
    $travelTime !== '' &&
    $seatNo !== '' &&
    $passengerCount > 0 &&
    $flightId > 0 &&
    $basePrice > 0
) {
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO booking (`name`,`phone`,`email`,`password`,`class_type`,`airline`,`origin_city`,`destination_city`,`travel_date`,`travel_time`,`seat_no`,`passenger_count`,`price`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)"
    );

    if ($stmt) {
        $emptyPassword = '';
        mysqli_stmt_bind_param(
            $stmt,
            "sssssssssssid",
            $name,
            $phone,
            $email,
            $emptyPassword,
            $class,
            $airline,
            $arr,
            $des,
            $travelDate,
            $travelTime,
            $seatNo,
            $passengerCount,
            $price
        );
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking Status</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="container text-center">

<?php if ($result) { ?>

<div class="card shadow-lg p-5 border-success">
<h1 class="text-success">Booking Successful!</h1>
<p class="lead">Thank you <b><?php echo htmlspecialchars($name, ENT_QUOTES); ?></b>, your flight from <b><?php echo htmlspecialchars($arr, ENT_QUOTES); ?></b> to <b><?php echo htmlspecialchars($des, ENT_QUOTES); ?></b> has been reserved.</p>
<p class="mb-1"><b>Travel Date:</b> <?php echo htmlspecialchars($travelDate, ENT_QUOTES); ?></p>
<p class="mb-1"><b>Travel Time:</b> <?php echo htmlspecialchars($travelTime, ENT_QUOTES); ?></p>
<p class="mb-1"><b>Passengers:</b> <?php echo (int) $passengerCount; ?></p>
<p class="mb-1"><b>Seat No:</b> <?php echo htmlspecialchars($seatNo, ENT_QUOTES); ?></p>
<p class="mb-3"><b>Total Price:</b> INR <?php echo number_format($price, 2); ?></p>

<form action="ticket.php" method="POST">

<input type="hidden" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>">
<input type="hidden" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES); ?>">
<input type="hidden" name="airline" value="<?php echo htmlspecialchars($airline, ENT_QUOTES); ?>">
<input type="hidden" name="arr" value="<?php echo htmlspecialchars($arr, ENT_QUOTES); ?>">
<input type="hidden" name="des" value="<?php echo htmlspecialchars($des, ENT_QUOTES); ?>">
<input type="hidden" name="tic" value="<?php echo htmlspecialchars($class, ENT_QUOTES); ?>">
<input type="hidden" name="travel_date" value="<?php echo htmlspecialchars($travelDate, ENT_QUOTES); ?>">
<input type="hidden" name="travel_time" value="<?php echo htmlspecialchars($travelTime, ENT_QUOTES); ?>">
<input type="hidden" name="seat_no" value="<?php echo htmlspecialchars($seatNo, ENT_QUOTES); ?>">
<input type="hidden" name="passenger_count" value="<?php echo (int) $passengerCount; ?>">
<input type="hidden" name="price" value="<?php echo htmlspecialchars((string) $price, ENT_QUOTES); ?>">

<button type="submit" class="btn btn-success mt-3">
Download Ticket (PDF)
</button>

</form>
<br>

<a href="index.php" class="btn btn-primary">Book Another Flight</a>

</div>

<?php } else { ?>

<div class="card shadow-lg p-5 border-danger">
<h1 class="text-danger">Booking Failed</h1>
<p class="lead">Something went wrong while saving your booking.</p>

<a href="index.php" class="btn btn-danger mt-3">Try Again</a>

</div>

<?php } ?>

</div>

</body>
</html>
