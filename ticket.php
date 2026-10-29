<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/config.php';
$user = require_login();
$ticketId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$ticketId) {
    http_response_code(404);
    exit('Complaint not found.');
}

$query = db()->prepare('SELECT t.*, d.name AS department_name, s.full_name AS student_name, s.email AS student_email, s.matric_number FROM tickets t JOIN users s ON s.id = t.student_id LEFT JOIN departments d ON d.id = t.department_id WHERE t.id = ?');
$query->execute([$ticketId]);
$ticket = $query->fetch();
if (!$ticket || ($user['role'] === 'student' && (int)$ticket['student_id'] !== (int)$user['id']) || ($user['role'] === 'department' && (int)$ticket['department_id'] !== (int)$user['department_id'])) {
    http_response_code(404);
    exit('Complaint not found.');
}
if (!in_array($user['role'], ['admin', 'department', 'student'], true)) {
    http_response_code(403);
    exit('You do not have permission to access this page.');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if ($user['role'] === 'student') {
        http_response_code(403);
        exit('Students cannot edit complaint updates.');
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        if (isset($_POST['assign_department']) && $user['role'] === 'admin') {
            $departmentId = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
            $departmentQuery = $pdo->prepare('SELECT id, name FROM departments WHERE id = ?');
            $departmentQuery->execute([$departmentId]);
            $department = $departmentQuery->fetch();
            if (!$department) {
                $error = 'Select a valid department.';
            } else {
                $assign = $pdo->prepare('UPDATE tickets SET department_id = ? WHERE id = ?');
                $assign->execute([$department['id'], $ticketId]);
                $log = $pdo->prepare('INSERT INTO ticket_updates (ticket_id, user_id, status, message) VALUES (?, ?, ?, ?)');
                $log->execute([$ticketId, $user['id'], $ticket['status'], 'Complaint routed to ' . $department['name'] . '.']);
                $pdo->commit();
                flash('Complaint routed to ' . $department['name'] . '.');
                redirect('ticket.php?id=' . $ticketId);
            }
        } else {
            $status = (string)($_POST['status'] ?? '');
            $message = trim((string)($_POST['message'] ?? ''));
            $validStatuses = ['Submitted', 'In Progress', 'Resolved', 'Closed'];
            if (!in_array($status, $validStatuses, true) || strlen($message) < 5 || strlen($message) > 5000) {
                $error = 'Choose a valid status and enter an update of at least 5 characters.';
            } else {
                $updateTicket = $pdo->prepare('UPDATE tickets SET status = ? WHERE id = ?');
                $updateTicket->execute([$status, $ticketId]);
                $log = $pdo->prepare('INSERT INTO ticket_updates (ticket_id, user_id, status, message) VALUES (?, ?, ?, ?)');
                $log->execute([$ticketId, $user['id'], $status, $message]);
                $pdo->commit();
                flash('Complaint update saved.');
                redirect('ticket.php?id=' . $ticketId);
            }
        }
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

$departments = $user['role'] === 'admin' ? db()->query('SELECT id, name FROM departments ORDER BY name')->fetchAll() : [];
$updatesQuery = db()->prepare('SELECT tu.*, u.full_name AS author FROM ticket_updates tu LEFT JOIN users u ON u.id = tu.user_id WHERE tu.ticket_id = ? ORDER BY tu.created_at DESC, tu.id DESC');
$updatesQuery->execute([$ticketId]);
$updates = $updatesQuery->fetchAll();
$backLink = match ($user['role']) {
    'admin' => 'admin.php',
    'department' => 'department.php',
    default => 'student.php',
};
app_header('Complaint details');
?>
<div class="panel-card">
    <?php show_flash(); ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <div class="ticket-heading">
        <div><a href="<?= e($backLink) ?>" class="text-muted"><i class="feather icon-arrow-left"></i> Back</a><div class="ticket-ref mt-3"><?= e($ticket['reference']) ?></div><h2 class="panel-title mt-2 mb-1"><?= e($ticket['subject']) ?></h2><span class="text-muted"><?= e($ticket['category']) ?></span></div>
        <span class="status-pill <?= e(status_class($ticket['status'])) ?>"><?= e($ticket['status']) ?></span>
    </div>
    <hr>
    <div class="row mb-3">
        <div class="col-md-4 mb-2"><small class="text-muted">Submitted by</small><div><?= e($ticket['student_name']) ?></div><?php if ($user['role'] !== 'student'): ?><small class="text-muted"><?= e($ticket['matric_number']) ?> · <?= e($ticket['student_email']) ?></small><?php endif; ?></div>
        <div class="col-md-4 mb-2"><small class="text-muted">Department</small><div><?= e($ticket['department_name'] ?? 'Awaiting assignment') ?></div></div>
        <div class="col-md-4 mb-2"><small class="text-muted">Submitted</small><div><?= e(date('M j, Y \a\t g:i a', strtotime($ticket['created_at']))) ?></div></div>
    </div>
    <h3 class="panel-title mt-4 mb-2">Complaint description</h3><div class="text-muted" style="white-space:pre-wrap;line-height:1.75"><?= e($ticket['description']) ?></div>
</div>

<?php if ($user['role'] === 'admin'): ?>
<div class="panel-card form-panel">
    <h3 class="panel-title">Route this complaint</h3>
    <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="assign_department" value="1">
        <div class="form-group"><label class="form-label">Assign to department</label><select name="department_id" class="custom-select" required><option value="">Choose a department</option><?php foreach ($departments as $department): ?><option value="<?= (int)$department['id'] ?>" <?= (int)$ticket['department_id'] === (int)$department['id'] ? 'selected' : '' ?>><?= e($department['name']) ?></option><?php endforeach; ?></select></div>
        <button class="btn btn-primary" type="submit">Save department assignment</button>
    </form>
</div>
<?php endif; ?>

<?php if (in_array($user['role'], ['admin', 'department'], true)): ?>
<div class="panel-card form-panel">
    <h3 class="panel-title">Post an update</h3>
    <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-group"><label class="form-label">Status</label><select name="status" class="custom-select" required><?php foreach (['Submitted', 'In Progress', 'Resolved', 'Closed'] as $status): ?><option value="<?= e($status) ?>" <?= $ticket['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label class="form-label">Message for the student</label><textarea class="form-control" name="message" minlength="5" maxlength="5000" required placeholder="Add a note about progress or next steps."></textarea></div>
        <button class="btn btn-primary" type="submit">Post update</button>
    </form>
</div>
<?php endif; ?>

<div class="panel-card form-panel"><h3 class="panel-title">Complaint history</h3>
    <?php if (!$updates): ?><div class="empty-state"><p>No updates have been recorded.</p></div>
    <?php else: ?><div class="timeline"><?php foreach ($updates as $update): ?><div class="timeline-item"><strong><?= e($update['status']) ?></strong><p><?= e($update['message']) ?></p><small><?= e($update['author'] ?? 'Support team') ?> · <?= e(date('M j, Y \a\t g:i a', strtotime($update['created_at']))) ?></small></div><?php endforeach; ?></div><?php endif; ?>
</div>
<?php app_footer(); ?>
