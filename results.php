<?php
// ============================================================
// results.php - Election Results Page
// Shows vote counts in table + bar chart (Chart.js)
// ============================================================
session_start();
require_once 'db.php';

// Must be logged in to view results
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user just voted (success message)
$just_voted = isset($_GET['voted']) && $_GET['voted'] == '1';

// Fetch candidates sorted by votes (descending)
$query = mysqli_query($conn, "SELECT * FROM candidates ORDER BY votes DESC");
$candidates = [];
while ($row = mysqli_fetch_assoc($query)) {
    $candidates[] = $row;
}

// Calculate total votes
$total_votes = 0;
foreach ($candidates as $c) {
    $total_votes += $c['votes'];
}

// Get max votes for bar width calculation
$max_votes = count($candidates) > 0 ? $candidates[0]['votes'] : 1;
if ($max_votes == 0) $max_votes = 1; // prevent division by zero

// Prepare data for Chart.js (JSON arrays)
$chart_labels = [];
$chart_data   = [];
foreach ($candidates as $c) {
    $chart_labels[] = $c['name'];
    $chart_data[]   = (int)$c['votes'];
}
$chart_labels_json = json_encode($chart_labels);
$chart_data_json   = json_encode($chart_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results – VoteNow 2025</title>
    <link rel="stylesheet" href="style.css">
    <!-- Chart.js from CDN (free, no API key needed) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="page-wrapper">
    <!-- Page Title -->
    <div class="page-title">📊 Election Results</div>
    <div class="page-subtitle">Election 2025 – MLC President | Live Vote Count</div>

    <!-- Just Voted Banner -->
    <?php if ($just_voted): ?>
        <div class="alert alert-success" style="margin-bottom:20px;">
            🎉 Thank you! Your vote has been recorded successfully.
        </div>
    <?php endif; ?>

    <!-- Total Votes Summary -->
    <div class="alert alert-info" style="margin-bottom:24px; font-size:15px;">
        📋 Total Votes Cast: <strong><?php echo $total_votes; ?></strong>
        &nbsp;|&nbsp;
        Total Candidates: <strong><?php echo count($candidates); ?></strong>
    </div>

    <!-- Results Table -->
    <table class="results-table">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Candidate Name</th>
                <th>Department</th>
                <th>Votes</th>
                <th>Percentage</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($candidates)): ?>
                <tr><td colspan="6" style="text-align:center; color:#aaa;">No data available.</td></tr>
            <?php else: ?>
                <?php foreach ($candidates as $index => $cand): ?>
                    <tr class="<?php echo ($index === 0 && $cand['votes'] > 0) ? 'rank-1' : ''; ?>">
                        <!-- Rank with medal for top 3 -->
                        <td>
                            <?php
                                $medals = ['🥇', '🥈', '🥉'];
                                echo isset($medals[$index]) ? $medals[$index] : ($index + 1);
                            ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($cand['name']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($cand['department']); ?></td>
                        <td><strong><?php echo $cand['votes']; ?></strong></td>
                        <td>
                            <?php
                                $pct = $total_votes > 0 ? round(($cand['votes'] / $total_votes) * 100, 1) : 0;
                                echo $pct . '%';
                            ?>
                        </td>
                        <td style="min-width:150px;">
                            <!-- Animated bar -->
                            <div class="vote-bar-container"
                                title="<?php echo $cand['votes']; ?> votes (<?php echo $pct; ?>%)">
                                <div class="vote-bar"
                                    style="width:0%"
                                    data-width="<?php echo $total_votes > 0 ? round(($cand['votes'] / $max_votes) * 100) : 0; ?>">
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Bar Chart Section -->
    <div class="chart-section">
        <h3>📈 Votes Bar Chart</h3>
        <?php if ($total_votes === 0): ?>
            <p style="color:#aaa; text-align:center; padding:40px 0;">
                No votes have been cast yet. Be the first to vote!
            </p>
        <?php else: ?>
            <canvas id="resultsChart" style="max-height:380px;"></canvas>
        <?php endif; ?>
    </div>

    <!-- Back to Vote -->
    <div style="text-align:center; margin-top:24px;">
        <a href="vote.php" class="btn btn-primary" style="display:inline-block; width:auto; padding:10px 28px;">
            🗳️ Go to Voting Page
        </a>
    </div>
</div>

<div class="footer">
    &copy; 2025 VoteNow – College Election System
</div>

<script src="script.js"></script>
<?php if ($total_votes > 0): ?>
<script>
    // Draw chart with data from PHP
    drawResultsChart(
        <?php echo $chart_labels_json; ?>,
        <?php echo $chart_data_json; ?>,
        <?php echo $total_votes; ?>
    );
</script>
<?php endif; ?>
</body>
</html>
