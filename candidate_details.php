<?php
// ============================================================
// candidate_details.php - Full Candidate Detail Page
// Shows name, department, achievements, contributions, past wins
// ============================================================
session_start();
require_once 'db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get candidate ID from URL parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: candidates.php");
    exit();
}

// Fetch candidate from DB
$stmt = mysqli_prepare($conn, "SELECT * FROM candidates WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header("Location: candidates.php");
    exit();
}

$cand = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($cand['name']); ?> – VoteNow 2025</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="page-wrapper" style="max-width:760px;">
    <!-- Back Link -->
    <a href="candidates.php" class="back-link">← Back to All Candidates</a>

    <div class="detail-card">
        <!-- Header: Avatar + Name + Dept -->
        <div class="detail-header">
            <div class="detail-avatar">
                <?php echo strtoupper(substr($cand['name'], 0, 1)); ?>
            </div>
            <div>
                <div class="c-name"><?php echo htmlspecialchars($cand['name']); ?></div>
                <div class="c-dept">
                    🏛️ <?php echo htmlspecialchars($cand['department']); ?>
                </div>
                <div style="margin-top:8px;">
                    <span class="badge">MLC President Candidate</span>
                    <span class="badge">Election 2025</span>
                </div>
            </div>
        </div>

        <!-- Achievements -->
        <div class="detail-section">
            <h4>🏆 Achievements</h4>
            <p>
                <?php
                // Split achievements by comma and display as separate lines
                $achievements = explode(',', $cand['achievements']);
                foreach ($achievements as $a) {
                    echo '• ' . htmlspecialchars(trim($a)) . '<br>';
                }
                ?>
            </p>
        </div>

        <!-- Contributions to University -->
        <div class="detail-section">
            <h4>🤝 Contributions to University</h4>
            <p>
                <?php
                $contributions = explode(',', $cand['contributions']);
                foreach ($contributions as $c_item) {
                    echo '• ' . htmlspecialchars(trim($c_item)) . '<br>';
                }
                ?>
            </p>
        </div>

        <!-- Past Election Wins -->
        <div class="detail-section">
            <h4>🎖️ Past Election Wins / Roles</h4>
            <p>
                <?php
                $past_wins = explode(',', $cand['past_wins']);
                foreach ($past_wins as $w) {
                    echo '• ' . htmlspecialchars(trim($w)) . '<br>';
                }
                ?>
            </p>
        </div>

        <!-- Vote Button (only if not voted yet) -->
        <?php
        // Check if user has voted
        $check = mysqli_prepare($conn, "SELECT has_voted FROM users WHERE id = ?");
        mysqli_stmt_bind_param($check, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($check);
        $check_result = mysqli_stmt_get_result($check);
        $user_row = mysqli_fetch_assoc($check_result);
        $has_voted = $user_row['has_voted'];
        ?>

        <?php if (!$has_voted): ?>
            <div style="margin-top:24px; text-align:center;">
                <form method="POST" action="vote.php">
                    <input type="hidden" name="candidate_id" value="<?php echo $cand['id']; ?>">
                    <button type="submit" class="btn btn-vote"
                        onclick="return confirmVote('<?php echo addslashes($cand['name']); ?>')"
                        style="font-size:16px; padding:12px 32px;">
                        🗳️ Vote for <?php echo htmlspecialchars($cand['name']); ?>
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="voted-banner" style="margin-top:20px;">
                ✅ You have already voted. <a href="results.php" style="color:#137333;">View Results</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="footer">
    &copy; 2025 VoteNow – College Election System
</div>

<script src="script.js"></script>
</body>
</html>
