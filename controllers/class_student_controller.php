<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'];

        if ($action === 'create') {
            try {
                $query = "INSERT INTO class_students (class_id, first_name, last_name, middle_name, gender, student_id, grade_level, section)
                          SELECT c.id, :first_name, :last_name, :middle_name, :gender, :student_id, c.grade_level, c.section
                          FROM classes c
                          WHERE c.id = :class_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":class_id", $_POST['class_id']);
                $stmt->bindParam(":first_name", $_POST['first_name']);
                $stmt->bindParam(":last_name", $_POST['last_name']);
                $stmt->bindParam(":middle_name", $_POST['middle_name']);
                $stmt->bindParam(":gender", $_POST['gender']);
                $stmt->bindParam(":student_id", $_POST['student_id']);
                $result = $stmt->execute();
                
                header('Location: ../views/classes.php?msg=' . ($result ? 'student_added' : 'error'));
                exit;
            } catch (PDOException $e) {
                die("Error: " . $e->getMessage());
            }
        }

        if ($action === 'import') {
            $class_id = $_POST['class_id'];
            
            if (!isset($_FILES['students_csv']) || $_FILES['students_csv']['error'] !== UPLOAD_ERR_OK) {
                header('Location: ../views/classes.php?msg=error_no_file (error code: ' . ($_FILES['students_csv']['error'] ?? 'no file') . ')');
                exit;
            }

            $file = $_FILES['students_csv']['tmp_name'];
            $handle = fopen($file, "r");
            if (!$handle) {
                header('Location: ../views/classes.php?msg=error_file');
                exit;
            }
            // Force UTF-8 BOM handling for CSV
            $bom = fread($handle, 3);
            if ($bom == pack("CCC",0xef,0xbb,0xbf)) {
                // UTF-8 BOM - already handled
            } else {
                rewind($handle);
            }

            // Skip header
            fgetcsv($handle);

            $imported = 0;
            $class_query = "SELECT grade_level, section FROM classes WHERE id = :class_id";
            $class_stmt = $db->prepare($class_query);
            $class_stmt->bindParam(":class_id", $class_id);
            $class_stmt->execute();
            $class_info = $class_stmt->fetch(PDO::FETCH_ASSOC);
            if (!$class_info) {
                fclose($handle);
                header('Location: ../views/classes.php?msg=error_class');
                exit;
            }

            while (($data = fgetcsv($handle)) !== FALSE) {
                if (count($data) < 5) continue; // Student ID(0),First(1),Last(2),Middle(3),Gender(4)

                $student_id = trim((string)$data[0]);
                $first_name = trim((string)$data[1]);
                $last_name = trim((string)$data[2]);
                $middle_name = isset($data[3]) ? trim((string)$data[3]) : null;
                $gender = trim((string)$data[4]);

                // Force UTF-8 encoding fix
                $student_id = mb_convert_encoding($student_id, 'UTF-8', 'auto');
                $first_name = mb_convert_encoding($first_name, 'UTF-8', 'auto');
                $last_name = mb_convert_encoding($last_name, 'UTF-8', 'auto');
                $middle_name = $middle_name ? mb_convert_encoding($middle_name, 'UTF-8', 'auto') : null;
                $gender = mb_convert_encoding($gender, 'UTF-8', 'auto');

                // Debug log
                error_log("Import row: ID=$student_id, First=$first_name, Last=$last_name, Gender=$gender");

                if (empty($student_id) || empty($first_name) || empty($last_name) || empty($gender)) continue;

                // Check duplicate student_id in class
                $dup_query = "SELECT id FROM class_students WHERE class_id = :class_id AND student_id = :student_id";
                $dup_stmt = $db->prepare($dup_query);
                $dup_stmt->bindParam(":class_id", $class_id);
                $dup_stmt->bindParam(":student_id", $student_id);
                $dup_stmt->execute();
                if ($dup_stmt->rowCount() > 0) continue;

                $query = "INSERT INTO class_students (class_id, first_name, last_name, middle_name, gender, student_id, grade_level, section) 
                          VALUES (:class_id, :first_name, :last_name, :middle_name, :gender, :student_id, :grade_level, :section)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":class_id", $class_id);
                $stmt->bindParam(":first_name", $first_name);
                $stmt->bindParam(":last_name", $last_name);
                $stmt->bindParam(":middle_name", $middle_name);
                $stmt->bindParam(":gender", $gender);
                $stmt->bindParam(":student_id", $student_id);
                $stmt->bindParam(":grade_level", $class_info['grade_level']);
                $stmt->bindParam(":section", $class_info['section']);
                
                if ($stmt->execute()) {
                    $imported++;
                }
            }
            fclose($handle);
            
            $msg = $imported > 0 ? "imported_{$imported}" : 'error_no_valid (checked ' . $total_rows . ' rows)';
            header("Location: ../views/classes.php?msg={$msg}");
            exit;
        }

    if ($action === 'update') {
        try {
            $query = "UPDATE class_students cs
                      JOIN classes c ON cs.class_id = c.id
                      SET cs.first_name = :first_name,
                          cs.last_name = :last_name,
                          cs.middle_name = :middle_name,
                          cs.gender = :gender,
                          cs.student_id = :student_id,
                          cs.grade_level = c.grade_level,
                          cs.section = c.section
                      WHERE cs.id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $_POST['id']);
            $stmt->bindParam(":first_name", $_POST['first_name']);
            $stmt->bindParam(":last_name", $_POST['last_name']);
            $stmt->bindParam(":middle_name", $_POST['middle_name']);
            $stmt->bindParam(":gender", $_POST['gender']);
            $stmt->bindParam(":student_id", $_POST['student_id']);
            $result = $stmt->execute();
            
            header('Location: ../views/edit_class.php?class_id=' . $_POST['class_id'] . '&msg=updated');
            exit;
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }

    if ($action === 'delete') {
        try {
            $query = "DELETE FROM class_students WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $_POST['id']);
            $result = $stmt->execute();
            
            header('Location: ../views/edit_class.php?class_id=' . $_POST['class_id'] . '&msg=' . ($result ? 'deleted' : 'error'));
            exit;
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>
