<!DOCTYPE html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style.css">
<?php
require_once 'config.php';

$id = trim($_GET['id'] ?? '');
if ($id === '') { die('Invalid student ID'); }

// Step 1 | look up the student so the confirmation screen can name them
$stmt = $pdo->prepare('SELECT first_name, last_name FROM students WHERE id = ?');
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    echo '<p>Student not found.</p>';
    $pdo = null;
    exit;
}

// Step 2 | delete only after the POST confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $d = $pdo->prepare('DELETE FROM students WHERE id = ?');
        $d->execute([$id]);

        if ($d->rowCount() === 1) {
            $pdo->commit();   // required: autocommit is off in config.php
            echo '<p>Student deleted successfully.</p>';
        } else {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            echo '<p>Failed to delete student.</p>';
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        error_log($e->getMessage());
        echo '<p>Failed to delete student.</p>';
    }
    echo '<p><a href="index.php">Back to list</a></p>';
    $pdo = null;
} else {
    // Confirmation screen
    ?>
    <h2>Confirm Delete</h2>
    <p>Are you sure you want to delete
       <strong><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></strong>?</p>
    <form method="POST" action="delete.php?id=<?= urlencode($id) ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <button type="submit">Yes, delete</button>
        <a href="index.php">Cancel</a>
    </form>
    <?php
}