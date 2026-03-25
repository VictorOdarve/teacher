<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$page_title = "Account Activity";
include '../includes/header.php';
include '../includes/nav.php';

$is_student = isset($_SESSION['role']) && $_SESSION['role'] === 'student';
$accountRows = [];
$myAccount = null;
$searchName = trim($_GET['search'] ?? '');

if ($is_student) {
    $myAccountQuery = "SELECT id, name, username, email, role, created_at, last_login_at
                       FROM users
                       WHERE id = :id
                       LIMIT 1";
    $myAccountStmt = $db->prepare($myAccountQuery);
    $myAccountStmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $myAccountStmt->execute();
    $myAccount = $myAccountStmt->fetch(PDO::FETCH_ASSOC);
} else {
    $accountsQuery = "SELECT
                        u.id,
                        u.name,
                        u.username,
                        u.email,
                        u.created_at,
                        u.last_login_at,
                        cs.student_id AS student_number
                      FROM users u
                      LEFT JOIN class_students cs ON cs.id = u.student_profile_id
                      WHERE u.role = 'student'
                        AND u.last_login_at IS NOT NULL";

    if ($searchName !== '') {
        $accountsQuery .= " AND u.name LIKE :search_name";
    }

    $accountsQuery .= " ORDER BY
                        u.last_login_at DESC,
                        u.created_at DESC";

    $accountsStmt = $db->prepare($accountsQuery);
    if ($searchName !== '') {
        $searchTerm = '%' . $searchName . '%';
        $accountsStmt->bindParam(':search_name', $searchTerm);
    }
    $accountsStmt->execute();
    $accountRows = $accountsStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="card">
    <h1>Account Activity</h1>

    <?php if ($is_student): ?>
        <?php if ($myAccount): ?>
            <table>
                <tbody>
                    <tr>
                        <th style="width: 220px;">Name</th>
                        <td><?php echo htmlspecialchars($myAccount['name']); ?></td>
                    </tr>
                    <tr>
                        <th>Email / Username</th>
                        <td><?php echo htmlspecialchars($myAccount['email'] ?: $myAccount['username']); ?></td>
                    </tr>
                    <tr>
                        <th>Account Created</th>
                        <td><?php echo $myAccount['created_at'] ? date('M d, Y h:i A', strtotime($myAccount['created_at'])) : '-'; ?></td>
                    </tr>
                    <tr>
                        <th>Last Opened (Login)</th>
                        <td><?php echo $myAccount['last_login_at'] ? date('M d, Y h:i A', strtotime($myAccount['last_login_at'])) : 'Never'; ?></td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <p>Account details not found.</p>
        <?php endif; ?>
    <?php else: ?>
        <h2>Student Account Activity</h2>
        <form method="GET" action="my_account.php" style="margin-bottom: 16px;">
            <div class="form-group" style="max-width: 420px;">
                <label for="search">Search Student Name</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    placeholder="Type student name..."
                    value="<?php echo htmlspecialchars($searchName); ?>"
                >
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($searchName !== ''): ?>
                <a href="my_account.php" class="btn">Clear</a>
            <?php endif; ?>
        </form>

        <?php if (count($accountRows) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student Number</th>
                        <th>Login Identifier</th>
                        <th>Account Created</th>
                        <th>Last Opened (Login)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accountRows as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['student_number'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?: $row['username']); ?></td>
                            <td><?php echo $row['created_at'] ? date('M d, Y h:i A', strtotime($row['created_at'])) : '-'; ?></td>
                            <td><?php echo $row['last_login_at'] ? date('M d, Y h:i A', strtotime($row['last_login_at'])) : 'Never'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No student accounts found.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
