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
}
?>
