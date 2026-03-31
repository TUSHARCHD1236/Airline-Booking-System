<?php
session_start();
unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email']);
header('Location: admin_login.php');
exit();
?>
