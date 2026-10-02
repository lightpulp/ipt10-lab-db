<?php
require_once 'db_connect.php';

$id = trim($_GET['id'] ?? '');
if ($id === '') { die('Invalid student ID'); }

$errors = [];
$fields = ['first_name','middle_name','last_name','birthday','sex',
           'email','student_number','program','enrolment_date'];
$v = array_fill_keys($fields, '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO(15): collect + trim, then the same rules as create.php
    foreach ($fields as $f) { $v[$f] = trim($_POST[$f] ?? ''); }

    foreach (['first_name', 'last_name'] as $f) {
        if (empty($v[$f]) || mb_strlen($v[$f]) < 2 || mb_strlen($v[$f]) > 100
            || !preg_match('/^[\p{L} ]+$/u', $v[$f])) {
            $errors[$f] = 'Required, 2-100 letters and spaces only.';
        }
    }
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Enter a valid email address.'; }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v['birthday'])) { $errors['birthday'] = 'Use YYYY-MM-DD.'; }
    if (!in_array($v['sex'], ['Male', 'Female'], true)) { $errors['sex'] = 'Select Male or Female.'; }
    if ($v['student_number'] === '') { $errors['student_number'] = 'Required.'; }
    if ($v['program'] === '') { $errors['program'] = 'Required.'; }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v['enrolment_date'])) { $errors['enrolment_date'] = 'Use YYYY-MM-DD.'; }

    if (empty($errors)) {
        $mid = $v['middle_name'] === '' ? null : $v['middle_name'];

        // TODO(16)
        $stmt = $conn->prepare(
            'UPDATE students
             SET first_name = ?, middle_name = ?, last_name = ?, birthday = ?, sex = ?,
                 email = ?, student_number = ?, program = ?, enrolment_date = ?
             WHERE id = ?'
        );
        $stmt->bind_param('ssssssssss',
            $v['first_name'], $mid, $v['last_name'], $v['birthday'], $v['sex'],
            $v['email'], $v['student_number'], $v['program'], $v['enrolment_date'], $id);

        try {
            $stmt->execute();

            // TODO(17)
            if ($stmt->affected_rows > 0) {
                echo '<p>Student updated successfully.</p>';
            } else {
                echo '<p>No changes were made.</p>';
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) { $errors['email'] = 'Email or student number already exists.'; }
            else { error_log($e->getMessage()); echo '<p>Update failed.</p>'; }
        }
        $stmt->close();
    }
} else {
    // Fetch the existing row and pre-fill the form
    $s = $conn->prepare(
        'SELECT first_name, middle_name, last_name, birthday, sex,
                email, student_number, program, enrolment_date
         FROM students WHERE id = ?'
    );
    $s->bind_param('s', $id);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $s->close();

    if (!$row) { echo '<p>Student not found.</p>'; $conn->close(); exit; }
    foreach ($fields as $f) { $v[$f] = (string)($row[$f] ?? ''); }
}
?>
<h2>Edit Student</h2>
<form method="POST">
  <p><label>First name
       <input name="first_name" value="<?= htmlspecialchars($v['first_name']) ?>"></label>
     <?= isset($errors['first_name']) ? htmlspecialchars($errors['first_name']) : '' ?></p>

  <p><label>Middle name
       <input name="middle_name" value="<?= htmlspecialchars($v['middle_name']) ?>"></label></p>

  <p><label>Last name
       <input name="last_name" value="<?= htmlspecialchars($v['last_name']) ?>"></label>
     <?= isset($errors['last_name']) ? htmlspecialchars($errors['last_name']) : '' ?></p>

  <p><label>Birthday
       <input type="date" name="birthday" value="<?= htmlspecialchars($v['birthday']) ?>"></label>
     <?= isset($errors['birthday']) ? htmlspecialchars($errors['birthday']) : '' ?></p>

  <p><label>Sex
       <select name="sex">
         <option value="">-- Select --</option>
         <option value="Male"   <?= $v['sex'] === 'Male'   ? 'selected' : '' ?>>Male</option>
         <option value="Female" <?= $v['sex'] === 'Female' ? 'selected' : '' ?>>Female</option>
       </select></label>
     <?= isset($errors['sex']) ? htmlspecialchars($errors['sex']) : '' ?></p>

  <p><label>Email
       <input type="email" name="email" value="<?= htmlspecialchars($v['email']) ?>"></label>
     <?= isset($errors['email']) ? htmlspecialchars($errors['email']) : '' ?></p>

  <p><label>Student number
       <input name="student_number" value="<?= htmlspecialchars($v['student_number']) ?>"></label>
     <?= isset($errors['student_number']) ? htmlspecialchars($errors['student_number']) : '' ?></p>

  <p><label>Program
       <input name="program" value="<?= htmlspecialchars($v['program']) ?>"></label>
     <?= isset($errors['program']) ? htmlspecialchars($errors['program']) : '' ?></p>

  <p><label>Enrolment date
       <input type="date" name="enrolment_date" value="<?= htmlspecialchars($v['enrolment_date']) ?>"></label>
     <?= isset($errors['enrolment_date']) ? htmlspecialchars($errors['enrolment_date']) : '' ?></p>

  <button type="submit">Update</button> <a href="index.php">Cancel</a>
</form>
<?php $conn->close(); ?>