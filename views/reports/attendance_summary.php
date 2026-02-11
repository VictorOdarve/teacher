<?php
require_once '../../config/database.php';
require_once '../../models/Attendance.php';

$database = new Database();
$db = $database->getConnection();
$attendance = new Attendance($db);

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$section = isset($_GET['section']) ? $_GET['section'] : null;
$subject = isset($_GET['subject']) ? $_GET['subject'] : null;

$report = $attendance->getReport($start_date, $end_date, $section, $subject);

// Calculate total school days (assuming 5 days a week, but this is a simplification)
$start = new DateTime($start_date);
$end = new DateTime($end_date);
$interval = $start->diff($end);
$total_days = $interval->days + 1; // Including start date
$total_school_days = floor($total_days / 7) * 5 + min($total_days % 7, 5); // Rough estimate
?>

<h2>Attendance Summary (<?php echo $start_date; ?> to <?php echo $end_date; ?>)</h2>
<table>
    <thead>
        <tr>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Total School Days</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Tardy</th>
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
                <td><?php echo isset($row['tardy_count']) ? $row['tardy_count'] : 0; ?></td>
                <td><?php echo $total_school_days > 0 ? round(($row['present_count'] / $total_school_days) * 100, 2) : 0; ?>%</td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
