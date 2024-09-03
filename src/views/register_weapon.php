<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../../phpqrcode/qrlib.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_arma = isset($_POST['tipo_arma']) ? $conn->real_escape_string(trim($_POST['tipo_arma'])) : '';
    $modelo = isset($_POST['modelo']) ? $conn->real_escape_string(trim($_POST['modelo'])) : '';
    $serie = isset($_POST['serie']) ? $conn->real_escape_string(trim($_POST['serie'])) : '';
    $ubicacion_actual = isset($_POST['ubicacion_actual']) ? $conn->real_escape_string(trim($_POST['ubicacion_actual'])) : '';
    $estado_arma = isset($_POST['estado_arma']) ? $conn->real_escape_string(trim($_POST['estado_arma'])) : '';

    if (empty($tipo_arma) || empty($modelo) || empty($serie) || empty($ubicacion_actual) || empty($estado_arma)) {
        header("Location: error.php?type=Validation Error&message=" . urlencode("All fields are required."));
        exit();
    } else {
        try {
            // Use prepared statement to prevent SQL injection
            $sql = "INSERT INTO arma (Serie, Tipo_arma, Modelo, Ubicacion_actual, Estado_arma) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $serie, $tipo_arma, $modelo, $ubicacion_actual, $estado_arma);

            if ($stmt->execute()) {
                // Generate QR code
                $qr_data = "Tipo: $tipo_arma\nModelo: $modelo\nSerie: $serie\nUbicacion: $ubicacion_actual\nEstado: $estado_arma";
                $qr_filename = 'weapon_' . $serie . '.png';
                $qr_path = __DIR__ . '/../qrcodes/' . $qr_filename;
                
                // Create the qrcodes directory if it doesn't exist
                if (!is_dir(__DIR__ . '/../qrcodes/')) {
                    mkdir(__DIR__ . '/../qrcodes/', 0777, true);
                }
                
                QRcode::png($qr_data, $qr_path);

                header("Location: success.php?message=Weapon+registered+successfully&qr=$qr_filename&type=weapon");
                exit();
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            if (strpos($error_message, "Duplicate entry") !== false) {
                $error_type = "Duplicate Entry";
                $error_message = "A weapon with this serial number already exists. Please use a unique serial number.";
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Arma</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class=container>
    <h1>Registrar Arma</h1>
    <form action="register_weapon.php" method="post">
        <label for="tipo_arma">Tipo de Arma:</label>
        <select id="tipo_arma" name="tipo_arma" required>
            <option value="">Selecionar</option>
            <option value="FUSIL">FUSIL</option>
            <option value="PISTOLA">PISTOLA</option>
            <option value="AMETRALLADORA">AMETRALLADORA</option>
            <option value="LANZAGRANADAS">LANZAGRANADAS</option>
            <option value="MORTERO">MORTERO</option>
        </select>

        <label for="modelo">Modelo:</label>
        <input type="text" id="modelo" name="modelo" required>

        <label for="serie">Serial:</label>
        <input type="text" id="serie" name="serie" required>

        <label for="ubicacion_actual">Armerillo Actual:</label>
        <input type="text" id="ubicacion_actual" name="ubicacion_actual" required>

        <label for="estado_arma">Condicion:</label>
        <select id="estado_arma" name="estado_arma" required>
            <option value="">Selecionar</option>
            <option value="BUEN ESTADO">BUEN ESTADO</option>
            <option value="REGULAR ESTADO">REGULAR ESTADO</option>
            <option value="FUERA DE SERVICIO">FUERA DE SERVICIO</option>
        </select>
        <button type="submit" class="button">Registrar</button>
        <a href="index.php" class="button">Volver</a>
    </form>
</div>
</body>
</html>