# Smart Baking – Recipe Contest System 🧁

Smart Baking is a PHP + MySQL web application where users can submit their best baking recipe **name + image**, and an **admin manually assigns ranks**. A public **leaderboard** shows the top recipes, with special highlighting for the Top 3.

This project was built as a **Web Technology** semester project using **XAMPP**, **PHP**, and **MySQL**.

---

## 🔧 Tech Stack

- **Frontend:** HTML5, CSS3 (simple responsive layout + small animations)
- **Backend:** PHP (procedural)
- **Database:** MySQL (via phpMyAdmin)
- **Server:** XAMPP (Apache + MySQL)
- **Version Control:** Git & GitHub  
  Repository: `https://github.com/fatema5352/Smart-Baking-Project`

---

## ✨ Features Overview

### 👤 User Module

- User Registration & Login (passwords hashed with `password_hash()`)
- Submit recipe:
  - Recipe **name**
  - **JPG/JPEG** image upload (validated)
- View own submissions:
  - Status: `Submitted to Admin` / `Ranked`
  - Rank (if assigned by admin)

### 👑 Admin Module

- Admin login (flagged by `is_admin` column in `users` table)
- **Admin Panel** to manage submissions:
  - View all user submissions
  - Preview recipe image
  - Assign / update **rank** (1, 2, 3, …)
  - Status auto-changes to `Ranked` when rank is saved

### 🏆 Leaderboard

- Shows all ranked recipes ordered by **rank**
- Highlights:
  - Rank 1 → Gold row
  - Rank 2 → Silver row
  - Rank 3 → Bronze row
- Displays recipe name, baker name, and image

### 🏠 Home Page

- Animated **winner banner** (current Rank #1 recipe)
- **Top 3** recipes section with small cards and thumbnails
- **Tip of the Day** (random baking tips)
- Contest snapshot:
  - Total submissions count
- Short “How Smart Baking Works” explanation
- Footer with student/college details

---

## 🗄️ Database Design

Main tables used:

### `users`

| Column        | Type           | Description                         |
|---------------|----------------|-------------------------------------|
| id            | INT, PK, AI    | User ID                             |
| name          | VARCHAR        | User full name                      |
| email         | VARCHAR, UNIQUE| Login email                         |
| password_hash | VARCHAR        | Hashed password                     |
| is_admin      | TINYINT(1)     | `1` = admin, `0` = normal user      |
| created_at    | DATETIME       | Registration time                   |

### `recipe_submissions`

| Column      | Type           | Description                                        |
|-------------|----------------|----------------------------------------------------|
| id          | INT, PK, AI    | Submission ID                                     |
| user_id     | INT, FK        | References `users.id`                             |
| recipe_name | VARCHAR        | Name of the recipe                                |
| image_path  | VARCHAR        | Path to uploaded image (`uploads/submissions/`)   |
| status      | VARCHAR        | `Submitted to Admin` / `Ranked`                   |
| rank        | INT, NULLABLE  | Manual rank assigned by admin                     |
| created_at  | DATETIME       | Submission time                                   |

> Optional enhancement: Add a **UNIQUE constraint** on `rank` so two entries can’t share the same rank.

---

## 🧩 How the System Works (Flow)

1. **User registers** and logs in.
2. User goes to **“Submit Contest Recipe”** and uploads:
   - Recipe name
   - JPG/JPEG image
3. Submission is stored in `recipe_submissions` with status:
   - `Submitted to Admin`
4. **Admin logs in**, opens **Admin Panel**, sees all submissions.
5. Admin enters a numeric **rank** for each recipe.
6. When a rank is saved:
   - Status changes to `Ranked`
7. **Leaderboard** page shows all ranked recipes sorted by rank.
8. **Homepage** automatically updates:
   - Winner banner (Rank 1)
   - Top 3 section
   - Total submissions count

---

## 🖥️ Local Setup (XAMPP)

1. Install **XAMPP** and start **Apache** & **MySQL**.
2. Clone this repository into your XAMPP `htdocs` directory:
   ```javascript
   bash
   cd C:\xampp\htdocs
   git clone https://github.com/fatema5352/Smart-Baking-Project.git```
3. Create a MySQL database (e.g. baking_db) via phpMyAdmin.
4. Import / create the required tables (as defined above).
5. Update db.php with your local DB details:
```javascript
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "baking_db";
```
6. In browser open:
   ```javascript
   http://localhost/Smart-Baking-Project/index.php
    ```
7. Create a normal user from ``` register.php.```
9. Make one user admin by setting ``` is_admin = 1``` in the ``` users``` table (via phpMyAdmin).
   
