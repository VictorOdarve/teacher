<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'student') {
        header('Location: views/student_performance.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

require_once 'config/database.php';

$error = '';

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

function ensureStudentUserAccounts(PDO $db): void
{
    $studentsQuery = "SELECT id, first_name, last_name, student_id FROM class_students";
    $studentsStmt = $db->query($studentsQuery);
    $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$students) {
        return;
    }

    $upsertSql = "INSERT INTO users (name, username, email, password, role, student_profile_id)
                  VALUES (:name, :username, :email, :password, 'student', :student_profile_id)
                  ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    password = VALUES(password),
                    role = 'student',
                    student_profile_id = VALUES(student_profile_id),
                    email = VALUES(email)";
    $upsertStmt = $db->prepare($upsertSql);

    foreach ($students as $student) {
        if (!isset($student['student_id']) || trim((string)$student['student_id']) === '') {
            continue;
        }

        $studentId = trim((string)$student['student_id']);
        $normalizedIdentifier = strtolower($studentId);
        if ($normalizedIdentifier === '') {
            continue;
        }

        // Student login identifier is the student number itself.
        $email = $normalizedIdentifier;
        $username = $normalizedIdentifier;
        $name = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $passwordHash = password_hash($studentId, PASSWORD_DEFAULT); // Student password = student ID number
        $studentProfileId = (int)$student['id'];

        $upsertStmt->bindParam(':name', $name);
        $upsertStmt->bindParam(':username', $username);
        $upsertStmt->bindParam(':email', $email);
        $upsertStmt->bindParam(':password', $passwordHash);
        $upsertStmt->bindParam(':student_profile_id', $studentProfileId, PDO::PARAM_INT);
        $upsertStmt->execute();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifierRaw = trim($_POST['username'] ?? '');
    $identifierNormalized = strtolower($identifierRaw);
    $password = $_POST['password'] ?? '';

    if ($identifierRaw === '' || $password === '') {
        $error = 'Please enter both email/username and password.';
    } else {
        $database = new Database();
        $db = $database->getConnection();

        try {
            ensureUsersTable($db);
            ensureStudentUserAccounts($db);

            $query = "SELECT id, name, username, email, password, role, student_profile_id
                      FROM users
                      WHERE username = :identifier_raw
                         OR email = :identifier_raw
                         OR username = :identifier_normalized
                         OR email = :identifier_normalized
                         OR username = CONCAT(:identifier_normalized, '@student.local')
                         OR email = CONCAT(:identifier_normalized, '@student.local')
                      LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':identifier_raw', $identifierRaw);
            $stmt->bindParam(':identifier_normalized', $identifierNormalized);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $passwordMatched = false;
            if ($user) {
                $passwordCandidates = [$password];
                if (($user['role'] ?? '') === 'student') {
                    $passwordCandidates[] = strtoupper($password);
                    $passwordCandidates[] = strtolower($password);
                }

                foreach ($passwordCandidates as $candidate) {
                    if (password_verify($candidate, $user['password'])) {
                        $passwordMatched = true;
                        break;
                    }
                }
            }

            if ($user && $passwordMatched) {
                $updateLastLoginSql = "UPDATE users SET last_login_at = NOW() WHERE id = :id";
                $updateLastLoginStmt = $db->prepare($updateLastLoginSql);
                $updateLastLoginStmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
                $updateLastLoginStmt->execute();

                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'] ?: 'teacher';
                $_SESSION['student_profile_id'] = $user['student_profile_id'] ? (int)$user['student_profile_id'] : null;

                if ($_SESSION['role'] === 'student') {
                    header('Location: views/student_performance.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Login setup failed. Please check database configuration.';
        }

        if ($error === '') {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Attendance System</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/assets/css/style.css'); ?>">
    <link rel="stylesheet" href="assets/css/login.css?v=<?php echo filemtime(__DIR__ . '/assets/css/login.css'); ?>">
</head>
<body class="login-body">
    <div class="auth-page">
        <div class="auth-card">
            <h1>LOGIN PAGE</h1>
            <p class="auth-subtitle">Sign in to continue to the attendance system</p>

            <?php if ($error !== ''): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Email or Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn auth-btn">Login</button>
            </form>

        </div>
    </div>
</body>
</html>
