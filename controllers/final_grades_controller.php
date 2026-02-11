<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'finalize') {
    $student_id = $_POST['student_id'];
    $q1_grade = $_POST['q1_grade'];
    $q2_grade = $_POST['q2_grade'];
    $q3_grade = $_POST['q3_grade'];
    $q4_grade = $_POST['q4_grade'];
    $raw_final_grade = $_POST['raw_final_grade'];
    $rounded_final_grade = $_POST['rounded_final_grade'];
    $remarks = $_POST['remarks'];

    $query = "INSERT INTO final_grades (student_id, q1_grade, q2_grade, q3_grade, q4_grade, raw_final_grade, rounded_final_grade, remarks)
              VALUES (:student_id, :q1_grade, :q2_grade, :q3_grade, :q4_grade, :raw_final_grade, :rounded_final_grade, :remarks)
              ON DUPLICATE KEY UPDATE q1_grade = :q1_grade2, q2_grade = :q2_grade2, q3_grade = :q3_grade2, q4_grade = :q4_grade2,
              raw_final_grade = :raw_final_grade2, rounded_final_grade = :rounded_final_grade2, remarks = :remarks2, finalized_at = CURRENT_TIMESTAMP";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':q1_grade', $q1_grade);
    $stmt->bindParam(':q2_grade', $q2_grade);
    $stmt->bindParam(':q3_grade', $q3_grade);
    $stmt->bindParam(':q4_grade', $q4_grade);
    $stmt->bindParam(':raw_final_grade', $raw_final_grade);
    $stmt->bindParam(':rounded_final_grade', $rounded_final_grade);
    $stmt->bindParam(':remarks', $remarks);
    $stmt->bindParam(':q1_grade2', $q1_grade);
    $stmt->bindParam(':q2_grade2', $q2_grade);
    $stmt->bindParam(':q3_grade2', $q3_grade);
    $stmt->bindParam(':q4_grade2', $q4_grade);
    $stmt->bindParam(':raw_final_grade2', $raw_final_grade);
    $stmt->bindParam(':rounded_final_grade2', $rounded_final_grade);
    $stmt->bindParam(':remarks2', $remarks);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->errorInfo()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
