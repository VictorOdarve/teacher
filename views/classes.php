<?php
require_once '../config/database.php';
require_once '../models/ClassModel.php';

$database = new Database();
$db = $database->getConnection();
$classModel = new ClassModel($db);

$classes = $classModel->getAll();

$page_title = "My Classes";
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <h1>My Classes</h1>
    
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-<?php echo $_GET['msg'] === 'error' ? 'error' : 'success'; ?>">
            <?php echo $_GET['msg'] === 'added' ? 'Class created successfully!' : 'An error occurred!'; ?>
        </div>
    <?php endif; ?>
    
    <button class="btn btn-success" onclick="document.getElementById('classForm').style.display='block'; this.style.display='none'">Create Class</button>
    
    <div id="classForm" style="display:none; margin-top: 20px;">
        <h2>Create New Class</h2>
        <form action="../controllers/class_controller.php" method="POST">
            <input type="hidden" name="action" value="create">
            
            <div class="form-group">
                <label>Grade Level:</label>
                <input type="text" name="grade_level" placeholder="e.g., Grade 10" required>
            </div>
            
            <div class="form-group">
                <label>Section:</label>
                <input type="text" name="section" placeholder="e.g., A, B, Einstein" required>
            </div>
            
            <div class="form-group">
                <label>Subject:</label>
                <input type="text" name="subject" placeholder="e.g., Mathematics" required>
            </div>
            
            <div class="form-group">
                <label>Subject Code:</label>
                <input type="text" name="subject_code" placeholder="e.g., MATH101" required>
            </div>
            
            <button type="submit" class="btn btn-success">Save Class</button>
            <button type="button" class="btn" onclick="document.getElementById('classForm').style.display='none'; document.querySelector('.btn-success').style.display='inline-block'">Cancel</button>
        </form>
    </div>
    
    <h2 style="margin-top: 40px;">My Classes</h2>
    <div class="class-grid">
        <?php 
        $classes->execute(); // Re-execute to reset pointer
        while ($row = $classes->fetch(PDO::FETCH_ASSOC)): 
            // Get schedules for this class
            $schedule_query = "SELECT * FROM schedules WHERE class_id = :class_id ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
            $schedule_stmt = $db->prepare($schedule_query);
            $schedule_stmt->bindParam(":class_id", $row['id']);
            $schedule_stmt->execute();
        ?>
        <div class="class-card">
            <h3><?php echo htmlspecialchars($row['subject']); ?></h3>
            <div style="display: flex; justify-content: space-between;">
                <div>
                    <p><strong>Grade Level:</strong> <?php echo htmlspecialchars($row['grade_level']); ?></p>
                    <p><strong>Section:</strong> <?php echo htmlspecialchars($row['section']); ?></p>
                    <p><strong>Subject Code:</strong> <?php echo htmlspecialchars($row['subject_code']); ?></p>
                </div>
                <?php if ($schedule_stmt->rowCount() > 0): ?>
                    <div style="text-align: right;">
                        <p><strong>Schedule:</strong></p>
                        <?php while ($sched = $schedule_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <p style="font-size: 14px; margin: 5px 0;">
                                <?php echo htmlspecialchars($sched['day_of_week']); ?>: 
                                <?php echo date('g:i A', strtotime($sched['start_time'])); ?> - 
                                <?php echo date('g:i A', strtotime($sched['end_time'])); ?> 
                                (<?php echo htmlspecialchars($sched['room']); ?>)
                            </p>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="class-actions">
                <button class="btn btn-success btn-sm" onclick="window.location.href='class_view.php?class_id=<?php echo $row['id']; ?>'">View</button>
                <button class="btn btn-primary btn-sm" onclick="openScheduleModal(<?php echo $row['id']; ?>)">Add Schedule</button>
                <button class="btn btn-primary btn-sm" onclick="openStudentModal(<?php echo $row['id']; ?>)">Add Student</button>
                <button class="btn btn-primary btn-sm" onclick="window.location.href='calendar.php?class_id=<?php echo $row['id']; ?>'">Activities Calendar</button>
                <button class="btn btn-warning btn-sm" onclick="window.location.href='edit_class.php?class_id=<?php echo $row['id']; ?>'">Edit</button>
                <form action="../controllers/class_controller.php" method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this class?')">Delete</button>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="scheduleModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeScheduleModal()">&times;</span>
        <h2>Add Schedule</h2>
        <form action="../controllers/schedule_controller.php" method="POST">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="class_id" id="modal_class_id">
            
            <div class="form-group">
                <label>Day of the Week:</label>
                <select name="day_of_week" required>
                    <option value="">Select Day</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Start Time:</label>
                <input type="time" name="start_time" required>
            </div>
            
            <div class="form-group">
                <label>End Time:</label>
                <input type="time" name="end_time" required>
            </div>
            
            <div class="form-group">
                <label>Room:</label>
                <input type="text" name="room" placeholder="e.g., Room 101" required>
            </div>
            
            <button type="submit" class="btn btn-success">Submit Schedule</button>
            <button type="button" class="btn" onclick="closeScheduleModal()">Cancel</button>
        </form>
    </div>
</div>

<div id="studentModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeStudentModal()">&times;</span>
        <h2>Add Student to Class</h2>
        <form action="../controllers/class_student_controller.php" method="POST">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="class_id" id="modal_student_class_id">
            
            <div class="form-group">
                <label>First Name:</label>
                <input type="text" name="first_name" required>
            </div>
            
            <div class="form-group">
                <label>Last Name:</label>
                <input type="text" name="last_name" required>
            </div>
            
            <div class="form-group">
                <label>Middle Name:</label>
                <input type="text" name="middle_name">
            </div>
            
            <div class="form-group">
                <label>Student ID:</label>
                <input type="text" name="student_id" required>
            </div>
            
            <button type="submit" class="btn btn-success">Add Student</button>
            <button type="button" class="btn" onclick="closeStudentModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
function openScheduleModal(classId) {
    document.getElementById('modal_class_id').value = classId;
    document.getElementById('scheduleModal').style.display = 'block';
}

function closeScheduleModal() {
    document.getElementById('scheduleModal').style.display = 'none';
}

function openStudentModal(classId) {
    document.getElementById('modal_student_class_id').value = classId;
    document.getElementById('studentModal').style.display = 'block';
}

function closeStudentModal() {
    document.getElementById('studentModal').style.display = 'none';
}

window.onclick = function(event) {
    const scheduleModal = document.getElementById('scheduleModal');
    const studentModal = document.getElementById('studentModal');
    if (event.target == scheduleModal) {
        closeScheduleModal();
    }
    if (event.target == studentModal) {
        closeStudentModal();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
