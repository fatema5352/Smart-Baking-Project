<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$errors = [];
$success = "";

// Handle submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $recipe_name = trim($_POST['recipe_name']);

    if ($recipe_name === "") {
        $errors[] = "Recipe name is required.";
    }

    // IMAGE REQUIRED
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Recipe image (JPG/JPEG) is required.";
    }

    $image_path = null;

    if (empty($errors)) {

        $allowedExt = ['jpg','jpeg'];
        $fileName   = $_FILES['photo']['name'];
        $tmpPath    = $_FILES['photo']['tmp_name'];

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            $errors[] = "Only JPG/JPEG files are allowed.";
        } else {
            $mime = mime_content_type($tmpPath);
            if ($mime !== "image/jpeg") {
                $errors[] = "Invalid file type. Only JPEG allowed.";
            } else {
                $uploadDir = "uploads/submissions/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newName = "recipe_" . time() . "_" . rand(1000,9999) . "." . $ext;
                $dest = $uploadDir . $newName;

                if (move_uploaded_file($tmpPath, $dest)) {
                    $image_path = $dest;
                } else {
                    $errors[] = "Failed to upload image.";
                }
            }
        }
    }

    if (empty($errors)) {

        $status = "Submitted to Admin";

        $stmt = $conn->prepare("
            INSERT INTO recipe_submissions (user_id, recipe_name, image_path, status)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $user_id, $recipe_name, $image_path, $status);

        if ($stmt->execute()) {
            $success = "Your recipe has been submitted. Status: Submitted to Admin.";
        } else {
            $errors[] = "Database error.";
        }

        $stmt->close();
    }
}

// Fetch user submissions
$stmt2 = $conn->prepare("
    SELECT recipe_name, status, rank, created_at
    FROM recipe_submissions
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>Submit Recipe - Smart Baking</title>
<style>
body { font-family: Arial; background: linear-gradient(to right,#ffe6f0,#fff5e6); margin:0; }
.page { max-width:900px; margin:0 auto; padding:20px; }
.card { background:#fff; padding:15px; border-radius:10px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
h1 { color:#cc0066; text-align:center; }
label { font-size:13px; }
input, button { padding:6px; margin-top:5px; }
button { background:#cc0066; color:white; border:none; border-radius:5px; cursor:pointer; }
button:hover { opacity:0.9; }
.error { color:red; font-size:13px; }
.success { color:green; font-size:13px; }
table { width:100%; border-collapse:collapse; font-size:13px; }
th, td { padding:8px; border-bottom:1px solid #eee; }
th { background:#f7d7e6; }
.rank { font-weight:bold; color:green; }
.status { font-weight:bold; color:#a0004a; }
</style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="page">
<h1>Submit Your Recipe</h1>

<div class="card">

<?php if (!empty($errors)): ?>
<div class="error">
<?php foreach($errors as $e) echo "<div>$e</div>"; ?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
<label>Recipe Name*</label><br>
<input type="text" name="recipe_name" required><br><br>

<label>Upload Image (JPG/JPEG only)*</label><br>
<input type="file" name="photo" accept=".jpg,.jpeg,image/jpeg" required><br><br>

<button type="submit">Submit Recipe</button>
</form>
</div>

<div class="card">
<h3>Your Submissions</h3>

<table>
<tr>
<th>Recipe</th>
<th>Status</th>
<th>Rank</th>
<th>Date</th>
</tr>

<?php while ($row = $result2->fetch_assoc()): ?>
<tr>
<td><?php echo htmlspecialchars($row['recipe_name']); ?></td>
<td class="status"><?php echo htmlspecialchars($row['status']); ?></td>
<td>
<?php
if ($row['rank'] === null) {
    echo "-";
} else {
    echo '<span class="rank">#' . (int)$row['rank'] . '</span>';
}
?>
</td>
<td><?php echo htmlspecialchars($row['created_at']); ?></td>
</tr>
<?php endwhile; ?>

</table>

</div>
</div>

</body>
</html>