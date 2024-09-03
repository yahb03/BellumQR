<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only admin or super_user can access this page
check_role(['admin', 'super_user']);

$error_message = '';
$success_message = '';
$weapon = null;

if (isset($_GET['serie'])) {
    $serie = $conn->real_escape_string($_GET['serie']);

    $stmt = $conn->prepare("SELECT * FROM arma WHERE Serie = ?");
    $stmt->bind_param("s", $serie);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $weapon = $result->fetch_assoc();
    } else {
        $error_message = "No weapon found with that serial number.";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_arma = $conn->real_escape_string($_POST['tipo_arma']);
    $modelo = $conn->real_escape_string($_POST['modelo']);
    $ubicacion_actual = $conn->real_escape_string($_POST['ubicacion_actual']);
    $estado_arma = $conn->real_escape_string($_POST['estado_arma']);
    $serie = $conn->real_escape_string($_POST['serie']);

    $stmt = $conn->prepare("UPDATE arma SET Tipo_arma = ?, Modelo = ?, Ubicacion_actual = ?, Estado_arma = ? WHERE Serie = ?");
    $stmt->bind_param("sssss", $tipo_arma, $modelo, $ubicacion_actual, $estado_arma, $serie);

    if ($stmt->execute()) {
        $success_message = "Weapon updated successfully";
    } else {
        $error_message = "Error updating record: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Arma</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Editar Arma</h1>
        
        <?php if ($error_message): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <?php if ($weapon): ?>
            <form action="edit_weapon.php" method="post">
                <input type="hidden" name="serie" value="<?php echo htmlspecialchars($weapon['Serie']); ?>">
                
                <div class="form-group">
                    <label for="tipo_arma">Tipo de Arma:</label>
                    <input type="text" id="tipo_arma" name="tipo_arma" value="<?php echo htmlspecialchars($weapon['Tipo_arma']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="modelo">Modelo:</label>
                    <input type="text" id="modelo" name="modelo" value="<?php echo htmlspecialchars($weapon['Modelo']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="ubicacion_actual">Ubicación Actual:</label>
                    <input type="text" id="ubicacion_actual" name="ubicacion_actual" value="<?php echo htmlspecialchars($weapon['Ubicacion_actual']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="estado_arma">Estado del Arma:</label>
                    <select id="estado_arma" name="estado_arma" required>
                        <option value="BUEN ESTADO" <?php echo ($weapon['Estado_arma'] == 'BUEN ESTADO') ? 'selected' : ''; ?>>BUEN ESTADO</option>
                        <option value="REGULAR ESTADO" <?php echo ($weapon['Estado_arma'] == 'REGULAR ESTADO') ? 'selected' : ''; ?>>REGULAR ESTADO</option>
                        <option value="FUERA DE SERVICIO" <?php echo ($weapon['Estado_arma'] == 'FUERA DE SERVICIO') ? 'selected' : ''; ?>>FUERA DE SERVICIO</option>
                    </select>
                </div>

                <button type="submit" class="btn">Actualizar Arma</button>
            </form>
        <?php endif; ?>

        <a href="view_weapons.php" class="btn">Volver a Armas</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>