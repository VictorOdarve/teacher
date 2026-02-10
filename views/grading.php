<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

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

$subject_query = "SELECT DISTINCT subject FROM classes ORDER BY subject";
$subject_stmt = $db->prepare($subject_query);
$subject_stmt->execute();
$subjects = $subject_stmt->fetchAll(PDO::FETCH_COLUMN);

$subject_filter = isset($_GET['subject']) ? $_GET['subject'] : '';

// Get students based on filters
if ($grade_filter && $section_filter) {
    $query = "SELECT DISTINCT cs.*, c.grade_level, c.section FROM class_students cs 
              JOIN classes c ON cs.class_id = c.id 
              WHERE c.grade_level = :grade AND c.section = :section 
              ORDER BY cs.last_name, cs.first_name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":grade", $grade_filter);
    $stmt->bindParam(":section", $section_filter);
    $stmt->execute();
    $students = $stmt;
} elseif ($grade_filter) {
    $query = "SELECT DISTINCT cs.*, c.grade_level, c.section FROM class_students cs 
              JOIN classes c ON cs.class_id = c.id 
              WHERE c.grade_level = :grade 
              ORDER BY cs.last_name, cs.first_name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":grade", $grade_filter);
    $stmt->execute();
    $students = $stmt;
} elseif ($section_filter) {
    $query = "SELECT DISTINCT cs.*, c.grade_level, c.section FROM class_students cs 
              JOIN classes c ON cs.class_id = c.id 
              WHERE c.section = :section 
              ORDER BY cs.last_name, cs.first_name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":section", $section_filter);
    $stmt->execute();
    $students = $stmt;
} else {
    $query = "SELECT DISTINCT cs.*, c.grade_level, c.section FROM class_students cs 
              JOIN classes c ON cs.class_id = c.id 
              ORDER BY cs.last_name, cs.first_name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $students = $stmt;
}

// Get saved grades for all students
$grades_query = "SELECT * FROM grades";
$grades_stmt = $db->prepare($grades_query);
$grades_stmt->execute();
$saved_grades = [];
while ($grade_row = $grades_stmt->fetch(PDO::FETCH_ASSOC)) {
    $saved_grades[$grade_row['student_id']][$grade_row['quarter']][$grade_row['grade_type']] = json_decode($grade_row['scores'], true);
}

$page_title = "Grading";
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <h1>Grading</h1>

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
        <div class="form-group" style="flex: 1;">
            <label>Subject:</label>
            <select id="subject_filter" onchange="applyFilters()">
                <option value="">All Subjects</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?php echo htmlspecialchars($subject); ?>" <?php echo $subject_filter == $subject ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($subject); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <h2>Student Grades</h2>
    <div style="margin-bottom: 20px;">
        <button class="btn btn-primary" onclick="selectQuarter(1)" id="q1_btn">Quarter 1</button>
        <button class="btn" onclick="selectQuarter(2)" id="q2_btn">Quarter 2</button>
        <button class="btn" onclick="selectQuarter(3)" id="q3_btn">Quarter 3</button>
        <button class="btn" onclick="selectQuarter(4)" id="q4_btn">Quarter 4</button>
    </div>
    <input type="hidden" id="selected_quarter" value="1">
    <?php if ($students->rowCount() > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>
                        <button class="btn btn-sm" onclick="showScores('ww')" style="padding: 3px 8px; font-size: 11px; margin-right: 3px;">Written Works</button>
                        <button class="btn btn-sm" onclick="showScores('pt')" style="padding: 3px 8px; font-size: 11px; margin-right: 3px;">Performance Tasks</button>
                        <button class="btn btn-sm" onclick="showScores('as')" style="padding: 3px 8px; font-size: 11px;">Assessment</button>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $students->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td id="scores_ww_<?php echo $row['id']; ?>" style="display: none;">
                        <div id="ww_container_<?php echo $row['id']; ?>">
                            <div>
                                <?php for($i = 1; $i <= 10; $i++): ?>
                                    <input type="text" class="score-input" data-student="<?php echo $row['id']; ?>" data-type="ww_total" placeholder="Total" style="background-color: #ffff99; width: 40px; padding: 3px; margin: 2px;">
                                <?php endfor; ?>
                            </div>
                            <div>
                                <?php for($i = 11; $i <= 20; $i++): ?>
                                    <input type="text" class="score-input" data-student="<?php echo $row['id']; ?>" data-type="ww" placeholder="Score" style="width: 40px; padding: 3px; margin: 2px;">
                                <?php endfor; ?>
                            </div>
                        </div>
                        <button onclick="addTextbox('ww_container_<?php echo $row['id']; ?>', <?php echo $row['id']; ?>, 'ww')" style="padding: 2px 6px; font-size: 12px; margin-top: 5px;">+</button>
                    </td>
                    <td id="scores_pt_<?php echo $row['id']; ?>" style="display: none;">
                        <div id="pt_container_<?php echo $row['id']; ?>">
                            <?php for($i = 1; $i <= 10; $i++): ?>
                                <input type="text" class="score-input" data-student="<?php echo $row['id']; ?>" data-type="pt" style="width: 40px; padding: 3px; margin: 2px;">
                            <?php endfor; ?>
                        </div>
                        <button onclick="addTextbox('pt_container_<?php echo $row['id']; ?>', <?php echo $row['id']; ?>, 'pt')" style="padding: 2px 6px; font-size: 12px; margin-top: 5px;">+</button>
                    </td>
                    <td id="scores_as_<?php echo $row['id']; ?>" style="display: none;">
                        <div id="as_container_<?php echo $row['id']; ?>">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <input type="text" class="score-input" data-student="<?php echo $row['id']; ?>" data-type="as" style="width: 40px; padding: 3px; margin: 2px;">
                            <?php endfor; ?>
                        </div>
                        <button onclick="addTextbox('as_container_<?php echo $row['id']; ?>', <?php echo $row['id']; ?>, 'as')" style="padding: 2px 6px; font-size: 12px; margin-top: 5px;">+</button>
                    </td>
                    <td>
                        <button class="btn btn-success btn-sm" onclick="saveGrades(<?php echo $row['id']; ?>)">Save</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No students found. Please select grade and section filters.</p>
    <?php endif; ?>
</div>

<script>
const savedGrades = <?php echo json_encode($saved_grades); ?>;

function applyFilters() {
    const grade = document.getElementById('grade_filter').value;
    const section = document.getElementById('section_filter').value;
    const subject = document.getElementById('subject_filter').value;
    
    let url = 'grading.php?';
    if (grade) url += 'grade=' + encodeURIComponent(grade) + '&';
    if (section) url += 'section=' + encodeURIComponent(section) + '&';
    if (subject) url += 'subject=' + encodeURIComponent(subject);
    
    window.location.href = url;
}

function selectQuarter(quarter) {
    document.getElementById('selected_quarter').value = quarter;
    
    // Update button styles
    for (let i = 1; i <= 4; i++) {
        const btn = document.getElementById('q' + i + '_btn');
        if (i === quarter) {
            btn.className = 'btn btn-primary';
        } else {
            btn.className = 'btn';
        }
    }
    
    // Load saved grades for this quarter
    loadSavedGrades(quarter);
}

function loadSavedGrades(quarter) {
    document.querySelectorAll('.score-input').forEach(input => {
        const studentId = input.getAttribute('data-student');
        const type = input.getAttribute('data-type');
        
        if (savedGrades[studentId] && savedGrades[studentId][quarter] && savedGrades[studentId][quarter][type]) {
            const scores = savedGrades[studentId][quarter][type];
            const inputs = document.querySelectorAll(`input[data-student="${studentId}"][data-type="${type}"]`);
            scores.forEach((score, index) => {
                if (inputs[index]) {
                    inputs[index].value = score;
                }
            });
        } else {
            input.value = '';
        }
    });
}

// Load grades on page load
window.addEventListener('DOMContentLoaded', function() {
    loadSavedGrades(1);
});

function showScores(type) {
    const allScores = document.querySelectorAll('[id^="scores_"]');
    allScores.forEach(el => el.style.display = 'none');
    
    const targetScores = document.querySelectorAll('[id^="scores_' + type + '_"]');
    targetScores.forEach(el => el.style.display = 'block');
}

function addTextbox(containerId, studentId, type) {
    const container = document.getElementById(containerId);
    const newInput = document.createElement('input');
    newInput.type = 'text';
    newInput.className = 'score-input';
    newInput.setAttribute('data-student', studentId);
    newInput.setAttribute('data-type', type);
    newInput.style.cssText = 'width: 40px; padding: 3px; margin: 2px;';
    container.appendChild(newInput);
}

function saveGrades(studentId) {
    const quarter = document.getElementById('selected_quarter').value;
    const types = ['ww', 'ww_total', 'pt', 'as'];
    let savedCount = 0;
    let totalToSave = 0;
    
    types.forEach(type => {
        const inputs = document.querySelectorAll(`input[data-student="${studentId}"][data-type="${type}"]`);
        const scores = [];
        inputs.forEach(input => {
            scores.push(input.value);
        });
        
        // Check if there are any scores to save
        if (scores.some(score => score.trim() !== '')) {
            totalToSave++;
            const formData = new FormData();
            formData.append('action', 'save');
            formData.append('student_id', studentId);
            formData.append('quarter', quarter);
            formData.append('grade_type', type);
            formData.append('scores', JSON.stringify(scores));
            
            fetch('../controllers/grades_controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    savedCount++;
                    // Update savedGrades object
                    if (!savedGrades[studentId]) savedGrades[studentId] = {};
                    if (!savedGrades[studentId][quarter]) savedGrades[studentId][quarter] = {};
                    savedGrades[studentId][quarter][type] = scores;
                    
                    if (savedCount === totalToSave) {
                        alert('Grades saved successfully!');
                    }
                } else {
                    console.error('Error:', data.error);
                    alert('Error saving grades. Check console for details.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Error saving grades: ' + error);
            });
        }
    });
    
    if (totalToSave === 0) {
        alert('Please enter some scores before saving.');
    }
}
</script>

<?php include '../includes/footer.php'; ?>
