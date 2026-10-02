<?php
require_once 'config.php';

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

            $sql = 'UPDATE students
                    SET first_name = ?, middle_name = ?, last_name = ?,
                        birthday = ?, sex = ?, email = ?, student_number = ?,
                        program = ?, enrolment_date = ?
                    WHERE id = ?';

            // The id must be the LAST element: it matches the final "?" in WHERE id = ?
            $data = [$v['first_name'], $mid, $v['last_name'], $v['birthday'], $v['sex'],
                    $v['email'], $v['student_number'], $v['program'], $v['enrolment_date'],
                    $id];

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($data);
                $pdo->commit();   // required: autocommit is off in config.php

                if ($stmt->rowCount() > 0) {
                    echo '<p>Student updated successfully.</p>';
                } else {
                    echo '<p>No changes were made.</p>';
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                if (($e->errorInfo[1] ?? 0) === 1062) {   // duplicate email or student number
                    $errors['email'] = 'Email or student number already exists.';
                } else {
                    error_log($e->getMessage());
                    echo '<p>Update failed.</p>';
                }
            }
    }
} $s = $pdo->prepare(
    'SELECT first_name, middle_name, last_name, birthday, sex,
            email, student_number, program, enrolment_date
     FROM students WHERE id = ?'
);
$s->execute([$id]);
$row = $s->fetch();

if (!$row) { echo '<p>Student not found.</p>'; $pdo = null; exit; }
foreach ($fields as $f) { $v[$f] = (string)($row[$f] ?? ''); }

?>

<!DOCTYPE html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style.css">

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

<?php $pdo = null; ?>