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
<?php 
        // Compute attendance stats
        $present_count = 0;
        $absent_count = 0;
        $late_count = 0;
        $excused_count = 0;
        foreach ($attendance_records as $record) {
            switch ($record['status']) {
                case 'present':
                    $present_count++;
                    break;
                case 'absent':
                    $absent_count++;
                    break;
                case 'late':
                    $late_count++;
                    break;
                case 'excused':
                    $excused_count++;
                    break;
            }
        }
        $total_days = count($attendance_records);
        $attendance_stats = [
            'present' => $present_count,
            'absent' => $absent_count,
            'late' => $late_count,
            'excused' => $excused_count,
            'total' => $total_days
        ];
        ?>
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

            <!-- Attendance Chart -->
            <h3 style="margin-top: 30px;">Attendance Summary Chart</h3>
<canvas id="attendanceChart" style="max-height: 250px; max-width: 100%;"></canvas>
            
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                const ctx = document.getElementById('attendanceChart').getContext('2d');
                const attendanceData = <?php echo json_encode($attendance_stats); ?>;
                
                if (attendanceData.total > 0) {
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Present', 'Absent', 'Late', 'Excused'],
                            datasets: [{
                                label: 'Days',
                                data: [attendanceData.present, attendanceData.absent, attendanceData.late, attendanceData.excused],
                                backgroundColor: [
                                    'rgba(75, 192, 192, 0.8)',   // Green for present
                                    'rgba(255, 99, 132, 0.8)',   // Red for absent
                                    'rgba(255, 205, 86, 0.8)',   // Yellow for late
                                    'rgba(54, 162, 235, 0.8)'    // Blue for excused
                                ],
                                borderColor: [
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(255, 205, 86, 1)',
                                    'rgba(54, 162, 235, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 5,
                                    ticks: {
                                        stepSize: 1
                                    },
                                    title: {
                                        display: true,
                                        text: 'Days',
                                        font: {
                                            size: 12
                                        }
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Status',
                                        font: {
                                            size: 12
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false,
                                },
                                title: {
                                    display: true,
                                    text: 'Attendance Distribution (Total: ' + attendanceData.total + ' days)'
                                }
                            }
                        }
                    });
                } else {
                    ctx.font = '20px Arial';
                    ctx.fillText('No attendance data', 50, 200);
                }
            </script>
        <?php else: ?>
            <p>No attendance records found.</p>
        <?php endif; ?>

        <!-- Grades Performance Line Chart -->
        <h2 style="margin-top: 40px;">Grades Performance</h2>
        <?php
        // Compute grade type averages per quarter for multi-line chart
        $quarters = ['1', '2', '3', '4'];
        $ww_data = [];
        $pt_data = [];
        $as_data = [];
        
        foreach ($quarters as $q) {
            $ww_avg = 0;
            $pt_avg = 0;
            $as_avg = 0;
            $has_data = false;
            
            foreach ($grades_records as $record) {
                if ($record['quarter'] == $q) {
                    $scores_sum = array_sum(json_decode($record['scores'], true));
                    $totals_sum = $record['total_score'] ? array_sum(json_decode($record['total_score'], true)) : 0;
                    if ($totals_sum > 0) {
                        $percent = ($scores_sum / $totals_sum) * 100;
                        switch ($record['grade_type']) {
                            case 'ww':
                                $ww_avg = $percent;
                                break;
                            case 'pt':
                                $pt_avg = $percent;
                                break;
                            case 'as':
                                $as_avg = $percent;
                                break;
                        }
                        $has_data = true;
                    }
                }
            }
            $ww_data[$q] = $has_data ? round($ww_avg, 1) : 0;
            $pt_data[$q] = $has_data ? round($pt_avg, 1) : 0;
            $as_data[$q] = $has_data ? round($as_avg, 1) : 0;
        }
        
        $grades_data = [
            'labels' => $quarters,
            'ww' => array_values($ww_data),
            'pt' => array_values($pt_data),
            'as' => array_values($as_data)
        ];
        ?>
        <canvas id="gradesChart" style="max-height: 250px; max-width: 100%; margin-bottom: 20px;"></canvas>
        <script>
            const gradesCtx = document.getElementById('gradesChart')?.getContext('2d');
            const gradesData = <?php echo json_encode($grades_data); ?>;
            if (gradesCtx) {
                new Chart(gradesCtx, {
                    type: 'line',
                    data: {
                        labels: ['Q' + gradesData.labels[0], 'Q' + gradesData.labels[1], 'Q' + gradesData.labels[2], 'Q' + gradesData.labels[3]],
                        datasets: [
                            {
                                label: 'Written Works',
                                data: gradesData.ww,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                tension: 0.4,
                                fill: false
                            },
                            {
                                label: 'Performance Tasks',
                                data: gradesData.pt,
                                borderColor: 'rgba(255, 99, 132, 1)',
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                tension: 0.4,
                                fill: false
                            },
                            {
                                label: 'Assessment',
                                data: gradesData.as,
                                borderColor: 'rgba(255, 205, 86, 1)',
                                backgroundColor: 'rgba(255, 205, 86, 0.2)',
                                tension: 0.4,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: { display: true, text: 'Percentage (%)' }
                            },
                            x: { title: { display: true, text: 'Quarter' } }
                        },
                        plugins: {
                            legend: { display: true, position: 'top' },
                            title: { display: true, text: 'Grades Performance by Type (WW, PT, AS)' }
                        }
                    }
                });
            }
        </script>

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
<button class="btn" style="float: right;" onclick="generateFinalGrade()">Generate Final Grade (Auto-computes quarters)</button>
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

        <script>
        function calculateAverage(quarter) {
            // Find the Calculate button for this quarter
            const button = document.querySelector('button[onclick="calculateAverage(' + quarter + ')"]');
            if (!button) {
                document.getElementById('quarter-grade-' + quarter).textContent = 'No data';
                return;
            }

            // Find WW row (has the button), PT/AS are next siblings
            const wwRow = button.closest('tr');
            const ptRow = wwRow.nextElementSibling;
            const asRow = ptRow ? ptRow.nextElementSibling : null;

            if (!ptRow || !asRow) {
                document.getElementById('quarter-grade-' + quarter).textContent = 'Incomplete rows';
                return;
            }

            // Extract %: col 6 (1-based nth-child(6)) for WW (has rowspan Actions col), col 5 for PT/AS
            const wwPctCell = wwRow.querySelector('td:nth-child(6)');
            const ptPctCell = ptRow.querySelector('td:nth-child(5)');
            const asPctCell = asRow.querySelector('td:nth-child(5)');

            const wwPct = wwPctCell ? parseFloat(wwPctCell.textContent.replace('%', '').trim()) || 0 : 0;
            const ptPct = ptPctCell ? parseFloat(ptPctCell.textContent.replace('%', '').trim()) || 0 : 0;
            const asPct = asPctCell ? parseFloat(asPctCell.textContent.replace('%', '').trim()) || 0 : 0;

            // Weighted average
            const quarterGrade = Math.round(((wwPct * 0.3) + (ptPct * 0.5) + (asPct * 0.2)) * 10) / 10;

            // Display with color (passing >=75 green)
            const gradeDiv = document.getElementById('quarter-grade-' + quarter);
            const color = quarterGrade >= 75 ? 'green' : 'red';
            gradeDiv.innerHTML = `<span style="color: ${color}; font-size: 18px; font-weight: bold;">Grade: ${quarterGrade}%</span>`;
            gradeDiv.textContent = `Grade: ${quarterGrade.toFixed(1)}`; // Plain text for parsing
        }
        </script>
    <?php else: ?>
        <p>Student not found.</p>
        <a href="students.php" class="btn">Back to Students</a>
    <?php endif; ?>
</div>

<script src="../assets/js/script.js"></script>

<?php include '../includes/footer.php'; ?>
