<?php
require_once '../config/database.php';
require_once '../models/ClassModel.php';
require_once '../models/Attendance.php';

$database = new Database();
$db = $database->getConnection();

$classModel = new ClassModel($db);
$attendanceModel = new Attendance($db);

// Get selected class_id from GET
$selected_class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;

// Get all classes for selector
$classes = $classModel->getAll()->fetchAll(PDO::FETCH_ASSOC);

// Today's schedule: classes with schedules for today
$today = date('l'); // e.g., Monday
$today_schedule = $classModel->getClassesWithSchedules($today)->fetchAll(PDO::FETCH_ASSOC);

// If class selected, get stats
$class_stats = null;
$recent_activities = null;
$students_attention = [];

if ($selected_class_id) {
    // Class Quick Stats
    $total_students = $classModel->getStudentsCount($selected_class_id);
    $today_date = date('Y-m-d');
    $attendance_stats = $attendanceModel->getAttendanceStatsForClass($selected_class_id, $today_date);
    $present = $attendance_stats['present'] ?? 0;
    $absent = $attendance_stats['absent'] ?? 0;
    $late = $attendance_stats['late'] ?? 0;

    $class_stats = [
        'total_students' => $total_students,
        'present' => $present,
        'absent' => $absent,
        'late' => $late
    ];

    // Recent Class Activities
    $recent_activities = $classModel->getRecentActivities($selected_class_id, 5)->fetchAll(PDO::FETCH_ASSOC);

    // Students Requiring Attention
    // 1. Students with 3+ absences this quarter (assume current quarter, but for simplicity, all time)
    $query_absences = "SELECT cs.id, CONCAT(cs.first_name, ' ', cs.last_name) as name, COUNT(a.id) as absences
                       FROM class_students cs
                       LEFT JOIN attendance a ON cs.id = a.student_id AND a.status = 'absent'
                       WHERE cs.class_id = :class_id
                       GROUP BY cs.id
                       HAVING absences >= 3";
    $stmt_absences = $db->prepare($query_absences);
    $stmt_absences->bindParam(":class_id", $selected_class_id);
    $stmt_absences->execute();
    $absences_students = $stmt_absences->fetchAll(PDO::FETCH_ASSOC);

    // 2. Students with failing grade in final_grades (assume <75)
    $query_failing = "SELECT cs.id, CONCAT(cs.first_name, ' ', cs.last_name) as name, fg.rounded_final_grade
                      FROM class_students cs
                      JOIN final_grades fg ON cs.id = fg.student_id
                      WHERE cs.class_id = :class_id AND fg.rounded_final_grade < 75";
    $stmt_failing = $db->prepare($query_failing);
    $stmt_failing->bindParam(":class_id", $selected_class_id);
    $stmt_failing->execute();
    $failing_students = $stmt_failing->fetchAll(PDO::FETCH_ASSOC);

    // 3. Students with missing graded work (no grades in current quarter, assume quarter 4)
    $current_quarter = 4; // Assume
    $query_missing = "SELECT cs.id, CONCAT(cs.first_name, ' ', cs.last_name) as name
                      FROM class_students cs
                      LEFT JOIN grades g ON cs.id = g.student_id AND g.quarter = :quarter
                      WHERE cs.class_id = :class_id AND g.id IS NULL";
    $stmt_missing = $db->prepare($query_missing);
    $stmt_missing->bindParam(":class_id", $selected_class_id);
    $stmt_missing->bindParam(":quarter", $current_quarter);
    $stmt_missing->execute();
    $missing_students = $stmt_missing->fetchAll(PDO::FETCH_ASSOC);

    // Combine into students_attention
    foreach ($absences_students as $student) {
        $students_attention[] = $student['name'] . ' - 3+ Absences (' . $student['absences'] . ')';
    }
    foreach ($failing_students as $student) {
        $students_attention[] = $student['name'] . ' - Failing Grade (' . $student['rounded_final_grade'] . ')';
    }
    foreach ($missing_students as $student) {
        $students_attention[] = $student['name'] . ' - Missing Graded Work';
    }
}
?>
