
<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");

    $db = $client->selectDatabase("student_activity");
    $collection = $db->selectCollection("logs");

    $result = $collection->insertOne([
        "action" => "connection_test",
        "message" => "PHP connected to MongoDB successfully",
        "created_at" => new MongoDB\BSON\UTCDateTime()
    ]);

    echo "SUCCESS! PHP connected to MongoDB.";
    echo "<br>Inserted ID: " . $result->getInsertedId();

} catch (Throwable $e) {
    echo "MongoDB connection failed: " .
         htmlspecialchars($e->getMessage());
}
?>
