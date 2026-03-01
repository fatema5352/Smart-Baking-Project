<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<style>
    .sb-nav-wrapper {
        width: 100%;
        background: #cc0066;
        color: #fff;
    }

    .sb-nav-inner {
        max-width: 1100px;
        margin: 0 auto;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        box-sizing: border-box;
    }

    .sb-nav-logo {
        font-weight: bold;
        font-size: 18px;
        letter-spacing: 0.5px;
    }

    .sb-nav-links {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: center;
    }

    .sb-nav-links a {
        color: #fff;
        text-decoration: none;
        font-size: 14px;
    }

    .sb-nav-links a:hover {
        text-decoration: underline;
    }

    .sb-nav-auth {
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .sb-nav-auth a {
        color: #fff;
        text-decoration: none;
        font-weight: bold;
        font-size: 13px;
    }

    .sb-nav-auth a:hover {
        text-decoration: underline;
    }

    @media (max-width: 700px) {
        .sb-nav-inner {
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
        }
        .sb-nav-links {
            justify-content: flex-start;
        }
        .sb-nav-auth {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>

<header class="sb-nav-wrapper">
    <div class="sb-nav-inner">
        <div class="sb-nav-logo">
            Smart Baking
        </div>

        <nav class="sb-nav-links">
            <a href="index.php">Home</a>
            <a href="recipes.php">Recipes</a>
            <a href="add.php">Master Recipes</a>
            <a href="submit_recipe.php">Submit Contest Recipe</a>
            <a href="leaderboard.php">Leaderboard</a>

            <?php if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                <a href="admin_submissions.php">Admin Panel</a>
            <?php endif; ?>
        </nav>

        <div class="sb-nav-auth">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span>Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <span>|</span>
                <a href="register.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</header>