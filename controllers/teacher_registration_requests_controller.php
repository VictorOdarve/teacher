<?php
require_once '../config/database.php';
require_once '../config/mailer.php';

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

function setTeacherRegistrationFlash(string $type, string $message): void
{
    $_SESSION['teacher_registration_flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function consumeTeacherRegistrationFlash(): array
{
    $flash = $_SESSION['teacher_registration_flash'] ?? [
        'type' => '',
        'message' => ''
    ];

    unset($_SESSION['teacher_registration_flash']);

    return [
        'type' => (string)($flash['type'] ?? ''),
        'message' => (string)($flash['message'] ?? '')
    ];
}

function getTeacherRegistrationNoticeFromCode(string $code): array
{
    switch ($code) {
        case 'approved':
            return [
                'type' => 'success',
                'message' => 'Teacher registration approved and approval email sent.'
            ];
        case 'approved_email_skipped':
            return [
                'type' => 'success',
                'message' => 'Teacher registration approved, but no email was sent because the mail setup is incomplete or the request has no email address.'
            ];
        case 'approved_email_failed':
            return [
                'type' => 'success',
                'message' => 'Teacher registration approved, but the approval email could not be sent.'
            ];
        case 'rejected':
            return [
                'type' => 'success',
                'message' => 'Teacher registration rejected and rejection email sent.'
            ];
        case 'rejected_email_skipped':
            return [
                'type' => 'success',
                'message' => 'Teacher registration rejected, but no email was sent because the mail setup is incomplete or the request has no email address.'
            ];
        case 'rejected_email_failed':
            return [
                'type' => 'success',
                'message' => 'Teacher registration rejected, but the rejection email could not be sent.'
            ];
        case 'duplicate_conflict':
            return [
                'type' => 'error',
                'message' => 'Approval failed because the username or email is already being used by another account.'
            ];
        case 'request_not_found':
            return [
                'type' => 'error',
                'message' => 'That teacher registration request could not be found.'
            ];
        default:
            return [
                'type' => '',
                'message' => ''
            ];
    }
}

function ensureUsersTable(PDO $db): void
{
    $createTableSql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(120) UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'teacher',
        student_profile_id INT NULL,
        last_login_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($createTableSql);

    $roleColumnCheck = $db->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($roleColumnCheck->rowCount() === 0) {
        $db->exec("ALTER TABLE users ADD COLUMN role ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'teacher' AFTER password");
    }

    $emailColumnCheck = $db->query("SHOW COLUMNS FROM users LIKE 'email'");
    if ($emailColumnCheck->rowCount() === 0) {
        $db->exec("ALTER TABLE users ADD COLUMN email VARCHAR(120) UNIQUE NULL AFTER username");
    }

    $studentProfileColumnCheck = $db->query("SHOW COLUMNS FROM users LIKE 'student_profile_id'");
    if ($studentProfileColumnCheck->rowCount() === 0) {
        $db->exec("ALTER TABLE users ADD COLUMN student_profile_id INT NULL AFTER role");
    }

    $lastLoginColumnCheck = $db->query("SHOW COLUMNS FROM users LIKE 'last_login_at'");
    if ($lastLoginColumnCheck->rowCount() === 0) {
        $db->exec("ALTER TABLE users ADD COLUMN last_login_at DATETIME NULL AFTER student_profile_id");
    }
}

function ensureTeacherProfilesTable(PDO $db): void
{
    $createTableSql = "CREATE TABLE IF NOT EXISTS teacher_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL UNIQUE,
        major VARCHAR(100) DEFAULT NULL,
        grade_level VARCHAR(50) DEFAULT NULL,
        section VARCHAR(50) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_teacher_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $db->exec($createTableSql);

    $majorColumnCheck = $db->query("SHOW COLUMNS FROM teacher_profiles LIKE 'major'");
    if ($majorColumnCheck->rowCount() === 0) {
        $db->exec("ALTER TABLE teacher_profiles ADD COLUMN major VARCHAR(100) DEFAULT NULL AFTER user_id");
    }

    $gradeColumnCheck = $db->query("SHOW COLUMNS FROM teacher_profiles LIKE 'grade_level'");
    if ($gradeColumnCheck->rowCount() === 0) {
        $db->exec("ALTER TABLE teacher_profiles ADD COLUMN grade_level VARCHAR(50) DEFAULT NULL AFTER major");
    }

    $sectionColumnCheck = $db->query("SHOW COLUMNS FROM teacher_profiles LIKE 'section'");
    if ($sectionColumnCheck->rowCount() === 0) {
        $db->exec("ALTER TABLE teacher_profiles ADD COLUMN section VARCHAR(50) DEFAULT NULL AFTER grade_level");
    }
}

function ensureTeacherRegistrationRequestsTable(PDO $db): void
{
    $createTableSql = "CREATE TABLE IF NOT EXISTS teacher_registration_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(120) DEFAULT NULL,
        password_hash VARCHAR(255) NOT NULL,
        major VARCHAR(100) DEFAULT NULL,
        grade_level VARCHAR(50) DEFAULT NULL,
        section VARCHAR(50) DEFAULT NULL,
        status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
        reviewed_by INT DEFAULT NULL,
        reviewed_at DATETIME DEFAULT NULL,
        requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_teacher_registration_status (status),
        INDEX idx_teacher_registration_username (username),
        INDEX idx_teacher_registration_email (email)
    )";
    $db->exec($createTableSql);
}

$database = new Database();
$db = $database->getConnection();

$dashboard_error = '';
$registration_feedback_message = '';
$registration_summary = [
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0
];
$registration_requests = [];

if ($db instanceof PDO) {
    try {
        ensureUsersTable($db);
        ensureTeacherProfilesTable($db);
        ensureTeacherRegistrationRequestsTable($db);

        $legacyNoticeCode = trim((string)($_GET['msg'] ?? ''));
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $legacyNoticeCode !== '') {
            $legacyNotice = getTeacherRegistrationNoticeFromCode($legacyNoticeCode);
            if ($legacyNotice['message'] !== '') {
                setTeacherRegistrationFlash($legacyNotice['type'], $legacyNotice['message']);
            }

            header('Location: teacher_registrations.php');
            exit;
        }

        $flashNotice = consumeTeacherRegistrationFlash();
        if ($flashNotice['type'] === 'success') {
            $registration_feedback_message = $flashNotice['message'];
        } elseif ($flashNotice['type'] === 'error') {
            $dashboard_error = $flashNotice['message'];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $requestId = (int)($_POST['request_id'] ?? 0);

            if ($requestId <= 0) {
                setTeacherRegistrationFlash('error', 'That teacher registration request could not be found.');
                header('Location: teacher_registrations.php');
                exit;
            }

            $requestStmt = $db->prepare(
                "SELECT *
                 FROM teacher_registration_requests
                 WHERE id = :request_id AND status = 'pending'
                 LIMIT 1"
            );
            $requestStmt->execute([':request_id' => $requestId]);
            $request = $requestStmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                setTeacherRegistrationFlash('error', 'That teacher registration request could not be found.');
                header('Location: teacher_registrations.php');
                exit;
            }

            if ($action === 'approve_registration') {
                $duplicateSql = 'SELECT id, username, email FROM users WHERE (username = :username';
                $duplicateParams = [':username' => strtolower((string)$request['username'])];

                if (!empty($request['email'])) {
                    $duplicateSql .= ' OR email = :email';
                    $duplicateParams[':email'] = strtolower((string)$request['email']);
                }

                $duplicateSql .= ') LIMIT 1';
                $duplicateStmt = $db->prepare($duplicateSql);
                $duplicateStmt->execute($duplicateParams);

                if ($duplicateStmt->fetch(PDO::FETCH_ASSOC)) {
                    setTeacherRegistrationFlash('error', 'Approval failed because the username or email is already being used by another account.');
                    header('Location: teacher_registrations.php');
                    exit;
                }

                $db->beginTransaction();

                $createTeacherStmt = $db->prepare(
                    "INSERT INTO users (name, username, email, password, role)
                     VALUES (:name, :username, :email, :password, 'teacher')"
                );
                $createTeacherStmt->execute([
                    ':name' => $request['name'],
                    ':username' => strtolower((string)$request['username']),
                    ':email' => !empty($request['email']) ? strtolower((string)$request['email']) : null,
                    ':password' => $request['password_hash']
                ]);

                $teacherUserId = (int)$db->lastInsertId();

                $profileStmt = $db->prepare(
                    "INSERT INTO teacher_profiles (user_id, major, grade_level, section)
                     VALUES (:user_id, :major, :grade_level, :section)"
                );
                $profileStmt->execute([
                    ':user_id' => $teacherUserId,
                    ':major' => !empty($request['major']) ? $request['major'] : null,
                    ':grade_level' => !empty($request['grade_level']) ? $request['grade_level'] : null,
                    ':section' => !empty($request['section']) ? $request['section'] : null
                ]);

                $reviewStmt = $db->prepare(
                    "UPDATE teacher_registration_requests
                     SET status = 'approved',
                         reviewed_by = :reviewed_by,
                         reviewed_at = NOW()
                     WHERE id = :request_id"
                );
                $reviewStmt->execute([
                    ':reviewed_by' => (int)$_SESSION['user_id'],
                    ':request_id' => $requestId
                ]);

                $db->commit();
                $mailResult = sendTeacherRegistrationStatusEmail($request, 'approved');
                if ($mailResult['status'] === 'sent') {
                    setTeacherRegistrationFlash('success', 'Teacher registration approved and approval email sent.');
                    header('Location: teacher_registrations.php');
                    exit;
                }

                if ($mailResult['status'] === 'failed') {
                    error_log('Teacher approval email failed for registration request #' . $requestId . ': ' . $mailResult['message']);
                    setTeacherRegistrationFlash('success', 'Teacher registration approved, but the approval email could not be sent.');
                    header('Location: teacher_registrations.php');
                    exit;
                }

                setTeacherRegistrationFlash('success', 'Teacher registration approved, but no email was sent because the mail setup is incomplete or the request has no email address.');
                header('Location: teacher_registrations.php');
                exit;
            }

            if ($action === 'reject_registration') {
                $rejectStmt = $db->prepare(
                    "UPDATE teacher_registration_requests
                     SET status = 'rejected',
                         reviewed_by = :reviewed_by,
                         reviewed_at = NOW()
                     WHERE id = :request_id AND status = 'pending'"
                );
                $rejectStmt->execute([
                    ':reviewed_by' => (int)$_SESSION['user_id'],
                    ':request_id' => $requestId
                ]);

                $mailResult = sendTeacherRegistrationStatusEmail($request, 'rejected');
                if ($mailResult['status'] === 'sent') {
                    setTeacherRegistrationFlash('success', 'Teacher registration rejected and rejection email sent.');
                    header('Location: teacher_registrations.php');
                    exit;
                }

                if ($mailResult['status'] === 'failed') {
                    error_log('Teacher rejection email failed for registration request #' . $requestId . ': ' . $mailResult['message']);
                    setTeacherRegistrationFlash('success', 'Teacher registration rejected, but the rejection email could not be sent.');
                    header('Location: teacher_registrations.php');
                    exit;
                }

                setTeacherRegistrationFlash('success', 'Teacher registration rejected, but no email was sent because the mail setup is incomplete or the request has no email address.');
                header('Location: teacher_registrations.php');
                exit;
            }
        }

        $summaryStmt = $db->query(
            "SELECT status, COUNT(*) AS total
             FROM teacher_registration_requests
             GROUP BY status"
        );

        foreach ($summaryStmt->fetchAll(PDO::FETCH_ASSOC) as $summaryRow) {
            $status = $summaryRow['status'] ?? '';
            if (array_key_exists($status, $registration_summary)) {
                $registration_summary[$status] = (int)$summaryRow['total'];
            }
        }

        $registrationsStmt = $db->query(
            "SELECT
                trr.id,
                trr.name,
                trr.username,
                trr.email,
                trr.major,
                trr.grade_level,
                trr.section,
                trr.status,
                trr.requested_at,
                trr.reviewed_at,
                reviewer.name AS reviewed_by_name
             FROM teacher_registration_requests trr
             LEFT JOIN users reviewer ON reviewer.id = trr.reviewed_by
             ORDER BY FIELD(trr.status, 'pending', 'approved', 'rejected'), trr.requested_at DESC"
        );
        $registration_requests = $registrationsStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $dashboard_error = 'Teacher registration requests could not be loaded.';
    }
} else {
    $dashboard_error = 'Database connection failed. Import the database first.';
}
?>
