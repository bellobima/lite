<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/config.php';

if (current_user()) {
    redirect('index.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $statement = db()->prepare('SELECT u.*, d.name AS department_name FROM users u LEFT JOIN departments d ON d.id = u.department_id WHERE u.email = ? LIMIT 1');
    $statement->execute([$email]);
    $user = $statement->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        redirect('index.php');
    }
    $error = 'The email or password is incorrect.';
}
app_header('Welcome back');
?>
<div class="auth-panel">
    <div class="auth-brand"><a class="brand" href="index.php"><span class="brand-mark">W</span><span>FCWM<span class="brand-sub">STUDENT SUPPORT</span></span></a></div>
    <div class="panel-card">
        <h2 class="panel-title mb-2">Sign in to your account</h2><p class="text-muted mb-4">Access your complaints and support portal.</p>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <?php show_flash(); ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="form-group"><label class="form-label">Email address</label><input class="form-control" type="email" name="email" required autocomplete="email"></div>
            <div class="form-group"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required autocomplete="current-password"></div>
            <button class="btn btn-primary btn-block mt-3" type="submit">Sign in</button>
        </form>
        <div class="text-center text-muted mt-4">New student? <a href="register.php">Create an account</a></div>
        <div class="text-center text-muted mt-2"><a href="setup.php">Initial administrator setup</a></div>
    </div>
</div>
<?php app_footer(); ?>
