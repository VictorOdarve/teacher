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
            <?php
            // Group grades by quarter
            $grouped_grades = [];
            foreach ($grades_records as $record) {
                $quarter = $record['quarter'];
                $type = $record['grade_type'];
                $grouped_grades[$quarter][$type] = $record;
            }
            ?>
            <table>
                <thead>
                    <tr>
                        <th>Quarter</th>
                        <th>Type</th>
                        <th>Scores</th>
                        <th>Total</th>
                        <th>Overall</th>
                        <th>Calculated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grouped_grades as $quarter => $types): ?>
                        <?php
                        $grade_types = ['ww', 'pt', 'as'];
                        $first = true;
                        foreach ($grade_types as $type):
                            $record = isset($types[$type]) ? $types[$type] : null;
                        ?>
                        <tr>
                            <?php if ($first): ?>
                                <td rowspan="<?php echo count($grade_types); ?>">Quarter <?php echo $quarter; ?></td>
                                <?php $first = false; ?>
                            <?php endif; ?>
                            <td>
                                <?php
                                if ($type == 'ww') echo 'Written Works';
                                elseif ($type == 'pt') echo 'Performance Tasks';
                                else echo 'Assessment';
                                ?>
                            </td>
                            <td><?php echo $record ? implode(' | ', array_filter(json_decode($record['scores'], true), function($v) { return !empty($v); })) : ''; ?></td>
                            <td><?php echo $record && $record['total_score'] ? implode(' | ', array_filter(json_decode($record['total_score'], true), function($v) { return !empty($v); })) : ''; ?></td>
                            <td>
                                <?php
                                if ($record) {
                                    $scores_sum = array_sum(json_decode($record['scores'], true));
                                    $totals_sum = $record['total_score'] ? array_sum(json_decode($record['total_score'], true)) : 0;
                                    if ($totals_sum > 0) {
                                        echo $scores_sum . '/' . $totals_sum;
                                    } else {
                                        echo '-';
                                    }
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if ($record && isset($totals_sum) && $totals_sum > 0) {
                                    $percentage = ($scores_sum / $totals_sum) * 100;
                                    echo round($percentage, 2) . '%';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <?php if ($type == 'ww'): ?>
                                <td rowspan="<?php echo count($grade_types); ?>">
                                    <button class="btn" onclick="calculateAverage(<?php echo $quarter; ?>)">Calculate</button>
                                    <div id="quarter-grade-<?php echo $quarter; ?>" style="margin-top: 10px; font-weight: bold;"></div>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No grades found.</p>
        <?php endif; ?>

        <div style="margin-top: 20px;">
            <a href="students.php" class="btn">Back to Students</a>
            <button class="btn" style="float: right;" onclick="generateFinalGrade()">Generate Final Grade</button>
        </div>

        <!-- Final Grade Modal -->
        <div id="final-grade-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
            <input type="hidden" id="modal-student-id" value="<?php echo $student_id; ?>">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; width: 90%; max-width: 900px; font-family: monospace; font-size: 16px;">
                <h2 style="text-align: center;">FINAL GRADE PANEL</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr><td>Student Name :</td><td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td></tr>
                    <tr><td>Grade Level :</td><td><?php echo htmlspecialchars($student['grade_level'] . ' – ' . $student['section']); ?></td></tr>
                    <tr><td>Subject :</td><td>Mathematics</td></tr>
                    <tr><td>School Year :</td><td>2025 – 2026</td></tr>
                </table>
                <h3 style="text-align: center;">QUARTER GRADES</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <th>Quarter 1</th>
                        <th>Quarter 2</th>
                        <th>Quarter 3</th>
                        <th>Quarter 4</th>
                    </tr>
                    <tr>
                        <td id="q1-grade">-</td>
                        <td id="q2-grade">-</td>
                        <td id="q3-grade">-</td>
                        <td id="q4-grade">-</td>
                    </tr>
                </table>
                <div style="margin-top: 20px;">
                    <strong>Computation:</strong><br>
                    (Q1 + Q2 + Q3 + Q4) / 4<br><br>
                    <strong>Raw Final Grade:</strong> <span id="raw-final" style="font-size: 28px; font-weight: bold;">-</span><br>
                    <strong>Rounded Final Grade:</strong> <span id="rounded-final" style="font-size: 28px; font-weight: bold;">-</span><br><br>
                    <strong>Remarks:</strong> <span id="remarks">-</span>
                </div>
                <div style="text-align: center; margin-top: 20px;">
                    <button class="btn" onclick="computeFinalGrade()">Compute Final Grade</button>
                    <button class="btn" onclick="finalizeGrade()">Finalize</button>
                    <button class="btn" onclick="editGrade()">Edit</button>
                </div>
                <button style="position: absolute; top: 10px; right: 10px;" onclick="closeModal()">X</button>
            </div>
        </div>
    <?php else: ?>
        <p>Student not found.</p>
        <a href="students.php" class="btn">Back to Students</a>
    <?php endif; ?>
</div>

<script src="../assets/js/script.js"></script>

<?php include '../includes/footer.php'; ?>
