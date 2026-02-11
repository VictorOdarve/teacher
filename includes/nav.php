<div class="sidebar">
    <h2>Menu</h2>
    <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '../' : ''; ?>index.php">Dashboard</a>
    <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>teacher_dashboard.php">Teacher Dashboard</a>
    <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>classes.php">My Classes</a>
    <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>mark_attendance.php">Attendance</a>
    <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>history.php">History</a>
    <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>grading.php">Grading</a>
    <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>students.php">Students</a>
    <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>reports.php">Reports</a>
</div>
<div class="main-content">
