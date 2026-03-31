<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("localhost","root","","airline_system");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];

// 🔍 CHECK EMAIL EXISTS
$check = "SELECT * FROM users WHERE email='$email'";
$result_check = mysqli_query($conn, $check);

if(mysqli_num_rows($result_check) > 0){
    $email_exists = true;
} else {
    $email_exists = false;

    // INSERT USER
    $sql = "INSERT INTO users(name,email,password)
    VALUES('$name','$email','$password')";
    $result = mysqli_query($conn,$sql);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Signup Status</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>

<?php if($email_exists){ ?>

Swal.fire({
  title: 'Email Already Exists ⚠️',
  text: 'Try logging in instead!',
  icon: 'warning',
  confirmButtonColor: '#f39c12'
}).then(() => {
  window.history.back();
});

<?php } else if($result){ ?>

Swal.fire({
  title: 'Account Created Successfully 🎉',
  text: 'Welcome <?php echo $name; ?>',
  icon: 'success',
  confirmButtonColor: '#28a745'
}).then(() => {
  window.location.href = 'index.php';
});

<?php } else { ?>

Swal.fire({
  title: 'Error ❌',
  text: 'Something went wrong!',
  icon: 'error'
}).then(() => {
  window.history.back();
});

<?php } ?>

</script>

</body>
</html>
