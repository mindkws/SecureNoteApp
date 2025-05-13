<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if (!isset($_POST['note_id'], $_POST['pin'])) {
    echo "Invalid request.";
    exit();
}

$note_id = intval($_POST['note_id']);
$entered_pin = $_POST['pin'];
$hashed_pin = hash('sha256', $entered_pin);

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "root";
$dbname = "secure_note_app";
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the hashed pin matches the one in the database
$stmt = $conn->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ? AND pin = ?");
$stmt->bind_param("iis", $note_id, $_SESSION['user_id'], $hashed_pin);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // PIN is correct
    header("Location: view_note.php?id=" . $note_id);
    exit();
} else {
    header("Location: PIN.php?id=" . $note_id);
    exit();
}

$stmt->close();
$conn->close();
?>
