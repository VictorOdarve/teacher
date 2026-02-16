<div class="sidebar">
    <h2>Menu</h2>
    <?php $is_student = isset($_SESSION['role']) && $_SESSION['role'] === 'student'; ?>

    <?php if ($is_student): ?>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>student_performance.php">My Performance</a>
    <?php else: ?>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '../' : ''; ?>index.php">Dashboard</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>teacher_dashboard.php">Teacher Dashboard</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>classes.php">My Classes</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>mark_attendance.php">Attendance</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>history.php">History</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>grading.php">Grading</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>students.php">Students</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>reports.php">Reports</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>my_account.php">Account Activity</a>
    <?php endif; ?>

    <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '../' : ''; ?>logout.php">Logout</a>
</div>
<div class="main-content">
