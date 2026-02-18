<?php
class Student {
    private $conn;
    private $table = "class_students";

    public function __construct($db) {
        $this->conn = $db;
    }

    private function tableExists($tableName) {
        $query = "SHOW TABLES LIKE :table_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":table_name", $tableName);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function getDefaultClassId() {
        $query = "SELECT id FROM classes ORDER BY id ASC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }

    public function create($student_id, $name, $email, $phone) {
        if ($this->tableExists("students")) {
            $query = "INSERT INTO students (student_id, name, email, phone) VALUES (:student_id, :name, :email, :phone)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":student_id", $student_id);
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":phone", $phone);
            return $stmt->execute();
        }

        $name = trim((string)$name);
        $first_name = $name;
        $last_name = "";
        if ($name !== "" && strpos($name, " ") !== false) {
            $parts = preg_split('/\s+/', $name);
            $first_name = array_shift($parts);
            $last_name = implode(" ", $parts);
        }

        $query = "INSERT INTO class_students (class_id, first_name, last_name, middle_name, gender, student_id, grade_level, section)
                  SELECT c.id, :first_name, :last_name, :middle_name, :gender, :student_id, c.grade_level, c.section
                  FROM classes c
                  WHERE c.id = :class_id";
        $stmt = $this->conn->prepare($query);
        $default_class_id = $this->getDefaultClassId();
        if ($default_class_id === null) {
            return false;
        }
        $middle_name = null;
        $default_gender = "Other";
        $stmt->bindParam(":class_id", $default_class_id);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->bindParam(":first_name", $first_name);
        $stmt->bindParam(":last_name", $last_name);
        $stmt->bindParam(":middle_name", $middle_name);
        $stmt->bindParam(":gender", $default_gender);
        return $stmt->execute();
    }

    public function getAll() {
        if ($this->tableExists("class_students")) {
            $query = "SELECT * FROM class_students ORDER BY last_name, first_name";
        } else {
            $query = "SELECT * FROM students ORDER BY name";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        if ($this->tableExists("class_students")) {
            $query = "SELECT * FROM class_students WHERE id = :id";
        } else {
            $query = "SELECT * FROM students WHERE id = :id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $student_id, $name, $email, $phone) {
        if ($this->tableExists("students")) {
            $query = "UPDATE students SET student_id = :student_id, name = :name, email = :email, phone = :phone WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":student_id", $student_id);
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":phone", $phone);
            return $stmt->execute();
        }

        $name = trim((string)$name);
        $first_name = $name;
        $last_name = "";
        if ($name !== "" && strpos($name, " ") !== false) {
            $parts = preg_split('/\s+/', $name);
            $first_name = array_shift($parts);
            $last_name = implode(" ", $parts);
        }

        $query = "UPDATE class_students cs
                  JOIN classes c ON cs.class_id = c.id
                  SET cs.student_id = :student_id,
                      cs.first_name = :first_name,
                      cs.last_name = :last_name,
                      cs.grade_level = c.grade_level,
                      cs.section = c.section
                  WHERE cs.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->bindParam(":first_name", $first_name);
        $stmt->bindParam(":last_name", $last_name);
        return $stmt->execute();
    }

    public function delete($id) {
        if ($this->tableExists("class_students")) {
            $query = "DELETE FROM class_students WHERE id = :id";
        } else {
            $query = "DELETE FROM students WHERE id = :id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>
