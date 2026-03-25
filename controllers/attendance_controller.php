<?php
require_once '../config/database.php';
require_once '../models/Attendance.php';

$database = new Database();
$db = $database->getConnection();
$attendance = new Attendance($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'mark') {
        $date = $_POST['date'];
        $success = true;
        
        foreach ($_POST['students'] as $student_id => $status) {
            if (!$attendance->mark($student_id, $date, $status, '')) {
                $success = false;
            }
        }
        
        header('Location: ../views/mark_attendance.php?msg=' . ($success ? 'marked' : 'error'));
        exit;
    }

    if ($action === 'update_status') {
        try {
            $query = "UPDATE attendance SET status = :status WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":status", $_POST['status']);
            $stmt->bindParam(":id", $_POST['id']);
            $result = $stmt->execute();
            
            $return_url = isset($_POST['return_url']) ? $_POST['return_url'] : '../views/history.php';
            header('Location: ../' . ($return_url ? 'views/' . $return_url : 'views/history.php') . '&msg=updated');
            exit;
        } catch (PDOException $e) {
            header('Location: ../views/history.php?msg=error');
            exit;
        }
    }

    if ($action === 'delete') {
        try {
            $query = "DELETE FROM attendance WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $_POST['id']);
            $result = $stmt->execute();
            
            $return_url = isset($_POST['return_url']) ? $_POST['return_url'] : '../views/history.php';
            header('Location: ../' . ($return_url ? 'views/' . $return_url : 'views/history.php') . '&msg=deleted');
            exit;
        } catch (PDOException $e) {
            header('Location: ../views/history.php?msg=error');
            exit;
        }
    }
}
?>
