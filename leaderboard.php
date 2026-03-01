<?php
session_start();
include "db.php";

$sql = "
SELECT rs.recipe_name, rs.rank, rs.image_path, u.name AS user_name
FROM recipe_submissions rs
JOIN users u ON u.id = rs.user_id
WHERE rs.rank IS NOT NULL
ORDER BY rs.rank ASC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>Leaderboard</title>
<style>
body { font-family: Arial; background: linear-gradient(to right,#ffe6f0,#fff5e6); text-align:center; }
table { margin:20px auto; width:80%; background:white; border-collapse:collapse; }
th, td { padding:10px; border-bottom:1px solid #eee; }
th { background:#cc0066; color:white; }
.gold { background:#ffd70026; }
.silver { background:#c0c0c026; }
.bronze { background:#cd7f3226; }
img { width:80px; border-radius:6px; }
</style>
</head>
<body>

<?php include "navbar.php"; ?>

<h1>Recipe Leaderboard</h1>

<table>
<tr>
<th>Rank</th>
<th>Recipe</th>
<th>Baker</th>
<th>Image</th>
</tr>

<?php
while ($row = $result->fetch_assoc()):
$class = "";
if ($row['rank'] == 1) $class = "gold";
elseif ($row['rank'] == 2) $class = "silver";
elseif ($row['rank'] == 3) $class = "bronze";
?>
<tr class="<?php echo $class; ?>">
<td>#<?php echo $row['rank']; ?></td>
<td><?php echo htmlspecialchars($row['recipe_name']); ?></td>
<td><?php echo htmlspecialchars($row['user_name']); ?></td>
<td><img src="<?php echo $row['image_path']; ?>"></td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>