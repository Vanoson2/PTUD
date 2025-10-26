<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Clear all admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_role']);

// Redirect to login page
header('Location: ./login.php');
exit;
?>
