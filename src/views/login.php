<?php
session_start();
require_once '../core/db.php'; // Adjust the path as needed

if (isset($_POST['login'])) {
    $cedula = filter_input(INPUT_POST, 'cedula', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    $query = "SELECT * FROM usuario WHERE Cedula = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['cedula'] = $user['Cedula'];
        $_SESSION['role'] = $user['role'];
        session_regenerate_id(true);

        if ($user['role'] == 'admin' || $user['role'] == 'super_user') {
            header("Location: ../views/index.php");
        } else {
            header("Location: ../views/exit.php");
        }
        exit();
    } else {
        $error_message = "Credenciales inválidas!";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bellum Tech System</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <img src="../assets/images/logo.png" alt="Bellum Tech System Logo" class="logo">
        <h1>Bellum Tech System</h1>
        <?php if (isset($error_message)) echo "<p class='error-message'>$error_message</p>"; ?>
        <form action="login.php" method="post">
            <div class="input-group">
                <label for="cedula">Cedula:</label>
                <input type="text" id="cedula" name="cedula" class="input-field" required>
            </div>
            <div class="input-group">
                <label for="password">Clave:</label>
                <input type="password" id="password" name="password" class="input-field" required>
            </div>
            <button type="submit" name="login" class="button">Entrar</button>
        </form>
    </div>
</body>
</html>