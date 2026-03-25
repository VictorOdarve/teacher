<?php
require_once '../controllers/admin_dashboard_controller.php';

$page_title = "Admin Dashboard";
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <div class="admin-hero">
        <div>
            <h1>Admin Dashboard</h1>
            <p class="admin-subtitle">Overview of users, classes, and daily activity.</p>
        </div>
        <div class="admin-meta">
            <span><?php echo htmlspecialchars($_SESSION['name'] ?? 'Administrator'); ?></span>
            <span><?php echo date('M d, Y'); ?></span>
        </div>
    </div>

    <?php if ($dashboard_error !== ''): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($dashboard_error); ?></div>
    <?php endif; ?>

    <div class="stats">
        <div class="stat-card">
            <h3>Admin Accounts</h3>
            <div class="number"><?php echo $overview['admins']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Teacher Accounts</h3>
            <div class="number"><?php echo $overview['teachers']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Student Accounts</h3>
            <div class="number"><?php echo $overview['student_accounts']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Classes</h3>
            <div class="number"><?php echo $overview['classes']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Enrolled Students</h3>
            <div class="number"><?php echo $overview['enrolled_students']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Logins Today</h3>
            <div class="number"><?php echo $overview['logins_today']; ?></div>
        </div>
    </div>

    <div class="admin-panels">
        <section class="admin-panel">
            <h2>Attendance Today</h2>
            <div class="admin-metrics">
                <div>
                    <strong>Present</strong>
                    <span><?php echo $attendance_breakdown['present']; ?></span>
                </div>
                <div>
                    <strong>Late</strong>
                    <span><?php echo $attendance_breakdown['late']; ?></span>
                </div>
                <div>
                    <strong>Excused</strong>
                    <span><?php echo $attendance_breakdown['excused']; ?></span>
                </div>
                <div>
                    <strong>Absent</strong>
                    <span><?php echo $attendance_breakdown['absent']; ?></span>
                </div>
            </div>
        </section>

        <section class="admin-panel">
            <h2>Quick Actions</h2>
            <div class="quick-links">
                <a href="teacher_management.php" class="btn">Teacher Management</a>
                <a href="students.php" class="btn btn-primary">Manage Students</a>
                <a href="classes.php" class="btn">Open Classes</a>
                <a href="reports.php" class="btn btn-success">View Reports</a>
                <a href="my_account.php" class="btn btn-warning">Account Activity</a>
            </div>
        </section>
    </div>

    <div class="admin-panels">
        <section class="admin-panel">
            <h2>Recent Logins</h2>
            <?php if (count($recent_logins) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Last Login</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_logins as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo htmlspecialchars($row['role']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($row['role'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y h:i A', strtotime($row['last_login_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No login activity recorded yet.</p>
            <?php endif; ?>
        </section>

        <section class="admin-panel">
            <h2>Grade Level Summary</h2>
            <?php if (count($grade_summary) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Grade Level</th>
                            <th>Classes</th>
                            <th>Students</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grade_summary as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['grade_level']); ?></td>
                                <td><?php echo (int)$row['total_classes']; ?></td>
                                <td><?php echo (int)$row['total_students']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No class data available.</p>
            <?php endif; ?>
        </section>
    </div>
</div>

<style>
.admin-hero {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    align-items: flex-start;
    margin-bottom: 24px;
}

.admin-subtitle {
    color: #555;
    margin-top: -8px;
}

.admin-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    text-align: right;
    color: #555;
    font-weight: 600;
}

.admin-panels {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
    margin-top: 24px;
}

.admin-panel {
    border: 1px solid #e3e3e3;
    border-radius: 12px;
    padding: 20px;
    background: #fafafa;
}

.admin-metrics {
    display: grid;
    grid-template-columns: repeat(2, minmax(120px, 1fr));
    gap: 16px;
}

.admin-metrics div {
    padding: 16px;
    border-radius: 10px;
    background: #fff;
    border: 1px solid #ececec;
}

.admin-metrics strong,
.admin-metrics span {
    display: block;
}

.admin-metrics span {
    font-size: 28px;
    font-weight: 700;
    color: #667eea;
    margin-top: 8px;
}

.quick-links {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.role-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}

.role-admin {
    background: #ffe3e3;
    color: #b02a37;
}

.role-teacher {
    background: #e3f2fd;
    color: #0b5ed7;
}

.role-student {
    background: #e6ffed;
    color: #198754;
}

@media (max-width: 768px) {
    .admin-hero {
        flex-direction: column;
    }

    .admin-meta {
        text-align: left;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
