
<?php
require_once "auth.php";

$logs = [];
$error = "";

try {
    require_once __DIR__ . "/vendor/autoload.php";

    $client = new MongoDB\Client("mongodb://localhost:27017");

    $collection = $client
        ->selectDatabase("student_activity")
        ->selectCollection("logs");

    // Get the latest 100 activity records
    $logs = $collection->find(
        [],
        [
            "sort" => ["created_at" => -1],
            "limit" => 100
        ]
    )->toArray();

} catch (Throwable $e) {
    error_log("Activity logs error: " . $e->getMessage());
    $error = "Unable to load activity logs. Check MongoDB connection.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Activity Logs</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        .log-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 5px;
            background: #e8eefc;
            color: #234a9b;
            font-size: 13px;
            font-weight: bold;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
        }

        .table-wrapper {
            overflow-x: auto;
        }
    </style>
</head>

<body>

<div class="container">

    <section class="card">

        <h1>Student Activity Logs</h1>

        <p>View the latest student activities recorded in MongoDB.</p>

        <a href="index.php" class="reset-button back-link">
            Back to Dashboard
        </a>

        <?php if ($error !== ""): ?>

            <p class="message error">
                <?= htmlspecialchars($error) ?>
            </p>

        <?php else: ?>

            <h3>Total Activities: <?= count($logs) ?></h3>

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Student ID</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (count($logs) > 0): ?>

                            <?php foreach ($logs as $log): ?>

                                <?php
                                $action = $log["action"] ?? "unknown";
                                $studentId = $log["student_id"] ?? "N/A";

                                $date = "Unknown";

                                if (isset($log["created_at"])) {
                                    $date = $log["created_at"]
                                        ->toDateTime()
                                        ->setTimezone(new DateTimeZone(date_default_timezone_get()))
                                        ->format("d-m-Y h:i:s A");
                                }
                                ?>

                                <tr>
                                    <td>
                                        <span class="log-badge">
                                            <?= htmlspecialchars($action) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars((string)$studentId) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($date) ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="3">No activity records found.</td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>

</div>

</body>
</html>
