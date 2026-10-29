<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/config.php';
$user = require_role('department');
$summaryQuery = db()->prepare("SELECT COUNT(*) AS total, SUM(status NOT IN ('Resolved','Closed')) AS active, SUM(status IN ('Resolved','Closed')) AS completed FROM tickets WHERE department_id = ?");
$summaryQuery->execute([$user['department_id']]);
$summary = $summaryQuery->fetch();
$statement = db()->prepare("SELECT t.*, u.full_name AS student_name, u.matric_number FROM tickets t JOIN users u ON u.id = t.student_id WHERE t.department_id = ? ORDER BY (t.status IN ('Resolved','Closed')) ASC, t.updated_at DESC LIMIT 5");
$statement->execute([$user['department_id']]);
$tickets = $statement->fetchAll();
app_header('Department queue', 'department');
?>
<div class="welcome-panel"><div><span class="eyebrow"><?= e(strtoupper($user['department_name'] ?? 'DEPARTMENT')) ?></span><h2>Your department queue</h2><p>Only complaints routed to your department appear here.</p></div><i class="feather icon-inbox welcome-icon"></i></div>
<div class="stat-grid">
    <div class="stat-card"><span class="stat-icon"><i class="feather icon-layers"></i></span><div class="stat-label">Assigned complaints</div><div class="stat-number"><?= (int)$summary['total'] ?></div></div>
    <div class="stat-card"><span class="stat-icon"><i class="feather icon-clock"></i></span><div class="stat-label">Open cases</div><div class="stat-number"><?= (int)$summary['active'] ?></div></div>
    <div class="stat-card"><span class="stat-icon"><i class="feather icon-check-circle"></i></span><div class="stat-label">Completed cases</div><div class="stat-number"><?= (int)$summary['completed'] ?></div></div>
</div>
<div class="panel-card">
    <div class="d-flex justify-content-between align-items-center mb-3"><h3 class="panel-title mb-0">Recent assigned cases</h3><a href="department-cases.php">View all cases <i class="feather icon-arrow-right"></i></a></div>
    <?php show_flash(); ?>
    <?php if (!$tickets): ?><div class="empty-state"><i class="feather icon-inbox"></i><p>There are no complaints assigned to your department yet.</p></div>
    <?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th>Reference</th><th>Student</th><th>Issue</th><th>Status</th><th>Updated</th></tr></thead><tbody>
        <?php foreach ($tickets as $ticket): ?><tr><td><a href="ticket.php?id=<?= (int)$ticket['id'] ?>"><?= e($ticket['reference']) ?></a></td><td><?= e($ticket['student_name']) ?><small class="d-block text-muted"><?= e($ticket['matric_number']) ?></small></td><td><?= e($ticket['subject']) ?><small class="d-block text-muted"><?= e($ticket['category']) ?></small></td><td><span class="status-pill <?= e(status_class($ticket['status'])) ?>"><?= e($ticket['status']) ?></span></td><td><?= e(date('M j, Y', strtotime($ticket['updated_at']))) ?></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</div>
<?php app_footer(); ?>
