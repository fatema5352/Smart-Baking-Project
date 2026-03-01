<?php
session_start();
include "db.php";

// Show SQL errors on screen while we’re developing
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// --- Filters ---
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$difficulty = isset($_GET['difficulty']) ? trim($_GET['difficulty']) : 'all';

// Build query
$sql = "SELECT id, name, bake_temp_c, bake_time_min, calories, difficulty, short_ingredients_text
        FROM recipes
        WHERE 1";

if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $sql .= " AND (name LIKE '%$s%' OR short_ingredients_text LIKE '%$s%')";
}

if ($difficulty !== '' && $difficulty !== 'all') {
    $d = $conn->real_escape_string($difficulty);
    $sql .= " AND difficulty = '$d'";
}

/* IMPORTANT CHANGE: use id for sorting instead of created_at
   so even if you forgot that column, there is no SQL error */
$sql .= " ORDER BY id DESC, name ASC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recipes - Smart Baking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right,#ffe6f0,#fff5e6);
            margin: 0;
            padding: 0;
        }

        h1 {
            color:#cc0066;
            text-align:center;
            margin-top: 20px;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align:center;
            color:#555;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .tip {
            text-align:center;
            color:#333;
            font-size:13px;
            margin-bottom: 20px;
        }

        .page-wrapper {
            max-width: 1100px;
            margin: 0 auto 40px auto;
            padding: 0 15px 40px 15px;
        }

        .filters {
            display:flex;
            flex-wrap:wrap;
            justify-content: space-between;
            align-items:center;
            gap:10px;
            background:#ffffffcc;
            padding:12px 15px;
            border-radius:10px;
            box-shadow:0 2px 8px rgba(0,0,0,0.08);
            margin-bottom:20px;
        }

        .filters form {
            display:flex;
            flex-wrap:wrap;
            gap:10px;
            width:100%;
            justify-content: space-between;
        }

        .filters-left,
        .filters-right {
            display:flex;
            flex-wrap:wrap;
            gap:10px;
        }

        .filters input[type="text"] {
            padding:7px 10px;
            border-radius:6px;
            border:1px solid #ddd;
            min-width:200px;
        }

        .filters select {
            padding:7px 10px;
            border-radius:6px;
            border:1px solid #ddd;
            min-width:140px;
        }

        .filters button {
            background:#cc0066;
            color:#fff;
            border:none;
            padding:8px 16px;
            border-radius:6px;
            cursor:pointer;
            font-weight:bold;
        }

        .filters button:hover {
            opacity:0.9;
        }

        .recipes-grid {
            display:grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap:15px;
        }

        .recipe-card {
            background:#fff;
            border-radius:12px;
            padding:15px 15px 12px 15px;
            box-shadow:0 2px 8px rgba(0,0,0,0.08);
            display:flex;
            flex-direction:column;
            justify-content:space-between;
        }

        .recipe-title {
            font-size:18px;
            font-weight:bold;
            margin:0 0 5px 0;
            color:#333;
        }

        .chip-row {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:8px;
            gap:6px;
        }

        .difficulty-badge {
            display:inline-block;
            padding:3px 8px;
            border-radius:999px;
            font-size:11px;
            font-weight:bold;
            text-transform:uppercase;
        }

        .difficulty-easy {
            background:#e0f8e8;
            color:#1b8150;
        }

        .difficulty-medium {
            background:#fff4d6;
            color:#a56a00;
        }

        .difficulty-hard {
            background:#ffe0e0;
            color:#b2002d;
        }

        .meta {
            font-size:11px;
            color:#777;
            text-align:right;
        }

        .meta span {
            margin-left:8px;
        }

        .calories-highlight {
            font-weight:bold;
            color:#cc0066;
        }

        .ingredients-preview {
            font-size:12px;
            color:#555;
            margin:6px 0 10px 0;
        }

        .ingredients-preview strong {
            font-weight:bold;
        }

        .card-footer {
            display:flex;
            justify-content:space-between;
            align-items:center;
            font-size:12px;
            margin-top:auto;
        }

        .view-link {
            text-decoration:none;
            color:#cc0066;
            font-weight:bold;
        }

        .view-link:hover {
            text-decoration:underline;
        }

        .empty-state {
            text-align:center;
            color:#666;
            margin-top:40px;
            font-size:14px;
        }

        @media (max-width: 600px) {
            .filters form {
                flex-direction:column;
                align-items:flex-start;
            }
            .filters-left,
            .filters-right {
                width:100%;
                justify-content:flex-start;
            }
        }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<h1>Smart Baking Recipes</h1>
<p class="subtitle">Browse all baked recipes and open any card to see full details.</p>
<p class="tip"><strong>Tip of the page:</strong> Use the search box to quickly find recipes by ingredient (e.g. “banana”, “paneer”).</p>

<div class="page-wrapper">

    <div class="filters">
        <form method="get" action="">
            <div class="filters-left">
                <input
                    type="text"
                    name="q"
                    placeholder="Search by name or ingredient..."
                    value="<?php echo htmlspecialchars($search); ?>"
                />

                <select name="difficulty">
                    <option value="all" <?php if($difficulty === 'all') echo 'selected'; ?>>All difficulties</option>
                    <option value="Easy" <?php if($difficulty === 'Easy') echo 'selected'; ?>>Easy</option>
                    <option value="Medium" <?php if($difficulty === 'Medium') echo 'selected'; ?>>Medium</option>
                    <option value="Hard" <?php if($difficulty === 'Hard') echo 'selected'; ?>>Hard</option>
                </select>
            </div>

            <div class="filters-right">
                <button type="submit">Apply</button>
            </div>
        </form>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="recipes-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    $id           = (int)$row['id'];
                    $name         = $row['name'];
                    $temp         = $row['bake_temp_c'];
                    $time         = $row['bake_time_min'];
                    $calories     = $row['calories'];
                    $difficultyDB = $row['difficulty'];
                    $shortIng     = $row['short_ingredients_text'];

                    $diffClass = 'difficulty-medium';
                    if (strcasecmp($difficultyDB, 'Easy') === 0)   $diffClass = 'difficulty-easy';
                    if (strcasecmp($difficultyDB, 'Hard') === 0)   $diffClass = 'difficulty-hard';
                ?>
                <div class="recipe-card">
                    <div>
                        <div class="chip-row">
                            <div class="recipe-title">
                                <?php echo htmlspecialchars($name); ?>
                            </div>
                            <span class="difficulty-badge <?php echo $diffClass; ?>">
                                <?php echo htmlspecialchars($difficultyDB ?: 'Medium'); ?>
                            </span>
                        </div>

                        <div class="meta">
                            <?php if (!is_null($calories)): ?>
                                <span class="calories-highlight"><?php echo (int)$calories; ?> kcal</span>
                            <?php endif; ?>
                            <?php if (!is_null($time)): ?>
                                <span><?php echo (int)$time; ?> min</span>
                            <?php endif; ?>
                            <?php if (!is_null($temp)): ?>
                                <span><?php echo (int)$temp; ?> °C</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($shortIng): ?>
                            <p class="ingredients-preview">
                                <strong>Key ingredients:</strong>
                                <?php echo htmlspecialchars($shortIng); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="card-footer">
                        <span>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                Logged in as <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            <?php else: ?>
                                Guest view
                            <?php endif; ?>
                        </span>
                        <a class="view-link" href="view.php?id=<?php echo $id; ?>">View recipe →</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            No recipes found for this filter.
        </div>
    <?php endif; ?>

</div>

</body>
</html>