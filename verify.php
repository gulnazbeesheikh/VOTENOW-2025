<?php
// ============================================================
// verify.php - OTP Verification Page
// Compares entered OTP with the one stored in session
// ============================================================
session_start();

// Must have phone and OTP stored from login page
if (!isset($_SESSION['login_phone']) || !isset($_SESSION['otp_generated'])) {
    header("Location: login.php");
    exit();
}

// If already logged in, go to vote
if (isset($_SESSION['user_id'])) {
    header("Location: vote.php");
    exit();
}

$error = '';
$phone = $_SESSION['login_phone'];

// Handle OTP verification form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp_entered']);
    $stored_otp  = $_SESSION['otp_generated'];

    if (empty($entered_otp)) {
        $error = "Please enter the OTP.";
    } elseif ($entered_otp === $stored_otp) {
        // OTP matched! Mark as verified
        $_SESSION['otp_verified'] = true;
        // Redirect to user details form
        header("Location: form.php");
        exit();
    } else {
        $error = "Incorrect OTP. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP – VoteNow 2025</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="card">
    <!-- Logo and Title -->
    <div style="text-align:center; margin-bottom:20px;">
        <div style="font-size:42px;">🔐</div>
        <div class="card-title">Verify OTP</div>
        <div class="card-subtitle">
            Enter the OTP sent to <strong><?php echo htmlspecialchars($phone); ?></strong>
        </div>
    </div>

    <!-- Simulated OTP Reminder -->
    <div class="otp-display" style="margin-bottom:20px;">
        <p>🔐 Your Simulated OTP:</p>
        <div class="otp-code"><?php echo htmlspecialchars($_SESSION['otp_generated']); ?></div>
        <p style="font-size:12px; color:#999; margin-top:6px;">
            (Shown here for simulation – normally delivered by SMS)
        </p>
    </div>

    <!-- Error Message -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- OTP Entry Form -->
    <form method="POST" action="verify.php">
        <div class="form-group">
            <label for="otp_entered">Enter OTP</label>
            <input
                type="text"
                id="otp_entered"
                name="otp_entered"
                placeholder="Enter the 6-digit OTP"
                maxlength="6"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                style="font-size:22px; letter-spacing:8px; text-align:center;"
                required
                autofocus
            >
        </div>

        <button type="submit" class="btn btn-primary">Verify & Continue →</button>
    </form>

    <!-- Back to Login -->
    <p style="text-align:center; margin-top:16px; font-size:13px;">
        <a href="login.php" style="color:#1a73e8;">← Back to Login</a>
    </p>
</div>

<script src="script.js"></script>
</body>
</html>
