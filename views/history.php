<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$grade_filter = isset($_GET['grade']) ? $_GET['grade'] : '';
$section_filter = isset($_GET['section']) ? $_GET['section'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Get distinct grades and sections
$grade_query = "SELECT DISTINCT grade_level FROM classes ORDER BY grade_level";
$grade_stmt = $db->prepare($grade_query);
$grade_stmt->execute();
$grades = $grade_stmt->fetchAll(PDO::FETCH_COLUMN);

$section_query = "SELECT DISTINCT section FROM classes ORDER BY section";
$section_stmt = $db->prepare($section_query);
$section_stmt->execute();
$sections = $section_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get attendance history
$query = "SELECT a.*, cs.student_id as student_number, cs.first_name, cs.last_name, c.grade_level, c.section 
          FROM attendance a 
          JOIN class_students cs ON a.student_id = cs.id 
          JOIN classes c ON cs.class_id = c.id 
          WHERE 1=1";

if ($date_filter) {
    $query .= " AND a.date = :date";
}
if ($grade_filter) {
    $query .= " AND c.grade_level = :grade";
}
if ($section_filter) {
    $query .= " AND c.section = :section";
}

$query .= " ORDER BY a.date DESC, cs.last_name, cs.first_name";

$stmt = $db->prepare($query);
if ($date_filter) {
    $stmt->bindParam(":date", $date_filter);
}
if ($grade_filter) {
    $stmt->bindParam(":grade", $grade_filter);
}
if ($section_filter) {
    $stmt->bindParam(":section", $section_filter);
}
$stmt->execute();
$history = $stmt;

$page_title = "Attendance History";
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <h1>Attendance History</h1>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px;">
        <div class="form-group">
            <label>Date:</label>
            <input type="date" id="date_filter" value="<?php echo $date_filter; ?>">
        </div>
        <div class="form-group">
            <label>Grade Level:</label>
            <select id="grade_filter">
                <option value="">All Grades</option>
                <?php foreach ($grades as $grade): ?>
                    <option value="<?php echo htmlspecialchars($grade); ?>" <?php echo $grade_filter == $grade ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($grade); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Section:</label>
            <select id="section_filter">
                <option value="">All Sections</option>
                <?php foreach ($sections as $section): ?>
                    <option value="<?php echo htmlspecialchars($section); ?>" <?php echo $section_filter == $section ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($section); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <button class="btn btn-primary" onclick="applyFilters()">Search</button>
    <button class="btn" onclick="clearFilters()">Clear Filters</button>

    <h2 style="margin-top: 30px;">Attendance Records</h2>
    <?php if ($history->rowCount() > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Grade</th>
                    <th>Section</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $history->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                    <td><?php echo htmlspecialchars($row['student_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['last_name'] . ', ' . $row['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['grade_level']); ?></td>
                    <td><?php echo htmlspecialchars($row['section']); ?></td>
                    <td>
                        <form action="../controllers/attendance_controller.php" method="POST" style="display:inline;" id="status_form_<?php echo $row['id']; ?>">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="return_url" value="history.php?<?php echo $_SERVER['QUERY_STRING']; ?>">
                            <select name="status" onchange="document.getElementById('status_form_<?php echo $row['id']; ?>').submit()" style="padding: 5px; border-radius: 5px;">
                                <option value="present" <?php echo $row['status'] == 'present' ? 'selected' : ''; ?>>Present</option>
                                <option value="late" <?php echo $row['status'] == 'late' ? 'selected' : ''; ?>>Late</option>
                                <option value="excused" <?php echo $row['status'] == 'excused' ? 'selected' : ''; ?>>Excused</option>
                                <option value="absent" <?php echo $row['status'] == 'absent' ? 'selected' : ''; ?>>Absent</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <form action="../controllers/attendance_controller.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="return_url" value="history.php?<?php echo $_SERVER['QUERY_STRING']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this attendance record?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No attendance records found for the selected filters.</p>
    <?php endif; ?>
</div>

<script>
function applyFilters() {
    const date = document.getElementById('date_filter').value;
    const grade = document.getElementById('grade_filter').value;
    const section = document.getElementById('section_filter').value;
    
    let url = 'history.php?';
    if (date) url += 'date=' + date + '&';
    if (grade) url += 'grade=' + encodeURIComponent(grade) + '&';
    if (section) url += 'section=' + encodeURIComponent(section);
    
    window.location.href = url;
}

function clearFilters() {
    window.location.href = 'history.php';
}
</script>

<?php include '../includes/footer.php'; ?>
