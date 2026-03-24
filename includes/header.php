<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_view_page = strpos($_SERVER['PHP_SELF'], '/views/') !== false;
$current_script = basename($_SERVER['PHP_SELF']);
$is_login_page = $current_script === 'login.php';

if (!isset($_SESSION['user_id']) && !$is_login_page) {
    header('Location: ' . ($is_view_page ? '../login.php' : 'login.php'));
    exit;
}

if (isset($_SESSION['user_id']) && !isset($_SESSION['role'])) {
    $_SESSION['role'] = 'teacher';
}

if (
    isset($_SESSION['user_id'], $_SESSION['role']) &&
    $_SESSION['role'] === 'student' &&
    !$is_login_page
) {
    $allowed_student_pages = ['student_performance.php', 'my_account.php', 'logout.php'];
    if (!in_array($current_script, $allowed_student_pages, true)) {
        header('Location: ' . ($is_view_page ? 'student_performance.php' : 'views/student_performance.php'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Attendance System'; ?></title>
    <link rel="stylesheet" href="<?php echo strpos($_SERVER['PHP_SELF'], '/views/') !== false ? '../' : ''; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="layout">
