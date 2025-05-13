<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Invalid note access.";
    exit();
}

$note_id = intval($_GET['id']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enter PIN</title>
    <style>
        body {
            background-color: #f9f9f9;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .pin-container {
            background: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            text-align: center;
        }

        input[type="password"] {
            padding: 10px;
            font-size: 18px;
            width: 150px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        h2 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <form class="pin-container" action="validate_pin.php" method="post">
        <h2>Enter 6-digit PIN</h2>
        <input type="hidden" name="note_id" value="<?php echo $note_id; ?>">
        <input type="password" name="pin" pattern="\d{6}" maxlength="6" required>
        <br>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
