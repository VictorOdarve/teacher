<?php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../index.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$page_title = "My Performance";
include '../includes/header.php';

$student_profile_id = isset($_SESSION['student_profile_id']) ? (int)$_SESSION['student_profile_id'] : 0;
$student = null;
$attendance_summary = [
    'total' => 0,
    'present' => 0,
    'late' => 0,
    'excused' => 0,
    'absent' => 0
];
$attendance_records = [];
$grades_records = [];
$final_grade = null;

if ($student_profile_id > 0) {
    $student_query = "SELECT cs.*, c.grade_level, c.section, c.subject
                      FROM class_students cs
                      JOIN classes c ON cs.class_id = c.id
                      WHERE cs.id = :id
                      LIMIT 1";
    $student_stmt = $db->prepare($student_query);
    $student_stmt->bindParam(':id', $student_profile_id, PDO::PARAM_INT);
    $student_stmt->execute();
    $student = $student_stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $attendance_summary_query = "SELECT
                                        COUNT(*) AS total,
                                        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present,
                                        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) AS late,
                                        SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) AS excused,
                                        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent
                                     FROM attendance
                                     WHERE student_id = :student_id";
        $attendance_summary_stmt = $db->prepare($attendance_summary_query);
        $attendance_summary_stmt->bindParam(':student_id', $student_profile_id, PDO::PARAM_INT);
        $attendance_summary_stmt->execute();
        $attendance_summary_row = $attendance_summary_stmt->fetch(PDO::FETCH_ASSOC);

        if ($attendance_summary_row) {
            $attendance_summary['total'] = (int)$attendance_summary_row['total'];
            $attendance_summary['present'] = (int)$attendance_summary_row['present'];
            $attendance_summary['late'] = (int)$attendance_summary_row['late'];
            $attendance_summary['excused'] = (int)$attendance_summary_row['excused'];
            $attendance_summary['absent'] = (int)$attendance_summary_row['absent'];
        }

        $attendance_records_query = "SELECT date, status
                                     FROM attendance
                                     WHERE student_id = :student_id
                                     ORDER BY date DESC
                                     LIMIT 20";
        $attendance_records_stmt = $db->prepare($attendance_records_query);
        $attendance_records_stmt->bindParam(':student_id', $student_profile_id, PDO::PARAM_INT);
        $attendance_records_stmt->execute();
        $attendance_records = $attendance_records_stmt->fetchAll(PDO::FETCH_ASSOC);

        $grades_query = "SELECT quarter, grade_type, scores, total_score
                         FROM grades
                         WHERE student_id = :student_id
                         ORDER BY quarter ASC, FIELD(grade_type, 'ww', 'pt', 'as')";
        $grades_stmt = $db->prepare($grades_query);
        $grades_stmt->bindParam(':student_id', $student_profile_id, PDO::PARAM_INT);
        $grades_stmt->execute();
        $grades_records = $grades_stmt->fetchAll(PDO::FETCH_ASSOC);

        $final_grade_query = "SELECT q1_grade, q2_grade, q3_grade, q4_grade, raw_final_grade, rounded_final_grade, remarks
                              FROM final_grades
                              WHERE student_id = :student_id
                              LIMIT 1";
        $final_grade_stmt = $db->prepare($final_grade_query);
        $final_grade_stmt->bindParam(':student_id', $student_profile_id, PDO::PARAM_INT);
        $final_grade_stmt->execute();
        $final_grade = $final_grade_stmt->fetch(PDO::FETCH_ASSOC);
    }
}

function decodeScoreArray($raw) {
    $decoded = json_decode((string)$raw, true);
    if (!is_array($decoded)) {
        return [];
    }

    return array_values(array_filter($decoded, function ($value) {
        return $value !== '' && $value !== null;
    }));
}

include '../includes/nav.php';
?>

<div class="card">
    <h1>My Performance</h1>

    <?php if (!$student): ?>
        <p>Your student profile is not linked yet. Ask your teacher/admin to set your `student_profile_id` in the `users` table.</p>
    <?php else: ?>
        <div style="margin-bottom: 24px;">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
            <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?></p>
            <p><strong>Class:</strong> <?php echo htmlspecialchars($student['grade_level'] . ' - ' . $student['section']); ?></p>
            <p><strong>Subject:</strong> <?php echo htmlspecialchars($student['subject']); ?></p>
        </div>

        <h2>Attendance Summary</h2>
        <div class="stats">
            <div class="stat-card">
                <h3>Total</h3>
                <div class="number"><?php echo $attendance_summary['total']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Present</h3>
                <div class="number"><?php echo $attendance_summary['present']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Late</h3>
                <div class="number"><?php echo $attendance_summary['late']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Excused</h3>
                <div class="number"><?php echo $attendance_summary['excused']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Absent</h3>
                <div class="number"><?php echo $attendance_summary['absent']; ?></div>
            </div>
        </div>

        <h2 style="margin-top: 30px;">Recent Attendance</h2>
        <?php if (count($attendance_records) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance_records as $record): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                            <td><?php echo ucfirst($record['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No attendance records yet.</p>
        <?php endif; ?>

        <h2 style="margin-top: 30px;">Grades</h2>
        <?php if (count($grades_records) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Quarter</th>
                        <th>Type</th>
                        <th>Score</th>
                        <th>Percent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grades_records as $grade): ?>
                        <?php
                        $scores = decodeScoreArray($grade['scores']);
                        $totals = decodeScoreArray($grade['total_score']);
                        $score_sum = array_sum($scores);
                        $total_sum = array_sum($totals);
                        $percent = $total_sum > 0 ? round(($score_sum / $total_sum) * 100, 2) . '%' : '-';

                        $grade_type_label = $grade['grade_type'] === 'ww'
                            ? 'Written Works'
                            : ($grade['grade_type'] === 'pt' ? 'Performance Task' : 'Assessment');
                        ?>
                        <tr>
                            <td><?php echo (int)$grade['quarter']; ?></td>
                            <td><?php echo htmlspecialchars($grade_type_label); ?></td>
                            <td><?php echo $total_sum > 0 ? $score_sum . '/' . $total_sum : '-'; ?></td>
                            <td><?php echo $percent; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No grades yet.</p>
        <?php endif; ?>

        <h2 style="margin-top: 30px;">Final Grade</h2>
        <?php if ($final_grade): ?>
            <table>
                <thead>
                    <tr>
                        <th>Q1</th>
                        <th>Q2</th>
                        <th>Q3</th>
                        <th>Q4</th>
                        <th>Raw</th>
                        <th>Rounded</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($final_grade['q1_grade']); ?></td>
                        <td><?php echo htmlspecialchars($final_grade['q2_grade']); ?></td>
                        <td><?php echo htmlspecialchars($final_grade['q3_grade']); ?></td>
                        <td><?php echo htmlspecialchars($final_grade['q4_grade']); ?></td>
                        <td><?php echo htmlspecialchars($final_grade['raw_final_grade']); ?></td>
                        <td><?php echo htmlspecialchars($final_grade['rounded_final_grade']); ?></td>
                        <td><?php echo htmlspecialchars($final_grade['remarks']); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <p>Final grade has not been generated yet.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
