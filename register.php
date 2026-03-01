<?php
session_start();
include "db.php";

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $pass1 = $_POST["password"];
    $pass2 = $_POST["confirm_password"];

    if ($name == "" || $email == "" || $pass1 == "" || $pass2 == "") {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    if ($pass1 !== $pass2) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($pass1) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "Email already registered. Please login.";
        } else {
            $password_hash = password_hash($pass1, PASSWORD_BCRYPT);
            $stmtInsert = $conn->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
            $stmtInsert->bind_param("sss", $name, $email, $password_hash);
            if ($stmtInsert->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $errors[] = "Error: " . $conn->error;
            }
            $stmtInsert->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Smart Baking</title>
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
        input[type=text], input[type=email], input[type=password] {
            width: 100%; padding: 8px; margin: 5px 0 10px;
        }
        button {
            background:#cc0066; color:#fff;
            border:none; padding:10px 20px; cursor:pointer;
            width:100%; border-radius:5px;
        }
        .error { color:red; margin-bottom:10px; font-size:13px; }
        .success { color:green; margin-bottom:10px; font-size:13px; }
        a { color:#cc0066; font-size:13px; }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container">
    <h2>Create Account</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <label>Name</label>
        <input type="text" name="name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Register</button>
    </form>

    <p style="text-align:center;margin-top:10px;">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>
</body>
</html>