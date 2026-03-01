<meta http-equiv="refresh" content="2;url=home.php">

<h2 style="text-align:center;margin-top:100px;">
🍰 Loading Smart Baking...
</h2>

<div id="tipBox" style="text-align:center;color:#cc0066;font-weight:bold;">
Loading tip...
</div>

<script>
let tips=[
"Preheat oven always!",
"Use fresh ingredients!",
"Sift flour properly!"
];

document.getElementById("tipBox").innerText =
tips[Math.floor(Math.random()*tips.length)];
</script>