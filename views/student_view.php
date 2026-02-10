<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$student_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Get student info
$student_query = "SELECT cs.*, c.grade_level, c.section FROM class_students cs 
                  JOIN classes c ON cs.class_id = c.id 
                  WHERE cs.id = :id";
$student_stmt = $db->prepare($student_query);
$student_stmt->bindParam(':id', $student_id);
$student_stmt->execute();
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);

// Get attendance records
$attendance_query = "SELECT * FROM attendance WHERE student_id = :id ORDER BY date DESC";
$attendance_stmt = $db->prepare($attendance_query);
$attendance_stmt->bindParam(':id', $student_id);
$attendance_stmt->execute();
$attendance_records = $attendance_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get grades
$grades_query = "SELECT * FROM grades WHERE student_id = :id ORDER BY quarter, grade_type";
$grades_stmt = $db->prepare($grades_query);
$grades_stmt->bindParam(':id', $student_id);
$grades_stmt->execute();
$grades_records = $grades_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "View Student";
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <h1>Student Information</h1>
    
    <?php if ($student): ?>
        <div style="margin-bottom: 30px;">
            <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
            <p><strong>Grade Level:</strong> <?php echo htmlspecialchars($student['grade_level']); ?></p>
            <p><strong>Section:</strong> <?php echo htmlspecialchars($student['section']); ?></p>
        </div>

        <h2>Attendance Records</h2>
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
            <p>No attendance records found.</p>
        <?php endif; ?>

        <h2 style="margin-top: 40px;">Grades</h2>
        <?php if (count($grades_records) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Quarter</th>
                        <th>Type</th>
                        <th>Scores</th>
                        <th>Total</th>
                        <th>Calculated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grades_records as $record): ?>
                    <tr>
                        <td>Quarter <?php echo $record['quarter']; ?></td>
                        <td>
                            <?php
                            if ($record['grade_type'] == 'ww') echo 'Written Works';
                            elseif ($record['grade_type'] == 'ww_total') echo 'Written Works (Total)';
                            elseif ($record['grade_type'] == 'pt') echo 'Performance Tasks';
                            else echo 'Assessment';
                            ?>
                        </td>
                        <td><?php echo implode(', ', json_decode($record['scores'], true)); ?></td>
                        <td><?php echo array_sum(json_decode($record['scores'], true)); ?></td>
                        <td>
                            <?php
                            $total = array_sum(json_decode($record['scores'], true));
                            if ($record['grade_type'] == 'ww') {
                                $calculated = $total / 5;
                                echo number_format($calculated, 2);
                            } elseif ($record['grade_type'] == 'ww_total') {
                                echo '-';
                            } elseif ($record['grade_type'] == 'pt') {
                                $calculated = $total / 3;
                                echo number_format($calculated, 2);
                            } else {
                                $calculated = $total / 2;
                                echo number_format($calculated, 2);
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No grades found.</p>
        <?php endif; ?>

        <div style="margin-top: 20px;">
            <a href="students.php" class="btn">Back to Students</a>
        </div>
    <?php else: ?>
        <p>Student not found.</p>
        <a href="students.php" class="btn">Back to Students</a>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
