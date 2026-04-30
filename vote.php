<?php
// ============================================================
// vote.php - Voting Page
// Displays candidates grouped by department, allows one vote
// ============================================================
session_start();
require_once 'db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Voter';
$error     = '';
$success   = '';

// Check if user has already voted (double-check from DB)
$check = mysqli_prepare($conn, "SELECT has_voted FROM users WHERE id = ?");
mysqli_stmt_bind_param($check, "i", $user_id);
mysqli_stmt_execute($check);
$check_result = mysqli_stmt_get_result($check);
$user_row = mysqli_fetch_assoc($check_result);
$has_voted = $user_row['has_voted'];

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidate_id'])) {
    if ($has_voted) {
        $error = "You have already voted! Multiple votes are not allowed.";
    } else {
        $candidate_id = (int)$_POST['candidate_id'];

        // Verify candidate exists
        $cand_check = mysqli_prepare($conn, "SELECT id FROM candidates WHERE id = ?");
        mysqli_stmt_bind_param($cand_check, "i", $candidate_id);
        mysqli_stmt_execute($cand_check);
        $cand_result = mysqli_stmt_get_result($cand_check);

        if (mysqli_num_rows($cand_result) === 0) {
            $error = "Invalid candidate selected.";
        } else {
            // Begin transaction for safe voting
            mysqli_begin_transaction($conn);
            try {
                // Insert vote record
                $ins = mysqli_prepare($conn, "INSERT INTO votes (user_id, candidate_id) VALUES (?, ?)");
                mysqli_stmt_bind_param($ins, "ii", $user_id, $candidate_id);
                mysqli_stmt_execute($ins);

                // Increment candidate vote count
                $upd = mysqli_prepare($conn, "UPDATE candidates SET votes = votes + 1 WHERE id = ?");
                mysqli_stmt_bind_param($upd, "i", $candidate_id);
                mysqli_stmt_execute($upd);

                // Mark user as voted
                $mark = mysqli_prepare($conn, "UPDATE users SET has_voted = 1 WHERE id = ?");
                mysqli_stmt_bind_param($mark, "i", $user_id);
                mysqli_stmt_execute($mark);

                mysqli_commit($conn);

                // Update session
                $_SESSION['has_voted'] = 1;

                // Redirect to results
                header("Location: results.php?voted=1");
                exit();
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = "Voting failed. Please try again.";
            }
        }
    }
}

// Fetch all candidates from DB, grouped by department
$cands_query = mysqli_query($conn, "SELECT * FROM candidates ORDER BY department, name");
$candidates_by_dept = [];
while ($row = mysqli_fetch_assoc($cands_query)) {
    $candidates_by_dept[$row['department']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote – VoteNow 2025</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="page-wrapper">
    <!-- Page Header -->
    <div class="voting-header">
        <h1>🗳️ We Value Your Vote</h1>
        <h2>Election 2025 – MLC President</h2>
    </div>

    <!-- Welcome Bar -->
    <div class="alert alert-info" style="margin-bottom:20px;">
        👋 Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong>!
        <?php if ($has_voted): ?>
            You have already cast your vote.
        <?php else: ?>
            Please select a candidate and vote.
        <?php endif; ?>
        &nbsp;|&nbsp;
        <a href="logout.php" style="color:#c5221f;">Logout</a>
    </div>

    <!-- Already Voted Banner -->
    <?php if ($has_voted): ?>
        <div class="voted-banner">
            ✅ You have successfully cast your vote. Thank you for participating!
            <br><a href="results.php" style="color:#137333;">View Live Results →</a>
        </div>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Candidates Grouped by Department -->
    <?php if (!$has_voted): ?>
        <?php if (empty($candidates_by_dept)): ?>
            <div class="alert alert-info">No candidates found in the database.</div>
        <?php else: ?>
            <?php foreach ($candidates_by_dept as $dept => $cands): ?>
                <div class="dept-section">
                    <h3>🏛️ <?php echo htmlspecialchars($dept); ?></h3>
                    <div class="candidates-grid">
                        <?php foreach ($cands as $cand): ?>
                            <div class="candidate-card">
                                <!-- Avatar: first letter of name -->
                                <div class="candidate-avatar">
                                    <?php echo strtoupper(substr($cand['name'], 0, 1)); ?>
                                </div>

                                <!-- Name + Department -->
                                <div class="candidate-info">
                                    <div class="c-name"><?php echo htmlspecialchars($cand['name']); ?></div>
                                    <div class="c-dept"><?php echo htmlspecialchars($cand['department']); ?></div>
                                </div>

                                <!-- Vote Button -->
                                <form method="POST" action="vote.php" style="margin:0;">
                                    <input type="hidden" name="candidate_id" value="<?php echo $cand['id']; ?>">
                                    <button type="submit" class="btn btn-vote"
                                        onclick="return confirmVote('<?php echo addslashes($cand['name']); ?>')">
                                        Vote
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>

    <!-- View Results Link -->
    <div style="text-align:center; margin-top:30px;">
        <a href="results.php" class="btn btn-primary" style="display:inline-block; width:auto; padding:10px 30px;">
            📊 View Live Results
        </a>
    </div>
</div>

<div class="footer">
    &copy; 2025 VoteNow – College Election System | Built for Academic Purpose
</div>

<script src="script.js"></script>
</body>
</html>
