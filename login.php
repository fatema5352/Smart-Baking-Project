<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $pass  = $_POST["password"];

    if ($email == "" || $pass == "") {
        $errors[] = "Please enter email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password_hash, is_admin FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($uid, $name, $password_hash, $is_admin);

        if ($stmt->num_rows == 1) {
            $stmt->fetch();
            if (password_verify($pass, $password_hash)) {
                $_SESSION["user_id"]    = $uid;
                $_SESSION["user_name"]  = $name;
                $_SESSION["user_email"] = $email;
                $_SESSION["is_admin"]   = (int)$is_admin;

                header("Location: index.php");
                exit;
            } else {
                $errors[] = "Invalid email or password.";
            }
        } else {
            $errors[] = "Invalid email or password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Smart Baking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right,#ffe6f0,#fff5e6);
            margin:0;
            padding:0;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        h2 { text-align:center; color:#cc0066; }
        input[type=email], input[type=password] {
            width: 100%; padding: 8px; margin: 5px 0 10px;
        }
        button {
            background:#cc0066; color:#fff;
            border:none; padding:10px 20px; cursor:pointer;
            width:100%; border-radius:5px;
        }
        .error { color:red; margin-bottom:10px; font-size:13px; }
        a { color:#cc0066; font-size:13px; }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container">
    <h2>Login</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <p style="text-align:center;margin-top:10px;">
        New here? <a href="register.php">Create an account</a>
    </p>
</div>
</body>
</html>