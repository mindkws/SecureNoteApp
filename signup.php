<?php

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
$username = $conn->real_escape_string($_POST['username']);
$email = $conn->real_escape_string($_POST['email']);
$password = $_POST['password'];
$hashedPassword = hash('sha256', $password);

// Prepare the SQL statement
$stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashedPassword);

// Execute the statement
if ($stmt->execute()) {
  header("Location: login.html");
  exit();
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
