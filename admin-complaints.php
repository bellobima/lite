<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/config.php';
require_role('admin');

$queryText = trim((string)($_GET['q'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));
$departmentId = filter_input(INPUT_GET, 'department_id', FILTER_VALIDATE_INT);
$statuses = ['Submitted', 'In Progress', 'Resolved', 'Closed'];
$conditions = [];
$params = [];
if ($queryText !== '') {
    $conditions[] = '(t.reference LIKE ? OR t.subject LIKE ? OR u.full_name LIKE ? OR u.matric_number LIKE ?)';
    $search = '%' . $queryText . '%';
    array_push($params, $search, $search, $search, $search);
}
if (in_array($status, $statuses, true)) {
    $conditions[] = 't.status = ?';
    $params[] = $status;
} else {
    $status = '';
}
if ($departmentId) {
    $conditions[] = 't.department_id = ?';
    $params[] = $departmentId;
}
$sql = 'SELECT t.id, t.reference, t.subject, t.category, t.status, t.created_at, d.name AS department_name, u.full_name AS student_name FROM tickets t JOIN users u ON u.id = t.student_id LEFT JOIN departments d ON d.id = t.department_id';
if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY (t.department_id IS NULL) DESC, t.updated_at DESC LIMIT 200';
$statement = db()->prepare($sql);
$statement->execute($params);
$tickets = $statement->fetchAll();
$departments = db()->query('SELECT id, name FROM departments ORDER BY name')->fetchAll();
app_header('All complaints', 'complaints');
?>
<div class="page-intro"><span class="eyebrow">ADMINISTRATION</span><h2>All complaints</h2><p>Search, filter, and review student cases across the college.</p></div>
<div class="panel-card">
    <form method="get" class="filter-form">
        <div class="form-group filter-search"><label class="form-label" for="q">Search cases</label><input id="q" class="form-control" name="q" value="<?= e($queryText) ?>" placeholder="Reference, student, matric number, or subject"></div>
        <div class="form-group"><label class="form-label" for="status">Status</label><select class="custom-select" id="status" name="status"><option value="">All statuses</option><?php foreach ($statuses as $option): ?><option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label class="form-label" for="department">Department</label><select class="custom-select" id="department" name="department_id"><option value="">All departments</option><?php foreach ($departments as $department): ?><option value="<?= (int)$department['id'] ?>" <?= $departmentId === (int)$department['id'] ? 'selected' : '' ?>><?= e($department['name']) ?></option><?php endforeach; ?></select></div>
        <button class="btn btn-primary filter-button" type="submit">Apply filters</button>
    </form>
</div>
<div class="panel-card">
    <div class="d-flex justify-content-between align-items-center mb-3"><h3 class="panel-title mb-0">Complaints</h3><span class="text-muted"><?= count($tickets) ?> shown (up to 200)</span></div>
    <?php if (!$tickets): ?><div class="empty-state"><i class="feather icon-search"></i><p>No complaints match those filters.</p></div>
    <?php else: ?><div class="table-responsive"><table class="table"><thead><tr><th>Reference</th><th>Student</th><th>Issue</th><th>Department</th><th>Status</th><th>Submitted</th></tr></thead><tbody>
        <?php foreach ($tickets as $ticket): ?><tr><td><a href="ticket.php?id=<?= (int)$ticket['id'] ?>"><?= e($ticket['reference']) ?></a></td><td><?= e($ticket['student_name']) ?></td><td><?= e($ticket['subject']) ?><small class="d-block text-muted"><?= e($ticket['category']) ?></small></td><td><?= e($ticket['department_name'] ?? 'Unassigned') ?></td><td><span class="status-pill <?= e(status_class($ticket['status'])) ?>"><?= e($ticket['status']) ?></span></td><td><?= e(date('M j, Y', strtotime($ticket['created_at']))) ?></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</div>
<?php app_footer(); ?>
