<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "airline_system");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

$stmt = mysqli_prepare($conn, "SELECT name, email, password FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
<?php
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    if ($password === trim($row['password'])) {
        $_SESSION['user'] = $row['email'];
        $_SESSION['name'] = $row['name'];
?>
Swal.fire({
  title: 'Login Successful',
  text: 'Welcome <?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>',
  icon: 'success',
  confirmButtonColor: '#28a745'
}).then(() => {
  window.location.replace('index.php');
});
<?php
    } else {
?>
Swal.fire({
  title: 'Wrong Password',
  text: 'Try again',
  icon: 'error',
  confirmButtonColor: '#dc3545'
}).then(() => {
  window.history.back();
});
<?php
    }
} else {
?>
Swal.fire({
  title: 'Email Not Found',
  text: 'Signup first',
  icon: 'warning',
  confirmButtonColor: '#f39c12'
}).then(() => {
  window.history.back();
});
<?php
}

mysqli_free_result($result);
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
</script>

</body>
</html>
