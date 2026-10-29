<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/config.php';
$user = require_role('admin');
$stats = db()->query("SELECT COUNT(*) AS total, SUM(status = 'Submitted') AS submitted, SUM(status = 'In Progress') AS in_progress, SUM(status IN ('Resolved','Closed')) AS completed FROM tickets")->fetch();
$tickets = db()->query('SELECT t.id, t.reference, t.subject, t.status, t.created_at, d.name AS department_name, u.full_name AS student_name FROM tickets t JOIN users u ON u.id = t.student_id LEFT JOIN departments d ON d.id = t.department_id ORDER BY (t.department_id IS NULL) DESC, t.updated_at DESC LIMIT 5')->fetchAll();
app_header('Admin overview', 'admin');
?>
<div class="welcome-panel"><div><span class="eyebrow">COMPLAINT MANAGEMENT</span><h2>Admin overview</h2><p>Review incoming complaints and send each case to the right team.</p></div><i class="feather icon-shield welcome-icon"></i></div>
<div class="stat-grid">
    <div class="stat-card"><span class="stat-icon"><i class="feather icon-layers"></i></span><div class="stat-label">All complaints</div><div class="stat-number"><?= (int)$stats['total'] ?></div></div>
    <div class="stat-card"><span class="stat-icon"><i class="feather icon-inbox"></i></span><div class="stat-label">Awaiting review</div><div class="stat-number"><?= (int)$stats['submitted'] ?></div></div>
    <div class="stat-card"><span class="stat-icon"><i class="feather icon-loader"></i></span><div class="stat-label">In progress</div><div class="stat-number"><?= (int)$stats['in_progress'] ?></div></div>
    <div class="stat-card"><span class="stat-icon"><i class="feather icon-check-circle"></i></span><div class="stat-label">Completed</div><div class="stat-number"><?= (int)$stats['completed'] ?></div></div>
</div>
<div class="panel-card">
    <div class="d-flex justify-content-between align-items-center mb-3"><h3 class="panel-title mb-0">Recent complaints</h3><a href="admin-complaints.php">View all complaints <i class="feather icon-arrow-right"></i></a></div>
    <?php if (!$tickets): ?><div class="empty-state"><i class="feather icon-inbox"></i><p>No complaints have been submitted yet.</p></div>
    <?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th>Reference</th><th>Student</th><th>Issue</th><th>Department</th><th>Status</th><th>Submitted</th></tr></thead><tbody>
        <?php foreach ($tickets as $ticket): ?><tr><td><a href="ticket.php?id=<?= (int)$ticket['id'] ?>"><?= e($ticket['reference']) ?></a></td><td><?= e($ticket['student_name']) ?></td><td><?= e($ticket['subject']) ?><small class="d-block text-muted"><?= e($ticket['category']) ?></small></td><td><?= e($ticket['department_name'] ?? 'Unassigned') ?></td><td><span class="status-pill <?= e(status_class($ticket['status'])) ?>"><?= e($ticket['status']) ?></span></td><td><?= e(date('M j, Y', strtotime($ticket['created_at']))) ?></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</div>
<div class="panel-card"><div class="d-flex justify-content-between align-items-center"><div><h3 class="panel-title mb-1">Department access</h3><p class="text-muted mb-0">Manage staff accounts and their department access.</p></div><a class="btn btn-primary" href="admin-departments.php">Manage departments &amp; staff</a></div></div>
<?php app_footer(); ?>
