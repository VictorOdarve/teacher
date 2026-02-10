<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get distinct grades and sections
$grade_query = "SELECT DISTINCT grade_level FROM classes ORDER BY grade_level";
$grade_stmt = $db->prepare($grade_query);
$grade_stmt->execute();
$grades = $grade_stmt->fetchAll(PDO::FETCH_COLUMN);

$section_query = "SELECT DISTINCT section FROM classes ORDER BY section";
$section_stmt = $db->prepare($section_query);
$section_stmt->execute();
$sections = $section_stmt->fetchAll(PDO::FETCH_COLUMN);

$query = "SELECT cs.*, c.grade_level, c.section FROM class_students cs 
          JOIN classes c ON cs.class_id = c.id 
          ORDER BY cs.last_name, cs.first_name";
$stmt = $db->prepare($query);
$stmt->execute();
$students = $stmt;

$page_title = "Manage Students";
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <h1>Manage Students</h1>

    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
        <div class="form-group" style="flex: 1;">
            <label>Search Name:</label>
            <input type="text" id="name_search" placeholder="Search by name..." style="width: 100%; padding: 8px;">
        </div>
        <div class="form-group" style="flex: 1;">
            <label>Grade Level:</label>
            <select id="grade_search" style="width: 100%; padding: 8px;">
                <option value="">All Grades</option>
                <?php foreach ($grades as $grade): ?>
                    <option value="<?php echo htmlspecialchars($grade); ?>"><?php echo htmlspecialchars($grade); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="flex: 1;">
            <label>Section:</label>
            <select id="section_search" style="width: 100%; padding: 8px;">
                <option value="">All Sections</option>
                <?php foreach ($sections as $section): ?>
                    <option value="<?php echo htmlspecialchars($section); ?>"><?php echo htmlspecialchars($section); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <h2 style="margin-top: 40px;">Student List</h2>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Grade Level</th>
                <th>Section</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $students->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                <td><?php echo htmlspecialchars($row['grade_level']); ?></td>
                <td><?php echo htmlspecialchars($row['section']); ?></td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="viewStudent(<?php echo $row['id']; ?>)">View</button>
                    <button class="btn btn-sm" onclick="editStudent(<?php echo $row['id']; ?>)">Edit</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
function filterStudents() {
    const nameSearch = document.getElementById('name_search').value.toLowerCase();
    const gradeSearch = document.getElementById('grade_search').value.toLowerCase();
    const sectionSearch = document.getElementById('section_search').value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const name = row.cells[1].textContent.toLowerCase();
        const grade = row.cells[2].textContent.toLowerCase();
        const section = row.cells[3].textContent.toLowerCase();
        
        const matchName = name.includes(nameSearch);
        const matchGrade = gradeSearch === '' || grade === gradeSearch;
        const matchSection = sectionSearch === '' || section === sectionSearch;
        
        if (matchName && matchGrade && matchSection) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function viewStudent(id) {
    window.location.href = 'student_view.php?id=' + id;
}

function editStudent(id) {
    alert('Edit student ID: ' + id);
    // Add edit functionality here
}

document.getElementById('name_search').addEventListener('keyup', filterStudents);
document.getElementById('grade_search').addEventListener('change', filterStudents);
document.getElementById('section_search').addEventListener('change', filterStudents);
</script>

<?php include '../includes/footer.php'; ?>
