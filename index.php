<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/config.php';

$user = current_user();
if ($user) {
    if ($user['role'] === 'admin') {
        redirect('admin.php');
    }
    if ($user['role'] === 'department') {
        redirect('department.php');
    }
    redirect('student.php');
}

$stats = db()->query("SELECT
    COUNT(*) AS submitted,
    COALESCE(SUM(status = 'Submitted'), 0) AS pending,
    COALESCE(SUM(status = 'In Progress'), 0) AS in_progress,
    COALESCE(SUM(status IN ('Resolved', 'Closed')), 0) AS resolved
    FROM tickets")->fetch();

app_header('Student support portal', 'home');
?>
<section class="hero-panel" aria-labelledby="hero-title">
    <div class="hero-image" role="img" aria-label="Students studying together in a college library" style="background-image:url('https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&amp;fit=crop&amp;w=1800&amp;q=85')"></div>
    <div class="hero-content">
        <span class="hero-kicker">FEDERAL COLLEGE OF WILDLIFE MANAGEMENT</span>
        <h2 id="hero-title">A better campus starts with being heard.</h2>
        <p>Share a concern with the right college team. We’ll keep you informed as your complaint moves forward.</p>
        <div class="hero-actions">
            <a class="btn btn-gold" href="register.php">Submit a complaint <i class="feather icon-arrow-right" aria-hidden="true"></i></a>
            <a class="btn btn-hero-secondary" href="#complaint-tracking">Track a complaint</a>
        </div>
        <div class="hero-assurance"><i class="feather icon-lock" aria-hidden="true"></i> Your complaint is shared only with authorized support staff.</div>
    </div>
</section>

<section class="stats-section" aria-labelledby="statistics-title">
    <div class="section-heading section-heading-inline">
        <div><span class="section-kicker">SUPPORT AT A GLANCE</span><h2 id="statistics-title">Complaints across the college</h2></div>
        <p>Live totals from the support portal. No student or complaint details are shown.</p>
    </div>
    <div class="stat-grid public-stat-grid">
        <article class="stat-card"><span class="stat-icon"><i class="feather icon-inbox" aria-hidden="true"></i></span><div class="stat-label">Submitted</div><div class="stat-number"><?= (int)$stats['submitted'] ?></div><small>All complaints received</small></article>
        <article class="stat-card"><span class="stat-icon"><i class="feather icon-clock" aria-hidden="true"></i></span><div class="stat-label">Pending</div><div class="stat-number"><?= (int)$stats['pending'] ?></div><small>Awaiting department review</small></article>
        <article class="stat-card"><span class="stat-icon"><i class="feather icon-loader" aria-hidden="true"></i></span><div class="stat-label">In progress</div><div class="stat-number"><?= (int)$stats['in_progress'] ?></div><small>Being handled by a team</small></article>
        <article class="stat-card"><span class="stat-icon"><i class="feather icon-check-circle" aria-hidden="true"></i></span><div class="stat-label">Resolved</div><div class="stat-number"><?= (int)$stats['resolved'] ?></div><small>Resolved or closed cases</small></article>
    </div>
</section>

<section class="how-section" aria-labelledby="how-title">
    <div class="section-heading"><span class="section-kicker">A CLEAR, SIMPLE PROCESS</span><h2 id="how-title">How it works</h2><p>From sharing a concern to seeing an outcome, every step is easy to follow.</p></div>
    <div class="steps-grid">
        <article class="step-card"><span class="step-number">01</span><span class="step-icon"><i class="feather icon-edit-3" aria-hidden="true"></i></span><h3>Tell us what happened</h3><p>Create a student account, choose the topic, and describe your concern with relevant details.</p></article>
        <article class="step-card"><span class="step-number">02</span><span class="step-icon"><i class="feather icon-share-2" aria-hidden="true"></i></span><h3>We route it to the right team</h3><p>Support staff review the case and assign it to the department best placed to help.</p></article>
        <article class="step-card"><span class="step-number">03</span><span class="step-icon"><i class="feather icon-check-circle" aria-hidden="true"></i></span><h3>Follow progress securely</h3><p>Use your reference number to check the latest status and updates to your case.</p></article>
    </div>
</section>

<section class="tracking-section" id="complaint-tracking" aria-labelledby="tracking-title">
    <div class="tracking-copy"><span class="section-kicker">ALREADY SUBMITTED A CASE?</span><h2 id="tracking-title">Your reference. Your updates.</h2><p>Enter the reference number from your confirmation. For your privacy, we’ll also ask you to verify your matric number.</p><div class="tracking-note"><i class="feather icon-shield" aria-hidden="true"></i> Complaint details are not revealed by reference number alone.</div></div>
    <form class="tracking-form" method="post" action="track.php" data-loading-label="Searching…">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-group"><label class="form-label" for="homepage-reference">Complaint reference number</label><input id="homepage-reference" class="form-control" name="reference" type="text" maxlength="32" required autocomplete="off" placeholder="e.g. FCWM-2610-A1B2C3D4E5" aria-describedby="tracking-reference-hint"><small class="form-hint" id="tracking-reference-hint">Enter the reference provided after you submitted your complaint.</small></div>
        <div class="form-group"><label class="form-label" for="homepage-matric">Student matric number</label><input id="homepage-matric" class="form-control" name="matric_number" type="text" maxlength="60" required autocomplete="off" placeholder="Your matric number"></div>
        <button class="btn btn-primary btn-block" type="submit">Search for my complaint <i class="feather icon-arrow-right" aria-hidden="true"></i></button>
        <div class="tracking-signin">Have an account? <a href="login.php">Sign in to your dashboard</a></div>
    </form>
</section>

<section class="support-section" aria-labelledby="support-title">
    <div class="section-heading"><span class="section-kicker">HERE WHEN YOU NEED US</span><h2 id="support-title">Student support</h2><p>Helpful information for making a complaint and getting assistance.</p></div>
    <div class="support-grid">
        <article class="support-card support-faq">
            <span class="support-card-icon"><i class="feather icon-help-circle" aria-hidden="true"></i></span>
            <h3>Frequently asked questions</h3>
            <details><summary>What should I include in my complaint?</summary><p>Choose the closest topic and explain what happened, when it happened, and the support or outcome you are seeking. Avoid including passwords or unnecessary sensitive information.</p></details>
            <details><summary>How do I follow my complaint?</summary><p>Keep the reference number shown after submission. Enter it with your matric number in the tracker above to see the latest status.</p></details>
            <details><summary>Who can see the details?</summary><p>Your complaint is visible to you and authorized administrators and staff assigned to handle it. Public status lookups require both your reference and matric number.</p></details>
        </article>
        <article class="support-card">
            <span class="support-card-icon"><i class="feather icon-clipboard" aria-hidden="true"></i></span>
            <h3>Before you submit</h3>
            <ul class="guideline-list"><li>Use a clear, specific subject for your concern.</li><li>Include useful dates and relevant context.</li><li>Choose the topic that best describes the issue.</li><li>Save your reference number for future updates.</li></ul>
        </article>
        <article class="support-card support-contact">
            <span class="support-card-icon"><i class="feather icon-message-circle" aria-hidden="true"></i></span>
            <h3>Contact student support</h3>
            <p>Need help using the portal? Contact the support team and we’ll help direct your enquiry.</p>
            <a class="support-phone" href="tel:+2348079406633"><i class="feather icon-phone" aria-hidden="true"></i> 0807 940 6633</a>
            <button class="btn btn-outline-primary btn-block mt-3" type="button" data-contact-open>Contact support</button>
            <p class="privacy-note"><i class="feather icon-lock" aria-hidden="true"></i> Share complaint details only through your secure account.</p>
        </article>
    </div>
</section>
<?php app_footer(); ?>
