<?php
require_once '../controllers/teacher_dashboard_controller.php';

$page_title = "Teacher Dashboard";
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <h1>Teacher Dashboard</h1>

    <!-- Class Selector -->
    <div class="form-group" style="margin-bottom: 30px;">
        <label for="class_selector">Select Class:</label>
        <select id="class_selector" onchange="changeClass()">
            <option value="">-- Select a Class --</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?php echo $class['id']; ?>" <?php echo ($selected_class_id == $class['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($class['grade_level'] . ' - ' . $class['section'] . ' - ' . $class['subject']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Today's Schedule -->
    <div class="dashboard-section">
        <h2>Today's Schedule</h2>
        <?php if (empty($today_schedule)): ?>
            <p>No classes scheduled for today.</p>
        <?php else: ?>
            <div class="schedule-list">
                <?php foreach ($today_schedule as $sched): ?>
                    <div class="schedule-item">
                        <strong><?php echo htmlspecialchars($sched['subject']); ?></strong> -
                        <?php echo htmlspecialchars($sched['grade_level'] . ' ' . $sched['section']); ?> -
                        <?php echo date('g:i A', strtotime($sched['start_time'])); ?> - <?php echo date('g:i A', strtotime($sched['end_time'])); ?> -
                        Room <?php echo htmlspecialchars($sched['room']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($selected_class_id && $class_stats): ?>
        <!-- Class Quick Stats -->
        <div class="dashboard-section">
            <h2>Class Quick Stats</h2>
            <div class="stats">
                <div class="stat-card">
                    <h3>Total Students</h3>
                    <div class="number"><?php echo $class_stats['total_students']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Present Today</h3>
                    <div class="number"><?php echo $class_stats['present']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Absent Today</h3>
                    <div class="number"><?php echo $class_stats['absent']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Late Today</h3>
                    <div class="number"><?php echo $class_stats['late']; ?></div>
                </div>
            </div>
        </div>

        <!-- Recent Class Activities -->
        <div class="dashboard-section">
            <h2>Recent Class Activities</h2>
            <?php if (empty($recent_activities)): ?>
                <p>No recent activities recorded.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($recent_activities as $activity): ?>
                        <li><?php echo date('M d, Y', strtotime($activity['activity_date'])); ?>: <?php echo htmlspecialchars($activity['activity_text']); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Students Requiring Attention -->
        <div class="dashboard-section">
            <h2>Students Requiring Attention</h2>
            <?php if (empty($students_attention)): ?>
                <p>No students currently flagged.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($students_attention as $student): ?>
                        <li><?php echo htmlspecialchars($student); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function changeClass() {
    const classId = document.getElementById('class_selector').value;
    if (classId) {
        window.location.href = '?class_id=' + classId;
    } else {
        window.location.href = 'teacher_dashboard.php';
    }
}
</script>

<style>
.dashboard-section {
    margin-bottom: 30px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.schedule-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.schedule-item {
    padding: 10px;
    background-color: #f9f9f9;
    border-radius: 3px;
}

.stats {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.stat-card {
    flex: 1;
    min-width: 150px;
    text-align: center;
    padding: 20px;
    background-color: #f0f0f0;
    border-radius: 5px;
}

.stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
}

.stat-card .number {
    font-size: 24px;
    font-weight: bold;
}
</style>

<?php include '../includes/footer.php'; ?>
