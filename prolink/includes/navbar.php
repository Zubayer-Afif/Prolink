<?php
// navbar.php — include on all authenticated pages
// Requires session to be started and user to be logged in
$navUserType = $_SESSION['type'] ?? '';
$navUserId = $_SESSION['user_id'] ?? '';

if ($navUserType === 'client') {
    $dashLink = '../client/dashboard.php';
    $profileLink = '../client/profile.php';
} else {
    $dashLink = '../freelancer/dashboard.php';
    $profileLink = '../freelancer/profile.php';
}
?>

<nav class="navbar">
    <div class="nav-left">
        <a href="<?php echo $profileLink; ?>" class="nav-profile-link" title="My Profile">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
            </svg>
            <span>Profile</span>
        </a>
    </div>
    <div class="nav-center">
        <a href="<?php echo $dashLink; ?>" class="nav-brand">ProLink</a>
    </div>
    <div class="nav-right">
        <a href="../auth/logout.php" class="nav-logout">Logout</a>
    </div>
</nav>