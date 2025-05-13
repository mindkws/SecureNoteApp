<?php
require_once 'encryption.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $is_protected = isset($_POST['is_protected']) ? 1 : 0;
    $user_id = $_SESSION['user_id'];
    $pin = $_POST['pin'] ?? '';
    $hashed_pin = hash('sha256', $pin);

    // Validate inputs
    if (!preg_match('/^\d{6}$/', $pin)) {
        die("Invalid PIN: Please enter exactly 6 digits.");
    }

    if (empty($title) || empty($content)) {
        $error = "Title and content are required.";
    } else {
        // Encrypt content if PIN-protected
        if ($is_protected) {
            $pin = $_SESSION['pin'] ?? null;
            $key = hash('sha256', $pin ?? '', true);
            $iv = substr(hash('sha256', $pin ?? ''), 0, 16);
            $encrypted_content = encryptAES($content, $key, $iv);
        } else {
            $encrypted_content = $content;
        }

        // Insert into database
        $conn = new mysqli('localhost', 'root', 'root', 'secure_note_app');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("INSERT INTO notes (user_id, title, content, pin) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $encrypted_content, $hashed_pin);

        if ($stmt->execute()) {
            header("Location: home.php");
            exit();
        } else {
            $error = "Failed to save note.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Note</title>
    <link rel="stylesheet" href="assets/createnote.css">
</head>
<body>
    <div class="form-container">
        <h1>Create a New Note</h1>
        <form action="create_note.php" method="POST">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="content">Content:</label>
                <textarea id="content" name="content" rows="10" required></textarea>
            </div>
            <div class="form-group">
            <label for="pin">Enter 6-digit PIN:</label>
            <input
                type="text"
                id="pin"
                name="pin"
                pattern="^\d{6}$"
                maxlength="6"
                inputmode="numeric"
                required
                placeholder="123456"
                title="Please enter exactly 6 digits"
            />
            </div>
            <button type="submit" class="submit-btn">Save Note</button>
        </form>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <a href="home.php" class="back-link">Back to Home</a>
    </div>
</body>
</html>


