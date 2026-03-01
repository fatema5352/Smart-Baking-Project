<?php
// show errors while developing (remove on final deployment)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

// -----------------------------------------------------
// 1. Fetch all ingredients for the dropdown
// -----------------------------------------------------
$ingredientsAll = [];
$resIng = $conn->query("SELECT id, name, default_unit FROM ingredients ORDER BY name ASC");
while ($row = $resIng->fetch_assoc()) {
    $ingredientsAll[] = $row;
}

// -----------------------------------------------------
// 2. Handle form submission
// -----------------------------------------------------
$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // basic recipe fields
    $name         = trim($_POST["name"]);
    $temp         = isset($_POST["bake_temp_c"]) && $_POST["bake_temp_c"] !== "" ? (int)$_POST["bake_temp_c"] : 0;
    $time         = isset($_POST["bake_time_min"]) && $_POST["bake_time_min"] !== "" ? (int)$_POST["bake_time_min"] : 0;
    $calories     = isset($_POST["calories"]) && $_POST["calories"] !== "" ? (int)$_POST["calories"] : 0;
    $difficulty   = isset($_POST["difficulty"]) ? trim($_POST["difficulty"]) : "Medium";
    $instructions = isset($_POST["instructions"]) ? trim($_POST["instructions"]) : "";

    // ingredient linking arrays
    $ing_ids = isset($_POST["ingredient_id"]) ? $_POST["ingredient_id"] : [];
    $qtys    = isset($_POST["quantity"]) ? $_POST["quantity"] : [];
    $units   = isset($_POST["unit"]) ? $_POST["unit"] : [];

    // ---- validation ----
    if ($name === "") {
        $errors[] = "Recipe name is required.";
    }

    // at least one ingredient selected
    $hasIngredient = false;
    if (!empty($ing_ids)) {
        foreach ($ing_ids as $iid) {
            if ($iid !== "") {
                $hasIngredient = true;
                break;
            }
        }
    }
    if (!$hasIngredient) {
        $errors[] = "Please select at least one ingredient.";
    }

    if (empty($errors)) {

        // Build short_ingredients_text from first few ingredient names
        $namesForShort = [];
        foreach ($ing_ids as $iid) {
            if ($iid === "") continue;
            foreach ($ingredientsAll as $ing) {
                if ((int)$ing["id"] === (int)$iid) {
                    $namesForShort[] = $ing["name"];
                    break;
                }
            }
        }
        $namesForShort = array_slice(array_unique($namesForShort), 0, 5);
        $shortText = implode(", ", $namesForShort);

        // insert into recipes table
        $stmt = $conn->prepare("
            INSERT INTO recipes (name, bake_temp_c, bake_time_min, calories, difficulty, short_ingredients_text, instructions)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "siiisss",
            $name,
            $temp,
            $time,
            $calories,
            $difficulty,
            $shortText,
            $instructions
        );

        if ($stmt->execute()) {
            $recipe_id = $stmt->insert_id;
            $stmt->close();

            // insert into recipe_ingredients table
            $stmt2 = $conn->prepare("
                INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit)
                VALUES (?, ?, ?, ?)
            ");

            for ($i = 0; $i < count($ing_ids); $i++) {
                if ($ing_ids[$i] === "") continue; // skip empty row

                $iid  = (int)$ing_ids[$i];
                $qty  = ($qtys[$i] !== "") ? (float)$qtys[$i] : 0;
                $unit = trim($units[$i]);

                if ($unit === "") {
                    // optional: try to use default unit from ingredients table
                    foreach ($ingredientsAll as $ing) {
                        if ((int)$ing["id"] === $iid) {
                            $unit = $ing["default_unit"] ?: "";
                            break;
                        }
                    }
                }

                $stmt2->bind_param("iids", $recipe_id, $iid, $qty, $unit);
                $stmt2->execute();
            }

            $stmt2->close();

            $success = "Recipe added successfully!";
        } else {
            $errors[] = "Error saving recipe: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Recipe - Smart Baking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right,#ffe6f0,#fff5e6);
            margin:0;
            padding:0;
        }
        .page-wrapper {
            max-width:900px;
            margin:0 auto;
            padding:20px 15px 40px 15px;
        }
        h1 {
            color:#cc0066;
            text-align:center;
            margin:20px 0 10px 0;
        }
        .tip {
            text-align:center;
            font-size:13px;
            color:#333;
            margin-bottom:15px;
        }
        .card {
            background:#ffffffcc;
            border-radius:12px;
            padding:15px 18px;
            box-shadow:0 2px 8px rgba(0,0,0,0.08);
        }
        .form-row {
            margin-bottom:10px;
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }
        label {
            font-size:13px;
            color:#444;
            min-width:120px;
        }
        input[type="text"],
        input[type="number"],
        select,
        textarea {
            padding:6px 8px;
            border-radius:6px;
            border:1px solid #ccc;
            font-size:13px;
            width:100%;
            box-sizing:border-box;
        }
        textarea {
            min-height:90px;
            resize:vertical;
        }
        .ingredients-table {
            width:100%;
            border-collapse:collapse;
            margin-top:5px;
            font-size:13px;
        }
        .ingredients-table th,
        .ingredients-table td {
            border-bottom:1px solid #eee;
            padding:6px;
        }
        .btn {
            background:#cc0066;
            color:#fff;
            border:none;
            padding:8px 16px;
            border-radius:6px;
            cursor:pointer;
            font-weight:bold;
            margin-top:10px;
        }
        .btn:hover { opacity:0.9; }

        .error { color:#b2002d; font-size:13px; margin-bottom:6px; }
        .success { color:#1b8150; font-size:13px; margin-bottom:6px; }

        .info-note { font-size:12px; color:#777; margin-top:4px; }

        @media (max-width: 700px) {
            .form-row { flex-direction:column; }
            label { min-width:0; }
        }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="page-wrapper">
    <h1>Add New Recipe</h1>
    <p class="tip"><strong>Tip:</strong> Link ingredients from the master list so you can reuse them in other recipes and calculate stats later.</p>

    <div class="card">

        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach($errors as $e) echo "<div>$e</div>"; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="post" action="">

            <div class="form-row">
                <label>Recipe Name*</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-row">
                <label>Bake Temperature (°C)</label>
                <input type="number" name="bake_temp_c" min="0">
            </div>

            <div class="form-row">
                <label>Bake Time (minutes)</label>
                <input type="number" name="bake_time_min" min="0">
            </div>

            <div class="form-row">
                <label>Approx Calories</label>
                <input type="number" name="calories" min="0">
            </div>

            <div class="form-row">
                <label>Difficulty</label>
                <select name="difficulty">
                    <option value="Easy">Easy</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="Hard">Hard</option>
                </select>
            </div>

            <div class="form-row">
                <label>Instructions / Method</label>
                <textarea name="instructions" placeholder="Write steps for the recipe..."></textarea>
            </div>

            <hr style="margin:15px 0;">

            <div class="form-row" style="flex-direction:column;">
                <label>Ingredients (link to master list)*</label>
                <table class="ingredients-table">
                    <tr>
                        <th>Ingredient</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                    </tr>
                    <?php for ($i = 0; $i < 6; $i++): ?>
                        <tr>
                            <td>
                                <select name="ingredient_id[]">
                                    <option value="">-- select --</option>
                                    <?php foreach ($ingredientsAll as $ing): ?>
                                        <option value="<?php echo $ing['id']; ?>">
                                            <?php echo htmlspecialchars($ing['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="quantity[]">
                            </td>
                            <td>
                                <input type="text" name="unit[]" placeholder="g / ml / pcs">
                            </td>
                        </tr>
                    <?php endfor; ?>
                </table>
                <div class="info-note">
                    You can leave unused rows empty. At least one ingredient must be selected.
                </div>
            </div>

            <button type="submit" class="btn">Save Recipe</button>
        </form>
    </div>
</div>

</body>
</html>