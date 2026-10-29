<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/config.php';
$user = require_role('student');
$categories = ['Academic and teaching', 'Examination and results', 'Fees and payments', 'Hostel and accommodation', 'Facilities and maintenance', 'Student welfare', 'ICT and online services', 'Harassment or safety', 'Other'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $category = trim((string)($_POST['category'] ?? ''));
    $subject = trim((string)($_POST['subject'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    if (!in_array($category, $categories, true) || $subject === '' || strlen($subject) > 180 || strlen($description) < 15 || strlen($description) > 10000) {
        $error = 'Choose a topic, enter a short subject, and describe the issue in at least 15 characters.';
    } else {
        $reference = 'FCWM-' . date('ym') . '-' . strtoupper(bin2hex(random_bytes(5)));
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $insert = $pdo->prepare('INSERT INTO tickets (reference, student_id, category, subject, description) VALUES (?, ?, ?, ?, ?)');
            $insert->execute([$reference, $user['id'], $category, $subject, $description]);
            $ticketId = (int)$pdo->lastInsertId();
            $update = $pdo->prepare('INSERT INTO ticket_updates (ticket_id, user_id, status, message) VALUES (?, ?, ?, ?)');
            $update->execute([$ticketId, $user['id'], 'Submitted', 'Complaint received. It is waiting for review and department assignment.']);
            $pdo->commit();
            flash('Complaint submitted. Keep your reference number safe: ' . $reference);
            redirect('ticket.php?id=' . $ticketId);
        } catch (Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }
}
app_header('Create a complaint', 'create');
?>
<div class="panel-card form-panel">
    <h2 class="panel-title mb-2">Tell us how we can help</h2>
    <p class="text-muted mb-4">Provide enough detail for our support team to understand and route your concern.</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-group"><label class="form-label">Topic</label><select class="custom-select" name="category" required><option value="">Select an issue topic</option><?php foreach ($categories as $category): ?><option value="<?= e($category) ?>" <?= ($category === ($_POST['category'] ?? '')) ? 'selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label class="form-label">Complaint subject</label><input class="form-control" type="text" name="subject" maxlength="180" required placeholder="A short summary of the issue" value="<?= e($_POST['subject'] ?? '') ?>"></div>
        <div class="form-group"><label class="form-label">Describe your concern</label><textarea class="form-control" name="description" maxlength="10000" minlength="15" required placeholder="Include relevant dates, people or services involved, and what outcome would help."><?= e($_POST['description'] ?? '') ?></textarea><div class="form-hint">Your complaint is visible only to you and authorized support staff.</div></div>
        <div class="d-flex justify-content-between align-items-center mt-4"><a class="text-muted" href="student.php">Cancel</a><button type="submit" class="btn btn-primary">Submit complaint <i class="feather icon-arrow-right"></i></button></div>
    </form>
</div>
<?php app_footer(); ?>
