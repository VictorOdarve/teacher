<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'create') {
        try {
            $query = "INSERT INTO class_students (class_id, first_name, last_name, middle_name, gender, student_id) VALUES (:class_id, :first_name, :last_name, :middle_name, :gender, :student_id)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":class_id", $_POST['class_id']);
            $stmt->bindParam(":first_name", $_POST['first_name']);
            $stmt->bindParam(":last_name", $_POST['last_name']);
            $stmt->bindParam(":middle_name", $_POST['middle_name']);
            $stmt->bindParam(":gender", $_POST['gender']);
            $stmt->bindParam(":student_id", $_POST['student_id']);
            $result = $stmt->execute();
            
            header('Location: ../views/classes.php?msg=' . ($result ? 'student_added' : 'error'));
            exit;
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    if ($action === 'update') {
        try {
            $query = "UPDATE class_students SET first_name = :first_name, last_name = :last_name, middle_name = :middle_name, gender = :gender, student_id = :student_id WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $_POST['id']);
            $stmt->bindParam(":first_name", $_POST['first_name']);
            $stmt->bindParam(":last_name", $_POST['last_name']);
            $stmt->bindParam(":middle_name", $_POST['middle_name']);
            $stmt->bindParam(":gender", $_POST['gender']);
            $stmt->bindParam(":student_id", $_POST['student_id']);
            $result = $stmt->execute();
            
            header('Location: ../views/edit_class.php?class_id=' . $_POST['class_id'] . '&msg=updated');
            exit;
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    if ($action === 'delete') {
        try {
            $query = "DELETE FROM class_students WHERE id = :id";
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
