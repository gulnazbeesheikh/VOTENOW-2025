<?php
// ============================================================
// candidates.php - Candidates List Page
// Shows all candidates in card format with View Details button
// ============================================================
session_start();
require_once 'db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all candidates
$query = mysqli_query($conn, "SELECT * FROM candidates ORDER BY department, name");
$candidates = [];
while ($row = mysqli_fetch_assoc($query)) {
    $candidates[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidates – VoteNow 2025</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="page-wrapper">
    <div class="page-title">👤 Candidates</div>
    <div class="page-subtitle">Election 2025 – MLC President | Meet the Candidates</div>

    <!-- Candidates Grid -->
    <?php if (empty($candidates)): ?>
        <div class="alert alert-info">No candidates found.</div>
    <?php else: ?>
        <div class="candidates-list-grid">
            <?php foreach ($candidates as $cand): ?>
                <div class="cand-list-card">
                    <!-- Avatar -->
                    <div class="cand-list-avatar">
                        <?php echo strtoupper(substr($cand['name'], 0, 1)); ?>
                    </div>

                    <!-- Info -->
                    <div class="c-name"><?php echo htmlspecialchars($cand['name']); ?></div>
                    <div class="c-dept"><?php echo htmlspecialchars($cand['department']); ?></div>

                    <!-- View Details Button -->
                    <a href="candidate_details.php?id=<?php echo $cand['id']; ?>" class="btn btn-details">
                        View Details
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="footer">
    &copy; 2025 VoteNow – College Election System
</div>

<script src="script.js"></script>
</body>
</html>
