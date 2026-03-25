CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(120) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'teacher',
    student_profile_id INT NULL,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS teacher_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    major VARCHAR(100) DEFAULT NULL,
    grade_level VARCHAR(50) DEFAULT NULL,
    section VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_teacher_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS teacher_registration_requests (
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
);

-- Insert default staff users
INSERT INTO users (name, username, password, role)
VALUES
    ('Administrator', 'admin', '$2y$10$2ivTkVJFBz5Xldn66iYFeeXTZ7shjBGKDyYD8AIydsfxmAmj71a02', 'admin'),
    ('Teacher', 'teacher', '$2y$10$YU4J1Hovqq.G74uniwBig.HBwUpPJ/oPnpqb49pvP2d1JooqAda2.', 'teacher')
ON DUPLICATE KEY UPDATE username = username;

CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grade_level VARCHAR(50) NOT NULL,
    section VARCHAR(50) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    subject_code VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    day_of_week VARCHAR(20) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS class_students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    student_id VARCHAR(50) NOT NULL,
    grade_level VARCHAR(50) NULL,
    section VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

ALTER TABLE class_students
    ADD COLUMN IF NOT EXISTS grade_level VARCHAR(50) NULL AFTER student_id,
    ADD COLUMN IF NOT EXISTS section VARCHAR(50) NULL AFTER grade_level;

UPDATE class_students cs
JOIN classes c ON cs.class_id = c.id
SET cs.grade_level = c.grade_level,
    cs.section = c.section
WHERE cs.grade_level IS NULL OR cs.section IS NULL;

CREATE TABLE IF NOT EXISTS class_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    activity_date DATE NOT NULL,
    activity_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_activity (class_id, activity_date)
);

CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'late', 'excused', 'absent') NOT NULL,
    remarks TEXT,
    grade_level VARCHAR(50) NOT NULL,
    section VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES class_students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (student_id, date)
);

ALTER TABLE attendance
    ADD COLUMN IF NOT EXISTS grade_level VARCHAR(50) NULL AFTER remarks,
    ADD COLUMN IF NOT EXISTS section VARCHAR(50) NULL AFTER grade_level;

UPDATE attendance a
JOIN class_students cs ON a.student_id = cs.id
JOIN classes c ON cs.class_id = c.id
SET a.grade_level = c.grade_level,
    a.section = c.section
WHERE a.grade_level IS NULL OR a.section IS NULL;

CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    quarter INT NOT NULL,
    grade_type ENUM('ww', 'pt', 'as') NOT NULL,
    scores TEXT,
    total_score TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES class_students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_grade (student_id, quarter, grade_type)
);

CREATE TABLE IF NOT EXISTS final_grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    q1_grade DECIMAL(5,2) NOT NULL,
    q2_grade DECIMAL(5,2) NOT NULL,
    q3_grade DECIMAL(5,2) NOT NULL,
    q4_grade DECIMAL(5,2) NOT NULL,
    raw_final_grade DECIMAL(5,2) NOT NULL,
    rounded_final_grade INT NOT NULL,
    remarks VARCHAR(50) NOT NULL,
    finalized_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES class_students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_final_grade (student_id)
);

-- Seed sample students: 15 students per class (and therefore per subject/class setup)
INSERT INTO class_students (class_id, first_name, last_name, middle_name, gender, student_id, grade_level, section)
SELECT
    c.id,
    ELT(
        FLOOR(1 + RAND() * 20),
        'Liam', 'Noah', 'Ethan', 'Lucas', 'Mason',
        'Olivia', 'Emma', 'Sophia', 'Ava', 'Mia',
        'James', 'Benjamin', 'Elijah', 'Amelia', 'Harper',
        'Isabella', 'Charlotte', 'Henry', 'Alexander', 'Daniel'
    ) AS first_name,
    ELT(
        FLOOR(1 + RAND() * 20),
        'Santos', 'Reyes', 'Cruz', 'Garcia', 'Mendoza',
        'Flores', 'Torres', 'Ramos', 'Castro', 'Aquino',
        'Diaz', 'Navarro', 'Fernandez', 'Villanueva', 'Gonzales',
        'Rivera', 'Morales', 'Domingo', 'Bautista', 'Salazar'
    ) AS last_name,
    NULL AS middle_name,
    CASE WHEN MOD(n.num, 2) = 0 THEN 'Female' ELSE 'Male' END AS gender,
    CONCAT(REPLACE(c.subject_code, ' ', ''), '-', LPAD(c.id, 2, '0'), '-', LPAD(n.num, 2, '0')) AS student_id,
    c.grade_level,
    c.section
FROM classes c
JOIN (
    SELECT 1 AS num UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
    UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
    UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
) n
WHERE NOT EXISTS (
    SELECT 1
    FROM class_students cs
    WHERE cs.class_id = c.id
      AND cs.student_id = CONCAT(REPLACE(c.subject_code, ' ', ''), '-', LPAD(c.id, 2, '0'), '-', LPAD(n.num, 2, '0'))
);
