<?php
// ============================================================
// login.php - Login with Phone Number + 4-Digit PIN
// New users set a PIN. Returning users enter their PIN.
// 100% offline. No OTP. No external API.
// ============================================================
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: vote.php");
    exit();
}

$error  = '';
$step   = 'phone';
$is_new = false;

// ---- STEP 1: Phone submitted ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['step'] ?? '') === 'phone') {
    $phone = trim($_POST['phone']);

    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Please enter a valid 10-digit phone number.";
    } else {
        $_SESSION['login_phone'] = $phone;

        // Check if phone exists in DB
        $stmt = mysqli_prepare($conn, "SELECT id, pin_hash FROM users WHERE phone = ?");
        mysqli_stmt_bind_param($stmt, "s", $phone);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            $_SESSION['login_user_id'] = $row['id'];
            $_SESSION['login_is_new']  = empty($row['pin_hash']) ? 1 : 0;
        } else {
            $_SESSION['login_is_new'] = 1; // Brand new voter
        }
        $step   = 'pin';
        $is_new = (bool)$_SESSION['login_is_new'];
    }
}

// ---- STEP 2: PIN submitted ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['step'] ?? '') === 'pin') {
    $phone  = $_SESSION['login_phone']  ?? '';
    $is_new = (bool)($_SESSION['login_is_new'] ?? 1);
    $pin    = trim($_POST['pin']);
    $step   = 'pin'; // stay on pin step if error

    if (!preg_match('/^[0-9]{4}$/', $pin)) {
        $error = "PIN must be exactly 4 digits.";
    } elseif ($is_new) {
        // New voter — save hashed PIN in session, go to details form
        $_SESSION['login_pin']    = password_hash($pin, PASSWORD_DEFAULT);
        $_SESSION['otp_verified'] = true;
        header("Location: form.php");
        exit();
    } else {
        // Returning voter — verify PIN against DB
        $uid  = $_SESSION['login_user_id'];
        $stmt = mysqli_prepare($conn, "SELECT id, pin_hash, has_voted, name FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $uid);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);

        if ($row && password_verify($pin, $row['pin_hash'])) {
            $_SESSION['user_id']   = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['has_voted'] = $row['has_voted'];
            unset($_SESSION['login_phone'], $_SESSION['login_user_id'], $_SESSION['login_is_new']);
            header("Location: vote.php");
            exit();
        } else {
            $error = "Incorrect PIN. Please try again.";
        }
    }
}

// Restore state for re-render after POST errors
if ($step === 'pin') {
    $is_new        = (bool)($_SESSION['login_is_new'] ?? 1);
    $phone_display = htmlspecialchars($_SESSION['login_phone'] ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – VoteNow 2025</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="card">
    <div style="text-align:center; margin-bottom:22px;">
        <div style="font-size:44px;">🗳️</div>
        <div class="card-title">VoteNow 2025</div>
        <div class="card-subtitle">College Election 2025 – MLC President</div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- STEP 1: Phone Number -->
    <?php if ($step === 'phone'): ?>
        <div class="alert alert-info">📱 Enter your mobile number to get started.</div>
        <form method="POST" action="login.php" style="margin-top:18px;">
            <input type="hidden" name="step" value="phone">
            <div class="form-group">
                <label>Mobile Number</label>
                <input type="text" name="phone"
                    placeholder="Enter 10-digit number"
                    maxlength="10"
                    oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                    autofocus required>
            </div>
            <button type="submit" class="btn btn-primary">Continue →</button>
        </form>

    <!-- STEP 2: PIN -->
    <?php elseif ($step === 'pin'): ?>
        <?php if ($is_new): ?>
            <div class="alert alert-success">
                ✅ New voter: <strong><?php echo $phone_display; ?></strong><br>
                Create a 4-digit PIN to secure your account. You'll use it every login.
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                👋 Welcome back! Enter your PIN for <strong><?php echo $phone_display; ?></strong>.
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" style="margin-top:18px;">
            <input type="hidden" name="step" value="pin">
            <div class="form-group">
                <label><?php echo $is_new ? '🔒 Create Your 4-Digit PIN' : '🔑 Enter Your PIN'; ?></label>
                <input type="password" name="pin"
                    placeholder="_ _ _ _"
                    maxlength="4"
                    oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                    style="font-size:28px; letter-spacing:12px; text-align:center;"
                    autofocus required>
                <small style="color:#999; margin-top:6px; display:block;">
                    <?php echo $is_new
                        ? 'Pick any 4 numbers. Remember this PIN — you will need it to login next time.'
                        : 'Forgot your PIN? Contact the election administrator.'; ?>
                </small>
            </div>
            <button type="submit" class="btn btn-primary">
                <?php echo $is_new ? 'Set PIN & Register →' : 'Login →'; ?>
            </button>
        </form>

        <p style="text-align:center; margin-top:14px; font-size:13px;">
            <a href="login.php" style="color:#1a73e8;">← Use a different number</a>
        </p>
    <?php endif; ?>

    <p style="text-align:center; margin-top:22px; font-size:12px; color:#ccc;">
        100% offline · No SMS · No internet required
    </p>
</div>

</body>
</html>
