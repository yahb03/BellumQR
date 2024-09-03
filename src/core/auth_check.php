<?php
session_start();
if (!isset($_SESSION['cedula']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// Function to check if user has required role
function check_role($required_roles) {
    if (!in_array($_SESSION['role'], $required_roles)) {
        header("Location: unauthorized.php");
        exit();
    }
}
?>