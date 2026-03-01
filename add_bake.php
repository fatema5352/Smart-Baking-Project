<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

if (!isset($_GET["recipe_id"])) {
    die("Recipe not selected.");
}
$recipe_id = (int)$_GET["recipe_id"];

$stmt = $conn->prepare("SELECT name FROM recipes WHERE id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$stmt->bind_result($recipe_name);
if (!$stmt->fetch()) {
    die("Recipe not found.");
}
$stmt->close();

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $comment = trim($_POST["comment"]);
    $rating  = (int)$_POST["rating"];

    $photo_path = null;
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == UPLOAD_ERR_OK) {
        $allowed = ["image/jpeg","image/png","image/jpg"];
        if (!in_array($_FILES["photo"]["type"], $allowed)) {
            $errors[] = "Only JPG and PNG images are allowed.";
        } else {
            $ext = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
            $newName = "bake_" . time() . "_" . rand(1000,9999) . "." . $ext;
            $uploadDir = "uploads/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $dest = $uploadDir . $newName;
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $dest)) {
                $photo_path = $dest;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO bakes (user_id, recipe_id, photo_path, comment, rating) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("iissi", $user_id, $recipe_id, $photo_path, $comment, $rating);
        if ($stmt->execute()) {
            $success = "Your bake is submitted! It will show in the leaderboard.";
        } else {
            $errors[] = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Bake - Smart Baking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right,#ffe6f0,#fff5e6);
            margin:0;
            padding:0;
        }
        .container {
            max-width: 500px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        h2 { text-align:center; color:#cc0066; }
        textarea, input[type=file], select {
            width: 100%; margin-bottom: 10px;
        }
        button {
            background:#cc0066; color:#fff;
            border:none; padding:10px 20px; cursor:pointer;
            width:100%; border-radius:5px;
        }
        .error { color:red; margin-bottom:10px; font-size:13px; }
        .success { color:green; margin-bottom:10px; font-size:13px; }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container">
    <h2>I baked: <?php echo htmlspecialchars($recipe_name); ?></h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>Upload Photo (optional)</label>
        <input type="file" name="photo">

        <label>Your comment (how it went?)</label>
        <textarea name="comment" rows="4"></textarea>

        <label>Rating</label>
        <select name="rating">
            <option value="5">⭐⭐⭐⭐⭐</option>
            <option value="4">⭐⭐⭐⭐</option>
            <option value="3">⭐⭐⭐</option>
            <option value="2">⭐⭐</option>
            <option value="1">⭐</option>
        </select>

        <button type="submit">Submit Bake</button>
    </form>
</div>
</body>
</html>