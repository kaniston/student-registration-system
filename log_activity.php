
<?php
function logActivity($action, $studentId) {
    try {
        require_once __DIR__ . '/vendor/autoload.php';

        $client = new MongoDB\Client("mongodb://localhost:27017");

        $collection = $client
            ->selectDatabase("student_activity")
            ->selectCollection("logs");

        $collection->insertOne([
            "action" => $action,
            "student_id" => (int)$studentId,
            "created_at" => new MongoDB\BSON\UTCDateTime()
        ]);

    } catch (Throwable $e) {
        error_log("MongoDB activity log failed: " . $e->getMessage());
    }
}
?>
