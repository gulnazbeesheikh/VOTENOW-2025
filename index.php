<?php
// ============================================================
// index.php - Landing Page
// Displays logo and redirects to login after 2.5 seconds
// ============================================================
session_start();

// If already logged in, go to vote page
if (isset($_SESSION['user_id'])) {
    header("Location: vote.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VoteNow 2025 – College Election</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="landing-page">
    <!-- Logo -->
    <div class="landing-logo">🗳️</div>

    <!-- Title -->
    <h1 class="landing-title">VoteNow 2025</h1>
    <p class="landing-subtitle">Online Voting System – College Election</p>
    <p class="landing-subtitle" style="color:#1a73e8; font-weight:bold;">MLC President Election</p>

    <!-- Redirect message with countdown -->
    <p class="redirect-msg">
        Redirecting to login in <strong><span id="countdown">3</span></strong> seconds...
    </p>
</div>

<script src="script.js"></script>
<script>
    // Auto-redirect to login after 3 seconds
    startRedirect('login.php', 3);
</script>

</body>
</html>
