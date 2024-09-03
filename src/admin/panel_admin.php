<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/auth_check.php';
check_role(['admin', 'super_user']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bellum Tech System - Panel de Administración</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <img src="../assets/images/logo.png" alt="Bellum Tech System Logo" class="logo">
        <h1>Panel de Administración - Bellum Tech System</h1>
        <div class="button-grid">
            <a href="../admin/view_users.php" class="button">Ver Usuarios</a>
            <a href="view_weapons.php" class="button">Ver Armas</a>
            <a href="view_assigned_weapons.php" class="button">Ver Asignaciones</a>
            <a href="../views/register_user.php" class="button">Registrar Usuario</a>
            <a href="../views/register_weapon.php" class="button">Registrar Arma</a>
            <a href="../views/assign_weapon.php" class="button">Asignar Arma</a>
            <a href="../views/index.php" class="button">Volver al Inicio</a>
            <a href="../views/logout.php" class="button">Cerrar Sesión</a>
        </div>
    </div>
    
</body>
</html>