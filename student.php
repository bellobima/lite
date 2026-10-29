<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/config.php';
$user = require_role('student');

$countQuery = db()->prepare("SELECT COUNT(*) AS total, SUM(status = 'Submitted') AS pending, SUM(status = 'In Progress') AS in_progress, SUM(status IN ('Resolved','Closed')) AS completed FROM tickets WHERE student_id = ?");
$countQuery->execute([$user['id']]);
$counts = $countQuery->fetch();
$ticketsQuery = db()->prepare('SELECT t.*, d.name AS department_name FROM tickets t LEFT JOIN departments d ON d.id = t.department_id WHERE t.student_id = ? ORDER BY t.created_at DESC LIMIT 10');
$ticketsQuery->execute([$user['id']]);
$tickets = $ticketsQuery->fetchAll();
app_header('My dashboard', 'home');
?>
<div class="welcome-panel student-hero">
    <div><span class="eyebrow">YOUR STUDENT SUPPORT SPACE</span><h2>Hello, <?= e(explode(' ', $user['full_name'])[0]) ?>.</h2><p>Share a concern or check on a complaint you’ve already submitted.</p><div class="hero-actions"><a class="btn btn-gold mt-3" href="create-ticket.php">Submit a complaint <i class="feather icon-arrow-up-right" aria-hidden="true"></i></a><a class="btn btn-hero-secondary mt-3" href="track.php">Track a complaint</a></div></div>
</div>
<div class="stat-grid">
    <div class="stat-card"><span class="stat-icon"><i class="feather icon-layers" aria-hidden="true"></i></span><div class="stat-label">Submitted</div><div class="stat-number"><?= (int)$counts['total'] ?></div><small>All your complaints</small></div>
    <div class="stat-card"><span class="stat-icon"><i class="feather icon-clock" aria-hidden="true"></i></span><div class="stat-label">Pending</div><div class="stat-number"><?= (int)$counts['pending'] ?></div><small>Awaiting review</small></div>
    <div class="stat-card"><span class="stat-icon"><i class="feather icon-loader" aria-hidden="true"></i></span><div class="stat-label">In progress</div><div class="stat-number"><?= (int)$counts['in_progress'] ?></div><small>Being handled</small></div>
    <div class="stat-card"><span class="stat-icon"><i class="feather icon-check-circle" aria-hidden="true"></i></span><div class="stat-label">Resolved</div><div class="stat-number"><?= (int)$counts['completed'] ?></div><small>Resolved or closed</small></div>
</div>
<div class="panel-card">
    <div class="d-flex justify-content-between align-items-center mb-3"><h3 class="panel-title mb-0">Your recent complaints</h3><a href="track.php">Track a complaint</a></div>
    <?php if (!$tickets): ?><div class="empty-state"><i class="feather icon-inbox"></i><p>You haven’t submitted a complaint yet.</p><a class="btn btn-primary mt-3" href="create-ticket.php">Create your first complaint</a></div>
    <?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th>Reference</th><th>Complaint</th><th>Department</th><th>Status</th><th>Submitted</th></tr></thead><tbody>
        <?php foreach ($tickets as $ticket): ?><tr><td><a href="ticket.php?id=<?= (int)$ticket['id'] ?>"><?= e($ticket['reference']) ?></a></td><td><?= e($ticket['subject']) ?></td><td><?= e($ticket['department_name'] ?? 'Awaiting assignment') ?></td><td><span class="status-pill <?= e(status_class($ticket['status'])) ?>"><?= e($ticket['status']) ?></span></td><td><?= e(date('M j, Y', strtotime($ticket['created_at']))) ?></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</div>
<?php app_footer(); ?>
