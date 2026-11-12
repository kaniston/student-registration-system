
<?php
require_once "auth.php";
require_once "db.php";
require_once "log_activity.php";

// Accept POST requests only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit;
}

// Verify CSRF token
if (
    !isset($_POST["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    die("Invalid request. Please go back and try again.");
}

// Get form values
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$course = trim($_POST["course"] ?? "");
$age = filter_var($_POST["age"] ?? "", FILTER_VALIDATE_INT);

// Validate inputs
if (
    $name === "" ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $course === "" ||
    $age === false ||
    $age < 16 ||
    $age > 21
) {
    header("Location: dashboard.php?error=invalid");
    exit;
}

// Insert student into MySQL
$sql = "INSERT INTO students (name, email, course, age)
        VALUES (?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error. Please try again.");
}

mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $course, $age);

if (mysqli_stmt_execute($stmt)) {
    $studentId = mysqli_insert_id($conn);

    // Record activity in MongoDB
    logActivity("student_registered", $studentId);

    mysqli_stmt_close($stmt);
    header("Location: dashboard.php?success=added");
    exit;
}

// Handle duplicate email
if (mysqli_errno($conn) === 1062) {
    mysqli_stmt_close($stmt);
    header("Location: dashboard.php?error=duplicate");
    exit;
}

mysqli_stmt_close($stmt);
header("Location: dashboard.php?error=failed");
exit;
?>
