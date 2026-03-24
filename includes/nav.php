<div class="sidebar">
    <h2>Menu</h2>
    <?php $is_student = isset($_SESSION['role']) && $_SESSION['role'] === 'student'; ?>

    <?php if ($is_student): ?>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>student_performance.php"><i class="fa-solid fa-chart-line"></i> My Performance</a>
    <?php else: ?>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '../' : ''; ?>index.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>teacher_dashboard.php"><i class="fa-solid fa-chalkboard-user"></i> Teacher Dashboard</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>classes.php"><i class="fa-solid fa-school"></i> My Classes</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>mark_attendance.php"><i class="fa-solid fa-clipboard-check"></i> Attendance</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>history.php"><i class="fa-solid fa-clock-rotate-left"></i> History</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>grading.php"><i class="fa-solid fa-star-half-stroke"></i> Grading</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>students.php"><i class="fa-solid fa-users"></i> Students</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>reports.php"><i class="fa-solid fa-file-chart-column"></i> Reports</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>predictions.php"><i class="fa-solid fa-brain"></i> Prediction</a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '' : 'views/'; ?>my_account.php"><i class="fa-solid fa-user-clock"></i> Account Activity</a>
    <?php endif; ?>

    <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '../' : ''; ?>logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>
<div class="main-wrapper">
    <div class="main-content">
