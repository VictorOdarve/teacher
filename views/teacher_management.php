<?php
require_once '../controllers/teacher_management_controller.php';

$page_title = "Teacher Management";
$teacherModalTitle = $teacher_modal_mode === 'edit' ? 'Edit Teacher' : 'Add New Teacher';
$teacherModalSubtitle = $teacher_modal_mode === 'edit'
    ? 'Update the teacher account and assignment details.'
    : 'Create a teacher account and save the profile details from one place.';
$teacherSubmitLabel = $teacher_modal_mode === 'edit' ? 'Update Teacher' : 'Save Teacher';
$teacherPasswordLabel = $teacher_modal_mode === 'edit' ? 'New Password' : 'Password';
$teacherPasswordPlaceholder = $teacher_modal_mode === 'edit'
    ? 'Leave blank to keep current password'
    : 'Enter a login password';
$teacherPasswordNote = $teacher_modal_mode === 'edit'
    ? 'Leave the password blank if you want to keep the current login password.'
    : 'The password you enter here becomes the teacher\'s login password.';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <div class="teacher-page-header">
        <div>
            <h1>Teacher Management</h1>
            <p class="teacher-page-subtitle">Teacher directory for admin review.</p>
        </div>
    </div>

    <?php if ($dashboard_error !== ''): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($dashboard_error); ?></div>
    <?php endif; ?>

    <?php if ($teacher_feedback_message !== ''): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($teacher_feedback_message); ?></div>
    <?php endif; ?>

    <div class="teacher-toolbar">
        <div class="teacher-search">
            <i class="fa-solid fa-filter"></i>
            <input
                type="text"
                id="teacherSearch"
                placeholder="Filter teachers by name, major, grade, section, or status"
                oninput="filterTeachers()"
            >
        </div>
        <button type="button" class="btn btn-success teacher-add-btn" onclick="openTeacherModal()">
            <i class="fa-solid fa-user-plus"></i>
            <span>Add New Teacher</span>
        </button>
    </div>

    <?php if (count($teachers) > 0): ?>
        <table id="teacherTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Major</th>
                    <th>Grade</th>
                    <th>Section</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teachers as $teacher): ?>
                    <?php
                    $teacherPayload = htmlspecialchars(json_encode([
                        'id' => (int)$teacher['id'],
                        'name' => $teacher['name'],
                        'username' => $teacher['username'],
                        'email' => $teacher['email'] ?? '',
                        'major' => $teacher['major'],
                        'major_value' => $teacher['major_value'] ?? '',
                        'grade_level' => $teacher['grade_level'],
                        'grade_level_value' => $teacher['grade_level_value'] ?? '',
                        'section' => $teacher['section'],
                        'section_value' => $teacher['section_value'] ?? '',
                        'status' => $teacher['status'],
                        'created_at' => $teacher['created_at'] ? date('M d, Y h:i A', strtotime($teacher['created_at'])) : 'Unavailable',
                        'last_login_at' => $teacher['last_login_at'] ? date('M d, Y h:i A', strtotime($teacher['last_login_at'])) : 'Never'
                    ]), ENT_QUOTES, 'UTF-8');
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                        <td><?php echo htmlspecialchars($teacher['major']); ?></td>
                        <td><?php echo htmlspecialchars($teacher['grade_level']); ?></td>
                        <td><?php echo htmlspecialchars($teacher['section']); ?></td>
                        <td>
                            <span class="teacher-status teacher-status-<?php echo strtolower($teacher['status']); ?>">
                                <?php echo htmlspecialchars($teacher['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="teacher-actions">
                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm teacher-action-btn"
                                    data-teacher="<?php echo $teacherPayload; ?>"
                                    onclick="openTeacherViewModal(this)"
                                >
                                    View
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm teacher-action-btn"
                                    data-teacher="<?php echo $teacherPayload; ?>"
                                    onclick="openEditTeacherModal(this)"
                                >
                                    Edit
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p id="teacherEmptyState" class="teacher-empty-state" style="display: none;">No teachers match the current filter.</p>
    <?php else: ?>
        <p>No teacher accounts found.</p>
    <?php endif; ?>
</div>

<div id="teacherViewModal" class="modal">
    <div class="modal-content teacher-view-modal-content">
        <span class="modal-close" onclick="closeTeacherViewModal()">&times;</span>
        <div class="teacher-modal-header">
            <h2>Teacher Details</h2>
            <p>Quick view of the selected teacher account and assignment details.</p>
        </div>

        <div class="teacher-view-grid">
            <div class="teacher-view-item">
                <span class="teacher-view-label">Full Name</span>
                <strong id="viewTeacherName"></strong>
            </div>
            <div class="teacher-view-item">
                <span class="teacher-view-label">Username</span>
                <strong id="viewTeacherUsername"></strong>
            </div>
            <div class="teacher-view-item">
                <span class="teacher-view-label">Email</span>
                <strong id="viewTeacherEmail"></strong>
            </div>
            <div class="teacher-view-item">
                <span class="teacher-view-label">Status</span>
                <strong id="viewTeacherStatus"></strong>
            </div>
            <div class="teacher-view-item">
                <span class="teacher-view-label">Major</span>
                <strong id="viewTeacherMajor"></strong>
            </div>
            <div class="teacher-view-item">
                <span class="teacher-view-label">Grade Level</span>
                <strong id="viewTeacherGradeLevel"></strong>
            </div>
            <div class="teacher-view-item">
                <span class="teacher-view-label">Section</span>
                <strong id="viewTeacherSection"></strong>
            </div>
            <div class="teacher-view-item">
                <span class="teacher-view-label">Last Login</span>
                <strong id="viewTeacherLastLogin"></strong>
            </div>
            <div class="teacher-view-item teacher-view-span-2">
                <span class="teacher-view-label">Account Created</span>
                <strong id="viewTeacherCreatedAt"></strong>
            </div>
        </div>
    </div>
</div>

<div id="teacherModal" class="modal" style="display: <?php echo $should_open_teacher_form ? 'block' : 'none'; ?>;">
    <div class="modal-content teacher-modal-content">
        <span class="modal-close" onclick="closeTeacherModal()">&times;</span>
        <div class="teacher-modal-header">
            <h2 id="teacherModalTitle"><?php echo htmlspecialchars($teacherModalTitle); ?></h2>
            <p id="teacherModalSubtitle"><?php echo htmlspecialchars($teacherModalSubtitle); ?></p>
        </div>

        <?php if ($teacher_form_error !== ''): ?>
            <div id="teacherFormError" class="alert alert-error"><?php echo htmlspecialchars($teacher_form_error); ?></div>
        <?php endif; ?>

        <form id="teacherForm" method="POST" action="teacher_management.php">
            <input type="hidden" id="teacherFormAction" name="action" value="<?php echo $teacher_modal_mode === 'edit' ? 'update_teacher' : 'create_teacher'; ?>">
            <input type="hidden" id="teacherIdField" name="teacher_id" value="<?php echo htmlspecialchars($teacher_form_values['teacher_id']); ?>">

            <div class="teacher-form-grid">
                <div class="form-group teacher-form-span-2">
                    <label for="teacherName">Full Name</label>
                    <input
                        type="text"
                        id="teacherName"
                        name="name"
                        value="<?php echo htmlspecialchars($teacher_form_values['name']); ?>"
                        placeholder="e.g., Maria Santos"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="teacherUsername">Username</label>
                    <input
                        type="text"
                        id="teacherUsername"
                        name="username"
                        value="<?php echo htmlspecialchars($teacher_form_values['username']); ?>"
                        placeholder="e.g., msantos"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="teacherPassword" id="teacherPasswordLabel"><?php echo htmlspecialchars($teacherPasswordLabel); ?></label>
                    <input
                        type="password"
                        id="teacherPassword"
                        name="password"
                        placeholder="<?php echo htmlspecialchars($teacherPasswordPlaceholder); ?>"
                        <?php echo $teacher_modal_mode === 'create' ? 'required' : ''; ?>
                    >
                </div>

                <div class="form-group teacher-form-span-2">
                    <label for="teacherEmail">Email Address</label>
                    <input
                        type="email"
                        id="teacherEmail"
                        name="email"
                        value="<?php echo htmlspecialchars($teacher_form_values['email']); ?>"
                        placeholder="Optional email address"
                    >
                </div>

                <div class="form-group">
                    <label for="teacherMajor">Major</label>
                    <select id="teacherMajor" name="major">
                        <option value="">Select major</option>
                        <?php foreach ($major_options as $majorOption): ?>
                            <option value="<?php echo htmlspecialchars($majorOption); ?>" <?php echo $teacher_form_values['major'] === $majorOption ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($majorOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="teacherGradeLevel">Grade Level</label>
                    <select id="teacherGradeLevel" name="grade_level">
                        <option value="">Select grade level</option>
                        <?php foreach ($grade_level_options as $gradeLevelOption): ?>
                            <option value="<?php echo htmlspecialchars($gradeLevelOption); ?>" <?php echo $teacher_form_values['grade_level'] === $gradeLevelOption ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($gradeLevelOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group teacher-form-span-2">
                    <label for="teacherSection">Section</label>
                    <select id="teacherSection" name="section">
                        <option value="">Select section</option>
                        <?php foreach ($section_options as $sectionOption): ?>
                            <option value="<?php echo htmlspecialchars($sectionOption); ?>" <?php echo $teacher_form_values['section'] === $sectionOption ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sectionOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <p class="teacher-form-note teacher-form-source-note">Major, grade level, and section choices come from the saved class and student records in the database.</p>
            <p class="teacher-form-note" id="teacherPasswordNote"><?php echo htmlspecialchars($teacherPasswordNote); ?></p>

            <div class="teacher-form-actions">
                <button type="submit" class="btn btn-success" id="teacherSubmitBtn"><?php echo htmlspecialchars($teacherSubmitLabel); ?></button>
                <button type="button" class="btn" onclick="closeTeacherModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function filterTeachers() {
    const input = document.getElementById('teacherSearch');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('teacherTable');
    const emptyState = document.getElementById('teacherEmptyState');

    if (!table) {
        return;
    }

    const rows = table.querySelectorAll('tbody tr');
    let visibleRows = 0;

    rows.forEach((row) => {
        const text = row.textContent.toLowerCase();
        const isVisible = text.includes(filter);
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) {
            visibleRows += 1;
        }
    });

    if (emptyState) {
        emptyState.style.display = visibleRows === 0 ? 'block' : 'none';
    }
}

function getTeacherData(button) {
    try {
        return JSON.parse(button.dataset.teacher || '{}');
    } catch (error) {
        return {};
    }
}

function clearTeacherFormError() {
    const errorBox = document.getElementById('teacherFormError');
    if (errorBox) {
        errorBox.style.display = 'none';
    }
}

function setTeacherFormMode(mode, teacher) {
    const isEdit = mode === 'edit';
    const fallbackTeacher = teacher || {};

    document.getElementById('teacherModalTitle').textContent = isEdit ? 'Edit Teacher' : 'Add New Teacher';
    document.getElementById('teacherModalSubtitle').textContent = isEdit
        ? 'Update the teacher account and assignment details.'
        : 'Create a teacher account and save the profile details from one place.';
    document.getElementById('teacherFormAction').value = isEdit ? 'update_teacher' : 'create_teacher';
    document.getElementById('teacherIdField').value = isEdit ? String(fallbackTeacher.id || '') : '';
    document.getElementById('teacherName').value = isEdit ? (fallbackTeacher.name || '') : '';
    document.getElementById('teacherUsername').value = isEdit ? (fallbackTeacher.username || '') : '';
    document.getElementById('teacherEmail').value = isEdit ? (fallbackTeacher.email || '') : '';
    document.getElementById('teacherMajor').value = isEdit ? (fallbackTeacher.major_value || '') : '';
    document.getElementById('teacherGradeLevel').value = isEdit ? (fallbackTeacher.grade_level_value || '') : '';
    document.getElementById('teacherSection').value = isEdit ? (fallbackTeacher.section_value || '') : '';
    document.getElementById('teacherPassword').value = '';
    document.getElementById('teacherPassword').required = !isEdit;
    document.getElementById('teacherPassword').placeholder = isEdit
        ? 'Leave blank to keep current password'
        : 'Enter a login password';
    document.getElementById('teacherPasswordLabel').textContent = isEdit ? 'New Password' : 'Password';
    document.getElementById('teacherPasswordNote').textContent = isEdit
        ? 'Leave the password blank if you want to keep the current login password.'
        : "The password you enter here becomes the teacher's login password.";
    document.getElementById('teacherSubmitBtn').textContent = isEdit ? 'Update Teacher' : 'Save Teacher';
}

function openTeacherModal() {
    clearTeacherFormError();
    setTeacherFormMode('create');
    document.getElementById('teacherModal').style.display = 'block';
}

function openEditTeacherModal(button) {
    clearTeacherFormError();
    setTeacherFormMode('edit', getTeacherData(button));
    document.getElementById('teacherModal').style.display = 'block';
}

function closeTeacherModal() {
    const modal = document.getElementById('teacherModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function openTeacherViewModal(button) {
    const teacher = getTeacherData(button);

    document.getElementById('viewTeacherName').textContent = teacher.name || '-';
    document.getElementById('viewTeacherUsername').textContent = teacher.username || '-';
    document.getElementById('viewTeacherEmail').textContent = teacher.email || 'Not Provided';
    document.getElementById('viewTeacherStatus').textContent = teacher.status || '-';
    document.getElementById('viewTeacherMajor').textContent = teacher.major || 'Not Assigned';
    document.getElementById('viewTeacherGradeLevel').textContent = teacher.grade_level || 'Not Assigned';
    document.getElementById('viewTeacherSection').textContent = teacher.section || 'Not Assigned';
    document.getElementById('viewTeacherLastLogin').textContent = teacher.last_login_at || 'Never';
    document.getElementById('viewTeacherCreatedAt').textContent = teacher.created_at || 'Unavailable';

    document.getElementById('teacherViewModal').style.display = 'block';
}

function closeTeacherViewModal() {
    const modal = document.getElementById('teacherViewModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

window.addEventListener('click', function(event) {
    const teacherModal = document.getElementById('teacherModal');
    const teacherViewModal = document.getElementById('teacherViewModal');

    if (teacherModal && event.target === teacherModal) {
        closeTeacherModal();
    }

    if (teacherViewModal && event.target === teacherViewModal) {
        closeTeacherViewModal();
    }
});

window.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeTeacherModal();
        closeTeacherViewModal();
    }
});
</script>

<style>
.teacher-page-header {
    margin-bottom: 20px;
}

.teacher-page-subtitle {
    color: #666;
}

.teacher-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.teacher-search {
    position: relative;
    flex: 1 1 420px;
    max-width: 620px;
}

.teacher-search i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #667eea;
}

.teacher-search input {
    width: 100%;
    padding: 12px 14px 12px 42px;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    font-size: 15px;
}

.teacher-add-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    white-space: nowrap;
}

.teacher-modal-content {
    max-width: 760px;
    max-height: calc(100vh - 80px);
    overflow-y: auto;
}

.teacher-view-modal-content {
    max-width: 680px;
}

.teacher-modal-header p {
    color: #666;
    margin-top: -10px;
    margin-bottom: 20px;
}

.teacher-form-grid,
.teacher-view-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px 18px;
}

.teacher-form-span-2,
.teacher-view-span-2 {
    grid-column: 1 / -1;
}

.teacher-form-note {
    margin-top: 8px;
    color: #666;
    font-size: 13px;
}

.teacher-form-source-note {
    margin-top: 14px;
}

.teacher-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 18px;
}

.teacher-form-actions .btn,
.teacher-action-btn {
    margin: 0;
}

.teacher-status {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}

.teacher-status-active {
    background: #e6ffed;
    color: #198754;
}

.teacher-status-inactive {
    background: #ffe3e3;
    color: #b02a37;
}

.teacher-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.teacher-view-item {
    border: 1px solid #e6eaf8;
    border-radius: 14px;
    padding: 16px;
    background: #f8faff;
}

.teacher-view-label {
    display: block;
    margin-bottom: 6px;
    color: #667eea;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.teacher-view-item strong {
    color: #2b2b2b;
    font-size: 15px;
    word-break: break-word;
}

.teacher-empty-state {
    margin-top: 14px;
    color: #666;
}

@media (max-width: 768px) {
    .teacher-toolbar {
        align-items: stretch;
    }

    .teacher-search {
        max-width: none;
    }

    .teacher-form-grid,
    .teacher-view-grid {
        grid-template-columns: 1fr;
    }

    .teacher-form-span-2,
    .teacher-view-span-2 {
        grid-column: auto;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
