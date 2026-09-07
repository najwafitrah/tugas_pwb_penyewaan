<?php
session_start();
if (isset($_SESSION['login'])) header("Location: admin/dashboard.php");
else header("Location: login.php");
exit;
?>