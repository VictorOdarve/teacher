<?php
require_once '../config/database.php';
require_once '../models/Student.php';

$database = new Database();
$db = $database->getConnection();
$student = new Student($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'create') {
        $result = $student->create($_POST['student_id'], $_POST['name'], $_POST['email'], $_POST['phone']);
        header('Location: ../views/students.php?msg=' . ($result ? 'added' : 'error'));
        exit;
    }

    if ($action === 'update') {
        $result = $student->update($_POST['id'], $_POST['student_id'], $_POST['name'], $_POST['email'], $_POST['phone']);
        header('Location: ../views/students.php?msg=' . ($result ? 'updated' : 'error'));
        exit;
    }

    if ($action === 'delete') {
        $result = $student->delete($_POST['id']);
        header('Location: ../views/students.php?msg=' . ($result ? 'deleted' : 'error'));
        exit;
    }
}
?>
