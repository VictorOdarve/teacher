<?php
session_start();

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'teacher';
    if ($role === 'student') {
        header('Location: views/student_performance.php');
    } elseif ($role === 'admin') {
        header('Location: views/admin_dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

require_once 'config/database.php';

$error = '';
$success = '';
$signup_error = '';
$active_auth_tab = 'login';
$login_identifier = '';
$signup_values = [
    'name' => '',
    'username' => '',
    'email' => '',
    'major' => '',
    'grade_level' => '',
    'section' => ''
];
$major_options = [];
$grade_level_options = [];
$section_options = [];

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

function tableExists(PDO $db, string $tableName): bool
{
    $stmt = $db->prepare('SHOW TABLES LIKE :table_name');
    $stmt->execute([':table_name' => $tableName]);
    return (bool)$stmt->fetchColumn();
}

function fetchDistinctColumnValues(PDO $db, string $tableName, string $columnName): array
{
    if (!tableExists($db, $tableName)) {
        return [];
    }

    $query = sprintf(
        "SELECT DISTINCT TRIM(%s) AS option_value FROM %s WHERE %s IS NOT NULL AND TRIM(%s) <> '' ORDER BY option_value ASC",
        $columnName,
        $tableName,
        $columnName,
        $columnName
    );
    $stmt = $db->query($query);
    $values = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return array_values(array_filter(array_map(
        static fn($value): string => trim((string)$value),
        $values
    ), static fn(string $value): bool => $value !== ''));
}

function mergeDistinctOptions(array ...$lists): array
{
    $merged = [];

    foreach ($lists as $list) {
        foreach ($list as $value) {
            $trimmedValue = trim((string)$value);
            if ($trimmedValue !== '') {
                $merged[$trimmedValue] = $trimmedValue;
            }
        }
    }

    $options = array_values($merged);
    sort($options, SORT_NATURAL | SORT_FLAG_CASE);

    return $options;
}

function buildTeacherSignupValues(array $input): array
{
    return [
        'name' => trim((string)($input['signup_name'] ?? '')),
        'username' => strtolower(trim((string)($input['signup_username'] ?? ''))),
        'email' => strtolower(trim((string)($input['signup_email'] ?? ''))),
        'major' => trim((string)($input['signup_major'] ?? '')),
        'grade_level' => trim((string)($input['signup_grade_level'] ?? '')),
        'section' => trim((string)($input['signup_section'] ?? ''))
    ];
}

function isValidTeacherOption(string $value, array $options): bool
{
    return $value === '' || in_array($value, $options, true);
}

function ensureDefaultStaffAccounts(PDO $db): void
{
    $defaultAccounts = [
        [
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => 'admin123',
            'role' => 'admin'
        ],
        [
            'name' => 'Teacher',
            'username' => 'teacher',
            'password' => 'teacher123',
            'role' => 'teacher'
        ]
    ];

    $upsertSql = "INSERT INTO users (name, username, email, password, role)
                  VALUES (:name, :username, NULL, :password, :role)
                  ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    password = VALUES(password),
                    role = VALUES(role)";
    $upsertStmt = $db->prepare($upsertSql);

    foreach ($defaultAccounts as $account) {
        $upsertStmt->execute([
            ':name' => $account['name'],
            ':username' => $account['username'],
            ':password' => password_hash($account['password'], PASSWORD_DEFAULT),
            ':role' => $account['role']
        ]);
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

$database = new Database();
$db = $database->getConnection();

if ($db instanceof PDO) {
    try {
        ensureUsersTable($db);
        ensureTeacherProfilesTable($db);
        ensureTeacherRegistrationRequestsTable($db);

        $major_options = mergeDistinctOptions(
            fetchDistinctColumnValues($db, 'classes', 'subject'),
            fetchDistinctColumnValues($db, 'teacher_profiles', 'major')
        );
        $grade_level_options = mergeDistinctOptions(
            fetchDistinctColumnValues($db, 'class_students', 'grade_level'),
            fetchDistinctColumnValues($db, 'classes', 'grade_level'),
            fetchDistinctColumnValues($db, 'teacher_profiles', 'grade_level')
        );
        $section_options = mergeDistinctOptions(
            fetchDistinctColumnValues($db, 'class_students', 'section'),
            fetchDistinctColumnValues($db, 'classes', 'section'),
            fetchDistinctColumnValues($db, 'teacher_profiles', 'section')
        );
    } catch (PDOException $e) {
        $major_options = [];
        $grade_level_options = [];
        $section_options = [];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authAction = $_POST['auth_action'] ?? 'login';
    $active_auth_tab = $authAction === 'teacher_signup' ? 'signup' : 'login';

    if (!$db instanceof PDO) {
        if ($authAction === 'teacher_signup') {
            $signup_error = 'Signup setup failed. Please check database configuration.';
        } else {
            $error = 'Login setup failed. Please check database configuration.';
        }
    } else {
        try {
            ensureUsersTable($db);
            ensureTeacherProfilesTable($db);
            ensureTeacherRegistrationRequestsTable($db);

            if ($authAction === 'teacher_signup') {
                $signup_values = buildTeacherSignupValues($_POST);
                $signupPassword = trim((string)($_POST['signup_password'] ?? ''));
                $signupConfirmPassword = trim((string)($_POST['signup_confirm_password'] ?? ''));

                if ($signup_values['name'] === '' || $signup_values['username'] === '' || $signupPassword === '' || $signupConfirmPassword === '') {
                    $signup_error = 'Please complete the required teacher signup fields.';
                } elseif (!preg_match('/^[A-Za-z0-9._-]+$/', $signup_values['username'])) {
                    $signup_error = 'Username may only contain letters, numbers, dots, underscores, and dashes.';
                } elseif (
                    $signup_values['email'] !== '' &&
                    filter_var($signup_values['email'], FILTER_VALIDATE_EMAIL) === false
                ) {
                    $signup_error = 'Please enter a valid email address.';
                } elseif ($signupPassword !== $signupConfirmPassword) {
                    $signup_error = 'Password confirmation does not match.';
                } elseif (!isValidTeacherOption($signup_values['major'], $major_options)) {
                    $signup_error = 'Please choose a major from the database list.';
                } elseif (!isValidTeacherOption($signup_values['grade_level'], $grade_level_options)) {
                    $signup_error = 'Please choose a grade level from the database list.';
                } elseif (!isValidTeacherOption($signup_values['section'], $section_options)) {
                    $signup_error = 'Please choose a section from the database list.';
                } else {
                    $duplicateUserSql = 'SELECT id, username, email FROM users WHERE (username = :username';
                    $duplicateUserParams = [':username' => $signup_values['username']];

                    if ($signup_values['email'] !== '') {
                        $duplicateUserSql .= ' OR email = :email';
                        $duplicateUserParams[':email'] = $signup_values['email'];
                    }

                    $duplicateUserSql .= ') LIMIT 1';
                    $duplicateUserStmt = $db->prepare($duplicateUserSql);
                    $duplicateUserStmt->execute($duplicateUserParams);
                    $duplicateUser = $duplicateUserStmt->fetch(PDO::FETCH_ASSOC);

                    if ($duplicateUser) {
                        if (
                            isset($duplicateUser['username']) &&
                            strtolower((string)$duplicateUser['username']) === $signup_values['username']
                        ) {
                            $signup_error = 'That username is already registered.';
                        } else {
                            $signup_error = 'That email address is already registered.';
                        }
                    } else {
                        $duplicateRequestSql = "SELECT id, username, email
                                                FROM teacher_registration_requests
                                                WHERE status = 'pending' AND (username = :username";
                        $duplicateRequestParams = [':username' => $signup_values['username']];

                        if ($signup_values['email'] !== '') {
                            $duplicateRequestSql .= ' OR email = :email';
                            $duplicateRequestParams[':email'] = $signup_values['email'];
                        }

                        $duplicateRequestSql .= ') LIMIT 1';
                        $duplicateRequestStmt = $db->prepare($duplicateRequestSql);
                        $duplicateRequestStmt->execute($duplicateRequestParams);
                        $duplicateRequest = $duplicateRequestStmt->fetch(PDO::FETCH_ASSOC);

                        if ($duplicateRequest) {
                            if (
                                isset($duplicateRequest['username']) &&
                                strtolower((string)$duplicateRequest['username']) === $signup_values['username']
                            ) {
                                $signup_error = 'A teacher registration with that username is already pending approval.';
                            } else {
                                $signup_error = 'A teacher registration with that email address is already pending approval.';
                            }
                        } else {
                            $createRequestStmt = $db->prepare(
                                "INSERT INTO teacher_registration_requests
                                    (name, username, email, password_hash, major, grade_level, section, status)
                                 VALUES
                                    (:name, :username, :email, :password_hash, :major, :grade_level, :section, 'pending')"
                            );
                            $createRequestStmt->execute([
                                ':name' => $signup_values['name'],
                                ':username' => $signup_values['username'],
                                ':email' => $signup_values['email'] !== '' ? $signup_values['email'] : null,
                                ':password_hash' => password_hash($signupPassword, PASSWORD_DEFAULT),
                                ':major' => $signup_values['major'] !== '' ? $signup_values['major'] : null,
                                ':grade_level' => $signup_values['grade_level'] !== '' ? $signup_values['grade_level'] : null,
                                ':section' => $signup_values['section'] !== '' ? $signup_values['section'] : null
                            ]);

                            $success = 'Teacher registration submitted. Please wait for admin approval before logging in.';
                            $login_identifier = $signup_values['username'];
                            $signup_values = [
                                'name' => '',
                                'username' => '',
                                'email' => '',
                                'major' => '',
                                'grade_level' => '',
                                'section' => ''
                            ];
                            $active_auth_tab = 'login';
                        }
                    }
                }
            } else {
                ensureDefaultStaffAccounts($db);
                ensureStudentUserAccounts($db);

                $identifierRaw = trim($_POST['username'] ?? '');
                $identifierNormalized = strtolower($identifierRaw);
                $password = $_POST['password'] ?? '';
                $login_identifier = $identifierRaw;

                if ($identifierRaw === '' || $password === '') {
                    $error = 'Please enter both email/username and password.';
                } else {
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
                        } elseif ($_SESSION['role'] === 'admin') {
                            header('Location: views/admin_dashboard.php');
                        } else {
                            header('Location: index.php');
                        }
                        exit;
                    }

                    if ($error === '') {
                        if (!$user) {
                            $registrationStatusStmt = $db->prepare(
                                "SELECT status
                                 FROM teacher_registration_requests
                                 WHERE username = :identifier_raw
                                    OR email = :identifier_raw
                                    OR username = :identifier_normalized
                                    OR email = :identifier_normalized
                                 ORDER BY requested_at DESC
                                 LIMIT 1"
                            );
                            $registrationStatusStmt->execute([
                                ':identifier_raw' => $identifierRaw,
                                ':identifier_normalized' => $identifierNormalized
                            ]);
                            $registrationStatus = $registrationStatusStmt->fetchColumn();

                            if ($registrationStatus === 'pending') {
                                $error = 'Your teacher registration is still pending admin approval.';
                            } elseif ($registrationStatus === 'rejected') {
                                $error = 'Your teacher registration was rejected. Please contact the administrator.';
                            } else {
                                $error = 'Invalid username or password.';
                            }
                        } else {
                            $error = 'Invalid username or password.';
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            if ($authAction === 'teacher_signup') {
                $signup_error = 'Teacher signup could not be completed right now.';
            } else {
                $error = 'Login setup failed. Please check database configuration.';
            }
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
        <div class="auth-card" data-active-tab="<?php echo htmlspecialchars($active_auth_tab); ?>">
            <div class="auth-switch" role="tablist" aria-label="Authentication options">
                <button
                    type="button"
                    class="auth-switch-btn <?php echo $active_auth_tab === 'login' ? 'is-active' : ''; ?>"
                    data-auth-tab-button="login"
                    onclick="switchAuthTab('login')"
                >
                    Login
                </button>
                <button
                    type="button"
                    class="auth-switch-btn <?php echo $active_auth_tab === 'signup' ? 'is-active' : ''; ?>"
                    data-auth-tab-button="signup"
                    onclick="switchAuthTab('signup')"
                >
                    Teacher Sign Up
                </button>
            </div>

            <div
                id="loginPanel"
                class="auth-panel <?php echo $active_auth_tab === 'login' ? 'is-active' : ''; ?>"
                data-auth-panel="login"
            >
                <h1>LOGIN PAGE</h1>
                <p class="auth-subtitle">Sign in to continue to the attendance system</p>

                <?php if ($success !== ''): ?>
                    <div class="alert alert-success auth-alert" data-auth-alert><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-error auth-alert" data-auth-alert><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <input type="hidden" name="auth_action" value="login">

                    <div class="form-group">
                        <label for="username">Email or Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?php echo htmlspecialchars($login_identifier); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn auth-btn">Login</button>
                </form>

                <p class="auth-hint auth-panel-hint">
                    New teacher?
                    <button type="button" class="auth-inline-link" onclick="switchAuthTab('signup')">Create an account</button>
                </p>
            </div>

            <div
                id="signupPanel"
                class="auth-panel <?php echo $active_auth_tab === 'signup' ? 'is-active' : ''; ?>"
                data-auth-panel="signup"
            >
                <h1>TEACHER SIGN UP</h1>
                <p class="auth-subtitle">Submit a teacher registration request for admin approval.</p>

                <?php if ($signup_error !== ''): ?>
                    <div class="alert alert-error auth-alert" data-auth-alert><?php echo htmlspecialchars($signup_error); ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <input type="hidden" name="auth_action" value="teacher_signup">

                    <div class="auth-grid">
                        <div class="form-group auth-grid-span-2">
                            <label for="signup_name">Full Name</label>
                            <input
                                type="text"
                                id="signup_name"
                                name="signup_name"
                                value="<?php echo htmlspecialchars($signup_values['name']); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="signup_username">Username</label>
                            <input
                                type="text"
                                id="signup_username"
                                name="signup_username"
                                value="<?php echo htmlspecialchars($signup_values['username']); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="signup_email">Email Address</label>
                            <input
                                type="email"
                                id="signup_email"
                                name="signup_email"
                                value="<?php echo htmlspecialchars($signup_values['email']); ?>"
                            >
                        </div>

                        <div class="form-group">
                            <label for="signup_password">Password</label>
                            <input type="password" id="signup_password" name="signup_password" required>
                        </div>

                        <div class="form-group">
                            <label for="signup_confirm_password">Confirm Password</label>
                            <input type="password" id="signup_confirm_password" name="signup_confirm_password" required>
                        </div>

                        <div class="form-group">
                            <label for="signup_major">Major</label>
                            <select id="signup_major" name="signup_major">
                                <option value="">Select major</option>
                                <?php foreach ($major_options as $majorOption): ?>
                                    <option value="<?php echo htmlspecialchars($majorOption); ?>" <?php echo $signup_values['major'] === $majorOption ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($majorOption); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="signup_grade_level">Grade Level</label>
                            <select id="signup_grade_level" name="signup_grade_level">
                                <option value="">Select grade level</option>
                                <?php foreach ($grade_level_options as $gradeLevelOption): ?>
                                    <option value="<?php echo htmlspecialchars($gradeLevelOption); ?>" <?php echo $signup_values['grade_level'] === $gradeLevelOption ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($gradeLevelOption); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group auth-grid-span-2">
                            <label for="signup_section">Section</label>
                            <select id="signup_section" name="signup_section">
                                <option value="">Select section</option>
                                <?php foreach ($section_options as $sectionOption): ?>
                                    <option value="<?php echo htmlspecialchars($sectionOption); ?>" <?php echo $signup_values['section'] === $sectionOption ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sectionOption); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <p class="auth-hint auth-signup-note">Major, grade level, and section choices come from saved school records in the database.</p>

                    <button type="submit" class="btn auth-btn">Submit Registration Request</button>
                </form>

                <p class="auth-hint auth-panel-hint">
                    Already have a teacher account?
                    <button type="button" class="auth-inline-link" onclick="switchAuthTab('login')">Go to login</button>
                </p>
            </div>
        </div>
    </div>
    <script>
    function switchAuthTab(tabName) {
        const card = document.querySelector('.auth-card');
        const panels = document.querySelectorAll('[data-auth-panel]');
        const buttons = document.querySelectorAll('[data-auth-tab-button]');

        if (!card) {
            return;
        }

        card.dataset.activeTab = tabName;

        panels.forEach((panel) => {
            panel.classList.toggle('is-active', panel.dataset.authPanel === tabName);
        });

        buttons.forEach((button) => {
            button.classList.toggle('is-active', button.dataset.authTabButton === tabName);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const card = document.querySelector('.auth-card');
        const authAlerts = document.querySelectorAll('[data-auth-alert]');
        switchAuthTab(card ? card.dataset.activeTab : 'login');

        authAlerts.forEach((alert) => {
            window.setTimeout(() => {
                alert.classList.add('is-hiding');

                window.setTimeout(() => {
                    alert.remove();
                }, 250);
            }, 3000);
        });
    });
    </script>
</body>
</html>
