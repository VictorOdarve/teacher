<?php
require_once '../config/database.php';
require_once '../models/ClassModel.php';

$database = new Database();
$db = $database->getConnection();
$classModel = new ClassModel($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'create') {
        $result = $classModel->create($_POST['grade_level'], $_POST['section'], $_POST['subject'], $_POST['subject_code']);
        header('Location: ../views/classes.php?msg=' . ($result ? 'added' : 'error'));
        exit;
    }

    if ($action === 'update') {
        $result = $classModel->update($_POST['id'], $_POST['grade_level'], $_POST['section'], $_POST['subject'], $_POST['subject_code']);
        header('Location: ../views/classes.php?msg=' . ($result ? 'updated' : 'error'));
        exit;
    }

    if ($action === 'delete') {
        $result = $classModel->delete($_POST['id']);
        header('Location: ../views/classes.php?msg=' . ($result ? 'deleted' : 'error'));
        exit;
    }
}
?>
