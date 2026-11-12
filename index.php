
<?php
require_once "auth.php";
require_once "db.php";
require_once "log_activity.php";

$message = "";
$messageType = "";

// Handle student registration
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register"])) {

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
        $message = "Please enter valid student details. Age must be between 16 and 21.";
        $messageType = "error";
    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO students (name, email, course, age) VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $course, $age);

        if (mysqli_stmt_execute($stmt)) {

            // Save registration activity in MongoDB
            $studentId = mysqli_insert_id($conn);
            logActivity("student_registered", $studentId);

            mysqli_stmt_close($stmt);

            header("Location: index.php?status=added");
            exit;

        } else {

            $message = mysqli_stmt_errno($stmt) === 1062
                ? "This email is already registered."
                : "Registration failed. Please try again.";

            $messageType = "error";
            mysqli_stmt_close($stmt);
        }
    }
}

// Display status messages
if (isset($_GET["status"])) {

    if ($_GET["status"] === "added") {
        $message = "Student registered successfully!";
        $messageType = "success";

    } elseif ($_GET["status"] === "updated") {
        $message = "Student updated successfully!";
        $messageType = "success";

    } elseif ($_GET["status"] === "deleted") {
        $message = "Student deleted successfully!";
        $messageType = "success";
    }
}

// Search students
$search = trim($_GET["search"] ?? "");

if ($search !== "") {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM students
         WHERE name LIKE ? OR email LIKE ? OR course LIKE ?
         ORDER BY id DESC"
    );

    $keyword = "%" . $search . "%";

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $keyword,
        $keyword,
        $keyword
    );

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

} else {

    $result = mysqli_query(
        $conn,
        "SELECT * FROM students ORDER BY id DESC"
    );
}

if (!$result) {
    die("Unable to load student records.");
}

// Total student count
$totalResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM students"
);

$totalStudents = mysqli_fetch_assoc($totalResult)["total"];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Registration System</title>

    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>

<body>

<div class="container">

    <header>
        <h1>Student Registration System</h1>
        <p>Manage student records efficiently</p>

        <p>
            Welcome,
            <?= htmlspecialchars($_SESSION["admin_username"]) ?>!
        </p>
         <a href="activity_logs.php">View Activity Logs</a>
         <a href="analytics.php">Dashboard Analytics</a>
        <a href="logout.php">Logout</a>
    </header>

    <!-- Total Students -->
    <section class="card stats">
        <h2>Total Registered Students</h2>
        <h1><?= (int)$totalStudents ?></h1>
    </section>

    <!-- Messages -->
    <?php if ($message !== ""): ?>
        <p class="message <?= htmlspecialchars($messageType) ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <!-- Registration Form -->
    <section class="card">

        <h2>Register New Student</h2>

        <form method="POST" id="studentForm">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>"
            >

            <label>Student Name</label>
            <input
                type="text"
                name="name"
                placeholder="Enter student name"
                maxlength="50"
                required
            >

            <label>Email Address</label>
            <input
                type="email"
                name="email"
                placeholder="Enter email address"
                maxlength="100"
                required
            >

            <label>Course</label>
            <select name="course" required>
                <option value="">Select Course</option>
                <option value="B.Sc Computer Science">B.Sc Computer Science</option>
                <option value="BCA">BCA</option>
                <option value="B.Com">B.Com</option>
                <option value="BBA">BBA</option>
                <option value="B.Sc Mathematics">B.Sc Mathematics</option>
            </select>

            <label>Age</label>
            <input
                type="number"
                name="age"
                min="16"
                max="21"
                placeholder="Enter age"
                required
            >

            <button type="submit" name="register">
                Register Student
            </button>

        </form>

    </section>

    <!-- Student Records -->
    <section class="card">

        <h2>Registered Students</h2>

        <!-- Search Form -->
        <form method="GET" class="search-form">

            <input
                type="search"
                name="search"
                placeholder="Search by name, email or course..."
                value="<?= htmlspecialchars($search) ?>"
            >

            <button type="submit">Search</button>

            <a href="index.php" class="reset-button">Reset</a>

        </form>

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Age</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (mysqli_num_rows($result) > 0): ?>

                        <?php while ($student = mysqli_fetch_assoc($result)): ?>

                            <tr>

                                <td><?= (int)$student["id"] ?></td>

                                <td>
                                    <?= htmlspecialchars($student["name"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($student["email"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($student["course"]) ?>
                                </td>

                                <td>
                                    <?= (int)$student["age"] ?>
                                </td>

                                <td class="actions">

                                    <a
                                        class="edit-button"
                                        href="edit_student.php?id=<?= (int)$student["id"] ?>"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="delete_student.php"
                                        class="delete-form"
                                        onsubmit="return confirm('Are you sure you want to delete this student?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$student["id"] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="delete-button"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6">No student records found.</td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</div>

</body>
</html>

<?php
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    mysqli_stmt_close($stmt);
}
?>
