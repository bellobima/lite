<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/config.php';

$administratorExists = (int)db()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() > 0;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$administratorExists) {
    verify_csrf();
    $name = trim((string)($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    if ($name === '' || strlen($name) > 160 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
        $error = 'Enter a name, valid email, and password with at least 12 characters.';
    } else {
        try {
            $statement = db()->prepare('INSERT INTO users (full_name, email, role, password_hash) VALUES (?, ?, "admin", ?)');
            $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            flash('Administrator account created. You can now sign in.');
            redirect('login.php');
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $error = 'An account already uses that email address.';
            } else {
                throw $exception;
            }
        }
    }
}
app_header('Administrator setup');
?>
<div class="auth-panel">
    <div class="panel-card">
        <h2 class="panel-title">Initial administrator setup</h2>
        <?php if ($administratorExists): ?>
            <div class="alert alert-info">An administrator account already exists. This setup page is disabled.</div><a class="btn btn-primary" href="login.php">Go to sign in</a>
        <?php else: ?>
            <p class="text-muted">Create the first administrator account. This page becomes unavailable once an admin exists.</p>
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="form-group"><label class="form-label">Administrator full name</label><input class="form-control" name="full_name" maxlength="160" required></div>
                <div class="form-group"><label class="form-label">Email address</label><input class="form-control" type="email" name="email" required></div>
                <div class="form-group"><label class="form-label">Password</label><input class="form-control" type="password" name="password" minlength="12" required><div class="form-hint">At least 12 characters.</div></div>
                <button class="btn btn-primary" type="submit">Create administrator</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php app_footer(); ?>
