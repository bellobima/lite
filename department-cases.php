<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/config.php';
$user = require_role('department');
$status = trim((string)($_GET['status'] ?? ''));
$statuses = ['Submitted', 'In Progress', 'Resolved', 'Closed'];
$params = [$user['department_id']];
$sql = 'SELECT t.*, u.full_name AS student_name, u.matric_number FROM tickets t JOIN users u ON u.id = t.student_id WHERE t.department_id = ?';
if (in_array($status, $statuses, true)) {
    $sql .= ' AND t.status = ?';
    $params[] = $status;
} else {
    $status = '';
}
$sql .= " ORDER BY (t.status IN ('Resolved','Closed')) ASC, t.updated_at DESC LIMIT 200";
$statement = db()->prepare($sql);
$statement->execute($params);
$tickets = $statement->fetchAll();
app_header('Assigned cases', 'cases');
?>
<div class="page-intro"><span class="eyebrow"><?= e(strtoupper($user['department_name'] ?? 'DEPARTMENT')) ?></span><h2>Assigned cases</h2><p>Review and update complaints routed to your department.</p></div>
<div class="panel-card"><form method="get" class="filter-form filter-form-compact"><div class="form-group"><label class="form-label" for="status">Filter by status</label><select class="custom-select" id="status" name="status"><option value="">All statuses</option><?php foreach ($statuses as $option): ?><option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div><button class="btn btn-primary filter-button" type="submit">Apply filter</button></form></div>
<div class="panel-card">
    <h3 class="panel-title">Your department’s complaints <span class="text-muted font-weight-normal">(up to 200)</span></h3>
    <?php if (!$tickets): ?><div class="empty-state"><i class="feather icon-inbox"></i><p>No complaints match this filter.</p></div>
    <?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th>Reference</th><th>Student</th><th>Issue</th><th>Status</th><th>Updated</th></tr></thead><tbody>
        <?php foreach ($tickets as $ticket): ?><tr><td><a href="ticket.php?id=<?= (int)$ticket['id'] ?>"><?= e($ticket['reference']) ?></a></td><td><?= e($ticket['student_name']) ?><small class="d-block text-muted"><?= e($ticket['matric_number']) ?></small></td><td><?= e($ticket['subject']) ?><small class="d-block text-muted"><?= e($ticket['category']) ?></small></td><td><span class="status-pill <?= e(status_class($ticket['status'])) ?>"><?= e($ticket['status']) ?></span></td><td><?= e(date('M j, Y', strtotime($ticket['updated_at']))) ?></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</div>
<?php app_footer(); ?>
