<?php
session_start();

session_unset();
session_destroy();

// Redirect to the actual login page
header("Location: login.php");  
exit();
?>