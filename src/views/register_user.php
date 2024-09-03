<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../../phpqrcode/qrlib.php'; 


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
    $cedula = isset($_POST['cedula']) ? $conn->real_escape_string(trim($_POST['cedula'])) : '';
    $nombre = isset($_POST['nombre']) ? $conn->real_escape_string(trim($_POST['nombre'])) : '';
    $apellido = isset($_POST['apellido']) ? $conn->real_escape_string(trim($_POST['apellido'])) : '';
    $grado = isset($_POST['grado']) ? $conn->real_escape_string(trim($_POST['grado'])) : '';
    $unidad = isset($_POST['unidad']) ? $conn->real_escape_string(trim($_POST['unidad'])) : '';
    $telefono = isset($_POST['telefono']) ? $conn->real_escape_string(trim($_POST['telefono'])) : '';
    $correo_electronico = isset($_POST['correo_electronico']) ? $conn->real_escape_string(trim($_POST['correo_electronico'])) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validate that required fields are not empty
    if (empty($cedula) || empty($nombre) || empty($apellido) || empty($grado) || empty($unidad) || empty($password)) {
        header("Location: error.php?type=Validation Error&message=" . urlencode("All required fields must be filled."));
        exit();
    } else {
        try {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Prepare the SQL statement
            $stmt = $conn->prepare("INSERT INTO usuario (Cedula, Nombre, Apellido, Grado, Unidad, Telefono, Correo_electronico, Password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $cedula, $nombre, $apellido, $grado, $unidad, $telefono, $correo_electronico, $hashed_password);

            // Execute the statement
            if ($stmt->execute()) {
                // Generate QR code
                $qr_data = "Cedula: $cedula\nNombre: $nombre\nApellido: $apellido\nGrado: $grado\nUnidad: $unidad\nTelefono: $telefono\nEmail: $correo_electronico";
                $qr_filename = 'user_' . $cedula . '.png';
                $qr_path = __DIR__ . '/../qrcodes/' . $qr_filename;
                
                // Create the qrcodes directory if it doesn't exist
                if (!is_dir(__DIR__ . '/../qrcodes/')) {
                    mkdir(__DIR__ . '/../qrcodes/', 0777, true);
                }
                
                QRcode::png($qr_data, $qr_path);

                header("Location: success.php?message=User+registered+successfully&qr=$qr_filename&type=user");
                exit();
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            if (strpos($error_message, "Duplicate entry") !== false) {
                $error_type = "Duplicate Entry";
                $error_message = "A user with this Cedula already exists. Please use a unique Cedula.";
            } else {
                $error_type = "Database Error";
            }
            header("Location: error.php?type=" . urlencode($error_type) . "&message=" . urlencode($error_message));
            exit();
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Registrar Usuario</h1>
        <form action="register_user.php" method="post">
            <div class="input-group">
                <label for="cedula">Cedula:</label>
                <input type="text" id="cedula" name="cedula" required>
            </div>

            <div class="input-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>

            <div class="input-group">
                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" required>
            </div>

            <div class="input-group">
                <label for="grado">Grado:</label>
                <input type="text" id="grado" name="grado" required>
            </div>

            <div class="input-group">
                <label for="unidad">Unidad:</label>
                <input type="text" id="unidad" name="unidad" required>
            </div>

            <div class="input-group">
                <label for="telefono">Telefono:</label>
                <input type="text" id="telefono" name="telefono">
            </div>

            <div class="input-group">
                <label for="correo_electronico">Email:</label>
                <input type="email" id="correo_electronico" name="correo_electronico">
            </div>

            <div class="input-group">
                <label for="password">Clave:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="button">Registrar</button>
            <a href="index.php" class="button">Volver</a>
        </form>

    </div>
</body>
</html>