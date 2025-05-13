<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$note_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$pin = $_SESSION['pin']; // Assuming PIN is stored in session

$conn = new mysqli('localhost', 'root', 'root', 'secure_note_app');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT is_protected FROM notes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $note_id, $user_id);
$stmt->execute();
$stmt->bind_result($is_protected);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($is_protected) {
        $entered_pin = $_POST['pin'];
        if ($entered_pin === $pin) {
            // Delete note
            $stmt = $conn->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $note_id, $user_id);
            $stmt->execute();
            $stmt->close();
            $conn->close();
            header("Location: home.php");
            exit();
        } else {
            $error = "Invalid PIN.";
        }
    } else {
        // Delete note without PIN
        $stmt = $conn->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $note_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        header("Location: home.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Note</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h1>Delete Note</h1>
    <p>Are you sure you want to delete this note?</p>
    <?php if ($is_protected): ?>
        <form method="POST">
            <label for="pin">Enter PIN to confirm deletion:</label>
            <input type="password" id="pin" name="pin" required><br><br>
            <button type="submit">Delete</button>
        </form>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php else: ?>
        <form method="POST">
            <button type="submit">Delete</button>
        </form>
    <?php endif; ?>
    <a href="home.php">Cancel</a>
</body>
</html>
