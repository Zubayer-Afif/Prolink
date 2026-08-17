<?php
include('../includes/auth.php');
checkUser('client');
include('../config/db.php');

$message = "";
$msgType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST['title'];
    $desc = $_POST['desc'];
    $budget = (!empty($_POST['budget'])) ? $_POST['budget'] : null;
    $skills = (!empty($_POST['skills'])) ? $_POST['skills'] : null;
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO jobs (user_id, title, description, budget, skills) VALUES (?, ?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("issds", $user_id, $title, $desc, $budget, $skills);

        if ($stmt->execute()) {
            $message = "Job posted successfully!";
            $msgType = "success";
        } else {
            $message = "Error posting job!";
            $msgType = "error";
        }
    } else {
        $message = "SQL Error!";
        $msgType = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Post Job - ProLink</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="has-navbar">
<?php include('../includes/navbar.php'); ?>

<div class="container">
    <h2>Post a Job</h2>

    <form method="POST">
        <input type="text" name="title" placeholder="Job Title" required>
        <textarea name="desc" placeholder="Job Description" required></textarea>
        <input type="number" name="budget" placeholder="Budget ($)" step="0.01" min="0">
        <input type="text" name="skills" placeholder="Required Skills (e.g. PHP, CSS, Design)">

        <button type="submit" class="btn post" style="width:100%; margin-top:12px;">Post Job</button>
    </form>

    <?php if (!empty($message)): ?>
        <div class="msg-<?php echo $msgType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
