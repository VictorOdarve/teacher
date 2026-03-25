<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $student_id = $_POST['student_id'];
    $quarter = $_POST['quarter'];
    $grade_type = $_POST['grade_type'];
    $scores = $_POST['scores'];
    $total_score = $_POST['total_score'];

    $query = "INSERT INTO grades (student_id, quarter, grade_type, scores, total_score)
              VALUES (:student_id, :quarter, :grade_type, :scores, :total_score)
              ON DUPLICATE KEY UPDATE scores = :scores2, total_score = :total_score2";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':quarter', $quarter);
    $stmt->bindParam(':grade_type', $grade_type);
    $stmt->bindParam(':scores', $scores);
    $stmt->bindParam(':total_score', $total_score);
    $stmt->bindParam(':scores2', $scores);
    $stmt->bindParam(':total_score2', $total_score);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->errorInfo()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
