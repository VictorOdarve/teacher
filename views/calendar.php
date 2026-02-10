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

// Get current month and year
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Get activities for this class and month
$query = "SELECT * FROM class_activities WHERE class_id = :class_id AND MONTH(activity_date) = :month AND YEAR(activity_date) = :year";
$stmt = $db->prepare($query);
$stmt->bindParam(":class_id", $class_id);
$stmt->bindParam(":month", $month);
$stmt->bindParam(":year", $year);
$stmt->execute();
$activities = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $activities[$row['activity_date']] = $row['activity_text'];
}

$page_title = "Activities Calendar";
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <h1>Activities Calendar - <?php echo htmlspecialchars($class['subject'] ?? 'Class'); ?></h1>
    <p><strong>Grade:</strong> <?php echo htmlspecialchars($class['grade_level'] ?? ''); ?> | <strong>Section:</strong> <?php echo htmlspecialchars($class['section'] ?? ''); ?></p>
    
    <div class="calendar-nav">
        <a href="?class_id=<?php echo $class_id; ?>&month=<?php echo date('m', strtotime("$year-$month-01 -1 month")); ?>&year=<?php echo date('Y', strtotime("$year-$month-01 -1 month")); ?>" class="btn btn-primary">Previous</a>
        <h2><?php echo date('F Y', strtotime("$year-$month-01")); ?></h2>
        <a href="?class_id=<?php echo $class_id; ?>&month=<?php echo date('m', strtotime("$year-$month-01 +1 month")); ?>&year=<?php echo date('Y', strtotime("$year-$month-01 +1 month")); ?>" class="btn btn-primary">Next</a>
    </div>
    
    <div class="calendar">
        <div class="calendar-header">
            <div class="calendar-day-name">Sun</div>
            <div class="calendar-day-name">Mon</div>
            <div class="calendar-day-name">Tue</div>
            <div class="calendar-day-name">Wed</div>
            <div class="calendar-day-name">Thu</div>
            <div class="calendar-day-name">Fri</div>
            <div class="calendar-day-name">Sat</div>
        </div>
        <div class="calendar-body">
            <?php
            $first_day = date('w', strtotime("$year-$month-01"));
            $days_in_month = date('t', strtotime("$year-$month-01"));
            
            // Empty cells before first day
            for ($i = 0; $i < $first_day; $i++) {
                echo '<div class="calendar-day empty"></div>';
            }
            
            // Days of the month
            for ($day = 1; $day <= $days_in_month; $day++) {
                $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
                $activity = isset($activities[$date]) ? $activities[$date] : '';
                $today = ($date == date('Y-m-d')) ? 'today' : '';
                
                echo '<div class="calendar-day ' . $today . '" onclick="openActivityModal(\'' . $date . '\', \'' . htmlspecialchars($activity, ENT_QUOTES) . '\')">';
                echo '<div class="day-number">' . $day . '</div>';
                if ($activity) {
                    echo '<div class="activity-text">' . htmlspecialchars($activity) . '</div>';
                }
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>

<div id="activityModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeActivityModal()">&times;</span>
        <h2>Add/Edit Activity</h2>
        <form action="../controllers/activity_controller.php" method="POST">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
            <input type="hidden" name="activity_date" id="modal_activity_date">
            <input type="hidden" name="month" value="<?php echo $month; ?>">
            <input type="hidden" name="year" value="<?php echo $year; ?>">
            
            <div class="form-group">
                <label>Date:</label>
                <input type="text" id="display_date" readonly>
            </div>
            
            <div class="form-group">
                <label>Activity:</label>
                <textarea name="activity_text" id="modal_activity_text" rows="4" placeholder="Enter activity details..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-success">Save Activity</button>
            <button type="button" class="btn" onclick="closeActivityModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
function openActivityModal(date, activity) {
    document.getElementById('modal_activity_date').value = date;
    document.getElementById('display_date').value = date;
    document.getElementById('modal_activity_text').value = activity;
    document.getElementById('activityModal').style.display = 'block';
}

function closeActivityModal() {
    document.getElementById('activityModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('activityModal');
    if (event.target == modal) {
        closeActivityModal();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
