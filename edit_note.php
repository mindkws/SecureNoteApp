<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$note_id = $_GET['id'] ?? null;
if (!$note_id || !is_numeric($note_id)) {
    die("Note ID is missing or invalid.");
}

$user_id = $_SESSION['user_id'];
$content = '';
$error = '';
$success = '';

$conn = new mysqli('localhost', 'root', 'root', 'secure_note_app');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch existing note to pre-fill form
$stmt = $conn->prepare("SELECT content, pin FROM notes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $note_id, $user_id);
$stmt->execute();
$stmt->bind_result($stored_content, $hashed_pin);
if ($stmt->fetch()) {
    $stmt->close();
} else {
    $stmt->close();
    $conn->close();
    die("Note not found or access denied.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_content'], $_POST['pin'])) {
    $entered_pin = $_POST['pin'];
    $new_content = $_POST['new_content'];

    if (hash('sha256', $entered_pin) === $hashed_pin) {
        $update_stmt = $conn->prepare("UPDATE notes SET content = ? WHERE id = ? AND user_id = ?");
        $update_stmt->bind_param("sii", $new_content, $note_id, $user_id);

        if ($update_stmt->execute()) {
            $success = "Note updated successfully.";
            $stored_content = $new_content; // Update local value for textarea
        } else {
            $error = "Failed to update the note.";
        }

        $update_stmt->close();
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
    <title>Edit Note</title>
    <link rel="stylesheet" href="assets/edit.css">
</head>
<body>
    <?php if ($success): ?>
        <p style="color: green;"><?php echo $success; ?></p>
        <a href="view_note.php?id=<?php echo $note_id; ?>">Back to Note</a>
    <?php else: ?>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <h1>Edit Note</h1>
            <label for="pin">Enter PIN:</label><br>
            <input type="password" name="pin" required><br><br>

            <label for="new_content">New Note Content:</label><br>
            <textarea name="new_content" rows="10" cols="50" required><?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    echo htmlspecialchars($_POST['new_content']);
                } else {
                    echo htmlspecialchars($stored_content);
                }
            ?></textarea><br><br>

            <button type="submit">Update Note</button>
            <button href="view_note.php?id=<?php echo $note_id; ?>">Cancel</button>
        </form>
        <br>
    <?php endif; ?>
</body>
</html>
