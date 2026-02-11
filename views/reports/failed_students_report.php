<?php
require_once '../../config/database.php';
require_once '../../models/Attendance.php';

$database = new Database();
$db = $database->getConnection();
$attendance = new Attendance($db);

$section = isset($_GET['section']) ? $_GET['section'] : '';
$subject = isset($_GET['subject']) ? $_GET['subject'] : '';

$query = "SELECT fg.student_id, cs.first_name, cs.last_name, c.grade_level, c.section, c.subject, fg.rounded_final_grade, fg.remarks
          FROM final_grades fg
          JOIN class_students cs ON fg.student_id = cs.id
          JOIN classes c ON cs.class_id = c.id
          WHERE fg.remarks = 'Failed'";

$conditions = [];
$params = [];
if ($section) {
    $conditions[] = "c.section = :section";
    $params[':section'] = $section;
}
if ($subject) {
    $conditions[] = "c.subject = :subject";
    $params[':subject'] = $subject;
}
if ($conditions) {
    $query .= " AND " . implode(" AND ", $conditions);
}
$query .= " ORDER BY cs.last_name, cs.first_name";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get grade level and section for header (assume all same if filtered)
$grade_level = '';
$sec = '';
if ($students) {
    $grade_level = $students[0]['grade_level'];
    $sec = $students[0]['section'];
}
?>

<h2>FAILED STUDENTS REPORT</h2>
<p>--------------------------------------------------</p>
<p>School Year: 2025–2026</p>
<p>Grade Level: <?php echo htmlspecialchars($grade_level . ' – ' . $sec); ?></p>

<table>
    <thead>
        <tr>
            <th>Student Name</th>
            <th>Subject</th>
            <th>Final Grade</th>
            <th>Remarks</th>
            <th>Attendance %</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($students as $student): ?>
            <?php
            // Calculate attendance %
            $student_id = $student['student_id'];
            $attendance_stmt = $db->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present FROM attendance WHERE student_id = :student_id");
            $attendance_stmt->bindParam(':student_id', $student_id);
            $attendance_stmt->execute();
            $att_data = $attendance_stmt->fetch(PDO::FETCH_ASSOC);
            $total_days = $att_data['total'];
            $present_days = $att_data['present'];
            $attendance_percent = $total_days > 0 ? round(($present_days / $total_days) * 100, 2) : 0;

            // Format student name
            $first_initial = substr($student['first_name'], 0, 1) . '.';
            $student_name = $first_initial . ' ' . $student['last_name'];
            ?>
            <tr>
                <td><?php echo htmlspecialchars($student_name); ?></td>
                <td><?php echo htmlspecialchars($student['subject']); ?></td>
                <td><?php echo htmlspecialchars($student['rounded_final_grade']); ?></td>
                <td><?php echo htmlspecialchars($student['remarks']); ?></td>
                <td><?php echo $attendance_percent; ?>%</td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
