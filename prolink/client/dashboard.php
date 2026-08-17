<?php
include('../includes/auth.php');
checkUser('client');
?>

<!DOCTYPE html>
<html>

<head>
    <title>Client Dashboard - ProLink</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="has-navbar">
    <?php include('../includes/navbar.php'); ?>

    <div class="container">
        <h1>Client Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Client'); ?>!</p>

        <div style="margin-top: 24px;">
            <a href="post_job.php" class="btn post">Post a Job</a>
            <a href="manage_jobs.php" class="btn btn-purple">Manage Jobs</a>
        </div>
    </div>

</body>

</html>