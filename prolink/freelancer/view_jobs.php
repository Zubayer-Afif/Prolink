<?php
include('../includes/auth.php');
checkUser('freelancer');
include('../config/db.php');

$freelancer_id = $_SESSION['user_id'];

// Get freelancer skills via SQL
$skillsStmt = $conn->prepare("SELECT skill FROM freelancer_skills WHERE user_id=?");
$skillsStmt->bind_param("i", $freelancer_id);
$skillsStmt->execute();
$skillsResult = $skillsStmt->get_result();
$freelancerSkills = [];
while ($s = $skillsResult->fetch_assoc()) {
    $freelancerSkills[] = strtolower(trim($s['skill']));
}

// Handle search filter
$searchSkill = trim($_GET['skill_search'] ?? '');

if ($searchSkill !== '') {
    // SQL LIKE query to filter jobs by skill keyword
    $likeParam = '%' . $searchSkill . '%';
    $stmt = $conn->prepare("
        SELECT j.*, u.name AS client_name, u.email AS client_email 
        FROM jobs j 
        JOIN users u ON u.user_id = j.user_id 
        WHERE j.job_status = 'open' AND LOWER(j.skills) LIKE LOWER(?) 
        ORDER BY j.job_id DESC
    ");
    $stmt->bind_param("s", $likeParam);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("
        SELECT j.*, u.name AS client_name, u.email AS client_email 
        FROM jobs j 
        JOIN users u ON u.user_id = j.user_id 
        WHERE j.job_status = 'open' 
        ORDER BY j.job_id DESC
    ");
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Browse Jobs - ProLink</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="has-navbar">
    <?php include('../includes/navbar.php'); ?>
    <div class="container container-wide">
        <h2>Available Jobs</h2>

        <!-- Skill Search Bar -->
        <form method="GET" class="search-bar">
            <div class="search-input-wrapper">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8" />
                    <path d="M21 21l-4.35-4.35" />
                </svg>
                <input type="text" name="skill_search" placeholder="Search by skill (e.g. PHP, JavaScript, Design...)" value="<?php echo htmlspecialchars($searchSkill); ?>">
                <button type="submit" class="search-btn">Search</button>
            </div>
            <?php if ($searchSkill !== ''): ?>
                <div class="search-active">
                    <span>Filtering by: <strong><?php echo htmlspecialchars($searchSkill); ?></strong></span>
                    <a href="view_jobs.php" class="search-clear">✕ Clear</a>
                </div>
            <?php endif; ?>
        </form>

        <?php if ($result->num_rows === 0): ?>
            <p class="empty-state">
                <?php if ($searchSkill !== ''): ?>
                    No jobs found matching skill "<strong><?php echo htmlspecialchars($searchSkill); ?></strong>".
                <?php else: ?>
                    No open jobs available right now.
                <?php endif; ?>
            </p>
        <?php endif; ?>
        <?php while ($row = $result->fetch_assoc()):
            // SQL-based skill matching: check if any freelancer skill exists in this job's skills
            $jobSkills = $row['skills'] ?? '';
            $matchStmt = $conn->prepare("
                SELECT COUNT(*) AS match_count 
                FROM freelancer_skills 
                WHERE user_id = ? 
                AND LOWER(?) LIKE CONCAT('%', LOWER(skill), '%')
            ");
            $matchStmt->bind_param("is", $freelancer_id, $jobSkills);
            $matchStmt->execute();
            $matchCount = $matchStmt->get_result()->fetch_assoc()['match_count'];

            $hasMatchingSkill = (trim($jobSkills) === '') || ($matchCount > 0);
        ?>
            <div class="card">
                <div class="card-footer" style="margin-top:0; margin-bottom:8px;">
                    <h3 style="margin:0;"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <span class="badge badge-open">Open</span>
                </div>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
                <p style="font-size:12px; color:#6b7280; margin-top:8px;">
                    Posted by: <?php echo htmlspecialchars($row['client_name'] ?? $row['client_email']); ?>
                    <?php if (!empty($row['budget'])): ?>
                        • Budget: <strong class="bid-amount">$<?php echo number_format($row['budget'], 2); ?></strong>
                    <?php endif; ?>
                </p>
                <?php if (!empty($row['skills'])): ?>
                    <div style="margin-top:8px;">
                        <?php
                        $displaySkills = array_map('trim', explode(',', $row['skills']));
                        foreach ($displaySkills as $dSkill):
                            if (trim($dSkill) === '') continue;
                            $isMatch = in_array(strtolower(trim($dSkill)), $freelancerSkills);
                        ?>
                            <span class="skill-tag <?php echo $isMatch ? 'skill-match' : ''; ?>"><?php echo htmlspecialchars(trim($dSkill)); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="card-footer">
                    <?php if ($hasMatchingSkill): ?>
                        <a href="apply_job.php?job_id=<?php echo $row['job_id']; ?>" class="btn btn-sm post">Apply</a>
                    <?php else: ?>
                        <span class="skills-mismatch">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            Skills don't match
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>

</html>