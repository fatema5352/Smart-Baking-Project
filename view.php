<?php
session_start();
include "db.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $error = "Recipe not found.";
} else {
    $recipe_id = (int)$_GET['id'];

    $stmt = $conn->prepare("SELECT id, name, bake_temp_c, bake_time_min, calories, difficulty, short_ingredients_text, instructions, created_at FROM recipes WHERE id = ?");
    $stmt->bind_param("i", $recipe_id);
    $stmt->execute();
    $recipeResult = $stmt->get_result();
    $recipe = $recipeResult->fetch_assoc();
    $stmt->close();

    if (!$recipe) {
        $error = "Recipe not found.";
    } else {
        $stmt2 = $conn->prepare("
            SELECT 
                ri.quantity,
                ri.unit,
                i.name AS ingredient_name,
                i.category AS ingredient_category
            FROM recipe_ingredients ri
            INNER JOIN ingredients i ON i.id = ri.ingredient_id
            WHERE ri.recipe_id = ?
            ORDER BY i.category ASC, i.name ASC
        ");
        $stmt2->bind_param("i", $recipe_id);
        $stmt2->execute();
        $ingredientsResult = $stmt2->get_result();
        $ingredients = [];
        while ($row = $ingredientsResult->fetch_assoc()) {
            $ingredients[] = $row;
        }
        $stmt2->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>
        <?php
        if (isset($recipe['name'])) {
            echo "Recipe: " . htmlspecialchars($recipe['name']) . " - Smart Baking";
        } else {
            echo "Recipe Details - Smart Baking";
        }
        ?>
    </title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right,#ffe6f0,#fff5e6);
            margin: 0;
            padding: 0;
        }
        .page-wrapper {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px 15px 40px 15px;
        }
        .back-link {
            display:inline-block;
            margin-bottom:10px;
            font-size:13px;
            text-decoration:none;
            color:#cc0066;
        }
        .back-link:hover { text-decoration:underline; }

        .recipe-header {
            background:#ffffffcc;
            padding:15px 18px;
            border-radius:12px;
            box-shadow:0 2px 8px rgba(0,0,0,0.08);
            margin-bottom:18px;
        }

        .recipe-title-row {
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:10px;
        }

        .recipe-title {
            font-size:24px;
            font-weight:bold;
            color:#333;
            margin:0;
        }

        .difficulty-badge {
            display:inline-block;
            padding:4px 10px;
            border-radius:999px;
            font-size:11px;
            font-weight:bold;
            text-transform:uppercase;
        }
        .difficulty-easy { background:#e0f8e8; color:#1b8150; }
        .difficulty-medium { background:#fff4d6; color:#a56a00; }
        .difficulty-hard { background:#ffe0e0; color:#b2002d; }

        .recipe-meta {
            margin-top:8px;
            font-size:13px;
            color:#666;
            display:flex;
            flex-wrap:wrap;
            gap:10px;
        }
        .recipe-meta span {
            display:inline-flex;
            align-items:center;
            gap:4px;
        }
        .meta-label { font-weight:bold; color:#444; }
        .calories-highlight { font-weight:bold; color:#cc0066; }

        .short-ingredients {
            margin-top:10px;
            font-size:13px;
            color:#555;
        }
        .short-ingredients strong { font-weight:bold; }

        .content-layout {
            display:grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.4fr);
            gap:20px;
            margin-top:20px;
        }

        .card {
            background:#ffffffcc;
            border-radius:12px;
            padding:15px 18px;
            box-shadow:0 2px 8px rgba(0,0,0,0.08);
        }
        .card h2 {
            margin-top:0;
            margin-bottom:10px;
            font-size:18px;
            color:#cc0066;
        }

        .ingredients-list {
            list-style:none;
            padding-left:0;
            margin:0;
            font-size:13px;
            color:#444;
        }
        .ingredients-list li {
            margin-bottom:6px;
            padding-left:14px;
            position:relative;
        }
        .ingredients-list li::before {
            content:'•';
            position:absolute;
            left:0;
            top:0;
            color:#cc0066;
        }
        .ingredient-category {
            font-size:11px;
            color:#777;
            margin-left:4px;
        }

        .instructions-text {
            font-size:13px;
            color:#444;
            white-space:pre-line;
            line-height:1.5;
        }

        .actions-row {
            margin-top:15px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            flex-wrap:wrap;
            gap:10px;
            font-size:13px;
        }

        .primary-btn,
        .secondary-btn {
            display:inline-block;
            padding:8px 14px;
            border-radius:20px;
            text-decoration:none;
            cursor:pointer;
            font-size:13px;
            border:none;
        }
        .primary-btn {
            background:#cc0066;
            color:#fff;
            font-weight:bold;
        }
        .primary-btn:hover { opacity:0.9; }

        .secondary-btn {
            background:#ffffff;
            color:#cc0066;
            border:1px solid #cc0066;
        }
        .secondary-btn:hover { background:#ffe6f0; }

        .login-note {
            font-size:12px;
            color:#666;
        }

        @media (max-width: 800px) {
            .content-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="page-wrapper">

    <a class="back-link" href="recipes.php">← Back to all recipes</a>

    <?php if (isset($error)): ?>
        <div class="recipe-header">
            <p style="color:#b2002d;font-weight:bold;"><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php else: ?>
        <?php
            $name         = $recipe['name'];
            $temp         = $recipe['bake_temp_c'];
            $time         = $recipe['bake_time_min'];
            $calories     = $recipe['calories'];
            $difficultyDB = $recipe['difficulty'];
            $shortIng     = $recipe['short_ingredients_text'];
            $instructions = $recipe['instructions'];
            $created_at   = $recipe['created_at'];

            $diffClass = 'difficulty-medium';
            if (strcasecmp($difficultyDB, 'Easy') === 0)   $diffClass = 'difficulty-easy';
            if (strcasecmp($difficultyDB, 'Hard') === 0)   $diffClass = 'difficulty-hard';
        ?>

        <div class="recipe-header">
            <div class="recipe-title-row">
                <h1 class="recipe-title">
                    <?php echo htmlspecialchars($name); ?>
                </h1>
                <span class="difficulty-badge <?php echo $diffClass; ?>">
                    <?php echo htmlspecialchars($difficultyDB ?: 'Medium'); ?>
                </span>
            </div>

            <div class="recipe-meta">
                <?php if (!is_null($calories)): ?>
                    <span>
                        <span class="meta-label">Calories:</span>
                        <span class="calories-highlight"><?php echo (int)$calories; ?> kcal</span>
                    </span>
                <?php endif; ?>

                <?php if (!is_null($time)): ?>
                    <span>
                        <span class="meta-label">Bake time:</span>
                        <span><?php echo (int)$time; ?> min</span>
                    </span>
                <?php endif; ?>

                <?php if (!is_null($temp)): ?>
                    <span>
                        <span class="meta-label">Temperature:</span>
                        <span><?php echo (int)$temp; ?> °C</span>
                    </span>
                <?php endif; ?>

                <?php if (!empty($created_at)): ?>
                    <span>
                        <span class="meta-label">Added on:</span>
                        <span><?php echo htmlspecialchars($created_at); ?></span>
                    </span>
                <?php endif; ?>
            </div>

            <?php if ($shortIng): ?>
                <div class="short-ingredients">
                    <strong>Quick ingredient overview:</strong>
                    <?php echo htmlspecialchars($shortIng); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="content-layout">
            <div class="card">
                <h2>Ingredients</h2>
                <?php if (!empty($ingredients)): ?>
                    <ul class="ingredients-list">
                        <?php foreach ($ingredients as $ing): ?>
                            <li>
                                <?php
                                    $qty = $ing['quantity'];
                                    $unit = $ing['unit'];
                                    $iname = $ing['ingredient_name'];
                                    $icat  = $ing['ingredient_category'];

                                    $qtyText = '';
                                    if (!is_null($qty)) {
                                        if ((float)$qty == (int)$qty) {
                                            $qtyText = (int)$qty;
                                        } else {
                                            $qtyText = $qty;
                                        }
                                        $qtyText .= ' ';
                                    }
                                    if ($unit) {
                                        $qtyText .= $unit . ' ';
                                    }
                                ?>
                                <?php echo htmlspecialchars($qtyText . $iname); ?>
                                <?php if ($icat): ?>
                                    <span class="ingredient-category">(<?php echo htmlspecialchars($icat); ?>)</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="font-size:13px;color:#777;">Ingredients not linked yet for this recipe.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2>Method</h2>
                <?php if (!empty($instructions)): ?>
                    <div class="instructions-text">
                        <?php echo nl2br(htmlspecialchars($instructions)); ?>
                    </div>
                <?php else: ?>
                    <p class="instructions-text" style="color:#777;">
                        Instructions have not been added yet for this recipe.
                    </p>
                <?php endif; ?>

                <div class="actions-row">
                    <div>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <span class="login-note">
                                Logged in as <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </span>
                        <?php else: ?>
                            <span class="login-note">
                                You are viewing as guest.
                            </span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a class="primary-btn" href="add_bake.php?recipe_id=<?php echo $recipe_id; ?>">
                                I baked this! 🎂
                            </a>
                        <?php else: ?>
                            <a class="secondary-btn" href="login.php">
                                Login to share your bake
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

</body>
</html>