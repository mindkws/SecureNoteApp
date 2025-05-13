<?php
session_start();

// Database connection parameters
$servername = "localhost";
$db_username = "root";
$db_password = "root";
$dbname = "secure_note_app";

// Create a new MySQLi connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve and sanitize user inputs
$username = $_POST['username'];
$enteredPassword = $_POST['password'];
$hashedEnteredPassword = hash('sha256', $enteredPassword);

// Retrieve the stored hashed password from the database
$stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if ($hashedEnteredPassword === $user['password']) {
        // Password is correct; proceed with login
        $_SESSION['user_id'] = $user['id'];
        header("Location: home.php");
        exit();
    } else {
        echo "Invalid username or password.";
    }
} else {
    echo "Invalid username or password.";
}


$stmt->close();
$conn->close();
?>
