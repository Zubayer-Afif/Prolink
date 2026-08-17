<?php
include('../includes/auth.php');
checkUser('freelancer');
?>

<!DOCTYPE html>
<html>

<head>
    <title>Freelancer Dashboard - ProLink</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="has-navbar">
    <?php include('../includes/navbar.php'); ?>

    <div class="container">
        <h1>Freelancer Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Freelancer'); ?>!</p>

        <div style="margin-top: 24px;">
            <a href="view_jobs.php" class="btn post">Browse Jobs</a>
            <a href="my_jobs.php" class="btn btn-purple">My Jobs</a>
        </div>
    </div>

</body>

</html>