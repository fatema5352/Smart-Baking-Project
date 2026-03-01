<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

// Must be admin
if (!isset($_SESSION['user_id'])) {
    die("Access denied: Please login as admin.");
}

$user_id = (int)$_SESSION['user_id'];

// Check is_admin flag from session (faster) – if you want double safety, also query DB
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Access denied: You are not an admin.");
}

$errors = [];
$success = "";

// Handle rank update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submission_id'])) {
    $submission_id = (int)$_POST['submission_id'];
    $rank          = trim($_POST['rank']);

    if ($rank === "" || !ctype_digit($rank)) {
        $errors[] = "Rank must be a positive number.";
    } else {
        $rankInt = (int)$rank;
        if ($rankInt <= 0) {
            $errors[] = "Rank must be greater than 0.";
        } else {
            $status = "Ranked";
            $stmt = $conn->prepare("
                UPDATE recipe_submissions
                SET rank = ?, status = ?
                WHERE id = ?
            ");
            $stmt->bind_param("isi", $rankInt, $status, $submission_id);
            if ($stmt->execute()) {
                $success = "Rank updated successfully.";
            } else {
                $errors[] = "Database error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Fetch all submissions
$subs = [];
$res = $conn->query("
    SELECT rs.id, rs.recipe_name, rs.status, rs.rank, rs.created_at,
           u.name AS user_name, u.email, rs.image_path
    FROM recipe_submissions rs
    JOIN users u ON u.id = rs.user_id
    ORDER BY rs.created_at DESC
");
while ($row = $res->fetch_assoc()) {
    $subs[] = $row;
}
$totalSubmissions = count($subs);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Recipe Submissions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right,#ffe6f0,#fff5e6);
            margin:0;
            padding:0;
        }
        .page-wrapper {
            max-width:1000px;
            margin:0 auto;
            padding:20px 15px 40px 15px;
        }
        h1 {
            color:#cc0066;
            text-align:center;
            margin:20px 0 10px 0;
        }
        .summary {
            text-align:center;
            font-size:13px;
            color:#333;
            margin-bottom:10px;
        }
        .card {
            background:#ffffffcc;
            border-radius:12px;
            padding:15px 18px;
            box-shadow:0 2px 8px rgba(0,0,0,0.08);
            margin-bottom:20px;
        }
        .error { color:#b2002d; font-size:13px; margin-bottom:6px; }
        .success { color:#1b8150; font-size:13px; margin-bottom:6px; }

        table {
            width:100%;
            border-collapse:collapse;
            font-size:13px;
            margin-top:10px;
        }
        th, td {
            padding:6px;
            border-bottom:1px solid #eee;
            text-align:left;
            vertical-align:top;
        }
        th {
            background:#f7d7e6;
        }
        .status-tag {
            font-weight:bold;
            color:#a0004a;
        }
        .rank-input {
            width:60px;
            padding:4px;
        }
        .small-btn {
            background:#cc0066;
            color:#fff;
            border:none;
            padding:4px 8px;
            border-radius:4px;
            cursor:pointer;
            font-size:12px;
        }
        .small-btn:hover { opacity:0.9; }
        img {
            max-width:80px;
            border-radius:6px;
        }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="page-wrapper">
    <h1>Admin – Recipe Submissions</h1>
    <div class="summary">
        Total submissions found: <strong><?php echo $totalSubmissions; ?></strong>
    </div>

    <div class="card">
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($totalSubmissions === 0): ?>
            <p style="font-size:13px;color:#666;">No submissions yet. Ask a user to submit a recipe from the “Submit Contest Recipe” page.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Preview</th>
                    <th>Recipe</th>
                    <th>Submitted By</th>
                    <th>Status</th>
                    <th>Rank</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($subs as $s): ?>
                    <tr>
                        <td><?php echo (int)$s['id']; ?></td>
                        <td>
                            <?php if (!empty($s['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($s['image_path']); ?>" alt="recipe image">
                            <?php else: ?>
                                <small>No image</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($s['recipe_name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($s['created_at']); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($s['user_name']); ?><br>
                            <small><?php echo htmlspecialchars($s['email']); ?></small>
                        </td>
                        <td class="status-tag"><?php echo htmlspecialchars($s['status']); ?></td>
                        <td>
                            <?php
                                if (is_null($s['rank'])) {
                                    echo "-";
                                } else {
                                    echo "#" . (int)$s['rank'];
                                }
                            ?>
                        </td>
                        <td>
                            <form method="post" style="margin:0;">
                                <input type="hidden" name="submission_id" value="<?php echo (int)$s['id']; ?>">
                                <input type="number" name="rank" class="rank-input" min="1"
                                       value="<?php echo is_null($s['rank']) ? '' : (int)$s['rank']; ?>">
                                <button type="submit" class="small-btn">Save Rank</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>