<?php
// ============================================================
// navbar.php - Reusable Navigation Bar (included in all pages)
// ============================================================

// Determine active page for highlighting nav links
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <!-- Left: Logo + Site Name -->
    <div class="navbar-brand">
        <div class="logo-icon">🗳️</div>
        <span>VoteNow 2025</span>
    </div>

    <!-- Right: Navigation Links -->
    <ul class="navbar-links">
        <li>
            <a href="vote.php" class="<?php echo ($current_page == 'vote.php') ? 'active' : ''; ?>">
                🏠 Home
            </a>
        </li>
        <li>
            <a href="results.php" class="<?php echo ($current_page == 'results.php') ? 'active' : ''; ?>">
                📊 Results
            </a>
        </li>
        <li>
            <a href="candidates.php" class="<?php echo ($current_page == 'candidates.php') ? 'active' : ''; ?>">
                👤 Candidates
            </a>
        </li>
    </ul>
</nav>
