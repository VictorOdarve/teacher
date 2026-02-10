<?php
require_once 'config/database.php';
require_once 'models/Student.php';
require_once 'models/Attendance.php';

$database = new Database();
$db = $database->getConnection();

$student = new Student($db);
$attendance = new Attendance($db);

$total_students = $student->getAll()->rowCount();
$today = date('Y-m-d');
$today_attendance = $attendance->getByDate($today)->rowCount();

$page_title = "Dashboard - Attendance System";
include 'includes/header.php';
include 'includes/nav.php';
?>

<div class="card">
    <h1>Dashboard</h1>
    
    <div class="stats">
        <div class="stat-card">
            <h3>Total Students</h3>
            <div class="number"><?php echo $total_students; ?></div>
        </div>
        <div class="stat-card">
            <h3>Today's Attendance</h3>
            <div class="number"><?php echo $today_attendance; ?></div>
        </div>
        <div class="stat-card">
            <h3>Date</h3>
            <div class="number" style="font-size: 20px;"><?php echo date('M d, Y'); ?></div>
        </div>
    </div>

    <div style="margin-top: 30px;">
        <h2>Quick Actions</h2>
        <a href="views/students.php" class="btn btn-primary">Manage Students</a>
        <a href="views/mark_attendance.php" class="btn btn-success">Mark Attendance</a>
        <a href="views/reports.php" class="btn">View Reports</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
