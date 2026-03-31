<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logout</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
Swal.fire({
  title: 'Logged Out',
  text: 'You have been logged out successfully',
  icon: 'success',
  confirmButtonColor: '#28a745'
}).then(() => {
  window.location.replace('index.php');
});
</script>
</body>
</html>
