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

app_header('Student support, made simple', 'home');
?>
<div class="welcome-panel hero-slider" data-hero-slider>
    <div class="hero-slide is-active" style="background-image:url('https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&amp;fit=crop&amp;w=1800&amp;q=85')" aria-hidden="true"></div>
    <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&amp;fit=crop&amp;w=1800&amp;q=85')" aria-hidden="true"></div>
    <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&amp;fit=crop&amp;w=1800&amp;q=85')" aria-hidden="true"></div>
    <div class="hero-content"><span class="eyebrow">FEDERAL COLLEGE OF WILDLIFE MANAGEMENT</span><h2>We’re here to help you.</h2><p>Tell us what’s happening and we’ll make sure your concern reaches the right team.</p><a class="btn btn-gold mt-3" href="register.php">Create a student account <i class="feather icon-arrow-right"></i></a></div>
    <div class="hero-controls" aria-label="Hero slideshow controls"><button type="button" class="hero-dot is-active" aria-label="Show slide 1" aria-pressed="true"></button><button type="button" class="hero-dot" aria-label="Show slide 2" aria-pressed="false"></button><button type="button" class="hero-dot" aria-label="Show slide 3" aria-pressed="false"></button></div>
</div>
<div class="row">
    <div class="col-lg-7">
        <div class="panel-card"><h3 class="panel-title">How it works</h3>
            <div class="d-flex mb-4"><span class="stat-icon mr-3"><i class="feather icon-edit-3"></i></span><div><strong>Submit your concern</strong><div class="text-muted mt-1">Choose a topic, explain the issue, and send it securely.</div></div></div>
            <div class="d-flex mb-4"><span class="stat-icon mr-3"><i class="feather icon-share-2"></i></span><div><strong>It reaches the right department</strong><div class="text-muted mt-1">College support staff route and review each complaint.</div></div></div>
            <div class="d-flex"><span class="stat-icon mr-3"><i class="feather icon-check-circle"></i></span><div><strong>Follow progress with your reference</strong><div class="text-muted mt-1">Use your reference number and matric number to check updates.</div></div></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel-card"><h3 class="panel-title">Already submitted a complaint?</h3><p class="text-muted">Check the latest status using your reference number and matric number.</p><a class="btn btn-primary btn-block" href="track.php">Track a complaint</a><div class="text-center text-muted mt-3">Have an account? <a href="login.php">Sign in</a></div></div>
        <div class="panel-card"><h3 class="panel-title">For college staff</h3><p class="text-muted mb-0">Sign in with your staff account to manage complaints assigned to your department.</p><a href="login.php" class="d-inline-block mt-3">Staff sign in <i class="feather icon-arrow-right"></i></a></div>
    </div>
</div>
<?php app_footer(); ?>
