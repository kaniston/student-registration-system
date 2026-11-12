
<?php
require_once "db.php";

$message = "";

$count = mysqli_query($conn, "SELECT COUNT(*) AS total FROM admins");
$adminExists = mysqli_fetch_assoc($count)["total"] > 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$adminExists) {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || strlen($password) < 8) {
        $message = "Enter a username and password of at least 8 characters.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO admins (username, password) VALUES (?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ss", $username, $hash);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Admin account created! Delete setup_admin.php now.";
            $adminExists = true;
        } else {
            $message = "Unable to create admin account.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Admin</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <section class="card">
        <h2>Create Administrator Account</h2>

        <p><?= htmlspecialchars($message) ?></p>

        <?php if (!$adminExists): ?>
            <form method="POST">
                <label>Username</label>
                <input type="text" name="username" required maxlength="50">

                <label>Password (minimum 8 characters)</label>
                <input type="password" name="password" required minlength="8">

                <button type="submit">Create Admin</button>
            </form>
        <?php else: ?>
            <p>Admin account exists. Remove this setup file.</p>
        <?php endif; ?>
    </section>
</div>
</body>
</html>
