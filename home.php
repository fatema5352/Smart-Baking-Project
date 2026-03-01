<!DOCTYPE html>
<html>
<head>
<title>Home - Smart Baking</title>
<style>
body{
    font-family: Arial;
    background: linear-gradient(to right,#ffe6f0,#fff5e6);
    text-align:center;
}
</style>
</head>
<body>

<?php include("navbar.php"); ?>

<h1>🍰 Welcome to Smart Baking Recipe Manager</h1>
<p>Discover delicious oven recipes with calories & difficulty levels!</p>

<h3>⭐ Tip of the Day</h3>
<div id="tipBox" style="background:white;padding:10px;margin:20px auto;width:60%;border-radius:10px;box-shadow:0 0 10px #ccc;color:#cc0066;font-weight:bold;">
Loading tip...
</div>

<script>
let tips=[
"Always preheat the oven before baking.",
"Use room temperature butter.",
"Do not open oven frequently.",
"Sift flour for fluffy texture.",
"Let cake cool before frosting.",
"Measure ingredients properly.",
"Use fresh baking powder.",
"Grease tray properly.",
"Add pinch of salt.",
"Use parchment paper."
];

document.getElementById("tipBox").innerText =
tips[Math.floor(Math.random()*tips.length)];
</script>

</body>
</html>