<?php
session_start();
require_once 'db.php';

$isLoggedIn = isset($_SESSION['user']);
$loggedInName = $_SESSION['name'] ?? '';
$loggedInEmail = $_SESSION['user'] ?? '';

$conn = getDbConnection();
$flightResult = mysqli_query($conn, "SELECT id, airline_name, origin_city, destination_city, base_price FROM flights WHERE status = 'active' ORDER BY airline_name, origin_city, destination_city");
$flights = [];
$airlines = [];
$origins = [];
$destinations = [];

if ($flightResult) {
    while ($row = mysqli_fetch_assoc($flightResult)) {
        $row['id'] = (int) $row['id'];
        $row['base_price'] = (float) $row['base_price'];
        $flights[] = $row;
        $airlines[$row['airline_name']] = true;
        $origins[$row['origin_city']] = true;
        $destinations[$row['destination_city']] = true;
    }
    mysqli_free_result($flightResult);
}

ksort($airlines);
ksort($origins);
ksort($destinations);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyHigh | Flight Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="sample.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="https://media.istockphoto.com/id/155439315/photo/passenger-airplane-flying-above-clouds-during-sunset.jpg?s=612x612&w=0&k=20&c=LJWadbs3B-jSGJBVy9s0f8gZMHi2NvWFXa3VJ2lFcL0="
                    alt="Logo" height="40" class="rounded me-2">
                <span>SkyHigh</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#book-flight">Book Flights</a></li>
                    <li class="nav-item"><a class="nav-link" href="booking_history.php">Check-In</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_login.php">Admin</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <?php if ($isLoggedIn) { ?>
                        <span class="text-white me-3">Welcome, <?php echo htmlspecialchars($loggedInName, ENT_QUOTES); ?></span>
                        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
                    <?php } else { ?>
                        <button class="btn btn-outline-light btn-sm me-2" data-bs-toggle="modal"
                            data-bs-target="#loginModal">
                            Login
                        </button>

                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#signupModal">
                            Sign Up
                        </button>
                    <?php } ?>
                </div>
            </div>
        </div>
    </nav>

    <header class="hero-section text-white text-center d-flex align-items-center">
        <div class="container">
            <h1 class="display-3 fw-bold">Explore the World Together</h1>
            <p class="lead">Find the best deals on flights to over 500+ destinations.</p>
        </div>
    </header>

    <div class="booking-form-section py-5" id="book-flight">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="booking-box shadow-lg p-4 p-md-5">
                        <h2 class="text-center mb-4 fw-bold text-primary">Flight Booking Counter</h2>

                        <form action="Airline_Booking.php" method="POST">
                            <input type="hidden" id="flight_id" name="flight_id" value="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="Name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="Name" name="name" placeholder="John Doe"
                                        value="<?php echo htmlspecialchars($loggedInName, ENT_QUOTES); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ph" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="ph" name="phone" placeholder="+91..."
                                        required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="em" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="em" name="email"
                                    placeholder="name@example.com"
                                    value="<?php echo htmlspecialchars($loggedInEmail, ENT_QUOTES); ?>"
                                    <?php echo $isLoggedIn ? 'readonly' : ''; ?> required>
                            </div>

                            <div class="mb-3">
                                <label class="d-block mb-2 fw-semibold">Select Class Type:</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="tic" id="ticket" value="Basic"
                                        autocomplete="off" checked>
                                    <label class="btn btn-outline-primary" for="ticket">Basic</label>

                                    <input type="radio" class="btn-check" name="tic" id="ticket1" value="silver"
                                        autocomplete="off">
                                    <label class="btn btn-outline-primary" for="ticket1">Silver</label>

                                    <input type="radio" class="btn-check" name="tic" id="ticket2" value="Gold"
                                        autocomplete="off">
                                    <label class="btn btn-outline-primary" for="ticket2">Gold</label>

                                    <input type="radio" class="btn-check" name="tic" id="ticket3" value="Premium"
                                        autocomplete="off">
                                    <label class="btn btn-outline-primary" for="ticket3">Premium</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Preferred Airline</label>
                                <select class="form-select" name="airline" required>
                                    <option value="" disabled selected>Choose Airline...</option>
                                    <?php foreach (array_keys($airlines) as $airlineName) { ?>
                                        <option value="<?php echo htmlspecialchars($airlineName, ENT_QUOTES); ?>"><?php echo htmlspecialchars($airlineName, ENT_QUOTES); ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Arriving From</label>
                                    <select class="form-select" name="arr" required>
                                        <option value="" disabled selected>Origin City</option>
                                        <?php foreach (array_keys($origins) as $originCity) { ?>
                                            <option value="<?php echo htmlspecialchars($originCity, ENT_QUOTES); ?>"><?php echo htmlspecialchars($originCity, ENT_QUOTES); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Destination</label>
                                    <select class="form-select" name="des" required>
                                        <option value="" disabled selected>Destination City</option>
                                        <?php foreach (array_keys($destinations) as $destinationCity) { ?>
                                            <option value="<?php echo htmlspecialchars($destinationCity, ENT_QUOTES); ?>"><?php echo htmlspecialchars($destinationCity, ENT_QUOTES); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="travelDate" class="form-label">Travel Date</label>
                                    <input type="date" class="form-control" id="travelDate" name="travel_date" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="travelTime" class="form-label">Travel Time</label>
                                    <select class="form-select" id="travelTime" name="travel_time" required>
                                        <option value="" disabled selected>Select Time</option>
                                        <option value="06:00">06:00 AM</option>
                                        <option value="09:30">09:30 AM</option>
                                        <option value="13:00">01:00 PM</option>
                                        <option value="16:30">04:30 PM</option>
                                        <option value="20:00">08:00 PM</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="passenger_count" class="form-label">Number of Passengers</label>
                                    <input type="number" class="form-control" id="passenger_count" name="passenger_count" min="1" max="10" value="1" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="seat_no" class="form-label">Seat Selection</label>
                                    <select class="form-select" id="seat_no" name="seat_no" required>
                                        <option value="" disabled selected>Select Seat</option>
                                        <option value="A1">A1</option>
                                        <option value="A2">A2</option>
                                        <option value="A3">A3</option>
                                        <option value="A4">A4</option>
                                        <option value="B1">B1</option>
                                        <option value="B2">B2</option>
                                        <option value="B3">B3</option>
                                        <option value="B4">B4</option>
                                        <option value="C1">C1</option>
                                        <option value="C2">C2</option>
                                        <option value="C3">C3</option>
                                        <option value="C4">C4</option>
                                        <option value="D1">D1</option>
                                        <option value="D2">D2</option>
                                        <option value="D3">D3</option>
                                        <option value="D4">D4</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="priceDisplay" class="form-label">Total Price</label>
                                    <input type="text" class="form-control bg-light fw-bold" id="priceDisplay" value="INR 5,000" readonly>
                                    <input type="hidden" id="price" name="price" value="5000">
                                    <div class="form-text">Price changes with class and seat selection.</div>
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="Id" required>
                                <label class="form-check-label text-muted" for="Id">
                                    I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a>
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">Confirm
                                Booking</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="container my-5">
        <h2 class="text-center mb-4">Popular Destinations</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card destination-card border-0 shadow-sm">
                    <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=500&q=60"
                        class="card-img-top" alt="Paris">
                    <div class="card-body">
                        <h5 class="card-title">Paris, France</h5>
                        <p class="card-text text-muted">Flights starting from &#x20B9 33500</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card destination-card border-0 shadow-sm">
                    <img src="https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=500&q=60"
                        class="card-img-top" alt="Tokyo">
                    <div class="card-body">
                        <h5 class="card-title">Tokyo, Japan</h5>
                        <p class="card-text text-muted">Flights starting from &#x20B9 61200</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card destination-card border-0 shadow-sm">
                    <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=500&q=60"
                        class="card-img-top" alt="Dubai">
                    <div class="card-body">
                        <h5 class="card-title">Dubai, UAE</h5>
                        <p class="card-text text-muted">Flights starting from &#x20B9 32300</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <p>&copy; 2026 SkyHigh Airline Booking System | College Project</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div id="modalIcon" class="display-1 mb-3"></div>
                    <p id="modalMessage" class="fs-5 text-muted"></p>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-primary px-5" data-bs-dismiss="modal">Understood</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        const bookingForm = document.querySelector('form[action="Airline_Booking.php"]');
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        const flights = <?php echo json_encode($flights); ?>;
        const airlineInput = document.querySelector('select[name="airline"]');
        const originInput = document.querySelector('select[name="arr"]');
        const destinationInput = document.querySelector('select[name="des"]');
        const flightIdInput = document.getElementById('flight_id');
        const travelDateInput = document.getElementById('travelDate');
        const travelTimeInput = document.getElementById('travelTime');
        const seatInput = document.getElementById('seat_no');
        const passengerCountInput = document.getElementById('passenger_count');
        const priceInput = document.getElementById('price');
        const priceDisplay = document.getElementById('priceDisplay');
        const classInputs = document.querySelectorAll('input[name="tic"]');

        const classPrices = {
            Basic: 5000,
            silver: 7500,
            Gold: 10000,
            Premium: 14000
        };

        const seatPrices = {
            A: 1200,
            B: 900,
            C: 600,
            D: 300
        };

        const today = new Date().toISOString().split('T')[0];
        travelDateInput.min = today;

        function updatePrice() {
            const selectedClass = document.querySelector('input[name="tic"]:checked').value;
            const matchedFlight = flights.find((flight) =>
                flight.airline_name === airlineInput.value &&
                flight.origin_city === originInput.value &&
                flight.destination_city === destinationInput.value
            );
            const selectedSeat = seatInput.value;
            const passengerCount = parseInt(passengerCountInput.value || '1', 10);
            const seatGroup = selectedSeat ? selectedSeat.charAt(0) : 'D';
            const basePrice = matchedFlight ? Number(matchedFlight.base_price) : 5000;
            const pricePerPassenger = basePrice + (classPrices[selectedClass] || 5000) + (seatPrices[seatGroup] || 0);
            const totalPrice = pricePerPassenger * passengerCount;

            flightIdInput.value = matchedFlight ? matchedFlight.id : '';
            priceInput.value = totalPrice;
            priceDisplay.value = 'INR ' + totalPrice.toLocaleString('en-IN');
        }

        classInputs.forEach((input) => {
            input.addEventListener('change', updatePrice);
        });

        seatInput.addEventListener('change', updatePrice);
        passengerCountInput.addEventListener('input', updatePrice);
        airlineInput.addEventListener('change', updatePrice);
        originInput.addEventListener('change', updatePrice);
        destinationInput.addEventListener('change', updatePrice);
        updatePrice();

        bookingForm.addEventListener('submit', function (e) {

            if (!isLoggedIn) {
                e.preventDefault();

                Swal.fire({
                    title: 'Login Required',
                    text: 'Please login first',
                    icon: 'warning',
                    confirmButtonColor: '#f39c12'
                }).then(() => {
                    const loginModalElement = document.getElementById('loginModal');
                    if (loginModalElement) {
                        const loginModal = new bootstrap.Modal(loginModalElement);
                        loginModal.show();
                    }
                });
                return;
            }

            const origin = originInput.value;
            const destination = destinationInput.value;
            const matchedFlight = flights.find((flight) =>
                flight.airline_name === airlineInput.value &&
                flight.origin_city === origin &&
                flight.destination_city === destination
            );

            if (origin === destination) {

                e.preventDefault(); // stop form

                const myModal = new bootstrap.Modal(document.getElementById('statusModal'));
                const modalTitle = document.getElementById('modalTitle');
                const modalMessage = document.getElementById('modalMessage');
                const modalIcon = document.getElementById('modalIcon');

                modalTitle.innerText = "Booking Error";
                modalTitle.className = "modal-title fw-bold text-danger";
                modalIcon.innerHTML = "⚠️";
                modalMessage.innerText = "You cannot fly to and from the same city.";

                myModal.show();
                return;
            }

            if (!matchedFlight) {
                e.preventDefault();

                Swal.fire({
                    title: 'Flight Not Available',
                    text: 'Please choose a valid airline and route from the available flights.',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            if (!travelDateInput.value || !travelTimeInput.value || !seatInput.value || !passengerCountInput.value) {
                e.preventDefault();

                Swal.fire({
                    title: 'Missing Booking Details',
                    text: 'Please select travel date, time, seat number and passenger count.',
                    icon: 'warning',
                    confirmButtonColor: '#f39c12'
                });
            }

        });
    </script>
    <!-- Login Modal -->
    <div class="modal fade" id="loginModal">

        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Login</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <form action="login.php" method="POST">

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button class="btn btn-primary w-100">Login</button>

                        <p class="text-center mt-3">
                            Don't have account?
                            <a href="#" data-bs-toggle="modal" data-bs-target="#signupModal" data-bs-dismiss="modal">
                                Create Account
                            </a>
                        </p>

                    </form>

                </div>

            </div>
        </div>
    </div>
    <!-- Sign Up Modal -->
    <div class="modal fade" id="signupModal">

        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Create Account</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <form action="signup.php" method="POST">

                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button class="btn btn-success w-100">Sign Up</button>

                        <p class="text-center mt-3">
                            Already have account?
                            <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">
                                Login
                            </a>
                        </p>

                    </form>

                </div>

            </div>
        </div>
    </div>
</body>

</html>
