<?php
require_once '../../config/database.php';
require_once '../../models/Attendance.php';

$database = new Database();
$db = $database->getConnection();
$attendance = new Attendance($db);

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$section = isset($_GET['section']) ? $_GET['section'] : '';
$subject = isset($_GET['subject']) ? $_GET['subject'] : '';
$grade_level = isset($_GET['grade_level']) ? $_GET['grade_level'] : '';

$report = $attendance->getReport($start_date, $end_date, $section, $subject, $grade_level);

// Calculate total school days (assuming 5 days a week, but this is a simplification)
$start = new DateTime($start_date);
$end = new DateTime($end_date);
$interval = $start->diff($end);
$total_days = $interval->days + 1;
$total_school_days = floor($total_days / 7) * 5 + min($total_days % 7, 5); ?>

<h2>Attendance Summary (<?php echo $start_date; ?> to <?php echo $end_date; ?>)</h2>

<?php
// Compute overall totals for pie chart (fixed PDO binds)
$total_query = "SELECT 
    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as overall_present,
    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as overall_absent,
    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as overall_late,
    SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as overall_excused
    FROM attendance 
    WHERE date BETWEEN :start_date AND :end_date";

$bind_section = false;
$bind_subject = false;
$bind_grade_level = false;

if ($section || $subject || $grade_level) {
    $filter_conditions = [];
    
    if ($section) {
        $filter_conditions[] = "cs.class_id IN (SELECT id FROM classes WHERE section = :section)";
        $bind_section = true;
    }
    if ($subject) {
        $filter_conditions[] = "cs.class_id IN (SELECT id FROM classes WHERE subject = :subject)";
        $bind_subject = true;
    }
    if ($grade_level) {
        $filter_conditions[] = "cs.class_id IN (SELECT id FROM classes WHERE grade_level = :grade_level)";
        $bind_grade_level = true;
    }
    
    $filter_clause = implode(' AND ', $filter_conditions);
    $total_query .= " AND student_id IN (SELECT cs.id FROM class_students cs WHERE " . $filter_clause . ")";
}

$total_stmt = $db->prepare($total_query);
$total_stmt->bindParam(':start_date', $start_date);
$total_stmt->bindParam(':end_date', $end_date);

if ($bind_section) {
    $total_stmt->bindParam(':section', $section);
}
if ($bind_subject) {
    $total_stmt->bindParam(':subject', $subject);
}
if ($bind_grade_level) {
    $total_stmt->bindParam(':grade_level', $grade_level);
}

$total_stmt->execute();
$total_row = $total_stmt->fetch(PDO::FETCH_ASSOC);
$overall_present = $total_row['overall_present'] ?? 0;
$overall_absent = $total_row['overall_absent'] ?? 0;
$overall_late = $total_row['overall_late'] ?? 0;
$overall_excused = $total_row['overall_excused'] ?? 0;
$total_attendance = $overall_present + $overall_absent + $overall_late + $overall_excused;
?>

<!-- Overall Attendance Pie Chart -->
<div style="margin-bottom: 20px;">
    <canvas id="attendancePieChart" style="max-height: 300px; max-width: 100%;"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const pieCtx = document.getElementById('attendancePieChart').getContext('2d');
const pieData = {
    present: <?php echo $overall_present; ?>,
    absent: <?php echo $overall_absent; ?>,
    late: <?php echo $overall_late; ?>,
    excused: <?php echo $overall_excused; ?>,
    total: <?php echo $total_attendance; ?>
};

if (pieData.total > 0) {
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: ['Present', 'Absent', 'Late (Tardy)', 'Excused'],
            datasets: [{
                data: [pieData.present, pieData.absent, pieData.late, pieData.excused],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(54, 162, 235, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'right' 
                },
                title: { 
                    display: true, 
                    text: 'Overall Attendance Distribution (Total: ' + pieData.total + ')'
                }
            }
        }
    });
}
</script>

<table>
    <thead>
        <tr>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Total School Days</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Tardy</th>
            <th>Excused</th>
            <th>Attendance %</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $report->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo $total_school_days; ?></td>
                <td><?php echo $row['present_count']; ?></td>
                <td><?php echo $row['absent_count']; ?></td>
                <td><?php echo $row['late_count'] ?? 0; ?></td>
                <td><?php echo $row['excused_count'] ?? 0; ?></td>
                <td><?php echo $total_school_days > 0 ? round(($row['present_count'] / $total_school_days) * 100, 2) : 0; ?>%</td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
