<?php
include('../includes/auth.php');
checkUser('freelancer');
include('../config/db.php');

$job_id = intval($_GET['job_id'] ?? 0);
$message = "";
$msgType = "";

if ($job_id === 0) {
    header("Location: view_jobs.php");
    exit();
}

$jobStmt = $conn->prepare("SELECT * FROM jobs WHERE job_id=? AND job_status='open'");
$jobStmt->bind_param("i", $job_id);
$jobStmt->execute();
$jobResult = $jobStmt->get_result();

if ($jobResult->num_rows === 0) {
    header("Location: view_jobs.php");
    exit();
}
$job = $jobResult->fetch_assoc();

$freelancer_id = $_SESSION['user_id'];

// SQL-based skill matching: count how many freelancer skills appear in the job's skills string
$matchStmt = $conn->prepare("
    SELECT COUNT(*) AS match_count 
    FROM freelancer_skills 
    WHERE user_id = ? 
    AND LOWER(?) LIKE CONCAT('%', LOWER(skill), '%')
");
$jobSkills = $job['skills'] ?? '';
$matchStmt->bind_param("is", $freelancer_id, $jobSkills);
$matchStmt->execute();
$matchCount = $matchStmt->get_result()->fetch_assoc()['match_count'];

// If job has no skills listed, anyone can apply. Otherwise, need at least 1 match.
$hasMatchingSkill = (trim($jobSkills) === '') || ($matchCount > 0);

// Get freelancer's skills list via SQL for displaying skill tags
$skillsStmt = $conn->prepare("SELECT skill FROM freelancer_skills WHERE user_id=?");
$skillsStmt->bind_param("i", $freelancer_id);
$skillsStmt->execute();
$skillsResult = $skillsStmt->get_result();
$freelancerSkills = [];
while ($s = $skillsResult->fetch_assoc()) {
    $freelancerSkills[] = strtolower(trim($s['skill']));
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $hasMatchingSkill) {
    $bid = $_POST['bid'];
    $cover = $_POST['cover'];

    $check = $conn->prepare("SELECT proposal_id FROM proposals WHERE job_id=? AND freelancer_id=?");
    $check->bind_param("ii", $job_id, $freelancer_id);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        $message = "You have already applied for this job.";
        $msgType = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO proposals (job_id, freelancer_id, bid_amount, cover_letter) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iids", $job_id, $freelancer_id, $bid, $cover);

        if ($stmt->execute()) {
            $message = "Proposal submitted successfully!";
            $msgType = "success";
        } else {
            $message = "Error submitting proposal.";
            $msgType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Apply for Job - ProLink</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="has-navbar">
    <?php include('../includes/navbar.php'); ?>
    <div class="container">
        <h2>Apply for Job</h2>
        <div class="card" style="margin-bottom:20px;">
            <h3><?php echo htmlspecialchars($job['title']); ?></h3>
            <p><?php echo htmlspecialchars($job['description']); ?></p>
            <?php if (!empty($job['budget'])): ?>
                <p style="font-size:13px; color:#9ca3af; margin-top:8px;">Budget: <strong
                        class="bid-amount">$<?php echo number_format($job['budget'], 2); ?></strong></p>
            <?php endif; ?>
            <?php if (!empty($job['skills'])): ?>
                <div style="margin-top:10px;">
                    <span style="font-size:12px; color:#9ca3af;">Required Skills:</span>
                    <div style="margin-top:4px;">
                        <?php
                        $displaySkills = array_map('trim', explode(',', $job['skills']));
                        foreach ($displaySkills as $dSkill):
                            if (trim($dSkill) === '') continue;
                            // Check each skill tag against freelancer skills using in_array
                            $isMatch = in_array(strtolower(trim($dSkill)), $freelancerSkills);
                        ?>
                            <span class="skill-tag <?php echo $isMatch ? 'skill-match' : 'skill-no-match'; ?>"><?php echo htmlspecialchars(trim($dSkill)); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($message)): ?>
            <div class="msg-<?php echo $msgType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (!$hasMatchingSkill): ?>
            <!-- Skills Mismatch Block -->
            <div class="skills-mismatch-box">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="15" y1="9" x2="9" y2="15" />
                    <line x1="9" y1="9" x2="15" y2="15" />
                </svg>
                <h3 style="color:#f87171; margin:8px 0 4px;">Skills Don't Match</h3>
                <p style="font-size:14px; color:#9ca3af;">Your skills don't match the requirements for this job. Update your profile skills to apply for relevant jobs.</p>
                <div style="margin-top:16px; display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">
                    <a href="profile.php" class="btn btn-sm btn-purple">Update Skills</a>
                    <a href="view_jobs.php" class="btn btn-sm btn-outline">← Browse Jobs</a>
                </div>
            </div>
        <?php elseif ($msgType !== 'success'): ?>
            <form method="POST">
                <input type="number" name="bid" placeholder="Your Bid Amount ($)" required min="1" step="0.01">
                <textarea name="cover" placeholder="Cover Letter — tell the client why you're the right fit..."
                    required></textarea>
                <button class="btn post" style="width:100%; margin-top:12px;">Submit Proposal</button>
            </form>
        <?php else: ?>
            <a href="view_jobs.php" class="btn btn-outline" style="margin-top:16px;">← Browse More Jobs</a>
        <?php endif; ?>
    </div>
</body>

</html>