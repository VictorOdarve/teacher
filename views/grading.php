<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$grade_filter = isset($_GET['grade']) ? $_GET['grade'] : '';
$section_filter = isset($_GET['section']) ? $_GET['section'] : '';

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

if ($grade_filter && $section_filter) {
    $query = "SELECT cs.*, c.grade_level, c.section FROM class_students cs
              JOIN classes c ON cs.class_id = c.id
              INNER JOIN (SELECT cs2.student_id, MIN(cs2.id) as min_id FROM class_students cs2
                          JOIN classes c2 ON cs2.class_id = c2.id
                          WHERE c2.grade_level = :grade AND c2.section = :section
                          GROUP BY cs2.student_id) sub ON cs.student_id = sub.student_id AND cs.id = sub.min_id
              ORDER BY cs.last_name, cs.first_name";
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

$grades_query = "SELECT g.*, cs.id as cs_id FROM grades g JOIN class_students cs ON g.student_id = cs.id";
$grades_stmt = $db->prepare($grades_query);
$grades_stmt->execute();
$saved_grades = [];
$saved_totals = [];
while ($grade_row = $grades_stmt->fetch(PDO::FETCH_ASSOC)) {
    $saved_grades[$grade_row['cs_id']][$grade_row['quarter']][$grade_row['grade_type']] = json_decode($grade_row['scores'], true);
    $saved_totals[$grade_row['cs_id']][$grade_row['quarter']][$grade_row['grade_type']] = json_decode($grade_row['total_score'], true);
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
    <div style="margin-top: 12px; margin-bottom: 16px;">
        <input type="text" id="student_search" placeholder="Search student name..." oninput="filterStudents()" style="padding: 8px 12px 8px 32px; border: 1px solid #ccc; border-radius: 6px; font-size: 13px; width: 260px; background: #fff url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'14\' height=\'14\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23aaa\' stroke-width=\'2\'%3E%3Ccircle cx=\'11\' cy=\'11\' r=\'8\'/%3E%3Cpath d=\'m21 21-4.35-4.35\'/%3E%3C/svg%3E') no-repeat 10px center;">
    </div>
    <?php if ($students->rowCount() > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>
                        <button class="btn btn-sm" id="btn_ww" onclick="showScores('ww')" style="padding: 3px 8px; font-size: 11px; margin-right: 3px;">Written Works</button>
                        <button class="btn btn-sm" id="btn_pt" onclick="showScores('pt')" style="padding: 3px 8px; font-size: 11px; margin-right: 3px;">Performance Tasks</button>
                        <button class="btn btn-sm" id="btn_as" onclick="showScores('as')" style="padding: 3px 8px; font-size: 11px;">Assessment</button>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $students->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['last_name'] . ', ' . $row['first_name']); ?></td>
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
                            <div>
                                <?php for($i = 1; $i <= 7; $i++): ?>
                                    <input type="text" class="score-input" data-student="<?php echo $row['id']; ?>" data-type="pt_total" placeholder="Total" style="background-color: #ffff99; width: 40px; padding: 3px; margin: 2px;">
                                <?php endfor; ?>
                            </div>
                            <div>
                                <?php for($i = 1; $i <= 7; $i++): ?>
                                    <input type="text" class="score-input" data-student="<?php echo $row['id']; ?>" data-type="pt" placeholder="Score" style="width: 40px; padding: 3px; margin: 2px;">
                                <?php endfor; ?>
                            </div>
                        </div>
                        <button onclick="addTextbox('pt_container_<?php echo $row['id']; ?>', <?php echo $row['id']; ?>, 'pt')" style="padding: 2px 6px; font-size: 12px; margin-top: 5px;">+</button>
                    </td>
                    <td id="scores_as_<?php echo $row['id']; ?>" style="display: none;">
                        <div id="as_container_<?php echo $row['id']; ?>">
                            <div>
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <input type="text" class="score-input" data-student="<?php echo $row['id']; ?>" data-type="as_total" placeholder="Total" style="background-color: #ffff99; width: 40px; padding: 3px; margin: 2px;">
                                <?php endfor; ?>
                            </div>
                            <div>
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <input type="text" class="score-input" data-student="<?php echo $row['id']; ?>" data-type="as" placeholder="Score" style="width: 40px; padding: 3px; margin: 2px;">
                                <?php endfor; ?>
                            </div>
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
const savedTotals = <?php echo json_encode($saved_totals); ?>;

function filterStudents() {
    const q = document.getElementById('student_search').value.toLowerCase();
    document.querySelectorAll('table tbody tr').forEach(tr => {
        const name = tr.cells[1] ? tr.cells[1].textContent.toLowerCase() : '';
        tr.style.display = name.includes(q) ? '' : 'none';
    });
}

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
    
    for (let i = 1; i <= 4; i++) {
        const btn = document.getElementById('q' + i + '_btn');
        if (i === quarter) {
            btn.className = 'btn btn-primary';
        } else {
            btn.className = 'btn';
        }
    }
    
    loadSavedGrades(quarter);
}

function loadSavedGrades(quarter) {
    document.querySelectorAll('.score-input').forEach(input => {
        const studentId = input.getAttribute('data-student');
        const type = input.getAttribute('data-type');

        if (type.includes('_total')) {
            const baseType = type.replace('_total', '');
            if (savedTotals[studentId] && savedTotals[studentId][quarter] && savedTotals[studentId][quarter][baseType]) {
                const totals = savedTotals[studentId][quarter][baseType];
                const inputs = document.querySelectorAll(`input[data-student="${studentId}"][data-type="${type}"]`);
                totals.forEach((total, index) => {
                    if (inputs[index]) {
                        inputs[index].value = total;
                    }
                });
            } else {
                input.value = '';
            }
        } else {
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
        }
    });
}

window.addEventListener('DOMContentLoaded', function() {
    loadSavedGrades(1);
    
    // Auto-sync total values across all students (same index, same type)
    document.addEventListener('input', function(e) {
        const input = e.target;
        if (!input.classList.contains('score-input')) return;
        const type = input.getAttribute('data-type');
        if (!type || !type.endsWith('_total')) return;

        const allSameType = Array.from(document.querySelectorAll('input.score-input[data-type="' + type + '"]'));
        const index = allSameType.indexOf(input);
        if (index === -1) return;

        // How many total inputs per student for this type?
        const firstStudentId = allSameType[0].getAttribute('data-student');
        const perStudent = allSameType.filter(i => i.getAttribute('data-student') === firstStudentId).length;
        const posInStudent = index % perStudent;

        allSameType.forEach(function(inp) {
            const allForStudent = allSameType.filter(i => i.getAttribute('data-student') === inp.getAttribute('data-student'));
            if (allForStudent[posInStudent]) allForStudent[posInStudent].value = input.value;
        });
    });
});

let activeScoreType = null;

function showScores(type) {
    activeScoreType = type;
    const allScores = document.querySelectorAll('[id^="scores_"]');
    allScores.forEach(el => el.style.display = 'none');

    ['ww', 'pt', 'as'].forEach(t => {
        const btn = document.getElementById('btn_' + t);
        if (btn) btn.style.cssText = t === type
            ? 'padding:3px 8px;font-size:11px;margin-right:3px;background:green;box-shadow:none;'
            : 'padding:3px 8px;font-size:11px;margin-right:3px;';
    });

    const targetScores = document.querySelectorAll('[id^="scores_' + type + '_"]');
    targetScores.forEach(el => el.style.display = 'block');
}

function addTextbox(containerId, studentId, type) {
    console.log('addTextbox called:', containerId, studentId, type);
    // SIMPLIFIED: Add pair to ALL matching containers directly
    ['ww', 'pt', 'as'].forEach(t => {
        if (t === type) {
            const containers = document.querySelectorAll('[id^="' + t + '_container_"]');
            containers.forEach(container => {
                if (container.children.length >= 2) {
                    const totalDiv = container.children[0];
                    const scoreDiv = container.children[1];
                    const sId = container.id.split('_')[2]; // extract ID from ww_container_1

                    // Total input
                    const totalInput = document.createElement('input');
                    totalInput.type = 'text';
                    totalInput.className = 'score-input';
                    totalInput.setAttribute('data-student', sId);
                    totalInput.setAttribute('data-type', type + '_total');
                    totalInput.placeholder = 'Total';
                    totalInput.style.cssText = 'width: 40px; padding: 3px; margin: 2px; background-color: #ffff99;';
                    totalDiv.appendChild(totalInput);

                    // Score input
                    const scoreInput = document.createElement('input');
                    scoreInput.type = 'text';
                    scoreInput.className = 'score-input';
                    scoreInput.setAttribute('data-student', sId);
                    scoreInput.setAttribute('data-type', type);
                    scoreInput.placeholder = 'Score';
                    scoreInput.style.cssText = 'width: 40px; padding: 3px; margin: 2px;';
                    scoreDiv.appendChild(scoreInput);
                }
            });
            console.log('Added pairs to', containers.length, 'containers for type', type);
        }
    });
}

function validateScores(studentId) {
    const types = ['ww', 'pt', 'as'];
    let errors = [];
    let isValid = true;

    document.querySelectorAll('.score-input').forEach(input => {
        input.style.border = '';
        input.title = '';
    });

    types.forEach(type => {
        const scoreInputs = document.querySelectorAll(`input[data-student="${studentId}"][data-type="${type}"]`);
        const totalInputs = document.querySelectorAll(`input[data-student="${studentId}"][data-type="${type}_total"]`);

        scoreInputs.forEach((scoreInput, index) => {
            const scoreValue = scoreInput.value.trim();
            const totalInput = totalInputs[index];
            const totalValue = totalInput ? totalInput.value.trim() : '';

            if (scoreValue !== '' && totalValue !== '') {
                const score = parseFloat(scoreValue);
                const total = parseFloat(totalValue);

                if (score < 0) {
                    scoreInput.style.border = '2px solid red';
                    scoreInput.title = 'Score cannot be less than 0';
                    errors.push(`Score (${score}) cannot be less than 0 (Total: ${total})`);
                    isValid = false;
                } else if (score > total) {
                    scoreInput.style.border = '2px solid red';
                    scoreInput.title = `Score (${score}) cannot be greater than Total (${total})`;
                    errors.push(`Score (${score}) cannot be greater than Total (${total})`);
                    isValid = false;
                }
            } else if (scoreValue !== '' && totalValue === '') {
                scoreInput.style.border = '2px solid red';
                scoreInput.title = 'Please enter a Total value first';
                errors.push('Please enter Total value for each Score');
                isValid = false;
            }
        });
    });

    if (!isValid) {
        alert('Validation Error:\n' + errors.join('\n'));
    }

    return isValid;
}

function saveGrades(studentId) {
    if (!validateScores(studentId)) {
        return;
    }

    // Collect all unique student IDs on the page
    const allStudentIds = [...new Set(
        Array.from(document.querySelectorAll('.score-input')).map(i => i.getAttribute('data-student'))
    )];

    const quarter = document.getElementById('selected_quarter').value;
    const types = ['ww', 'pt', 'as'];
    let savedCount = 0;
    let totalToSave = 0;

    allStudentIds.forEach(sid => {
        types.forEach(type => {
            const scoreInputs = document.querySelectorAll(`input[data-student="${sid}"][data-type="${type}"]`);
            const totalInputs = document.querySelectorAll(`input[data-student="${sid}"][data-type="${type}_total"]`);
            const scores = [];
            const totals = [];

            scoreInputs.forEach(input => scores.push(input.value));
            totalInputs.forEach(input => totals.push(input.value));

            if (scores.some(s => s.trim() !== '') || totals.some(t => t.trim() !== '')) {
                totalToSave++;
                const formData = new FormData();
                formData.append('action', 'save');
                formData.append('student_id', sid);
                formData.append('quarter', quarter);
                formData.append('grade_type', type);
                formData.append('scores', JSON.stringify(scores));
                formData.append('total_score', JSON.stringify(totals));

                fetch('../controllers/grades_controller.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        savedCount++;
                        if (!savedGrades[sid]) savedGrades[sid] = {};
                        if (!savedGrades[sid][quarter]) savedGrades[sid][quarter] = {};
                        savedGrades[sid][quarter][type] = scores;

                        if (!savedTotals[sid]) savedTotals[sid] = {};
                        if (!savedTotals[sid][quarter]) savedTotals[sid][quarter] = {};
                        savedTotals[sid][quarter][type] = totals;

                        if (savedCount === totalToSave) {
                            alert('Grades saved successfully!');
                            if (activeScoreType) showScores(activeScoreType);
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
    });

    if (totalToSave === 0) {
        alert('Please enter some scores before saving.');
    }
}
</script>

<?php include '../includes/footer.php'; ?>

