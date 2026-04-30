<?php
// ============================================================
// form.php - Voter Details Form (after PIN setup)
// Saves name, department, voter_id, phone, and hashed PIN
// ============================================================
session_start();
require_once 'db.php';

// Must have gone through login PIN setup
if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['user_id'])) {
    header("Location: vote.php");
    exit();
}

$error = '';
$phone = $_SESSION['login_phone']  ?? '';
$pin_hash = $_SESSION['login_pin'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']);
    $department = trim($_POST['department']);
    $voter_id   = trim($_POST['voter_id']);

    if (empty($name) || empty($department) || empty($voter_id)) {
        $error = "All fields are required.";
    } elseif (strlen($name) < 3) {
        $error = "Please enter a valid full name (at least 3 characters).";
    } elseif (strlen($voter_id) < 4) {
        $error = "Voter ID must be at least 4 characters.";
    } elseif (empty($pin_hash)) {
        $error = "Session expired. Please login again.";
    } else {
        // Check voter_id not already used
        $chk = mysqli_prepare($conn, "SELECT id FROM users WHERE voter_id = ?");
        mysqli_stmt_bind_param($chk, "s", $voter_id);
        mysqli_stmt_execute($chk);
        if (mysqli_num_rows(mysqli_stmt_get_result($chk)) > 0) {
            $error = "This Voter ID is already registered. Please check your ID.";
        } else {
            // Insert new user WITH pin_hash
            $stmt = mysqli_prepare($conn,
                "INSERT INTO users (name, phone, voter_id, department, pin_hash) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssss", $name, $phone, $voter_id, $department, $pin_hash);

            if (mysqli_stmt_execute($stmt)) {
                $new_id = mysqli_insert_id($conn);
                $_SESSION['user_id']   = $new_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['has_voted'] = 0;
                unset($_SESSION['login_phone'], $_SESSION['login_pin'],
                      $_SESSION['otp_verified'], $_SESSION['login_is_new']);
                header("Location: vote.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Details – VoteNow 2025</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="card">
    <div style="text-align:center; margin-bottom:20px;">
        <div style="font-size:42px;">📋</div>
        <div class="card-title">Voter Registration</div>
        <div class="card-subtitle">Fill in your details to complete registration</div>
    </div>

    <div class="alert alert-success">
        ✅ PIN set! Phone: <strong><?php echo htmlspecialchars($phone); ?></strong>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="form.php">
        <div class="form-group">
            <label>👤 Full Name</label>
            <input type="text" name="name" placeholder="Enter your full name"
                value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label>🏛️ Department</label>
            <select name="department" required>
                <option value="">-- Select Department --</option>
                <?php
                $depts = ['Computer Science','Electronics Engineering','Mechanical Engineering',
                          'Civil Engineering','Information Technology','Electrical Engineering',
                          'Chemical Engineering','Other'];
                foreach ($depts as $d) {
                    $sel = (isset($_POST['department']) && $_POST['department'] === $d) ? 'selected' : '';
                    echo "<option value=\"$d\" $sel>$d</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>🪪 Voter ID / Enrollment No.</label>
            <input type="text" name="voter_id" placeholder="e.g., CS2021001"
                value="<?php echo isset($_POST['voter_id']) ? htmlspecialchars($_POST['voter_id']) : ''; ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Register & Proceed to Vote →</button>
    </form>
</div>

</body>
</html>
