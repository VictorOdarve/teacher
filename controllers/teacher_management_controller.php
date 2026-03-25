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

function seedTeacherProfiles(PDO $db): void
{
    $seedSql = "INSERT INTO teacher_profiles (user_id, major, grade_level, section)
                SELECT u.id, NULL, NULL, NULL
                FROM users u
                LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
                WHERE u.role = 'teacher' AND tp.user_id IS NULL";
    $db->exec($seedSql);
}

function fetchDistinctTeacherOptions(PDO $db, string $query): array
{
    $stmt = $db->query($query);
    $options = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return array_values(array_filter(array_map(
        static fn($value): string => trim((string)$value),
        $options
    ), static fn(string $value): bool => $value !== ''));
}

function buildTeacherFormValues(array $input): array
{
    return [
        'teacher_id' => isset($input['teacher_id']) ? (string)(int)$input['teacher_id'] : '',
        'name' => trim((string)($input['name'] ?? '')),
        'username' => strtolower(trim((string)($input['username'] ?? ''))),
        'email' => strtolower(trim((string)($input['email'] ?? ''))),
        'major' => trim((string)($input['major'] ?? '')),
        'grade_level' => trim((string)($input['grade_level'] ?? '')),
        'section' => trim((string)($input['section'] ?? ''))
    ];
}

function isValidTeacherOption(string $value, array $options): bool
{
    return $value === '' || in_array($value, $options, true);
}

$database = new Database();
$db = $database->getConnection();

$dashboard_error = '';
$teacher_feedback_message = '';
$teacher_form_error = '';
$should_open_teacher_form = false;
$teacher_modal_mode = 'create';
$teacher_form_values = buildTeacherFormValues([]);
$major_options = [];
$grade_level_options = [];
$section_options = [];
$teachers = [];

if ($db instanceof PDO) {
    try {
        ensureTeacherProfilesTable($db);
        seedTeacherProfiles($db);

        $major_options = fetchDistinctTeacherOptions(
            $db,
            "SELECT option_value
             FROM (
                 SELECT DISTINCT TRIM(subject) AS option_value
                 FROM classes
                 WHERE subject IS NOT NULL AND TRIM(subject) <> ''
                 UNION
                 SELECT DISTINCT TRIM(major) AS option_value
                 FROM teacher_profiles
                 WHERE major IS NOT NULL AND TRIM(major) <> ''
             ) teacher_major_options
             ORDER BY option_value ASC"
        );

        $grade_level_options = fetchDistinctTeacherOptions(
            $db,
            "SELECT option_value
             FROM (
                 SELECT DISTINCT TRIM(grade_level) AS option_value
                 FROM class_students
                 WHERE grade_level IS NOT NULL AND TRIM(grade_level) <> ''
                 UNION
                 SELECT DISTINCT TRIM(grade_level) AS option_value
                 FROM classes
                 WHERE grade_level IS NOT NULL AND TRIM(grade_level) <> ''
                 UNION
                 SELECT DISTINCT TRIM(grade_level) AS option_value
                 FROM teacher_profiles
                 WHERE grade_level IS NOT NULL AND TRIM(grade_level) <> ''
             ) teacher_grade_options
             ORDER BY option_value ASC"
        );

        $section_options = fetchDistinctTeacherOptions(
            $db,
            "SELECT option_value
             FROM (
                 SELECT DISTINCT TRIM(section) AS option_value
                 FROM class_students
                 WHERE section IS NOT NULL AND TRIM(section) <> ''
                 UNION
                 SELECT DISTINCT TRIM(section) AS option_value
                 FROM classes
                 WHERE section IS NOT NULL AND TRIM(section) <> ''
                 UNION
                 SELECT DISTINCT TRIM(section) AS option_value
                 FROM teacher_profiles
                 WHERE section IS NOT NULL AND TRIM(section) <> ''
             ) teacher_section_options
             ORDER BY option_value ASC"
        );

        if (($_GET['msg'] ?? '') === 'teacher_added') {
            $teacher_feedback_message = 'New teacher added successfully.';
        } elseif (($_GET['msg'] ?? '') === 'teacher_updated') {
            $teacher_feedback_message = 'Teacher details updated successfully.';
        }

        $teacherAction = $_POST['action'] ?? '';
        if (
            $_SERVER['REQUEST_METHOD'] === 'POST' &&
            ($teacherAction === 'create_teacher' || $teacherAction === 'update_teacher')
        ) {
            $should_open_teacher_form = true;
            $teacher_modal_mode = $teacherAction === 'update_teacher' ? 'edit' : 'create';
            $teacher_form_values = buildTeacherFormValues($_POST);
            $password = trim((string)($_POST['password'] ?? ''));
            $teacherId = (int)($teacher_form_values['teacher_id'] ?: 0);

            if (
                $teacher_modal_mode === 'edit' &&
                $teacherId <= 0
            ) {
                $teacher_form_error = 'Teacher record not found.';
            } elseif (
                $teacher_form_values['name'] === '' ||
                $teacher_form_values['username'] === '' ||
                ($teacher_modal_mode === 'create' && $password === '')
            ) {
                $teacher_form_error = 'Name, username, and password are required.';
            } elseif (!preg_match('/^[A-Za-z0-9._-]+$/', $teacher_form_values['username'])) {
                $teacher_form_error = 'Username may only contain letters, numbers, dots, underscores, and dashes.';
            } elseif (
                $teacher_form_values['email'] !== '' &&
                filter_var($teacher_form_values['email'], FILTER_VALIDATE_EMAIL) === false
            ) {
                $teacher_form_error = 'Please enter a valid email address.';
            } elseif (!isValidTeacherOption($teacher_form_values['major'], $major_options)) {
                $teacher_form_error = 'Please choose a major from the database list.';
            } elseif (!isValidTeacherOption($teacher_form_values['grade_level'], $grade_level_options)) {
                $teacher_form_error = 'Please choose a grade level from the database list.';
            } elseif (!isValidTeacherOption($teacher_form_values['section'], $section_options)) {
                $teacher_form_error = 'Please choose a section from the database list.';
            } else {
                if ($teacher_modal_mode === 'edit') {
                    $teacherExistsStmt = $db->prepare(
                        "SELECT id
                         FROM users
                         WHERE id = :teacher_id AND role = 'teacher'
                         LIMIT 1"
                    );
                    $teacherExistsStmt->execute([':teacher_id' => $teacherId]);
                    if (!$teacherExistsStmt->fetchColumn()) {
                        $teacher_form_error = 'Teacher record not found.';
                    }
                }

                $duplicateSql = 'SELECT id, username, email FROM users WHERE (username = :username';
                $duplicateParams = [':username' => $teacher_form_values['username']];

                if ($teacher_form_values['email'] !== '') {
                    $duplicateSql .= ' OR email = :email';
                    $duplicateParams[':email'] = $teacher_form_values['email'];
                }

                $duplicateSql .= ')';

                if ($teacher_modal_mode === 'edit') {
                    $duplicateSql .= ' AND id <> :teacher_id';
                    $duplicateParams[':teacher_id'] = $teacherId;
                }

                $duplicateSql .= ' LIMIT 1';

                if ($teacher_form_error === '') {
                    $duplicateStmt = $db->prepare($duplicateSql);
                    $duplicateStmt->execute($duplicateParams);
                    $duplicateUser = $duplicateStmt->fetch(PDO::FETCH_ASSOC);

                    if ($duplicateUser) {
                        if (
                            isset($duplicateUser['username']) &&
                            strtolower((string)$duplicateUser['username']) === $teacher_form_values['username']
                        ) {
                            $teacher_form_error = 'That username is already in use.';
                        } else {
                            $teacher_form_error = 'That email address is already in use.';
                        }
                    } else {
                        try {
                            $db->beginTransaction();

                            if ($teacher_modal_mode === 'edit') {
                                $updateUserSql = "UPDATE users
                                                  SET name = :name,
                                                      username = :username,
                                                      email = :email";
                                $updateUserParams = [
                                    ':name' => $teacher_form_values['name'],
                                    ':username' => $teacher_form_values['username'],
                                    ':email' => $teacher_form_values['email'] !== '' ? $teacher_form_values['email'] : null,
                                    ':teacher_id' => $teacherId
                                ];

                                if ($password !== '') {
                                    $updateUserSql .= ", password = :password";
                                    $updateUserParams[':password'] = password_hash($password, PASSWORD_DEFAULT);
                                }

                                $updateUserSql .= " WHERE id = :teacher_id AND role = 'teacher'";
                                $updateUserStmt = $db->prepare($updateUserSql);
                                $updateUserStmt->execute($updateUserParams);
                            } else {
                                $createTeacherStmt = $db->prepare(
                                    "INSERT INTO users (name, username, email, password, role)
                                     VALUES (:name, :username, :email, :password, 'teacher')"
                                );
                                $createTeacherStmt->execute([
                                    ':name' => $teacher_form_values['name'],
                                    ':username' => $teacher_form_values['username'],
                                    ':email' => $teacher_form_values['email'] !== '' ? $teacher_form_values['email'] : null,
                                    ':password' => password_hash($password, PASSWORD_DEFAULT)
                                ]);

                                $teacherId = (int)$db->lastInsertId();
                            }

                            $profileStmt = $db->prepare(
                                "INSERT INTO teacher_profiles (user_id, major, grade_level, section)
                                 VALUES (:user_id, :major, :grade_level, :section)
                                 ON DUPLICATE KEY UPDATE
                                    major = VALUES(major),
                                    grade_level = VALUES(grade_level),
                                    section = VALUES(section)"
                            );
                            $profileStmt->execute([
                                ':user_id' => $teacherId,
                                ':major' => $teacher_form_values['major'] !== '' ? $teacher_form_values['major'] : null,
                                ':grade_level' => $teacher_form_values['grade_level'] !== '' ? $teacher_form_values['grade_level'] : null,
                                ':section' => $teacher_form_values['section'] !== '' ? $teacher_form_values['section'] : null
                            ]);

                            $db->commit();
                            header('Location: teacher_management.php?msg=' . ($teacher_modal_mode === 'edit' ? 'teacher_updated' : 'teacher_added'));
                            exit;
                        } catch (PDOException $e) {
                            if ($db->inTransaction()) {
                                $db->rollBack();
                            }
                            $teacher_form_error = $teacher_modal_mode === 'edit'
                                ? 'The teacher details could not be updated.'
                                : 'The teacher account could not be created.';
                        }
                    }
                }
            }
        }

        $teachersQuery = "SELECT
                            u.id,
                            u.name,
                            u.username,
                            u.email,
                            u.created_at,
                            u.last_login_at,
                            NULLIF(TRIM(tp.major), '') AS major_value,
                            COALESCE(NULLIF(TRIM(tp.major), ''), 'Not Assigned') AS major,
                            NULLIF(TRIM(tp.grade_level), '') AS grade_level_value,
                            COALESCE(NULLIF(TRIM(tp.grade_level), ''), 'Not Assigned') AS grade_level,
                            NULLIF(TRIM(tp.section), '') AS section_value,
                            COALESCE(NULLIF(TRIM(tp.section), ''), 'Not Assigned') AS section,
                            'Active' AS status
                          FROM users u
                          LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
                          WHERE u.role = 'teacher'
                          ORDER BY u.name ASC, u.username ASC";
        $teachersStmt = $db->query($teachersQuery);
        $teachers = $teachersStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $dashboard_error = 'Teacher management data could not be loaded.';
    }
} else {
    $dashboard_error = 'Database connection failed. Import the database first.';
}
?>
