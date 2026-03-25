<?php
require_once '../controllers/teacher_registration_requests_controller.php';

$page_title = "Teacher New Registrations";
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="card">
    <div class="registration-page-header">
        <div>
            <h1>Teacher New Registrations</h1>
            <p class="registration-page-subtitle">Review teacher sign-up requests and decide whether to approve or reject them.</p>
        </div>
    </div>

    <?php if ($dashboard_error !== ''): ?>
        <div class="alert alert-error registration-feedback registration-feedback-error" data-feedback-notification>
            <?php echo htmlspecialchars($dashboard_error); ?>
        </div>
    <?php endif; ?>

    <?php if ($registration_feedback_message !== ''): ?>
        <div class="alert alert-success registration-feedback registration-feedback-success" data-feedback-notification>
            <?php echo htmlspecialchars($registration_feedback_message); ?>
        </div>
    <?php endif; ?>

    <div class="stats registration-stats">
        <div class="stat-card">
            <h3>Pending</h3>
            <div class="number"><?php echo (int)$registration_summary['pending']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Approved</h3>
            <div class="number"><?php echo (int)$registration_summary['approved']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Rejected</h3>
            <div class="number"><?php echo (int)$registration_summary['rejected']; ?></div>
        </div>
    </div>

    <?php if (count($registration_requests) > 0): ?>
        <div class="registration-table-wrap">
            <table class="registration-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Major</th>
                        <th>Grade Level</th>
                        <th>Section</th>
                        <th>Requested</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registration_requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['name']); ?></td>
                            <td><?php echo htmlspecialchars($request['username']); ?></td>
                            <td class="registration-col-email"><?php echo htmlspecialchars($request['email'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($request['major'] ?: 'Not Assigned'); ?></td>
                            <td><?php echo htmlspecialchars($request['grade_level'] ?: 'Not Assigned'); ?></td>
                            <td><?php echo htmlspecialchars($request['section'] ?: 'Not Assigned'); ?></td>
                            <td class="registration-col-requested"><?php echo $request['requested_at'] ? date('M d, Y h:i A', strtotime($request['requested_at'])) : '-'; ?></td>
                            <td class="registration-col-status">
                                <span class="registration-status registration-status-<?php echo htmlspecialchars(strtolower((string)$request['status'])); ?>">
                                    <?php echo htmlspecialchars(ucfirst((string)$request['status'])); ?>
                                </span>
                                <?php if (!empty($request['reviewed_by_name']) && !empty($request['reviewed_at'])): ?>
                                    <div class="registration-reviewed-meta">
                                        <?php echo htmlspecialchars($request['reviewed_by_name']); ?> -
                                        <?php echo date('M d, Y h:i A', strtotime($request['reviewed_at'])); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="registration-col-action">
                                <?php if (($request['status'] ?? '') === 'pending'): ?>
                                    <div class="registration-actions">
                                        <form method="POST" action="teacher_registrations.php" id="approve-request-<?php echo (int)$request['id']; ?>">
                                            <input type="hidden" name="action" value="approve_registration">
                                            <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
                                            <button
                                                type="button"
                                                class="btn btn-success btn-sm registration-action-btn"
                                                onclick="openRegistrationConfirmDialog(this, event)"
                                                data-confirm-form="approve-request-<?php echo (int)$request['id']; ?>"
                                                data-confirm-title="Approve Teacher Registration"
                                                data-confirm-message="Approve this teacher registration and send the approval notification email?"
                                                data-confirm-button="Approve"
                                                data-confirm-button-class="btn-success"
                                            >
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="teacher_registrations.php" id="reject-request-<?php echo (int)$request['id']; ?>">
                                            <input type="hidden" name="action" value="reject_registration">
                                            <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
                                            <button
                                                type="button"
                                                class="btn btn-danger btn-sm registration-action-btn"
                                                onclick="openRegistrationConfirmDialog(this, event)"
                                                data-confirm-form="reject-request-<?php echo (int)$request['id']; ?>"
                                                data-confirm-title="Reject Teacher Registration"
                                                data-confirm-message="Reject this teacher registration and send the rejection notification email?"
                                                data-confirm-button="Reject"
                                                data-confirm-button-class="btn-danger"
                                            >
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="registration-reviewed-label">Reviewed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No teacher registration requests found.</p>
    <?php endif; ?>
</div>

<div class="registration-confirm-overlay" id="registration-confirm-overlay" hidden>
    <div
        class="registration-confirm-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="registration-confirm-title"
        aria-describedby="registration-confirm-message"
    >
        <div class="registration-confirm-badge">Notification</div>
        <h3 id="registration-confirm-title">Confirm Action</h3>
        <p id="registration-confirm-message">Are you sure you want to continue?</p>
        <div class="registration-confirm-actions-modal">
            <button type="button" class="btn registration-confirm-cancel" id="registration-confirm-cancel" onclick="closeRegistrationConfirmDialog(event)">Cancel</button>
            <button type="button" class="btn registration-confirm-submit" id="registration-confirm-submit" onclick="submitRegistrationConfirmDialog(event)">Continue</button>
        </div>
    </div>
</div>

<style>
.registration-page-header {
    margin-bottom: 20px;
}

.registration-feedback {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 1200;
    max-width: 360px;
    padding: 14px 18px;
    border-radius: 18px;
    box-shadow: 0 18px 45px rgba(24, 39, 75, 0.18);
    transition: opacity 0.22s ease, transform 0.22s ease;
    animation: registration-feedback-slide 0.28s ease;
}

.registration-feedback-success {
    border-left: 5px solid #198754;
}

.registration-feedback-error {
    border-left: 5px solid #b02a37;
}

.registration-page-subtitle {
    color: #666;
}

.registration-stats {
    margin-bottom: 20px;
}

.registration-table-wrap {
    width: 100%;
    overflow-x: auto;
    padding-bottom: 10px;
}

.registration-table {
    min-width: 1120px;
    margin: 0;
}

.registration-table th,
.registration-table td {
    vertical-align: middle;
}

.registration-table td {
    white-space: normal;
    word-break: break-word;
}

.registration-table tr:hover {
    transform: none;
    box-shadow: none;
    background: #f7f8ff;
}

.registration-col-email {
    min-width: 240px;
}

.registration-col-requested {
    min-width: 150px;
}

.registration-col-status {
    min-width: 150px;
}

.registration-col-action {
    min-width: 150px;
}

.registration-status {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}

.registration-status-pending {
    background: #fff3cd;
    color: #856404;
}

.registration-status-approved {
    background: #e6ffed;
    color: #198754;
}

.registration-status-rejected {
    background: #ffe3e3;
    color: #b02a37;
}

.registration-reviewed-meta {
    margin-top: 6px;
    color: #777;
    font-size: 12px;
    line-height: 1.4;
}

.registration-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: stretch;
}

.registration-actions form {
    margin: 0;
    width: 100%;
}

.registration-action-btn {
    margin: 0;
    width: 100%;
    padding: 10px 14px;
    border-radius: 16px;
}

.registration-reviewed-label {
    color: #777;
    font-size: 13px;
    font-weight: 600;
}

.registration-confirm-overlay {
    position: fixed;
    inset: 0;
    z-index: 1300;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: rgba(15, 23, 42, 0.42);
    backdrop-filter: blur(6px);
}

.registration-confirm-overlay[hidden] {
    display: none !important;
}

.registration-confirm-card {
    width: min(100%, 420px);
    background: linear-gradient(180deg, #ffffff 0%, #f5f8ff 100%);
    border-radius: 24px;
    padding: 28px;
    box-shadow: 0 24px 60px rgba(16, 24, 40, 0.22);
}

.registration-confirm-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    padding: 6px 12px;
    border-radius: 999px;
    background: rgba(98, 110, 234, 0.12);
    color: #5564d8;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.registration-confirm-card h3 {
    margin: 0 0 12px;
    color: #1d2a57;
    font-size: 24px;
}

.registration-confirm-card p {
    margin: 0;
    color: #52607a;
    line-height: 1.6;
}

.registration-confirm-actions-modal {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 24px;
}

.registration-confirm-cancel,
.registration-confirm-submit {
    min-width: 118px;
    border-radius: 14px;
}

.registration-confirm-cancel {
    background: #eef2ff;
    color: #42507a;
}

@keyframes registration-feedback-slide {
    from {
        opacity: 0;
        transform: translateY(-12px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .registration-feedback {
        left: 16px;
        right: 16px;
        top: 16px;
        max-width: none;
    }

    .registration-stats {
        grid-template-columns: 1fr;
    }

    .registration-actions {
        flex-direction: column;
    }

    .registration-confirm-card {
        padding: 22px;
        border-radius: 20px;
    }

    .registration-confirm-actions-modal {
        flex-direction: column-reverse;
    }

    .registration-confirm-cancel,
    .registration-confirm-submit {
        width: 100%;
    }
}
</style>

<script>
(function () {
    const confirmOverlay = document.getElementById('registration-confirm-overlay');
    const confirmTitle = document.getElementById('registration-confirm-title');
    const confirmMessage = document.getElementById('registration-confirm-message');
    const confirmSubmit = document.getElementById('registration-confirm-submit');
    const confirmCancel = document.getElementById('registration-confirm-cancel');
    const feedbackNotifications = document.querySelectorAll('[data-feedback-notification]');
    let pendingForm = null;

    if (!confirmOverlay || !confirmTitle || !confirmMessage || !confirmSubmit || !confirmCancel) {
        return;
    }

    function closeConfirmDialog() {
        pendingForm = null;
        confirmOverlay.setAttribute('hidden', 'hidden');
        confirmSubmit.classList.remove('btn-success', 'btn-danger');
    }

    function openConfirmDialog(button) {
        pendingForm = document.getElementById(button.dataset.confirmForm);
        if (!pendingForm) {
            return;
        }

        confirmTitle.textContent = button.dataset.confirmTitle || 'Confirm Action';
        confirmMessage.textContent = button.dataset.confirmMessage || 'Are you sure you want to continue?';
        confirmSubmit.textContent = button.dataset.confirmButton || 'Continue';
        confirmSubmit.classList.remove('btn-success', 'btn-danger');
        confirmSubmit.classList.add(button.dataset.confirmButtonClass || 'btn-success');
        confirmOverlay.removeAttribute('hidden');
    }

    function submitConfirmDialog() {
        if (pendingForm) {
            pendingForm.submit();
        }
    }

    window.openRegistrationConfirmDialog = function (button, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        openConfirmDialog(button);
    };

    window.closeRegistrationConfirmDialog = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        closeConfirmDialog();
    };

    window.submitRegistrationConfirmDialog = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        submitConfirmDialog();
    };

    confirmSubmit.addEventListener('click', submitConfirmDialog);
    confirmCancel.addEventListener('click', closeConfirmDialog);

    confirmOverlay.addEventListener('click', function (event) {
        if (event.target === confirmOverlay) {
            closeConfirmDialog();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !confirmOverlay.hasAttribute('hidden')) {
            closeConfirmDialog();
        }
    });

    feedbackNotifications.forEach(function (notification) {
        window.setTimeout(function () {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-12px)';
            window.setTimeout(function () {
                notification.remove();
            }, 220);
        }, 4200);
    });
})();
</script>

<?php include '../includes/footer.php'; ?>
