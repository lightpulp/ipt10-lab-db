<?php
require_once 'db_connect.php';

$id = trim($_GET['id'] ?? '');
if ($id === '') { die('Invalid student ID'); }

// TODO(18)
$s = $conn->prepare('SELECT first_name, last_name FROM students WHERE id = ?');
$s->bind_param('s', $id);
$s->execute();
$r = $s->get_result()->fetch_assoc();
$s->close();

if (!$r) {
    echo '<p>Student not found.</p>';
    $conn->close(); exit;
}

// TODO(19)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = $conn->prepare('DELETE FROM students WHERE id = ?');
    $d->bind_param('s', $id);
    $d->execute();

    if ($d->affected_rows === 1) {
        echo '<p>Student deleted successfully.</p>';
    } else {
        echo '<p>Failed to delete student.</p>';
    }
    $d->close();
    $conn->close();
} else {
    // TODO(20)
    ?>

<!DOCTYPE html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style.css">


    <h2>Confirm Delete</h2>
    <p>Are you sure you want to delete
       <strong><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></strong>?</p>
    <form method="POST" action="delete.php?id=<?= urlencode($id) ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <button type="submit">Yes, delete</button>
        <a href="index.php">Cancel</a>
    </form>
    <?php
}
?>