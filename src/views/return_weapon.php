<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../../phpqrcode/qrlib.php'; 

function getWeaponDetails($conn, $serie) {
    $sql = "SELECT * FROM arma WHERE Serie = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $serie);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getUserDetails($conn, $cedula) {
    $sql = "SELECT * FROM usuario WHERE Cedula = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serie = $_POST['serie'];
    $cedula = $_POST['cedula'];
    $fecha_devolucion = date('Y-m-d');
    $nuevo_estado = $_POST['nuevo_estado'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Update the asignacion table
        $sql_asignacion = "UPDATE asignacion SET Fecha_devolucion=? WHERE Serie=? AND Cedula=? AND Fecha_devolucion IS NULL";
        $stmt_asignacion = $conn->prepare($sql_asignacion);
        $stmt_asignacion->bind_param("sss", $fecha_devolucion, $serie, $cedula);
        $stmt_asignacion->execute();

        // Update the arma table
        $sql_arma = "UPDATE arma SET Estado_arma=?, Ubicacion_actual='Armería' WHERE Serie=?";
        $stmt_arma = $conn->prepare($sql_arma);
        $stmt_arma->bind_param("ss", $nuevo_estado, $serie);
        $stmt_arma->execute();

        // Commit the transaction
        $conn->commit();

        // Get weapon and user details
        $weapon = getWeaponDetails($conn, $serie);
        $user = getUserDetails($conn, $cedula);

        // Generate QR code with return information
        $qr_data = "Devolución de Arma:\n";
        $qr_data .= "Fecha: $fecha_devolucion\n";
        $qr_data .= "Arma:\n";
        $qr_data .= "  Serie: {$weapon['Serie']}\n";
        $qr_data .= "  Tipo: {$weapon['Tipo_arma']}\n";
        $qr_data .= "  Modelo: {$weapon['Modelo']}\n";
        $qr_data .= "  Nuevo Estado: $nuevo_estado\n";
        $qr_data .= "Usuario:\n";
        $qr_data .= "  Cedula: {$user['Cedula']}\n";
        $qr_data .= "  Nombre: {$user['Nombre']} {$user['Apellido']}\n";
        $qr_data .= "  Grado: {$user['Grado']}\n";
        $qr_data .= "  Unidad: {$user['Unidad']}\n";

        $qr_filename = 'return_' . $serie . '_' . $cedula . '.png';
        $qr_path = __DIR__ . '/../qrcodes/' . $qr_filename;
        QRcode::png($qr_data, $qr_path);

        header("Location: success.php?message=Weapon+returned+successfully&qr=$qr_filename&type=return");
        exit();
    } catch (Exception $e) {
        // An error occurred; rollback the transaction
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entregar Armamento</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <div class=container>
    <h1>Entregar Armamento</h1>
    <form action="return_weapon.php" method="post" id="returnForm">
        <label for="serie">Serial del Arma:</label>
        <input type="text" id="serie" name="serie" required>
        <button type="button" id="searchWeapon">Buscar</button>
        <div id="weaponInfo"></div>
        
        <label for="cedula">Cedula / ID:</label>
        <input type="text" id="cedula" name="cedula" required>
        <button type="button" id="searchUser">Buscar</button>
        <div id="userInfo"></div>
        
        <label for="nuevo_estado">Condicion de Entrega:</label>
        <select name="nuevo_estado" id="nuevo_estado" required>
            <option value="">Seleciona</option>
            <option value="BUEN ESTADO">BUEN ESTADO</option>
            <option value="REGULAR ESTADO">REGULAR ESTADO</option>
            <option value="FUERA SERVICIO">FUERA SERVICIO</option>
        </select>
        
        <button type="submit" id="returnButton" disabled>Entregar</button>
    </form>
    <a href="index.php" class="button">Volver</a>

    <script>
    $(document).ready(function() {
        $('#searchWeapon').click(function() {
            var serie = $('#serie').val();
            $.ajax({
                url: 'search_weapon.php',
                type: 'POST',
                data: {serie: serie},
                success: function(response) {
                    $('#weaponInfo').html(response);
                    checkReturnButton();
                }
            });
        });

        $('#searchUser').click(function() {
            var cedula = $('#cedula').val();
            $.ajax({
                url: 'search_user.php',
                type: 'POST',
                data: {cedula: cedula},
                success: function(response) {
                    $('#userInfo').html(response);
                    checkReturnButton();
                }
            });
        });

        function checkReturnButton() {
            if ($('#weaponInfo').text().includes("found") && $('#userInfo').text().includes("found")) {
                $('#returnButton').prop('disabled', false);
            } else {
                $('#returnButton').prop('disabled', true);
            }
        }
    });
    </script>
    </div>
</body>
</html>