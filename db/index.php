<?php
require_once 'db_connect.php';

$sql = 'SELECT id, first_name, last_name, email, enrolment_date
        FROM students
        ORDER BY enrolment_date DESC, id ASC';
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style.css">



<h2>All Student Records</h2>
<table>

    <thead>

    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Enrolled</th>
        <th>Actions</th>
    </tr>

    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['enrolment_date']) ?></td>
        <td>
        <a href="view.php?id=<?= $row['id'] ?>">View</a>
        <a href="edit.php?id=<?= $row['id'] ?>">Edit</a>
        <a href="delete.php?id=<?= $row['id'] ?>"
        onclick="return confirm('Are you sure?')">Delete</a>
        </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

  <a href="create.php" class="btn btn-success">+ Add Student</a>
</div>
<?php
mysqli_free_result($result);
mysqli_close($conn);
?>