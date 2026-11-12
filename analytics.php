
<?php
require_once "auth.php";
require_once "db.php";

$totalStudents = 0;
$courseLabels = [];
$courseCounts = [];

$registered = 0;
$updated = 0;
$deleted = 0;

$totalResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM students");
if ($totalResult) {
    $totalStudents = (int) mysqli_fetch_assoc($totalResult)["total"];
}

// Students grouped by course
$courseResult = mysqli_query(
    $conn,
    "SELECT course, COUNT(*) AS total
     FROM students
     GROUP BY course
     ORDER BY total DESC"
);

if ($courseResult) {
    while ($row = mysqli_fetch_assoc($courseResult)) {
        $courseLabels[] = $row["course"];
        $courseCounts[] = (int) $row["total"];
    }
}

// MongoDB activity summary
try {
    require_once __DIR__ . "/vendor/autoload.php";

    $client = new MongoDB\Client("mongodb://localhost:27017");
    $collection = $client
        ->selectDatabase("student_activity")
        ->selectCollection("logs");

    $registered = $collection->countDocuments([
        "action" => "student_registered"
    ]);

    $updated = $collection->countDocuments([
        "action" => "student_updated"
    ]);

    $deleted = $collection->countDocuments([
        "action" => "student_deleted"
    ]);

} catch (Throwable $e) {
    error_log("Analytics MongoDB error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analytics</title>
    <link rel="stylesheet" href="css/style.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .analytics-card {
            background: #f4f6fb;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .analytics-card h2 {
            font-size: 30px;
            margin: 10px 0;
        }

        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            min-height: 300px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

<div class="container">

    <section class="card">
        <h1>Dashboard Analytics</h1>
        <p>Student statistics and activity overview</p>

        <a href="index.php" class="reset-button back-link">
            Back to Dashboard
        </a>

        <div class="analytics-grid">

            <div class="analytics-card">
                <h3>Total Students</h3>
                <h2><?= $totalStudents ?></h2>
            </div>

            <div class="analytics-card">
                <h3>Registrations</h3>
                <h2><?= $registered ?></h2>
            </div>

            <div class="analytics-card">
                <h3>Updates</h3>
                <h2><?= $updated ?></h2>
            </div>

            <div class="analytics-card">
                <h3>Deletions</h3>
                <h2><?= $deleted ?></h2>
            </div>

        </div>

        <div class="chart-container">
            <h2>Students by Course</h2>
            <canvas id="courseChart"></canvas>
        </div>

        <div class="chart-container">
            <h2>MongoDB Activity Summary</h2>
            <canvas id="activityChart"></canvas>
        </div>

    </section>

</div>

<script>
const courseLabels = <?= json_encode($courseLabels) ?>;
const courseCounts = <?= json_encode($courseCounts) ?>;

new Chart(document.getElementById("courseChart"), {
    type: "bar",
    data: {
        labels: courseLabels,
        datasets: [{
            label: "Number of Students",
            data: courseCounts,
            backgroundColor: "#4f46e5"
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

new Chart(document.getElementById("activityChart"), {
    type: "doughnut",
    data: {
        labels: ["Registered", "Updated", "Deleted"],
        datasets: [{
            data: [
                <?= $registered ?>,
                <?= $updated ?>,
                <?= $deleted ?>
            ],
            backgroundColor: ["#16a34a", "#2563eb", "#dc2626"]
        }]
    },
    options: {
        responsive: true
    }
});
</script>

</body>
</html>
