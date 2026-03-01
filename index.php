<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

/* -----------------------------
   1️⃣ Tip of the Day
------------------------------ */
$tips = [
    "Always preheat your oven before baking.",
    "Use room temperature butter for smoother batter.",
    "Measure flour by spooning into the cup and leveling it.",
    "Let cakes cool fully before frosting.",
    "Don’t overmix your cake batter – it makes cakes dense.",
    "Use parchment paper to prevent sticking.",
];
$tip = $tips[array_rand($tips)];

/* -----------------------------
   2️⃣ Fetch Top 3 Ranked Recipes
------------------------------ */
$topThree = [];
$sqlTop3 = "
    SELECT rs.recipe_name, rs.image_path, rs.rank, u.name AS user_name
    FROM recipe_submissions rs
    JOIN users u ON u.id = rs.user_id
    WHERE rs.rank IS NOT NULL
    ORDER BY rs.rank ASC, rs.created_at ASC
    LIMIT 3
";
$resTop3 = $conn->query($sqlTop3);
if ($resTop3) {
    while ($row = $resTop3->fetch_assoc()) {
        $topThree[] = $row;
    }
}

// Winner is rank 1 (first in topThree)
$winner = count($topThree) > 0 ? $topThree[0] : null;

/* -----------------------------
   3️⃣ Count Total Submissions
------------------------------ */
$sqlCount = "SELECT COUNT(*) AS total FROM recipe_submissions";
$resCount = $conn->query($sqlCount);
$totalSubmissions = 0;
if ($resCount) {
    $rowCount = $resCount->fetch_assoc();
    $totalSubmissions = (int)$rowCount['total'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Smart Baking - Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right,#ffe6f0,#fff5e6);
            margin:0;
            padding:0;
        }

        .page-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px 15px 40px 15px;
        }

        h1 {
            text-align:center;
            color:#cc0066;
            margin-bottom:5px;
        }

        .subtitle {
            text-align:center;
            color:#555;
            font-size:14px;
            margin-bottom:15px;
        }

        /* Winner banner with animation */
        .winner-banner {
            background: linear-gradient(135deg, #ffccf2, #ffe9b8);
            border-radius:16px;
            padding:18px 18px 16px 18px;
            display:flex;
            align-items:center;
            gap:16px;
            box-shadow:0 8px 16px rgba(0,0,0,0.18);
            margin-bottom:20px;
            animation: floatBanner 3s ease-in-out infinite;
        }
        @keyframes floatBanner {
            0%   { transform: translateY(0);   box-shadow:0 8px 16px rgba(0,0,0,0.18); }
            50%  { transform: translateY(-5px); box-shadow:0 14px 24px rgba(0,0,0,0.24); }
            100% { transform: translateY(0);   box-shadow:0 8px 16px rgba(0,0,0,0.18); }
        }

        .winner-icon {
            font-size:42px;
        }

        .winner-text {
            flex:1;
        }

        .winner-title {
            margin:0;
            color:#b2004a;
            font-size:20px;
            font-weight:bold;
        }

        .winner-subline {
            margin:4px 0 0 0;
            font-size:14px;
            color:#444;
        }

        .winner-meta {
            margin-top:6px;
            font-size:13px;
            color:#555;
        }

        .winner-img-wrapper {
            width:120px;
            height:120px;
            border-radius:14px;
            overflow:hidden;
            background:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .winner-img-wrapper img {
            max-width:100%;
            max-height:100%;
            object-fit:cover;
        }

        /* Layout rows */
        .row {
            display:flex;
            flex-wrap:wrap;
            gap:18px;
            margin-bottom:18px;
        }

        .col-half {
            flex: 1 1 48%;
            min-width:280px;
        }

        .card {
            background:#ffffffcc;
            border-radius:12px;
            padding:15px 18px;
            box-shadow:0 2px 8px rgba(0,0,0,0.08);
        }

        .card h2 {
            margin-top:0;
            margin-bottom:8px;
            font-size:18px;
            color:#cc0066;
        }

        .tip-text {
            font-size:14px;
            color:#333;
        }

        .stats-text {
            font-size:14px;
            color:#444;
        }

        /* Top 3 section */
        .top3-grid {
            display:grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap:12px;
        }
        .top3-card {
            background:#fff;
            border-radius:10px;
            padding:10px;
            box-shadow:0 1px 5px rgba(0,0,0,0.08);
            text-align:left;
            display:flex;
            gap:10px;
        }
        .top3-rank-badge {
            min-width:32px;
            height:32px;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:14px;
            font-weight:bold;
            color:#fff;
        }
        .rank-1 { background:#f5b300; } /* gold */
        .rank-2 { background:#a0a0a0; } /* silver */
        .rank-3 { background:#b96d3b; } /* bronze */

        .top3-info {
            flex:1;
        }
        .top3-name {
            font-size:14px;
            font-weight:bold;
            margin:0 0 2px 0;
            color:#333;
        }
        .top3-baker {
            font-size:12px;
            color:#666;
            margin:0 0 4px 0;
        }
        .top3-thumb {
            width:60px;
            height:60px;
            border-radius:8px;
            overflow:hidden;
            background:#f9f9f9;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .top3-thumb img {
            max-width:100%;
            max-height:100%;
            object-fit:cover;
        }

        /* How it works list */
        .steps-list {
            list-style:none;
            padding-left:0;
            margin:4px 0 0 0;
            font-size:13px;
            color:#444;
        }
        .steps-list li {
            margin-bottom:6px;
        }

        /* Buttons */
        .btn-row {
            display:flex;
            justify-content:center;
            gap:15px;
            flex-wrap:wrap;
            margin-top:18px;
        }

        .btn {
            text-decoration:none;
            background:#cc0066;
            color:#fff;
            padding:10px 18px;
            border-radius:20px;
            font-size:14px;
            font-weight:bold;
        }

        .btn.secondary {
            background:#fff;
            color:#cc0066;
            border:1px solid #cc0066;
        }

        .btn:hover {
            opacity:0.9;
        }

        @media (max-width: 700px) {
            .winner-banner {
                flex-direction:column;
                align-items:flex-start;
            }
            .winner-img-wrapper {
                align-self:center;
            }
        }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="page-wrapper">

    <h1>Welcome to Smart Baking</h1>
    <p class="subtitle">
        Submit your best recipe photo, let the admin rank it, and climb the Smart Baking leaderboard!
    </p>

    <!-- 🏆 Animated Winner Banner -->
    <div class="winner-banner">
        <div class="winner-icon">👑</div>
        <div class="winner-text">
            <p class="winner-title">Current Champion</p>
            <?php if ($winner): ?>
                <p class="winner-subline">
                    <strong><?php echo htmlspecialchars($winner['recipe_name']); ?></strong>
                    by <?php echo htmlspecialchars($winner['user_name']); ?>
                </p>
                <p class="winner-meta">
                    Rank: #<?php echo (int)$winner['rank']; ?> – Think you can beat this?
                </p>
            <?php else: ?>
                <p class="winner-subline">
                    No winner yet – your recipe could be the first champion!
                </p>
            <?php endif; ?>
        </div>
        <div class="winner-img-wrapper">
            <?php if ($winner && !empty($winner['image_path'])): ?>
                <img src="<?php echo htmlspecialchars($winner['image_path']); ?>" alt="Winner Image">
            <?php else: ?>
                <span style="font-size:12px;color:#777;padding:6px;text-align:center;">No image yet</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Row: Tip + Stats/How it works -->
    <div class="row">
        <!-- Tip of the day -->
        <div class="col-half">
            <div class="card">
                <h2>🍰 Tip of the Day</h2>
                <p class="tip-text">
                    <?php echo htmlspecialchars($tip); ?>
                </p>
            </div>
        </div>

        <!-- Stats + How it works -->
        <div class="col-half">
            <div class="card">
                <h2>📊 Contest Snapshot</h2>
                <p class="stats-text">
                    Total contest submissions so far: <strong><?php echo $totalSubmissions; ?></strong>
                </p>
                <h2 style="margin-top:12px;">⚙️ How Smart Baking Works</h2>
                <ul class="steps-list">
                    <li><strong>Step 1:</strong> Create an account and log in.</li>
                    <li><strong>Step 2:</strong> Go to <em>Submit Contest Recipe</em> and upload your recipe name + JPG image.</li>
                    <li><strong>Step 3:</strong> Admin reviews submissions and assigns ranks.</li>
                    <li><strong>Step 4:</strong> Check your rank on the <em>Leaderboard</em>!</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Top 3 section -->
    <div class="card">
        <h2>🏅 Top 3 Recipes</h2>
        <?php if (count($topThree) === 0): ?>
            <p style="font-size:13px;color:#666;">
                No ranked recipes yet. Once admin assigns ranks, the top 3 will appear here.
            </p>
        <?php else: ?>
            <div class="top3-grid">
                <?php foreach ($topThree as $t): ?>
                    <?php
                        $rank = (int)$t['rank'];
                        $badgeClass = "rank-1";
                        if ($rank == 2) $badgeClass = "rank-2";
                        if ($rank == 3) $badgeClass = "rank-3";
                    ?>
                    <div class="top3-card">
                        <div class="top3-rank-badge <?php echo $badgeClass; ?>">
                            #<?php echo $rank; ?>
                        </div>
                        <div class="top3-info">
                            <p class="top3-name">
                                <?php echo htmlspecialchars($t['recipe_name']); ?>
                            </p>
                            <p class="top3-baker">
                                by <?php echo htmlspecialchars($t['user_name']); ?>
                            </p>
                        </div>
                        <div class="top3-thumb">
                            <?php if (!empty($t['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($t['image_path']); ?>" alt="Recipe Image">
                            <?php else: ?>
                                <span style="font-size:11px;color:#777;">No image</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Action buttons -->
    <div class="btn-row">
        <a class="btn" href="submit_recipe.php">Submit Recipe</a>
        <a class="btn secondary" href="leaderboard.php">View Leaderboard</a>
        <a class="btn secondary" href="recipes.php">Browse Master Recipes</a>
    </div>

</div>
<!-- ================= FOOTER ================= -->
<footer class="sb-footer">
    <div class="footer-inner">
        
        <div class="footer-col">
            <h3>Smart Baking</h3>
            <p>
                A web-based recipe contest system where users submit
                recipes and admin assigns ranks manually.
            </p>
        </div>

        <div class="footer-col">
            <h3>Project Details</h3>
            <p><strong>Student Name:</strong> Fatema Dohadwala</p>
            <p><strong>Roll No:</strong> 03</p>
            <p><strong>Course:</strong> B.Sc. Computer Science</p>
            <p><strong>Subject:</strong> Web Technology</p>
        </div>

        <div class="footer-col">
            <h3>College</h3>
            <p>Burhani College</p>
            <p>University of Mumbai</p>
            <p>Academic Year: 2025–2026</p>
        </div>

    </div>

    <div class="footer-bottom">
        © <?php echo date("Y"); ?> Smart Baking Project | Developed for Academic Purpose
    </div>
</footer>

<style>
.sb-footer {
    background:#cc0066;
    color:#fff;
    margin-top:40px;
    padding-top:25px;
}

.footer-inner {
    max-width:1100px;
    margin:0 auto;
    padding:0 15px 20px 15px;
    display:flex;
    flex-wrap:wrap;
    gap:30px;
    justify-content:space-between;
}

.footer-col {
    flex:1 1 250px;
    font-size:13px;
}

.footer-col h3 {
    margin-top:0;
    margin-bottom:10px;
    font-size:16px;
    border-bottom:1px solid #ffffff55;
    padding-bottom:5px;
}

.footer-col p {
    margin:4px 0;
}

.footer-bottom {
    text-align:center;
    font-size:12px;
    padding:10px;
    background:#b00055;
}
</style>
</body>
</html>