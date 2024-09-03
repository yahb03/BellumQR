<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .error-container {
            background-color: #ffebee;
            border: 1px solid #f44336;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }
        .error-title {
            color: #d32f2f;
            font-size: 1.2em;
            margin-bottom: 10px;
        }
        .error-message {
            color: #333;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #1976d2;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h1>Error Occurred</h1>
    <div class="error-container">
        <div class="error-title">
            <?php 
            if (isset($_GET['type'])) {
                echo htmlspecialchars($_GET['type']);
            } else {
                echo "An error occurred";
            }
            ?>
        </div>
        <div class="error-message">
            <?php
            if (isset($_GET['message'])) {
                $error_message = htmlspecialchars($_GET['message']);
                if (strpos($error_message, "Duplicate entry") !== false) {
                    if (strpos($error_message, "arma.PRIMARY") !== false) {
                        echo "A weapon with this serial number already exists. Please use a unique serial number.";
                    } elseif (strpos($error_message, "usuario.PRIMARY") !== false) {
                        echo "A user with this ID already exists. Please use a unique ID.";
                    } else {
                        echo "A duplicate entry was detected. Please check your input and try again.";
                    }
                } elseif (strpos($error_message, "Cannot add or update a child row") !== false) {
                    echo "The referenced item does not exist. Please check your input and try again.";
                } else {
                    echo $error_message;
                }
            } else {
                echo "An unexpected error occurred. Please try again or contact support if the problem persists.";
            }
            ?>
        </div>
    </div>
    <a href="index.php" class="back-link">Back to Home</a>
</body>
</html>