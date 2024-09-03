<?php
require_once 'auth_check.php';
// Check if user has admin or super_user role
check_role(['admin', 'super_user']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bellum Tech System</title>
    <link rel="stylesheet" href="src/styles.css">
</head>
<body>
    <div class="container">
        <img src="src/logo.png" alt="Bellum Tech System Logo" class="logo">
        <h1>Bellum Tech System</h1>
        <div class="button-grid">
    <a href="register_user.php" class="button">Registrar Usuario</a>
    <a href="register_weapon.php" class="button">Registrar Arma</a>
    <a href="assign_weapon.php" class="button">Asignar Arma</a>
    <a href="return_weapon.php" class="button">Devolver Arma</a>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'super_user'): ?>
        <a href="admin/panel_admin.php" class="button">Administración</a>
    <?php endif; ?>
    <a href="logout.php" class="button">Salir</a>
    </div>
    </div>
</body>
</html>