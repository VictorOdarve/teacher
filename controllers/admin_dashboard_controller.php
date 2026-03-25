<?php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$role = $_SESSION['role'] ?? 'teacher';
if ($role !== 'admin') {
    if ($role === 'student') {
        header('Location: student_performance.php');
    } else {
        header('Location: ../index.php');
    }
    exit;
}

function fetchCount(PDO $db, string $query, array $params = []): int
{
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

$database = new Database();
$db = $database->getConnection();

$today = date('Y-m-d');
$dashboard_error = '';
$overview = [
    'admins' => 0,
    'teachers' => 0,
    'student_accounts' => 0,
    'classes' => 0,
    'enrolled_students' => 0,
    'logins_today' => 0
];
$attendance_breakdown = [
    'present' => 0,
    'late' => 0,
    'excused' => 0,
    'absent' => 0
];
$grade_summary = [];
$recent_logins = [];

if ($db instanceof PDO) {
    $overview['admins'] = fetchCount($db, "SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $overview['teachers'] = fetchCount($db, "SELECT COUNT(*) FROM users WHERE role = 'teacher'");
    $overview['student_accounts'] = fetchCount($db, "SELECT COUNT(*) FROM users WHERE role = 'student'");
    $overview['classes'] = fetchCount($db, "SELECT COUNT(*) FROM classes");
    $overview['enrolled_students'] = fetchCount($db, "SELECT COUNT(*) FROM class_students");
    $overview['logins_today'] = fetchCount(
        $db,
        "SELECT COUNT(*) FROM users WHERE last_login_at IS NOT NULL AND DATE(last_login_at) = :today",
        [':today' => $today]
    );

    $attendanceStmt = $db->prepare("SELECT status, COUNT(*) AS total FROM attendance WHERE date = :today GROUP BY status");
    $attendanceStmt->execute([':today' => $today]);
    foreach ($attendanceStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $status = $row['status'] ?? '';
        if (array_key_exists($status, $attendance_breakdown)) {
            $attendance_breakdown[$status] = (int)$row['total'];
        }
    }

    $gradeSummaryQuery = "SELECT
                            c.grade_level,
                            COUNT(DISTINCT c.id) AS total_classes,
                            COUNT(cs.id) AS total_students
                          FROM classes c
                          LEFT JOIN class_students cs ON cs.class_id = c.id
                          GROUP BY c.grade_level
                          ORDER BY c.grade_level ASC";
    $gradeSummaryStmt = $db->query($gradeSummaryQuery);
    $grade_summary = $gradeSummaryStmt->fetchAll(PDO::FETCH_ASSOC);

    $recentLoginsQuery = "SELECT name, username, role, last_login_at
                          FROM users
                          WHERE last_login_at IS NOT NULL
                          ORDER BY last_login_at DESC
                          LIMIT 8";
    $recentLoginsStmt = $db->query($recentLoginsQuery);
    $recent_logins = $recentLoginsStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $dashboard_error = 'Database connection failed. Import the database first.';
}
?>
