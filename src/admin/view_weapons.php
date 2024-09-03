<?php
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/auth_check.php';

// Ensure only admin or super_user can access this page
check_role(['admin', 'super_user']);

$sql = "SELECT * FROM arma WHERE is_active = TRUE";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Armamento</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .table-container {
            max-height: 70vh;
            overflow-y: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            position: sticky;
            top: 0;
            background-color: #2c2c2c;
            color: #AE8A50;
            cursor: pointer;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #444;
        }
        tr:nth-child(even) {
            background-color: #1c1c1c;
        }
        tr:hover {
            background-color: #333;
        }
        .search-box {
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 5px 10px;
            margin: 2px;
            text-decoration: none;
            color: #fff;
            background-color: #AE8A50;
            border-radius: 3px;
        }
        .btn-delete {
            background-color: #d9534f;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Ver Armamento</h1>
        
        <?php if (isset($_GET['message'])): ?>
            <p class="message"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Buscar...">
        </div>

        <div class="table-container">
            <table id="weaponTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">Serial</th>
                        <th onclick="sortTable(1)">Tipo</th>
                        <th onclick="sortTable(2)">Modelo</th>
                        <th onclick="sortTable(3)">Ubicación</th>
                        <th onclick="sortTable(4)">Condición</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['Serie']) . "</td>
                                    <td>" . htmlspecialchars($row['Tipo_arma']) . "</td>
                                    <td>" . htmlspecialchars($row['Modelo']) . "</td>
                                    <td>" . htmlspecialchars($row['Ubicacion_actual']) . "</td>
                                    <td>" . htmlspecialchars($row['Estado_arma']) . "</td>
                                    <td>
                                        <a href='edit_weapon.php?serie=" . htmlspecialchars($row['Serie']) . "' class='btn btn-edit'>Editar</a>
                                        <a href='delete_weapon.php?serie=" . htmlspecialchars($row['Serie']) . "' class='btn btn-delete' onclick='return confirm(\"¿Estás seguro de que quieres desactivar esta arma?\")'>Desactivar</a>
                                    </td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No se encontraron armas</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <a href="panel_admin.php" class="btn btn-back">Volver</a>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("weaponTable");
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td");
                for (var j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        } else {
                            tr[i].style.display = "none";
                        }
                    }
                }
            }
        });

        // Sorting functionality
        function sortTable(n) {
            var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            table = document.getElementById("weaponTable");
            switching = true;
            dir = "asc";
            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    if (dir == "asc") {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else {
                    if (switchcount == 0 && dir == "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>