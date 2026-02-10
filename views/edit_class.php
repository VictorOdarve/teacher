<?php
require_once '../config/database.php';
require_once '../models/ClassModel.php';

$database = new Database();
$db = $database->getConnection();
$classModel = new ClassModel($db);

$class_id = isset($_GET['class_id']) ? $_GET['class_id'] : 0;

// Get class info
$class = $classModel->getById($class_id);

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

$page_title = "Edit Class";
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <h1>Edit Class</h1>

    <h2>Class Information</h2>
    <form action="../controllers/class_controller.php" method="POST">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?php echo $class_id; ?>">
        
        <div class="form-group">
            <label>Grade Level:</label>
            <input type="text" name="grade_level" value="<?php echo htmlspecialchars($class['grade_level']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Section:</label>
            <input type="text" name="section" value="<?php echo htmlspecialchars($class['section']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Subject:</label>
            <input type="text" name="subject" value="<?php echo htmlspecialchars($class['subject']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Subject Code:</label>
            <input type="text" name="subject_code" value="<?php echo htmlspecialchars($class['subject_code']); ?>" required>
        </div>
        
        <button type="submit" class="btn btn-success">Update Class Info</button>
    </form>

    <h2 style="margin-top: 40px;">Schedules</h2>
    <?php if ($schedules->rowCount() > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Room</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $schedules->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td>
                        <form action="../controllers/schedule_controller.php" method="POST" style="display:inline;" id="schedule_form_<?php echo $row['id']; ?>">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                            <select name="day_of_week" onchange="document.getElementById('schedule_form_<?php echo $row['id']; ?>').submit()">
                                <option value="Monday" <?php echo $row['day_of_week'] == 'Monday' ? 'selected' : ''; ?>>Monday</option>
                                <option value="Tuesday" <?php echo $row['day_of_week'] == 'Tuesday' ? 'selected' : ''; ?>>Tuesday</option>
                                <option value="Wednesday" <?php echo $row['day_of_week'] == 'Wednesday' ? 'selected' : ''; ?>>Wednesday</option>
                                <option value="Thursday" <?php echo $row['day_of_week'] == 'Thursday' ? 'selected' : ''; ?>>Thursday</option>
                                <option value="Friday" <?php echo $row['day_of_week'] == 'Friday' ? 'selected' : ''; ?>>Friday</option>
                                <option value="Saturday" <?php echo $row['day_of_week'] == 'Saturday' ? 'selected' : ''; ?>>Saturday</option>
                                <option value="Sunday" <?php echo $row['day_of_week'] == 'Sunday' ? 'selected' : ''; ?>>Sunday</option>
                            </select>
                    </td>
                    <td>
                            <input type="time" name="start_time" value="<?php echo $row['start_time']; ?>" onchange="document.getElementById('schedule_form_<?php echo $row['id']; ?>').submit()">
                    </td>
                    <td>
                            <input type="time" name="end_time" value="<?php echo $row['end_time']; ?>" onchange="document.getElementById('schedule_form_<?php echo $row['id']; ?>').submit()">
                    </td>
                    <td>
                            <input type="text" name="room" value="<?php echo htmlspecialchars($row['room']); ?>" onchange="document.getElementById('schedule_form_<?php echo $row['id']; ?>').submit()">
                        </form>
                    </td>
                    <td>
                        <form action="../controllers/schedule_controller.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this schedule?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No schedules added yet.</p>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $students->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <form action="../controllers/class_student_controller.php" method="POST" style="display:contents;" id="student_form_<?php echo $row['id']; ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                        <td><input type="text" name="student_id" value="<?php echo htmlspecialchars($row['student_id']); ?>" onchange="document.getElementById('student_form_<?php echo $row['id']; ?>').submit()"></td>
                        <td><input type="text" name="last_name" value="<?php echo htmlspecialchars($row['last_name']); ?>" onchange="document.getElementById('student_form_<?php echo $row['id']; ?>').submit()"></td>
                        <td><input type="text" name="first_name" value="<?php echo htmlspecialchars($row['first_name']); ?>" onchange="document.getElementById('student_form_<?php echo $row['id']; ?>').submit()"></td>
                        <td><input type="text" name="middle_name" value="<?php echo htmlspecialchars($row['middle_name']); ?>" onchange="document.getElementById('student_form_<?php echo $row['id']; ?>').submit()"></td>
                    </form>
                    <td>
                        <form action="../controllers/class_student_controller.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this student?')">Delete</button>
                        </form>
                    </td>
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
