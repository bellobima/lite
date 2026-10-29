<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $provided = $_POST['csrf_token'] ?? '';
    if (!is_string($provided) || !hash_equals(csrf_token(), $provided)) {
        http_response_code(400);
        exit('The form expired. Please go back, refresh the page, and try again.');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        redirect('login.php');
    }
    return $user;
}

function require_role(string ...$roles): array
{
    $user = require_login();
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('You do not have permission to access this page.');
    }
    return $user;
}

function status_class(string $status): string
{
    return match ($status) {
        'Resolved', 'Closed' => 'status-done',
        'In Progress' => 'status-progress',
        default => 'status-new',
    };
}

function app_header(string $title, string $active = ''): void
{
    $user = current_user();
    $role = $user['role'] ?? 'guest';
    $notification = null;
    $notificationUrl = '';
    $notificationLabel = '';
    if ($role === 'admin') {
        $notification = (int)db()->query("SELECT COUNT(*) FROM tickets WHERE status = 'Submitted'")->fetchColumn();
        $notificationUrl = 'admin-complaints.php?status=Submitted';
        $notificationLabel = 'New complaints awaiting review';
    } elseif ($role === 'department') {
        $statement = db()->prepare("SELECT COUNT(*) FROM tickets WHERE department_id = ? AND status NOT IN ('Resolved','Closed')");
        $statement->execute([$user['department_id']]);
        $notification = (int)$statement->fetchColumn();
        $notificationUrl = 'department-cases.php';
        $notificationLabel = 'Open cases assigned to your department';
    } elseif ($role === 'student') {
        $statement = db()->prepare("SELECT COUNT(*) FROM tickets WHERE student_id = ? AND status NOT IN ('Resolved','Closed')");
        $statement->execute([$user['id']]);
        $notification = (int)$statement->fetchColumn();
        $notificationUrl = 'student.php';
        $notificationLabel = 'Your open complaints';
    }
    $menus = match ($role) {
        'admin' => [
            ['admin.php', 'Overview', 'icon-grid', 'admin'],
            ['admin-complaints.php', 'All complaints', 'icon-inbox', 'complaints'],
            ['admin-departments.php', 'Departments & staff', 'icon-users', 'staff'],
        ],
        'department' => [
            ['department.php', 'Overview', 'icon-grid', 'department'],
            ['department-cases.php', 'Assigned cases', 'icon-inbox', 'cases'],
        ],
        'student' => [
            ['index.php', 'My dashboard', 'icon-grid', 'home'],
            ['create-ticket.php', 'Create a complaint', 'icon-plus-circle', 'create'],
            ['track.php', 'Track a complaint', 'icon-search', 'track'],
        ],
        default => [
            ['index.php', 'Home', 'icon-home', 'home'],
            ['track.php', 'Track a complaint', 'icon-search', 'track'],
        ],
    };
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#102B46">
    <title><?= e($title) ?> | FCWM Student Support</title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link rel="stylesheet" href="assets/css/bootstrap-material.css">
    <link rel="stylesheet" href="assets/css/shreerang-material.css">
    <link rel="stylesheet" href="assets/css/uikit.css">
    <link rel="stylesheet" href="assets/fonts/feather.css">
    <link rel="stylesheet" href="app.css">
</head>
<body>
<div class="app-shell">
    <aside class="app-sidebar">
        <a class="brand" href="index.php"><span class="brand-mark">W</span><span>FCWM<span class="brand-sub">STUDENT SUPPORT</span></span></a>
        <div class="sidebar-label"><?= e(ucfirst($role)) ?> portal</div>
        <nav class="side-nav">
            <?php foreach ($menus as [$url, $label, $icon, $key]): ?>
                <a class="<?= $active === $key ? 'active' : '' ?>" href="<?= e($url) ?>"><i class="feather <?= e($icon) ?>"></i><span><?= e($label) ?></span></a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-bottom">
            <div class="sidebar-help"><span class="help-icon"><i class="feather icon-help-circle"></i></span><strong>Need help?</strong><small>Our support team is here for you.</small><button class="contact-open" type="button" data-contact-open>Contact support <i class="feather icon-arrow-right"></i></button></div>
            <?php if ($user): ?>
                <form action="logout.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="logout-button" type="submit"><i class="feather icon-log-out"></i> Sign out</button></form>
            <?php endif; ?>
            <div class="sidebar-copyright">Federal College of Wildlife Management</div>
        </div>
    </aside>
    <main class="app-main">
        <header class="topbar">
            <div><span class="eyebrow">FEDERAL COLLEGE OF WILDLIFE MANAGEMENT</span><h1><?= e($title) ?></h1></div>
            <div class="topbar-actions">
                <?php if ($notification !== null): ?>
                    <a class="notification-link" href="<?= e($notificationUrl) ?>" aria-label="<?= e($notificationLabel . ': ' . $notification) ?>" title="<?= e($notificationLabel) ?>">
                        <i class="feather icon-bell" aria-hidden="true"></i><span class="notification-count"><?= $notification ?></span><span class="notification-label"><?= e($role === 'admin' ? 'Awaiting review' : 'Open cases') ?></span>
                    </a>
                <?php endif; ?>
                <div class="topbar-user">
                <?php if ($user): ?>
                    <span class="avatar"><?= e(strtoupper(substr($user['full_name'], 0, 1))) ?></span>
                    <span class="user-label"><strong><?= e($user['full_name']) ?></strong><small><?= e(ucfirst($role)) ?><?= !empty($user['department_name']) ? ' · ' . e($user['department_name']) : '' ?></small></span>
                <?php else: ?>
                    <a class="btn btn-outline-primary btn-sm" href="login.php">Sign in</a>
                <?php endif; ?>
                </div>
            </div>
        </header>
        <section class="page-content">
    <?php
}

function app_footer(): void
{
    ?>
        </section>
        <footer class="app-footer">
            <span>© <?= date('Y') ?> Federal College of Wildlife Management</span>
            <nav class="footer-links" aria-label="Footer navigation">
                <a href="index.php">Home</a><a href="track.php">Track a complaint</a><a href="login.php">Sign in</a><button class="footer-contact contact-open" type="button" data-contact-open>Contact support</button>
            </nav>
        </footer>
    </main>
</div>
<button class="whatsapp-float" type="button" aria-label="Contact us on WhatsApp" data-whatsapp-open>
    <svg viewBox="0 0 32 32" aria-hidden="true"><path d="M16.02 3.2A12.72 12.72 0 0 0 5.08 22.42L3.3 28.9l6.63-1.74A12.7 12.7 0 1 0 16.02 3.2Zm0 23.1a10.34 10.34 0 0 1-5.27-1.45l-.38-.23-3.93 1.03 1.05-3.83-.25-.4a10.37 10.37 0 1 1 8.78 4.88Zm5.69-7.77c-.31-.16-1.84-.91-2.13-1.01-.29-.11-.5-.16-.71.16-.21.31-.82 1.01-1.01 1.22-.19.21-.37.23-.68.08-.32-.16-1.32-.49-2.51-1.55a9.47 9.47 0 0 1-1.74-2.16c-.18-.31-.02-.48.14-.64.14-.14.32-.37.47-.55.16-.19.21-.32.32-.53.1-.21.05-.4-.03-.56-.08-.16-.71-1.7-.98-2.33-.26-.61-.52-.53-.71-.54h-.61c-.21 0-.56.08-.85.4-.29.32-1.11 1.08-1.11 2.64s1.14 3.06 1.29 3.27c.16.21 2.24 3.42 5.42 4.8.76.32 1.35.52 1.81.66.76.24 1.45.2 2 .12.61-.09 1.84-.75 2.1-1.48.26-.73.26-1.35.18-1.48-.08-.13-.29-.21-.61-.37Z"/></svg>
</button>
<div class="contact-backdrop" data-contact-backdrop hidden>
    <section class="contact-modal" role="dialog" aria-modal="true" aria-labelledby="contact-title" aria-describedby="contact-description">
        <button class="contact-close" type="button" aria-label="Close contact window" data-contact-close>&times;</button>
        <span class="contact-kicker">WE'RE HERE TO HELP</span>
        <h2 id="contact-title">Talk to our support team</h2>
        <p id="contact-description">Send a message on WhatsApp and our team will help direct your enquiry.</p>
        <label class="contact-label" for="contact-message">How can we help?</label>
        <textarea id="contact-message" rows="3" maxlength="1000" placeholder="Write a short message…"></textarea>
        <button class="btn btn-primary contact-send" type="button" data-whatsapp-open>Continue to WhatsApp <i class="feather icon-arrow-right"></i></button>
        <a class="contact-phone" href="tel:+2348079406633">Or call 0807 940 6633</a>
    </section>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
    <?php
}

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function show_flash(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    echo '<div class="alert alert-' . e($flash['type']) . '">' . e($flash['message']) . '</div>';
}
