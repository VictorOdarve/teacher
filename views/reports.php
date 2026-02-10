<?php
require_once '../config/database.php';
require_once '../models/Attendance.php';

$database = new Database();
$db = $database->getConnection();
$attendance = new Attendance($db);

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

$report = $attendance->getReport($start_date, $end_date);

$page_title = "Attendance Reports";
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <h1>Attendance Reports</h1>

    <form method="GET">
        <div class="form-group">
            <label>Start Date:</label>
            <input type="date" name="start_date" value="<?php echo $start_date; ?>" required>
        </div>

        <div class="form-group">
            <label>End Date:</label>
            <input type="date" name="end_date" value="<?php echo $end_date; ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Generate Report</button>
    </form>

    <h2>Report: <?php echo date('M d, Y', strtotime($start_date)); ?> to <?php echo date('M d, Y', strtotime($end_date)); ?></h2>
    
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Total Days</th>
                <th>Attendance %</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $report->fetch(PDO::FETCH_ASSOC)): 
                $total = $row['present_count'] + $row['absent_count'];
                $percentage = $total > 0 ? round(($row['present_count'] / $total) * 100, 2) : 0;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td class="status-present"><?php echo $row['present_count']; ?></td>
                <td class="status-absent"><?php echo $row['absent_count']; ?></td>
                <td><?php echo $total; ?></td>
                <td><?php echo $percentage; ?>%</td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
