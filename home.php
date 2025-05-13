<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// DB Connection
$servername = "localhost";
$db_username = "root";
$db_password = "root";
$dbname = "secure_note_app";
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch notes
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, title FROM notes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home - Your Notes</title>
    <link rel="stylesheet" href="assets/home.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h2>Secure Notes</h2>
            <nav>
                <ul>
                    <li><a href="create_note.php">Create Note</a></li>
                    <li><a href="logout.php">Sign Out</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <h1>Your Notes</h1>
            <?php if ($result->num_rows > 0): ?>
                <div class="note-grid">
                    <?php while ($note = $result->fetch_assoc()): ?>
                        <div class="note-card">
                            <a href="PIN.php?id=<?php echo $note['id']; ?>">
                                <?php echo htmlspecialchars($note['title']); ?>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>You have no notes yet.</p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
