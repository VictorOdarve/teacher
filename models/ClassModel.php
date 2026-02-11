<?php
class ClassModel {
    private $conn;
    private $table = "classes";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($grade_level, $section, $subject, $subject_code) {
        $query = "INSERT INTO " . $this->table . " (grade_level, section, subject, subject_code) VALUES (:grade_level, :section, :subject, :subject_code)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":grade_level", $grade_level);
        $stmt->bindParam(":section", $section);
        $stmt->bindParam(":subject", $subject);
        $stmt->bindParam(":subject_code", $subject_code);
        return $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY grade_level, section";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $grade_level, $section, $subject, $subject_code) {
        $query = "UPDATE " . $this->table . " SET grade_level = :grade_level, section = :section, subject = :subject, subject_code = :subject_code WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":grade_level", $grade_level);
        $stmt->bindParam(":section", $section);
        $stmt->bindParam(":subject", $subject);
        $stmt->bindParam(":subject_code", $subject_code);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getClassesWithSchedules($day_of_week = null) {
        $query = "SELECT c.*, s.day_of_week, s.start_time, s.end_time, s.room
                  FROM " . $this->table . " c
                  LEFT JOIN schedules s ON c.id = s.class_id";
        if ($day_of_week) {
            $query .= " WHERE s.day_of_week = :day_of_week";
        }
        $query .= " ORDER BY c.grade_level, c.section";
        $stmt = $this->conn->prepare($query);
        if ($day_of_week) {
            $stmt->bindParam(":day_of_week", $day_of_week);
        }
        $stmt->execute();
        return $stmt;
    }

    public function getStudentsCount($class_id) {
        $query = "SELECT COUNT(*) as count FROM class_students WHERE class_id = :class_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":class_id", $class_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    public function getRecentActivities($class_id, $limit = 5) {
        $query = "SELECT activity_date, activity_text FROM class_activities
                  WHERE class_id = :class_id
                  ORDER BY activity_date DESC
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":class_id", $class_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
}
?>
