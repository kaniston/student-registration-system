
<?php
require_once "auth.php";
require_once "db.php";
require_once "log_activity.php";

// Allow POST requests only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// Verify CSRF token
if (
    !isset($_POST["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    die("Invalid security token. Please refresh the page and try again.");
}

// Validate student ID
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

// Delete student using prepared statement
$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM students WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {

    // Record deletion activity in MongoDB only if a student was deleted
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        logActivity("student_deleted", $id);
    }

    mysqli_stmt_close($stmt);

    header("Location: index.php?status=deleted");
    exit;
}

mysqli_stmt_close($stmt);

die("Unable to delete student. Please try again.");
?>
