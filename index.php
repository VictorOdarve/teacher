<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'] ?? 'teacher';
if ($role === 'student') {
    header('Location: views/student_performance.php');
    exit;
}

if ($role === 'admin') {
    header('Location: views/admin_dashboard.php');
    exit;
}

require_once 'config/database.php';
require_once 'models/Student.php';
require_once 'models/Attendance.php';
require_once 'models/ClassModel.php';

$database = new Database();
$db = $database->getConnection();

$total_students = 0;
$total_classes = 0;
$today_attendance = 0;
$today_absent = 0;
$dashboard_error = '';

if ($db instanceof PDO) {
    $student = new Student($db);
    $attendance = new Attendance($db);
    $class_model = new ClassModel($db);

    $total_students_row = $db->query("SELECT COUNT(*) as count FROM class_students")->fetch(PDO::FETCH_ASSOC);
    $total_students = (int)($total_students_row['count'] ?? 0);
    $total_classes_row = $db->query("SELECT COUNT(*) as count FROM classes")->fetch(PDO::FETCH_ASSOC);
    $total_classes = (int)($total_classes_row['count'] ?? 0);
    $today = date('Y-m-d');
    $today_attendance = $attendance->getByDate($today)->rowCount();

    $query = "SELECT COUNT(*) as count FROM attendance WHERE date = :date AND status = 'absent'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":date", $today);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $today_absent = (int)($row['count'] ?? 0);
} else {
    $dashboard_error = 'Database connection failed. Import the database first.';
}

$page_title = "Dashboard - Attendance System";
include 'includes/header.php';
include 'includes/nav.php';
?>

<div class="card">
    <h1>Dashboard</h1>

    <?php if ($dashboard_error !== ''): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($dashboard_error); ?></div>
    <?php endif; ?>

    <div class="stats">
        <div class="stat-card">
            <h3>Total Students</h3>
            <div class="number"><?php echo $total_students; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Classes</h3>
            <div class="number"><?php echo $total_classes; ?></div>
        </div>
        <div class="stat-card">
            <h3>Today's Present</h3>
            <div class="number"><?php echo $today_attendance; ?></div>
        </div>
        <div class="stat-card">
            <h3>Today's Absent</h3>
            <div class="number"><?php echo $today_absent; ?></div>
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
