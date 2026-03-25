<div class="sidebar">
    <h2>Menu</h2>
    <?php
    $is_view_page = strpos($_SERVER['PHP_SELF'], '/views/') !== false;
    $view_prefix = $is_view_page ? '' : 'views/';
    $root_prefix = $is_view_page ? '../' : '';
    $role = $_SESSION['role'] ?? 'teacher';
    $is_student = $role === 'student';
    $is_admin = $role === 'admin';
    ?>

    <?php if ($is_student): ?>
        <a href="<?php echo $view_prefix; ?>student_performance.php"><i class="fa-solid fa-chart-line"></i> My Performance</a>
    <?php elseif ($is_admin): ?>
        <a href="<?php echo $view_prefix; ?>admin_dashboard.php"><i class="fa-solid fa-user-shield"></i> Admin Dashboard</a>
        <a href="<?php echo $view_prefix; ?>teacher_registrations.php"><i class="fa-solid fa-user-check"></i> Teacher New Registration</a>
        <a href="<?php echo $view_prefix; ?>teacher_management.php"><i class="fa-solid fa-chalkboard"></i> Teacher Management</a>
        <a href="<?php echo $view_prefix; ?>students.php"><i class="fa-solid fa-users"></i> Students</a>
        <a href="<?php echo $view_prefix; ?>classes.php"><i class="fa-solid fa-school"></i> Classes</a>
        <a href="<?php echo $view_prefix; ?>mark_attendance.php"><i class="fa-solid fa-clipboard-check"></i> Attendance</a>
        <a href="<?php echo $view_prefix; ?>reports.php"><i class="fa-solid fa-file-chart-column"></i> Reports</a>
        <a href="<?php echo $view_prefix; ?>teacher_dashboard.php"><i class="fa-solid fa-chalkboard-user"></i> Teacher View</a>
        <a href="<?php echo $view_prefix; ?>my_account.php"><i class="fa-solid fa-user-clock"></i> Account Activity</a>
    <?php else: ?>
        <a href="<?php echo $root_prefix; ?>index.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="<?php echo $view_prefix; ?>teacher_dashboard.php"><i class="fa-solid fa-chalkboard-user"></i> Teacher Dashboard</a>
        <a href="<?php echo $view_prefix; ?>classes.php"><i class="fa-solid fa-school"></i> My Classes</a>
        <a href="<?php echo $view_prefix; ?>mark_attendance.php"><i class="fa-solid fa-clipboard-check"></i> Attendance</a>
        <a href="<?php echo $view_prefix; ?>history.php"><i class="fa-solid fa-clock-rotate-left"></i> History</a>
        <a href="<?php echo $view_prefix; ?>grading.php"><i class="fa-solid fa-star-half-stroke"></i> Grading</a>
        <a href="<?php echo $view_prefix; ?>students.php"><i class="fa-solid fa-users"></i> Students</a>
        <a href="<?php echo $view_prefix; ?>reports.php"><i class="fa-solid fa-file-chart-column"></i> Reports</a>
        <a href="<?php echo $view_prefix; ?>predictions.php"><i class="fa-solid fa-brain"></i> Prediction</a>
        <a href="<?php echo $view_prefix; ?>my_account.php"><i class="fa-solid fa-user-clock"></i> Account Activity</a>
    <?php endif; ?>

    <a href="<?php echo $root_prefix; ?>logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>
<div class="main-wrapper">
    <div class="main-content">
