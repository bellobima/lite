<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/config.php';
require_role('admin');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $departmentId = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
    $password = (string)($_POST['password'] ?? '');
    if ($name === '' || strlen($name) > 160 || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$departmentId || strlen($password) < 10) {
        $error = 'Enter a name, valid email, department, and password with at least 10 characters.';
    } else {
        $departmentCheck = db()->prepare('SELECT id FROM departments WHERE id = ?');
        $departmentCheck->execute([$departmentId]);
        if (!$departmentCheck->fetch()) {
            $error = 'Please select a valid department.';
        } else {
            try {
                $createAgent = db()->prepare('INSERT INTO users (full_name, email, department_id, role, password_hash) VALUES (?, ?, ?, "department", ?)');
                $createAgent->execute([$name, $email, $departmentId, password_hash($password, PASSWORD_DEFAULT)]);
                flash('Department staff account created.');
                redirect('admin-departments.php');
            } catch (PDOException $exception) {
                if ($exception->getCode() === '23000') {
                    $error = 'That email address is already registered.';
                } else {
                    throw $exception;
                }
            }
        }
    }
}
$departments = db()->query('SELECT id, name FROM departments ORDER BY name')->fetchAll();
$staff = db()->query('SELECT u.full_name, u.email, d.name AS department_name, u.created_at FROM users u LEFT JOIN departments d ON d.id = u.department_id WHERE u.role = "department" ORDER BY d.name, u.full_name')->fetchAll();
app_header('Departments & staff', 'staff');
?>
<div class="page-intro"><span class="eyebrow">ADMINISTRATION</span><h2>Departments &amp; staff</h2><p>Manage staff access for each department’s assigned complaints.</p></div>
<?php show_flash(); ?>
<div class="panel-card form-panel">
    <h3 class="panel-title">Create a department staff account</h3>
    <p class="text-muted">Staff can view and update complaints routed to their assigned department.</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-group"><label class="form-label">Staff full name</label><input class="form-control" name="full_name" maxlength="160" required value="<?= e($_POST['full_name'] ?? '') ?>"></div>
        <div class="form-group"><label class="form-label">Staff email</label><input class="form-control" type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></div>
        <div class="form-group"><label class="form-label">Department</label><select class="custom-select" name="department_id" required><option value="">Select a department</option><?php foreach ($departments as $department): ?><option value="<?= (int)$department['id'] ?>" <?= (string)$department['id'] === (string)($_POST['department_id'] ?? '') ? 'selected' : '' ?>><?= e($department['name']) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label class="form-label">Temporary password</label><input class="form-control" type="password" name="password" minlength="10" required><div class="form-hint">Share this securely with the account holder.</div></div>
        <button class="btn btn-primary" type="submit">Create staff account</button>
    </form>
</div>
<div class="panel-card">
    <h3 class="panel-title">Department accounts</h3>
    <?php if (!$staff): ?><div class="empty-state"><i class="feather icon-users"></i><p>No department staff accounts have been created.</p></div>
    <?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th>Name</th><th>Email</th><th>Department</th><th>Added</th></tr></thead><tbody><?php foreach ($staff as $member): ?><tr><td><?= e($member['full_name']) ?></td><td><?= e($member['email']) ?></td><td><?= e($member['department_name'] ?? 'Unassigned') ?></td><td><?= e(date('M j, Y', strtotime($member['created_at']))) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</div>
<?php app_footer(); ?>
