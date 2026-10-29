<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/config.php';
$user = current_user();
$ticket = null;
$error = '';
$reference = strtoupper(trim((string)($_POST['reference'] ?? '')));
$matric = strtoupper(trim((string)($_POST['matric_number'] ?? '')));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if ($user && $user['role'] === 'student') {
        $query = db()->prepare('SELECT t.*, d.name AS department_name FROM tickets t LEFT JOIN departments d ON d.id = t.department_id WHERE t.reference = ? AND t.student_id = ?');
        $query->execute([$reference, $user['id']]);
    } else {
        $query = db()->prepare('SELECT t.*, d.name AS department_name FROM tickets t JOIN users u ON u.id = t.student_id LEFT JOIN departments d ON d.id = t.department_id WHERE t.reference = ? AND UPPER(u.matric_number) = ?');
        $query->execute([$reference, $matric]);
    }
    $ticket = $query->fetch();
    if (!$ticket) {
        $error = 'We could not find a complaint with those details. Please check the reference and matric number.';
    }
}
app_header('Track a complaint', 'track');
?>
<div class="panel-card form-panel">
    <h2 class="panel-title mb-2">Check your complaint status</h2>
    <p class="text-muted mb-4">Enter the reference number from your confirmation and the student matric number used to submit it.</p>
    <?php if ($error): ?><div class="alert alert-warning"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-group"><label class="form-label">Complaint reference</label><input class="form-control" name="reference" required placeholder="FCWM-YYMM-XXXXXXXXXX" value="<?= e($reference) ?>"></div>
        <?php if (!$user || $user['role'] !== 'student'): ?><div class="form-group"><label class="form-label">Student matric number</label><input class="form-control" name="matric_number" required value="<?= e($matric) ?>"></div><?php endif; ?>
        <button class="btn btn-primary mt-2" type="submit">Find complaint</button>
    </form>
</div>
<?php if ($ticket): ?>
<div class="panel-card form-panel">
    <div class="ticket-heading"><div><span class="ticket-ref"><?= e($ticket['reference']) ?></span><h3 class="panel-title mt-2 mb-1"><?= e($ticket['subject']) ?></h3><span class="text-muted"><?= e($ticket['category']) ?></span></div><span class="status-pill <?= e(status_class($ticket['status'])) ?>"><?= e($ticket['status']) ?></span></div>
    <hr><div class="row"><div class="col-md-6"><small class="text-muted">Assigned department</small><div><?= e($ticket['department_name'] ?? 'Awaiting assignment') ?></div></div><div class="col-md-6"><small class="text-muted">Last updated</small><div><?= e(date('M j, Y \a\t g:i a', strtotime($ticket['updated_at']))) ?></div></div></div>
    <?php if ($user && $user['role'] === 'student' && $ticket['student_id'] === $user['id']): ?><a href="ticket.php?id=<?= (int)$ticket['id'] ?>" class="btn btn-outline-primary mt-4">View full complaint history</a><?php endif; ?>
</div>
<?php endif; ?>
<?php app_footer(); ?>
