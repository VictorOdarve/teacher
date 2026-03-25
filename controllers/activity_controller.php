<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'save') {
        try {
            $query = "INSERT INTO class_activities (class_id, activity_date, activity_text) VALUES (:class_id, :activity_date, :activity_text) ON DUPLICATE KEY UPDATE activity_text = :activity_text";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":class_id", $_POST['class_id']);
            $stmt->bindParam(":activity_date", $_POST['activity_date']);
            $stmt->bindParam(":activity_text", $_POST['activity_text']);
            $result = $stmt->execute();
            
            header('Location: ../views/calendar.php?class_id=' . $_POST['class_id'] . '&month=' . $_POST['month'] . '&year=' . $_POST['year'] . '&msg=saved');
            exit;
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>
