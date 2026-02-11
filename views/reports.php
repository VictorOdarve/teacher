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

$report = $attendance->getReport($start_date, $end_date);

// Get unique sections and subjects for dropdowns
$classes = $classModel->getAll();
$sections = [];
$subjects = [];
while ($class = $classes->fetch(PDO::FETCH_ASSOC)) {
    if (!in_array($class['section'], $sections)) {
        $sections[] = $class['section'];
    }
    if (!in_array($class['subject'], $subjects)) {
        $subjects[] = $class['subject'];
    }
}

$page_title = "Attendance Reports";
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <h1>Attendance Reports</h1>

    <div class="form-group">
        <label>Select Report Type:</label>
        <select id="report-type" style="width: 200px;">
            <option value="">Select a report...</option>
            <option value="attendance_summary">Attendance Summary</option>
            <option value="passed_students">Passed Students</option>
            <option value="failed_students_report">Failed Students Report</option>
        </select>
    </div>

    <div class="form-group">
        <label for="section">Section:</label>
        <select id="section" style="width: 150px;">
            <option value="">All Sections</option>
            <?php foreach ($sections as $sec): ?>
                <option value="<?php echo htmlspecialchars($sec); ?>" <?php echo $section == $sec ? 'selected' : ''; ?>><?php echo htmlspecialchars($sec); ?></option>
            <?php endforeach; ?>
        </select>
        <label for="subject">Subject:</label>
        <select id="subject" style="width: 150px;">
            <option value="">All Subjects</option>
            <?php foreach ($subjects as $sub): ?>
                <option value="<?php echo htmlspecialchars($sub); ?>" <?php echo $subject == $sub ? 'selected' : ''; ?>><?php echo htmlspecialchars($sub); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="date-filters" class="form-group" style="float: right;">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" value="<?php echo $start_date; ?>">
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" value="<?php echo $end_date; ?>">
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
    var startDate = document.getElementById('start_date').value;
    var endDate = document.getElementById('end_date').value;

    // Hide date filters for passed_students and failed_students_report
    if (selectedValue === 'passed_students' || selectedValue === 'failed_students_report') {
        document.getElementById('date-filters').style.display = 'none';
    } else {
        document.getElementById('date-filters').style.display = 'block';
    }

    if (selectedValue) {
        var url = 'reports/' + selectedValue + '.php?section=' + encodeURIComponent(section) + '&subject=' + encodeURIComponent(subject);
        // Only add date parameters if not passed_students or failed_students_report
        if (selectedValue !== 'passed_students' && selectedValue !== 'failed_students_report') {
            url += '&start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate);
        }
        fetch(url)
            .then(response => response.text())
            .then(data => {
                document.getElementById('report-content').innerHTML = data;
            })
            .catch(error => {
                console.error('Error loading report:', error);
                document.getElementById('report-content').innerHTML = '<p>Error loading report.</p>';
            });
    } else {
        document.getElementById('report-content').innerHTML = '';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
