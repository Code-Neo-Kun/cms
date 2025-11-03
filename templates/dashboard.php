<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get data with safe checks
$todays_meetings = function_exists('cosign_get_todays_meetings') ? cosign_get_todays_meetings() : [];
$todays_tasks = function_exists('cosign_get_todays_tasks') ? cosign_get_todays_tasks() : [];
$task_stats = function_exists('cosign_get_task_statistics') ? cosign_get_task_statistics() : ['total' => 0, 'open' => 0, 'completed' => 0];
$meeting_stats = function_exists('cosign_get_meeting_statistics') ? cosign_get_meeting_statistics() : ['scheduled' => 0];
?>

<style>
    :root {
        --primary-color: #000066;
        --secondary-color: #92b92a;
        --text-color: #2c3e50;
        --light-gray: #f8f9fa;
        --border-color: #e2e8f0;
        --shadow: 0 2px 4px rgba(0,0,0,0.1);
        --hover-shadow: 0 4px 6px rgba(0,0,0,0.1);
        --transition: all 0.3s ease;
        --radius: 8px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .cosign-dashboard {
        background-color: var(--light-gray);
        padding: 2rem;
        color: var(--text-color);
    }

    .dashboard-header {
        background: linear-gradient(135deg, var(--primary-color), #000099);
        color: white;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: -2rem -2rem 2rem -2rem;
        box-shadow: var(--shadow);
    }

    .logo {
        font-size: 1.75rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        font-size: 0.95rem;
    }

    .user-info .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .card {
        background: white;
        border-radius: var(--radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        transition: var(--transition);
        border: 1px solid var(--border-color);
    }

    .card:hover {
        box-shadow: var(--hover-shadow);
        transform: translateY(-2px);
        border-color: rgba(146, 185, 42, 0.3);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary-color), #000099);
        color: white;
        padding: 1.25rem 1.5rem;
        border-radius: calc(var(--radius) - 2px) calc(var(--radius) - 2px) 0 0;
        margin: -1.5rem -1.5rem 1.5rem -1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
        font-size: 1.1rem;
        position: relative;
        overflow: hidden;
    }

    .card-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
    }

    .button-group {
        display: flex !important;
        gap: 0.75rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }   

    .btn {
        padding: 0.6rem 1.2rem;
        border: none;
        border-radius: var(--radius);
        cursor: pointer;
        color: white;
        text-decoration: none;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: var(--transition);
        font-weight: 500;
    }

    .btn i {
        font-size: 1rem;
    }

    .btn-primary {
        background-color: var(--secondary-color);
    }

    .btn-primary:hover {
        background-color: #7a9c23;
    }

    .btn-warning {
        background-color: #f59e0b;
    }

    .btn-warning:hover {
        background-color: #d97706;
    }

    .btn-danger {
        background-color: #ef4444;
    }

    .btn-danger:hover {
        background-color: #dc2626;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 1rem;
    }

    th, td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }

    th {
        background-color: var(--light-gray);
        font-weight: 600;
        color: var(--primary-color);
        position: sticky;
        top: 0;
    }

    tr:hover td {
        background-color: rgba(146, 185, 42, 0.05);
    }

    .notification-badge {
        background-color: #ef4444;
        color: white;
        border-radius: 12px;
        padding: 0.25rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }

    .stat-card {
        background: white;
        padding: 2rem 1.5rem;
        border-radius: var(--radius);
        text-align: center;
        box-shadow: var(--shadow);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(to right, var(--secondary-color), #a8d130);
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--hover-shadow);
    }

    .stat-icon {
        font-size: 2rem;
        color: var(--secondary-color);
        margin-bottom: 1rem;
        background: rgba(146, 185, 42, 0.1);
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.1);
        background: rgba(146, 185, 42, 0.2);
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-color);
        line-height: 1.2;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .status-scheduled {
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .status-completed {
        background-color: #dcfce7;
        color: #15803d;
    }

    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }

    .priority-high {
        color: #ef4444;
        font-weight: 600;
    }

    .priority-medium {
        color: #f59e0b;
        font-weight: 600;
    }

    .priority-low {
        color: #22c55e;
        font-weight: 600;
    }

    .table-actions {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        padding: 0.4rem;
        border-radius: 4px;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        background: none;
        color: var(--text-color);
    }

    .action-btn:hover {
        color: var(--primary-color);
        background-color: var(--light-gray);
    }

    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #64748b;
        background: linear-gradient(to bottom, transparent, rgba(146, 185, 42, 0.03));
        border-radius: var(--radius);
        margin: 1rem 0;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #94a3b8;
        background: white;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        box-shadow: var(--shadow);
    }

    .empty-state p {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .empty-state small {
        display: block;
        color: #94a3b8;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .cosign-dashboard {
            padding: 1rem;
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .stat-card {
            padding: 1.5rem 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }

        .stat-number {
            font-size: 2rem;
        }

        .button-group {
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .card {
            padding: 1rem;
        }

        .card-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
            padding: 1rem;
        }

        table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        th, td {
            padding: 0.75rem;
        }

        .empty-state {
            padding: 2rem 1rem;
        }

        .empty-state i {
            width: 60px;
            height: 60px;
            font-size: 2rem;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
            padding: 1rem;
        }

        .user-info {
            flex-direction: column;
            gap: 0.5rem;
        }
    }

    /* Leave Request Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-container {
        background-color: white;
        border-radius: var(--radius);
        width: 90%;
        max-width: 500px;
        position: relative;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        animation: modal-appear 0.3s ease-out;
    }

    @keyframes modal-appear {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, var(--primary-color), #000099);
        color: white;
        border-radius: var(--radius) var(--radius) 0 0;
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0.5rem;
        line-height: 1;
        opacity: 0.8;
        transition: var(--transition);
    }

    .modal-close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        background-color: var(--light-gray);
        border-radius: 0 0 var(--radius) var(--radius);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--text-color);
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        font-size: 0.95rem;
        transition: var(--transition);
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 3px rgba(146, 185, 42, 0.1);
    }

    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }
</style>

<!-- Enqueue Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="cosign-dashboard">
    <div class="dashboard-header">
        <div class="logo">CosignMPlanner</div>
        <div class="user-info">
            <div class="avatar">
                <?php echo substr(wp_get_current_user()->display_name, 0, 1); ?>
            </div>
            <span><?php echo esc_html(wp_get_current_user()->display_name); ?></span>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-number"><?php echo esc_html($task_stats['total']); ?></div>
            <div class="stat-label">Total Tasks</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-number"><?php echo esc_html($task_stats['open']); ?></div>
            <div class="stat-label">Open Tasks</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-number"><?php echo esc_html($meeting_stats['scheduled']); ?></div>
            <div class="stat-label">Scheduled Meetings</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="stat-number"><?php echo esc_html($task_stats['completed']); ?></div>
            <div class="stat-label">Completed Tasks</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <div class="card-header">
                Quick Actions
                <div class="button-group">
                    <a href="<?php echo admin_url('admin.php?page=cosign-add-meeting'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Meeting/Task
                    </a>
                    <button class="btn btn-warning">
                        <i class="fas fa-file-invoice-dollar"></i> Create Quote
                    </button>
                    <button class="btn btn-danger">
                        <i class="fas fa-comments"></i> Comments <span class="notification-badge">12</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Daily Operations
                <div class="button-group">
                    <a href="<?php echo admin_url('admin.php?page=cosign-daily-closing'); ?>" class="btn btn-primary">
                        <i class="fas fa-check-circle"></i> Day Closing
                    </a>
                    <button class="btn btn-warning">
                        <i class="fas fa-calendar-minus"></i> Request Leave
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <div class="card-header">
                Today's Meetings
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Event Location</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($todays_meetings)) : ?>
                        <?php foreach ($todays_meetings as $meeting) : ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($meeting->title); ?></strong>
                                </td>
                                <td>
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo esc_html($meeting->location); ?>
                                </td>
                                <td><?php echo esc_html(date('M d, Y', strtotime($meeting->start_time))); ?></td>
                                <td><?php echo esc_html(date('h:i A', strtotime($meeting->start_time))); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($meeting->status); ?>">
                                        <?php echo esc_html($meeting->status); ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <button class="action-btn" onclick="editMeeting(<?php echo $meeting->id; ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn" onclick="deleteMeeting(<?php echo $meeting->id; ?>)" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fas fa-calendar-check"></i>
                                    <p>No meetings scheduled for today</p>
                                    <small>Click "New Meeting/Task" to schedule a meeting</small>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="card-header">
                Today's Tasks
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Priority</th>
                        <th>Task Name</th>
                        <th>Client Name</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($todays_tasks)) : ?>
                        <?php foreach ($todays_tasks as $task) : ?>
                            <tr>
                                <td>
                                    <span class="priority-<?php echo strtolower($task->priority); ?>">
                                        <i class="fas fa-flag"></i>
                                        <?php echo esc_html($task->priority); ?>
                                    </span>
                                </td>
                                <td><strong><?php echo esc_html($task->title); ?></strong></td>
                                <td><?php echo esc_html($task->client_id); ?></td>
                                <td><?php echo esc_html(date('M d, Y', strtotime($task->assigned_on))); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($task->status); ?>">
                                        <?php echo esc_html($task->status); ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <button class="action-btn" onclick="editTask(<?php echo $task->id; ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn" onclick="deleteTask(<?php echo $task->id; ?>)" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fas fa-tasks"></i>
                                    <p>No tasks scheduled for today</p>
                                    <small>Create a new task to get started</small>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Leave Request Modal -->
<div id="leaveRequestModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fas fa-calendar-minus"></i>
                Request Leave
            </div>
            <button type="button" class="modal-close" onclick="closeLeaveModal()">&times;</button>
        </div>
        <form id="leaveRequestForm">
            <div class="modal-body">
                <div class="form-group">
                    <label for="leaveDate">
                        <i class="fas fa-calendar"></i>
                        Leave Date <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" id="leaveDate" name="leave_date" required 
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label for="leaveReason">
                        <i class="fas fa-comment-alt"></i>
                        Reason for Leave <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea id="leaveReason" name="leave_reason" required
                            placeholder="Please provide the reason for your leave request..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeLeaveModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Leave Request Modal Functions
    window.openLeaveModal = function() {
        $('#leaveRequestModal').fadeIn(300);
        $('#leaveRequestModal').css('display', 'flex');
        // Set default date to today
        $('#leaveDate').val(new Date().toISOString().split('T')[0]);
    };

    window.closeLeaveModal = function() {
        $('#leaveRequestModal').fadeOut(300);
    };

    // Close modal when clicking outside
    $('#leaveRequestModal').on('click', function(e) {
        if (e.target === this) {
            closeLeaveModal();
        }
    });

    // Handle Leave Request Form Submission
    $('#leaveRequestForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: cosignData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cosign_request_leave',
                nonce: cosignData.nonce,
                leave_date: $('#leaveDate').val(),
                leave_reason: $('#leaveReason').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('Leave request submitted successfully!');
                    closeLeaveModal();
                    $('#leaveRequestForm')[0].reset();
                } else {
                    alert(response.data || 'Failed to submit leave request. Please try again.');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Bind click handler to Request Leave button
    $('.btn-warning:contains("Request Leave")').on('click', function() {
        openLeaveModal();
    });

    // Edit meeting function
    window.editMeeting = function(id) {
        window.location.href = '<?php echo admin_url("admin.php?page=cosign-add-meeting&meeting_id="); ?>' + id;
    };

    // Delete meeting function
    window.deleteMeeting = function(id) {
        if (!confirm('Are you sure you want to delete this meeting?')) {
            return;
        }

        $.ajax({
            url: cosignData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cosign_delete_meeting',
                nonce: cosignData.nonce,
                meeting_id: id
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    };

    // Edit task function
    window.editTask = function(id) {
        window.location.href = '<?php echo admin_url("admin.php?page=cosign-add-meeting&task_id="); ?>' + id;
    };

    // Delete task function
    window.deleteTask = function(id) {
        if (!confirm('Are you sure you want to delete this task?')) {
            return;
        }

        $.ajax({
            url: cosignData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cosign_delete_task',
                nonce: cosignData.nonce,
                task_id: id
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    };
});
</script>