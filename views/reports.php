<?php
require_once '../config/database.php';
require_once '../models/Attendance.php';
require_once '../models/ClassModel.php';

$database = new Database();
$db = $database->getConnection();
$attendance = new Attendance($db);
$classModel = new ClassModel($db);

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$section = isset($_GET['section']) ? $_GET['section'] : '';
$subject = isset($_GET['subject']) ? $_GET['subject'] : '';
$grade_level = isset($_GET['grade_level']) ? $_GET['grade_level'] : '';

$report = $attendance->getReport($start_date, $end_date, $section, $subject, $grade_level);

// Get unique sections and subjects for dropdowns
$classes = $classModel->getAll();
$sections = [];
$subjects = [];
$grade_levels = [];
while ($class = $classes->fetch(PDO::FETCH_ASSOC)) {
    if (!in_array($class['section'], $sections)) {
        $sections[] = $class['section'];
    }
    if (!in_array($class['subject'], $subjects)) {
        $subjects[] = $class['subject'];
    }
    if (!in_array($class['grade_level'], $grade_levels)) {
        $grade_levels[] = $class['grade_level'];
    }
}

$page_title = "Attendance Reports";
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <h1>Reports</h1>

    <div class="form-group">
        <label>Select Report Type:</label>
        <select id="report-type" style="width: 200px;">
            <option value="">Select a report...</option>
            <option value="attendance_report">Attendance Report</option>
            <option value="passed_students">Passed Students</option>
            <option value="failed_students_report">Failed Students Report</option>
        </select>
    </div>

    <div class="form-group">
        <label for="section">Section:</label>
        <select id="section" style="width: 150px;">
            <option value="">All Sections</option>
            <?php foreach ($sections as $sec): ?>
                <option value="<?php echo htmlspecialchars($sec, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $section == $sec ? 'selected' : ''; ?>><?php echo htmlspecialchars($sec, ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
        </select>
        <label for="subject">Subject:</label>
        <select id="subject" style="width: 150px;">
            <option value="">All Subjects</option>
            <?php foreach ($subjects as $sub): ?>
                <option value="<?php echo htmlspecialchars($sub, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $subject == $sub ? 'selected' : ''; ?>><?php echo htmlspecialchars($sub, ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
        </select>
        <label for="grade_level">Grade Level:</label>
        <select id="grade_level" style="width: 150px;">
            <option value="">All Grades</option>
            <?php foreach ($grade_levels as $gl): ?>
                <option value="<?php echo htmlspecialchars($gl, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $grade_level == $gl ? 'selected' : ''; ?>><?php echo htmlspecialchars($gl, ENT_QUOTES, 'UTF-8'); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="date-filters" class="form-group" style="float: right;">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" value="<?php echo htmlspecialchars($start_date, ENT_QUOTES, 'UTF-8'); ?>">
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" value="<?php echo htmlspecialchars($end_date, ENT_QUOTES, 'UTF-8'); ?>">
    </div>

    <div class="form-group" id="search-box" style="display:none; margin-top: 10px;">
        <label for="search_name"><i class="fa-solid fa-magnifying-glass"></i> Search Name:</label>
        <input type="text" id="search_name" placeholder="Type student name..." oninput="filterTable()" style="width: 220px;">
    </div>

    <div id="report-content"></div>
</div>

<script>
document.getElementById('report-type').addEventListener('change', function() {
    loadReport();
});

document.getElementById('section').addEventListener('change', function() {
    loadReport();
});

document.getElementById('subject').addEventListener('change', function() {
    loadReport();
});

document.getElementById('grade_level').addEventListener('change', function() {
    loadReport();
});

document.getElementById('start_date').addEventListener('change', function() {
    loadReport();
});

document.getElementById('end_date').addEventListener('change', function() {
    loadReport();
});

function loadReport() {
    var selectedValue = document.getElementById('report-type').value;
    var section = document.getElementById('section').value;
    var subject = document.getElementById('subject').value;
    var grade_level = document.getElementById('grade_level').value;
    var startDate = document.getElementById('start_date').value;
    var endDate = document.getElementById('end_date').value;

    // Hide date filters for passed_students and failed_students_report
    if (selectedValue === 'passed_students' || selectedValue === 'failed_students_report') {
        document.getElementById('date-filters').style.display = 'none';
    } else {
        document.getElementById('date-filters').style.display = 'block';
    }

    if (selectedValue) {
        var url = 'reports/' + selectedValue + '.php?section=' + encodeURIComponent(section) + '&subject=' + encodeURIComponent(subject) + '&grade_level=' + encodeURIComponent(grade_level);
        // Only add date parameters if not passed_students or failed_students_report
        if (selectedValue !== 'passed_students' && selectedValue !== 'failed_students_report') {
            url += '&start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate);
        }
        fetch(url)
            .then(response => response.text())
            .then(data => {
                document.getElementById('report-content').innerHTML = data;
                document.getElementById('search_name').value = '';
                document.getElementById('search-box').style.display = 'block';
                // Re-init charts after AJAX load
                if (typeof Chart !== 'undefined') {
                    const canvases = document.querySelectorAll('#report-content canvas');
                    canvases.forEach(canvas => {
                        if (canvas.id === 'attendancePieChart' && !canvas.chart) {
                            const ctx = canvas.getContext('2d');
                            const pieData = JSON.parse(canvas.dataset.piedata || '{}');
                            new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: ['Present', 'Absent', 'Tardy', 'Excused'],
                                    datasets: [{
                                        data: [pieData.present, pieData.absent, pieData.late, pieData.excused],
                                        backgroundColor: ['#4BC0C0','#FF6384','#FFCE56','#36A2EB']
                                    }]
                                },
                                options: { responsive: true, plugins: { legend: { position: 'right' } } }
                            });
                            canvas.chart = true;
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error loading report:', error);
                document.getElementById('report-content').innerHTML = '<p>Error loading report.</p>';
            });
    } else {
        document.getElementById('report-content').innerHTML = '';
        document.getElementById('search-box').style.display = 'none';
    }
}

function filterTable() {
    var search = document.getElementById('search_name').value.toLowerCase();
    var rows = document.querySelectorAll('#report-content table tbody tr');
    rows.forEach(function(row) {
        var name = row.cells[0] ? row.cells[0].textContent.toLowerCase() : '';
        row.style.display = name.includes(search) ? '' : 'none';
    });
}
</script>

<?php include '../includes/footer.php'; ?>
