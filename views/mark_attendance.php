<?php
require_once '../config/database.php';
require_once '../models/Student.php';
require_once '../models/Attendance.php';

$database = new Database();
$db = $database->getConnection();
$student = new Student($db);
$attendance = new Attendance($db);

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$grade_filter = isset($_GET['grade']) ? $_GET['grade'] : '';
$section_filter = isset($_GET['section']) ? $_GET['section'] : '';

// Get distinct grades and sections
$grade_query = "SELECT DISTINCT grade_level FROM classes ORDER BY grade_level";
$grade_stmt = $db->prepare($grade_query);
$grade_stmt->execute();
$grades = $grade_stmt->fetchAll(PDO::FETCH_COLUMN);

$section_query = "SELECT DISTINCT section FROM classes ORDER BY section";
$section_stmt = $db->prepare($section_query);
$section_stmt->execute();
$sections = $section_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get students based on filters (unique per student_id)
if ($grade_filter && $section_filter) {
    $query = "SELECT cs.*, c.grade_level, c.section FROM class_students cs 
              JOIN classes c ON cs.class_id = c.id 
              INNER JOIN (SELECT cs2.student_id, MIN(cs2.id) as min_id FROM class_students cs2
                          JOIN classes c2 ON cs2.class_id = c2.id
                          WHERE c2.grade_level = :grade AND c2.section = :section
                          GROUP BY cs2.student_id) sub ON cs.student_id = sub.student_id AND cs.id = sub.min_id
              ORDER BY cs.last_name ASC, cs.first_name ASC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":grade", $grade_filter);
    $stmt->bindParam(":section", $section_filter);
    $stmt->execute();
    $students = $stmt;
} elseif ($grade_filter) {
    $query = "SELECT cs.*, c.grade_level, c.section FROM class_students cs 
              JOIN classes c ON cs.class_id = c.id 
              INNER JOIN (SELECT cs2.student_id, MIN(cs2.id) as min_id FROM class_students cs2 
                          JOIN classes c2 ON cs2.class_id = c2.id 
                          WHERE c2.grade_level = :grade 
                          GROUP BY cs2.student_id) sub ON cs.student_id = sub.student_id AND cs.id = sub.min_id 
              ORDER BY cs.last_name, cs.first_name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":grade", $grade_filter);
    $stmt->execute();
    $students = $stmt;
} elseif ($section_filter) {
    $query = "SELECT cs.*, c.grade_level, c.section FROM class_students cs 
              JOIN classes c ON cs.class_id = c.id 
              INNER JOIN (SELECT cs2.student_id, MIN(cs2.id) as min_id FROM class_students cs2 
                          JOIN classes c2 ON cs2.class_id = c2.id 
                          WHERE c2.section = :section 
                          GROUP BY cs2.student_id) sub ON cs.student_id = sub.student_id AND cs.id = sub.min_id 
              ORDER BY cs.last_name, cs.first_name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":section", $section_filter);
    $stmt->execute();
    $students = $stmt;
} else {
    $query = "SELECT cs.*, c.grade_level, c.section FROM class_students cs 
              JOIN classes c ON cs.class_id = c.id 
              INNER JOIN (SELECT student_id, MIN(id) as min_id FROM class_students GROUP BY student_id) sub 
              ON cs.student_id = sub.student_id AND cs.id = sub.min_id 
              ORDER BY cs.last_name, cs.first_name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $students = $stmt;
}

$existing_attendance = [];
$att_records = $attendance->getByDate($date);
while ($row = $att_records->fetch(PDO::FETCH_ASSOC)) {
    $existing_attendance[$row['student_id']] = $row;
}

$page_title = "Mark Attendance";
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Mark Attendance</h1>
        <div style="font-size: 18px; font-weight: bold; color: #667eea;">
            <?php echo date('F d, Y'); ?>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-<?php echo $_GET['msg'] === 'error' ? 'error' : 'success'; ?>">
            <?php echo $_GET['msg'] === 'marked' ? 'Attendance marked successfully!' : 'An error occurred!'; ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
        <div class="form-group" style="flex: 1;">
            <label>Grade Level:</label>
            <select id="grade_filter" onchange="applyFilters()">
                <option value="">All Grades</option>
                <?php foreach ($grades as $grade): ?>
                    <option value="<?php echo htmlspecialchars($grade); ?>" <?php echo $grade_filter == $grade ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($grade); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="flex: 1;">
            <label>Section:</label>
            <select id="section_filter" onchange="applyFilters()">
                <option value="">All Sections</option>
                <?php foreach ($sections as $section): ?>
                    <option value="<?php echo htmlspecialchars($section); ?>" <?php echo $section_filter == $section ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($section); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <form action="../controllers/attendance_controller.php" method="POST">
        <input type="hidden" name="action" value="mark">
        <input type="hidden" name="date" value="<?php echo $date; ?>">

        <h2>Students</h2>
        <div style="position: relative; max-width: 400px; margin-bottom: 20px;">
            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999;">🔍</span>
            <input type="text" id="searchName" placeholder="Search by name..." onkeyup="filterTable()" style="width: 100%; padding: 10px 10px 10px 35px; border: 2px solid #e0e0e0; border-radius: 10px;">
        </div>
        <table id="studentTable">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Grade</th>
                    <th>Section</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $students->fetch(PDO::FETCH_ASSOC)): 
                    $current_status = isset($existing_attendance[$row['id']]) ? $existing_attendance[$row['id']]['status'] : 'present';
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['last_name'] . ', ' . $row['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['grade_level']); ?></td>
                    <td><?php echo htmlspecialchars($row['section']); ?></td>
                    <td>
                        <select name="students[<?php echo $row['id']; ?>]" required>
                            <option value="present" <?php echo $current_status === 'present' ? 'selected' : ''; ?>>Present</option>
                            <option value="late" <?php echo $current_status === 'late' ? 'selected' : ''; ?>>Late</option>
                            <option value="excused" <?php echo $current_status === 'excused' ? 'selected' : ''; ?>>Excused</option>
                            <option value="absent" <?php echo $current_status === 'absent' ? 'selected' : ''; ?>>Absent</option>
                        </select>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <button type="submit" class="btn btn-success">Save Attendance</button>
    </form>
</div>

<script>
function applyFilters() {
    const grade = document.getElementById('grade_filter').value;
    const section = document.getElementById('section_filter').value;
    
    let url = 'mark_attendance.php?';
    if (grade) url += 'grade=' + encodeURIComponent(grade) + '&';
    if (section) url += 'section=' + encodeURIComponent(section);
    
    window.location.href = url;
}

function filterTable() {
    const input = document.getElementById('searchName');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('studentTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const nameCell = rows[i].getElementsByTagName('td')[1];
        if (nameCell) {
            const nameText = nameCell.textContent || nameCell.innerText;
            if (nameText.toLowerCase().indexOf(filter) > -1) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }
}
</script>

<?php include '../includes/footer.php'; ?>
