
<?php
require_once "auth.php";
require_once "db.php";
require_once "log_activity.php";

$message = "";

// Get and validate student ID
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die("Invalid student ID.");
}

// Fetch student details
$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM students WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$student) {
    die("Student record not found.");
}

// Update student details
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Verify CSRF token
    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        die("Invalid security token. Please refresh the page and try again.");
    }

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $age = filter_input(INPUT_POST, "age", FILTER_VALIDATE_INT);

    if (
        $name === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $course === "" ||
        $age === false || $age === null ||
        $age < 16 || $age > 21
    ) {
        $message = "Please enter valid details. Age must be between 16 and 21.";
    } else {

        $updateStmt = mysqli_prepare(
            $conn,
            "UPDATE students
             SET name = ?, email = ?, course = ?, age = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $updateStmt,
            "sssii",
            $name,
            $email,
            $course,
            $age,
            $id
        );

        if (mysqli_stmt_execute($updateStmt)) {

            // Record update activity in MongoDB
            logActivity("student_updated", $id);

            mysqli_stmt_close($updateStmt);

            header("Location: index.php?status=updated");
            exit;

        } else {

            $message = mysqli_stmt_errno($updateStmt) === 1062
                ? "This email is already registered."
                : "Unable to update student details.";
        }

        mysqli_stmt_close($updateStmt);
    }

    // Keep entered values if validation or update fails
    $student["name"] = $name;
    $student["email"] = $email;
    $student["course"] = $course;
    $student["age"] = $age;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="container">

    <section class="card">

        <h1>Edit Student Details</h1>

        <?php if ($message !== ""): ?>
            <p class="message error">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>"
            >

            <label>Student Name</label>
            <input
                type="text"
                name="name"
                value="<?= htmlspecialchars($student["name"]) ?>"
                maxlength="50"
                required
            >

            <label>Email Address</label>
            <input
                type="email"
                name="email"
                value="<?= htmlspecialchars($student["email"]) ?>"
                maxlength="100"
                required
            >

            <label>Course</label>
            <select name="course" required>
                <option value="">Select Course</option>

                <?php
                $courses = [
                    "B.Sc Computer Science",
                    "BCA",
                    "B.Com",
                    "BBA",
                    "B.Sc Mathematics"
                ];

                foreach ($courses as $course):
                ?>
                    <option
                        value="<?= htmlspecialchars($course) ?>"
                        <?= $student["course"] === $course ? "selected" : "" ?>
                    >
                        <?= htmlspecialchars($course) ?>
                    </option>
                <?php endforeach; ?>

            </select>

            <label>Age</label>
            <input
                type="number"
                name="age"
                min="16"
                max="21"
                value="<?= htmlspecialchars((string)$student["age"]) ?>"
                required
            >

            <button type="submit">Update Student</button>

            <a href="index.php" class="reset-button">Back</a>

        </form>

    </section>

</div>

</body>
</html>
