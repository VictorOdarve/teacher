-- Run this SQL in your teacherdb database

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES class_students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (student_id, date)
);

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

-- Add gender column to class_students if it doesn't exist
ALTER TABLE class_students ADD COLUMN IF NOT EXISTS gender ENUM('Male', 'Female', 'Other') NOT NULL AFTER middle_name;

-- Insert sample classes (subjects)
INSERT INTO classes (grade_level, section, subject, subject_code) VALUES
('Grade 10', 'A', 'Mathematics', 'MATH101'),
('Grade 10', 'B', 'English', 'ENG101'),
('Grade 10', 'C', 'Science', 'SCI101'),
('Grade 10', 'D', 'History', 'HIST101');

-- Insert sample students for Mathematics (class_id 1)
INSERT INTO class_students (class_id, first_name, last_name, middle_name, gender, student_id) VALUES
(1, 'Juan', 'Dela Cruz', 'Santos', 'Male', 'STU001'),
(1, 'Maria', 'Garcia', 'Reyes', 'Female', 'STU002'),
(1, 'Pedro', 'Ramos', 'Lopez', 'Male', 'STU003'),
(1, 'Ana', 'Torres', 'Mendoza', 'Female', 'STU004'),
(1, 'Carlos', 'Flores', 'Hernandez', 'Male', 'STU005'),
(1, 'Sofia', 'Rivera', 'Gutierrez', 'Female', 'STU006'),
(1, 'Miguel', 'Morales', 'Jimenez', 'Male', 'STU007'),
(1, 'Isabella', 'Ortiz', 'Ruiz', 'Female', 'STU008'),
(1, 'Luis', 'Sanchez', 'Fernandez', 'Male', 'STU009'),
(1, 'Camila', 'Martinez', 'Gonzalez', 'Female', 'STU010'),
(1, 'Diego', 'Perez', 'Rodriguez', 'Male', 'STU011'),
(1, 'Valentina', 'Lopez', 'Martinez', 'Female', 'STU012'),
(1, 'Andres', 'Hernandez', 'Garcia', 'Male', 'STU013'),
(1, 'Gabriela', 'Gomez', 'Diaz', 'Female', 'STU014'),
(1, 'Fernando', 'Vargas', 'Morales', 'Male', 'STU015'),
(1, 'Lucia', 'Castro', 'Silva', 'Female', 'STU016'),
(1, 'Ricardo', 'Romero', 'Torres', 'Male', 'STU017'),
(1, 'Elena', 'Reyes', 'Flores', 'Female', 'STU018'),
(1, 'Oscar', 'Gutierrez', 'Rivera', 'Male', 'STU019'),
(1, 'Natalia', 'Jimenez', 'Ortiz', 'Female', 'STU020');

-- Insert sample students for English (class_id 2)
INSERT INTO class_students (class_id, first_name, last_name, middle_name, gender, student_id) VALUES
(2, 'Emilio', 'Alvarez', 'Santos', 'Male', 'STU021'),
(2, 'Rosa', 'Mendoza', 'Garcia', 'Female', 'STU022'),
(2, 'Antonio', 'Reyes', 'Ramos', 'Male', 'STU023'),
(2, 'Carmen', 'Torres', 'Lopez', 'Female', 'STU024'),
(2, 'Francisco', 'Flores', 'Hernandez', 'Male', 'STU025'),
(2, 'Dolores', 'Rivera', 'Gutierrez', 'Female', 'STU026'),
(2, 'Javier', 'Morales', 'Jimenez', 'Male', 'STU027'),
(2, 'Pilar', 'Ortiz', 'Ruiz', 'Female', 'STU028'),
(2, 'Manuel', 'Sanchez', 'Fernandez', 'Male', 'STU029'),
(2, 'Teresa', 'Martinez', 'Gonzalez', 'Female', 'STU030'),
(2, 'Rafael', 'Perez', 'Rodriguez', 'Male', 'STU031'),
(2, 'Beatriz', 'Lopez', 'Martinez', 'Female', 'STU032'),
(2, 'Alberto', 'Hernandez', 'Garcia', 'Male', 'STU033'),
(2, 'Monica', 'Gomez', 'Diaz', 'Female', 'STU034'),
(2, 'Enrique', 'Vargas', 'Morales', 'Male', 'STU035'),
(2, 'Silvia', 'Castro', 'Silva', 'Female', 'STU036'),
(2, 'Roberto', 'Romero', 'Torres', 'Male', 'STU037'),
(2, 'Adriana', 'Reyes', 'Flores', 'Female', 'STU038'),
(2, 'Sergio', 'Gutierrez', 'Rivera', 'Male', 'STU039'),
(2, 'Patricia', 'Jimenez', 'Ortiz', 'Female', 'STU040');

-- Insert sample students for Science (class_id 3)
INSERT INTO class_students (class_id, first_name, last_name, middle_name, gender, student_id) VALUES
(3, 'Victor', 'Alvarez', 'Santos', 'Male', 'STU041'),
(3, 'Laura', 'Mendoza', 'Garcia', 'Female', 'STU042'),
(3, 'Eduardo', 'Reyes', 'Ramos', 'Male', 'STU043'),
(3, 'Gloria', 'Torres', 'Lopez', 'Female', 'STU044'),
(3, 'Hugo', 'Flores', 'Hernandez', 'Male', 'STU045'),
(3, 'Angela', 'Rivera', 'Gutierrez', 'Female', 'STU046'),
(3, 'Ramon', 'Morales', 'Jimenez', 'Male', 'STU047'),
(3, 'Cristina', 'Ortiz', 'Ruiz', 'Female', 'STU048'),
(3, 'Felipe', 'Sanchez', 'Fernandez', 'Male', 'STU049'),
(3, 'Mercedes', 'Martinez', 'Gonzalez', 'Female', 'STU050'),
(3, 'Ignacio', 'Perez', 'Rodriguez', 'Male', 'STU051'),
(3, 'Alicia', 'Lopez', 'Martinez', 'Female', 'STU052'),
(3, 'Salvador', 'Hernandez', 'Garcia', 'Male', 'STU053'),
(3, 'Eva', 'Gomez', 'Diaz', 'Female', 'STU054'),
(3, 'Guillermo', 'Vargas', 'Morales', 'Male', 'STU055'),
(3, 'Consuelo', 'Castro', 'Silva', 'Female', 'STU056'),
(3, 'Arturo', 'Romero', 'Torres', 'Male', 'STU057'),
(3, 'Ines', 'Reyes', 'Flores', 'Female', 'STU058'),
(3, 'Mario', 'Gutierrez', 'Rivera', 'Male', 'STU059'),
(3, 'Lourdes', 'Jimenez', 'Ortiz', 'Female', 'STU060');

-- Insert sample students for History (class_id 4)
INSERT INTO class_students (class_id, first_name, last_name, middle_name, gender, student_id) VALUES
(4, 'Pablo', 'Alvarez', 'Santos', 'Male', 'STU061'),
(4, 'Isabel', 'Mendoza', 'Garcia', 'Female', 'STU062'),
(4, 'Ruben', 'Reyes', 'Ramos', 'Male', 'STU063'),
(4, 'Margarita', 'Torres', 'Lopez', 'Female', 'STU064'),
(4, 'Esteban', 'Flores', 'Hernandez', 'Male', 'STU065'),
(4, 'Raquel', 'Rivera', 'Gutierrez', 'Female', 'STU066'),
(4, 'Agustin', 'Morales', 'Jimenez', 'Male', 'STU067'),
(4, 'Esperanza', 'Ortiz', 'Ruiz', 'Female', 'STU068'),
(4, 'Julio', 'Sanchez', 'Fernandez', 'Male', 'STU069'),
(4, 'Victoria', 'Martinez', 'Gonzalez', 'Female', 'STU070'),
(4, 'Nicolas', 'Perez', 'Rodriguez', 'Male', 'STU071'),
(4, 'Amelia', 'Lopez', 'Martinez', 'Female', 'STU072'),
(4, 'Domingo', 'Hernandez', 'Garcia', 'Male', 'STU073'),
(4, 'Celia', 'Gomez', 'Diaz', 'Female', 'STU074'),
(4, 'Hector', 'Vargas', 'Morales', 'Male', 'STU075'),
(4, 'Blanca', 'Castro', 'Silva', 'Female', 'STU076'),
(4, 'Armando', 'Romero', 'Torres', 'Male', 'STU077'),
(4, 'Olga', 'Reyes', 'Flores', 'Female', 'STU078'),
(4, 'Ernesto', 'Gutierrez', 'Rivera', 'Male', 'STU079'),
(4, 'Milagros', 'Jimenez', 'Ortiz', 'Female', 'STU080');




