<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/config.php';

if (current_user()) {
    redirect('index.php');
}
$departments = db()->query('SELECT id, name FROM departments ORDER BY name')->fetchAll();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $matric = strtoupper(trim((string)($_POST['matric_number'] ?? '')));
    $departmentId = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
    $password = (string)($_POST['password'] ?? '');
    if ($name === '' || strlen($name) > 160 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($matric) < 3 || strlen($matric) > 60 || !$departmentId || strlen($password) < 10) {
        $error = 'Please complete every field. Your password must be at least 10 characters.';
    } else {
        $departmentCheck = db()->prepare('SELECT id FROM departments WHERE id = ?');
        $departmentCheck->execute([$departmentId]);
        if (!$departmentCheck->fetch()) {
            $error = 'Please select a valid department.';
        } else {
            try {
                $statement = db()->prepare('INSERT INTO users (full_name, email, matric_number, department_id, role, password_hash) VALUES (?, ?, ?, ?, "student", ?)');
                $statement->execute([$name, $email, $matric, $departmentId, password_hash($password, PASSWORD_DEFAULT)]);
                flash('Your account is ready. Sign in to submit a complaint.');
                redirect('login.php');
            } catch (PDOException $exception) {
                if ($exception->getCode() === '23000') {
                    $error = 'That email address or matric number is already registered.';
                } else {
                    throw $exception;
                }
            }
        }
    }
}
app_header('Create your student account');
?>
<div class="auth-panel">
    <div class="auth-brand"><a class="brand" href="index.php"><span class="brand-mark">W</span><span>FCWM<span class="brand-sub">STUDENT SUPPORT</span></span></a></div>
    <div class="panel-card">
        <h2 class="panel-title mb-2">Create a student account</h2><p class="text-muted mb-4">Use your college details to get started.</p>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="form-group"><label class="form-label">Full name</label><input class="form-control" type="text" name="full_name" maxlength="160" required value="<?= e($_POST['full_name'] ?? '') ?>" autocomplete="name"></div>
            <div class="form-group"><label class="form-label">Email address</label><input class="form-control" type="email" name="email" maxlength="190" required value="<?= e($_POST['email'] ?? '') ?>" autocomplete="email"></div>
            <div class="form-group"><label class="form-label">Matric number</label><input class="form-control" type="text" name="matric_number" maxlength="60" required value="<?= e($_POST['matric_number'] ?? '') ?>"></div>
            <div class="form-group"><label class="form-label">Department</label><select class="custom-select" name="department_id" required><option value="">Select your department</option><?php foreach ($departments as $department): ?><option value="<?= (int)$department['id'] ?>" <?= (string)($department['id']) === (string)($_POST['department_id'] ?? '') ? 'selected' : '' ?>><?= e($department['name']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label class="form-label">Password</label><input class="form-control" type="password" name="password" minlength="10" required autocomplete="new-password"><div class="form-hint">Use at least 10 characters.</div></div>
            <button class="btn btn-primary btn-block mt-3" type="submit">Create account</button>
        </form>
        <div class="text-center text-muted mt-4">Already registered? <a href="login.php">Sign in</a></div>
    </div>
</div>
<?php app_footer(); ?>
