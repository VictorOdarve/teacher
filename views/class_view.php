<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$class_id = isset($_GET['class_id']) ? $_GET['class_id'] : 0;

// Get class info
$query = "SELECT * FROM classes WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $class_id);
$stmt->execute();
$class = $stmt->fetch(PDO::FETCH_ASSOC);

// Get schedules
$query = "SELECT * FROM schedules WHERE class_id = :class_id ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
$stmt = $db->prepare($query);
$stmt->bindParam(":class_id", $class_id);
$stmt->execute();
$schedules = $stmt;

// Get students
$query = "SELECT * FROM class_students WHERE class_id = :class_id ORDER BY last_name, first_name";
$stmt = $db->prepare($query);
$stmt->bindParam(":class_id", $class_id);
$stmt->execute();
$students = $stmt;

$page_title = "View Class";
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <h1><?php echo htmlspecialchars($class['subject']); ?></h1>
    
    <div class="class-info">
        <p><strong>Grade Level:</strong> <?php echo htmlspecialchars($class['grade_level']); ?></p>
        <p><strong>Section:</strong> <?php echo htmlspecialchars($class['section']); ?></p>
        <p><strong>Subject Code:</strong> <?php echo htmlspecialchars($class['subject_code']); ?></p>
    </div>

    <h2 style="margin-top: 40px;">Schedule</h2>
    <?php if ($schedules->rowCount() > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Room</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $schedules->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['day_of_week']); ?></td>
                    <td><?php echo date('g:i A', strtotime($row['start_time'])); ?></td>
                    <td><?php echo date('g:i A', strtotime($row['end_time'])); ?></td>
                    <td><?php echo htmlspecialchars($row['room']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No schedule added yet.</p>
    <?php endif; ?>

    <h2 style="margin-top: 40px;">Students</h2>
    <?php if ($students->rowCount() > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $students->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['middle_name']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No students added yet.</p>
    <?php endif; ?>

    <div style="margin-top: 30px;">
        <a href="classes.php" class="btn">Back to Classes</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
