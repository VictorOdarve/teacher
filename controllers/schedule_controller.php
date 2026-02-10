<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'create') {
        try {
            $query = "INSERT INTO schedules (class_id, day_of_week, start_time, end_time, room) VALUES (:class_id, :day_of_week, :start_time, :end_time, :room)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":class_id", $_POST['class_id']);
            $stmt->bindParam(":day_of_week", $_POST['day_of_week']);
            $stmt->bindParam(":start_time", $_POST['start_time']);
            $stmt->bindParam(":end_time", $_POST['end_time']);
            $stmt->bindParam(":room", $_POST['room']);
            $result = $stmt->execute();
            
            header('Location: ../views/classes.php?msg=' . ($result ? 'schedule_added' : 'error'));
            exit;
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    if ($action === 'update') {
        try {
            $query = "UPDATE schedules SET day_of_week = :day_of_week, start_time = :start_time, end_time = :end_time, room = :room WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $_POST['id']);
            $stmt->bindParam(":day_of_week", $_POST['day_of_week']);
            $stmt->bindParam(":start_time", $_POST['start_time']);
            $stmt->bindParam(":end_time", $_POST['end_time']);
            $stmt->bindParam(":room", $_POST['room']);
            $result = $stmt->execute();
            
            header('Location: ../views/edit_class.php?class_id=' . $_POST['class_id'] . '&msg=updated');
            exit;
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    if ($action === 'delete') {
        try {
            $query = "DELETE FROM schedules WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $_POST['id']);
            $result = $stmt->execute();
            
            header('Location: ../views/edit_class.php?class_id=' . $_POST['class_id'] . '&msg=' . ($result ? 'deleted' : 'error'));
            exit;
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>
