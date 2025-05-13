<?php
session_start();
require_once 'encryption.php'; // Ensure this file contains the decryptAES function

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$note_id = $_GET['id'] ?? null;
if (!$note_id || !is_numeric($note_id)) {
    die("Note ID is missing or invalid.");
}

$user_id = $_SESSION['user_id'];
$error = null;
$content = null;

$conn = new mysqli('localhost', 'root', 'root', 'secure_note_app');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_note'])) {
    $stmt = $conn->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $note_id, $user_id);
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: home.php");
        exit();
    } else {
        $error = "Failed to delete the note.";
    }
}

// Fetch note details
$stmt = $conn->prepare("SELECT title, content, pin FROM notes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $note_id, $user_id);
$stmt->execute();
$stmt->bind_result($title, $encrypted_content, $hashed_pin);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pin'])) {
    $entered_pin = $_POST['pin'] ?? '';
    if (password_verify($entered_pin, $hashed_pin)) {
        $key = hash('sha256', $entered_pin, true);
        $iv = substr(hash('sha256', $entered_pin), 0, 16);
        $content = decryptAES($encrypted_content, $key, $iv);
    } else {
        $error = "Invalid PIN.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Note</title>
    <link rel="stylesheet" href="assets/viewnote.css">
</head>
<body>
    <div class="note-container">
        <h1><?php echo htmlspecialchars($title); ?></h1>
        <?php if ($content === null): ?>
            <form method="POST">
                <?php echo htmlspecialchars($encrypted_content ?? ''); ?>
            </form>
            <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

            <!-- Delete and Edit Buttons -->
            <div style="display: flex; gap: 10px;">
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this note?');">
                    <input type="hidden" name="delete_note" value="1">
                    <button type="submit">Delete</button>
                </form>

                <form action="edit_note.php" method="GET">
                    <input type="hidden" name="id" value="<?php echo $note_id; ?>">
                    <button type="submit">Edit</button>
                </form>
            </div>
        <?php endif; ?>
        <br>
        <a href="home.php">Back to Home</a>
    </div>
</body>
</html>
