<?php
require_once 'config.php';

$errors = [];
$first_name = $middle_name = $last_name = $email = $birthday = $sex = '';
$student_number = $program = $enrolment_date = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name     = trim($_POST['first_name'] ?? '');
    $middle_name    = trim($_POST['middle_name'] ?? '');
    $last_name      = trim($_POST['last_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $birthday       = trim($_POST['birthday'] ?? '');
    $sex            = trim($_POST['sex'] ?? '');
    $student_number = trim($_POST['student_number'] ?? '');
    $program        = trim($_POST['program'] ?? '');
    $enrolment_date = trim($_POST['enrolment_date'] ?? '');

    // TODO(10)
    if (empty($first_name) || mb_strlen($first_name) < 2 || mb_strlen($first_name) > 100
        || !preg_match('/^[\p{L} ]+$/u', $first_name)) {
        $errors['first_name'] = 'First name is required (2-100 letters and spaces only).';
    }
    if (empty($last_name) || mb_strlen($last_name) < 2 || mb_strlen($last_name) > 100
        || !preg_match('/^[\p{L} ]+$/u', $last_name)) {
        $errors['last_name'] = 'Last name is required (2-100 letters and spaces only).';
    }

    // TODO(11)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)
        || !checkdate((int)substr($birthday, 5, 2), (int)substr($birthday, 8, 2), (int)substr($birthday, 0, 4))) {
        $errors['birthday'] = 'Enter a valid date in YYYY-MM-DD format.';
    }

    // TODO(12)
    if (!in_array($sex, ['Male', 'Female'], true)) {
        $errors['sex'] = 'Select Male or Female.';
    }

    // Remaining required columns
    if ($student_number === '') { $errors['student_number'] = 'Student number is required.'; }
    if ($program === '')        { $errors['program'] = 'Program is required.'; }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $enrolment_date)) {
        $errors['enrolment_date'] = 'Enter a valid date in YYYY-MM-DD format.';
    }

    if (empty($errors)) {
    $mid = $middle_name === '' ? null : $middle_name;   // middle_name may be NULL

    $sql = 'INSERT INTO students
            (id, first_name, middle_name, last_name, birthday, sex, email,
             student_number, program, enrolment_date)
            VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?)';

    // Same order as the placeholders (nine values; id uses UUID())
    $data = [$first_name, $mid, $last_name, $birthday, $sex,
             $email, $student_number, $program, $enrolment_date];

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);

        if ($stmt->rowCount() === 1) {
            $pdo->commit();   // required: autocommit is off in config.php
            echo '<p>Student created successfully!</p>';
            $first_name = $middle_name = $last_name = $email = $birthday = $sex = '';
            $student_number = $program = $enrolment_date = '';
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        if (($e->errorInfo[1] ?? 0) === 1062) {   // duplicate email or student number
            $errors['email'] = 'Email or student number already exists.';
        } else {
            error_log($e->getMessage());
            echo '<p>Could not save the student.</p>';
        }
    }
}
}
?>

<!DOCTYPE html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style.css">

<h2>Add New Student</h2>
<form method="POST">
  <p><label>First name <input name="first_name" value="<?= htmlspecialchars($first_name) ?>"></label>
     <?= isset($errors['first_name']) ? htmlspecialchars($errors['first_name']) : '' ?></p>
  <p><label>Middle name <input name="middle_name" value="<?= htmlspecialchars($middle_name) ?>"></label></p>
  <p><label>Last name <input name="last_name" value="<?= htmlspecialchars($last_name) ?>"></label>
     <?= isset($errors['last_name']) ? htmlspecialchars($errors['last_name']) : '' ?></p>
  <p><label>Birthday <input type="date" name="birthday" value="<?= htmlspecialchars($birthday) ?>"></label>
     <?= isset($errors['birthday']) ? htmlspecialchars($errors['birthday']) : '' ?></p>
  <p><label>Sex
       <select name="sex">
         <option value="">-- Select --</option>
         <option value="Male"   <?= $sex === 'Male'   ? 'selected' : '' ?>>Male</option>
         <option value="Female" <?= $sex === 'Female' ? 'selected' : '' ?>>Female</option>
       </select></label>
     <?= isset($errors['sex']) ? htmlspecialchars($errors['sex']) : '' ?></p>
  <p><label>Email <input type="email" name="email" value="<?= htmlspecialchars($email) ?>"></label>
     <?= isset($errors['email']) ? htmlspecialchars($errors['email']) : '' ?></p>
  <p><label>Student number <input name="student_number" value="<?= htmlspecialchars($student_number) ?>"></label>
     <?= isset($errors['student_number']) ? htmlspecialchars($errors['student_number']) : '' ?></p>
  <p><label>Program <input name="program" value="<?= htmlspecialchars($program) ?>"></label>
     <?= isset($errors['program']) ? htmlspecialchars($errors['program']) : '' ?></p>
  <p><label>Enrolment date <input type="date" name="enrolment_date" value="<?= htmlspecialchars($enrolment_date) ?>"></label>
     <?= isset($errors['enrolment_date']) ? htmlspecialchars($errors['enrolment_date']) : '' ?></p>
  <button type="submit">Save</button> <a href="index.php">Cancel</a>
</form>

<?php $pdo = null; ?>