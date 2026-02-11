<?php
class Attendance {
    private $conn;
    private $table = "attendance";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function mark($student_id, $date, $status, $remarks = '') {
        $query = "INSERT INTO " . $this->table . " (student_id, date, status, remarks) VALUES (:student_id, :date, :status, :remarks) ON DUPLICATE KEY UPDATE status = :status, remarks = :remarks";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":remarks", $remarks);
        return $stmt->execute();
    }

    public function getByDate($date) {
        $query = "SELECT a.*, s.student_id, s.name FROM " . $this->table . " a JOIN students s ON a.student_id = s.id WHERE a.date = :date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":date", $date);
        $stmt->execute();
        return $stmt;
    }

    public function getByStudent($student_id, $start_date = null, $end_date = null) {
        $query = "SELECT * FROM " . $this->table . " WHERE student_id = :student_id";
        if ($start_date && $end_date) {
            $query .= " AND date BETWEEN :start_date AND :end_date";
        }
        $query .= " ORDER BY date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        if ($start_date && $end_date) {
            $stmt->bindParam(":start_date", $start_date);
            $stmt->bindParam(":end_date", $end_date);
        }
        $stmt->execute();
        return $stmt;
    }

    public function getReport($start_date, $end_date, $section = null, $subject = null) {
        $query = "SELECT cs.id, cs.student_id, CONCAT(cs.first_name, ' ', cs.last_name) as name,
                  SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                  SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                  SUM(CASE WHEN a.status = 'tardy' THEN 1 ELSE 0 END) as tardy_count
                  FROM class_students cs
                  LEFT JOIN " . $this->table . " a ON cs.id = a.student_id AND a.date BETWEEN :start_date AND :end_date
                  JOIN classes c ON cs.class_id = c.id";
        $conditions = [];
        if ($section) {
            $conditions[] = "c.section = :section";
        }
        if ($subject) {
            $conditions[] = "c.subject = :subject";
        }
        if ($conditions) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        $query .= " GROUP BY cs.id ORDER BY cs.last_name, cs.first_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":start_date", $start_date);
        $stmt->bindParam(":end_date", $end_date);
        if ($section) {
            $stmt->bindParam(":section", $section);
        }
        if ($subject) {
            $stmt->bindParam(":subject", $subject);
        }
        $stmt->execute();
        return $stmt;
    }

    public function getAttendanceStatsForClass($class_id, $date) {
        $query = "SELECT status, COUNT(*) as count
                  FROM " . $this->table . " a
                  JOIN class_students cs ON a.student_id = cs.id
                  WHERE cs.class_id = :class_id AND a.date = :date
                  GROUP BY status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":class_id", $class_id);
        $stmt->bindParam(":date", $date);
        $stmt->execute();
        $stats = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats[$row['status']] = $row['count'];
        }
        return $stats;
    }
}
?>
