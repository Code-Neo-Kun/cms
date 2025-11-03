<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Get users for assignment dropdown
$users = CoSignPlanner\cosign_get_users_list();
$clients = CoSignPlanner\cosign_get_clients_list();
?>

<div class="wrap cosign-wrap">
    <div class="cosign-header">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <button type="button" class="cosign-btn" id="add-task-btn">Add New Task</button>
    </div>

    <div class="cosign-filters">
        <select id="filter-priority">
            <option value="">All Priorities</option>
            <option value="High">High</option>
            <option value="Medium">Medium</option>
            <option value="Low">Low</option>
        </select>
        
        <select id="filter-status">
            <option value="">All Status</option>
            <option value="open">Open</option>
            <option value="in-progress">In Progress</option>
            <option value="completed">Completed</option>
        </select>

        <select id="filter-assigned">
            <option value="">All Users</option>
            <?php foreach ($users as $user): ?>
                <option value="<?php echo esc_attr($user['ID']); ?>">
                    <?php echo esc_html($user['display_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" id="search-tasks" placeholder="Search tasks...">
    </div>

    <table class="cosign-table" id="tasks-table">
        <thead>
            <tr>
                <th>Sr. No.</th>
                <th>Assigned On</th>
                <th>Assigned By</th>
                <th>Priority</th>
                <th>Task</th>
                <th>Task Description</th>
                <th>Est. Delivery Date</th>
                <th>Deadline Date</th>
                <th>File</th>
                <th>Task Understood</th>
                <th>Task Done</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Tasks will be loaded here via AJAX -->
        </tbody>
    </table>
</div>

<!-- Add/Edit Task Modal -->
<div id="task-modal" style="display:none;" class="cosign-modal">
    <form id="task-form" class="cosign-form">
        <?php wp_nonce_field('cosign_nonce', 'cosign_nonce'); ?>
        <input type="hidden" name="task_id" id="task-id">
        
        <div class="cosign-form-row">
            <label for="task-title">Task Title *</label>
            <input type="text" id="task-title" name="title" required>
        </div>

        <div class="cosign-form-row">
            <label for="task-description">Description *</label>
            <textarea id="task-description" name="description" required></textarea>
        </div>

        <div class="cosign-form-row">
            <label for="task-priority">Priority *</label>
            <select id="task-priority" name="priority" required>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
        </div>

        <div class="cosign-form-row">
            <label for="task-assigned">Assign To *</label>
            <select id="task-assigned" name="assigned_to" required>
                <option value="">Select User</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo esc_attr($user['ID']); ?>">
                        <?php echo esc_html($user['display_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="cosign-form-row">
            <label for="task-client">Client</label>
            <select id="task-client" name="client_id">
                <option value="">Select Client</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?php echo esc_attr($client->id); ?>">
                        <?php echo esc_html($client->company_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="cosign-form-row">
            <label for="task-delivery-date">Estimated Delivery Date *</label>
            <input type="date" id="task-delivery-date" name="estimated_delivery_date" required>
        </div>

        <div class="cosign-form-row">
            <label for="task-deadline">Deadline Date *</label>
            <input type="date" id="task-deadline" name="deadline_date" required>
        </div>

        <div class="cosign-form-row">
            <label for="task-file">Upload File</label>
            <input type="file" id="task-file" name="task_file">
        </div>

        <div class="cosign-form-actions">
            <button type="submit" class="cosign-btn">Save Task</button>
            <button type="button" class="cosign-btn secondary" onclick="closeTaskModal()">Cancel</button>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Load tasks on page load
    loadTasks();

    // Initialize filters
    $('#filter-priority, #filter-status, #filter-assigned').on('change', loadTasks);
    $('#search-tasks').on('keyup', debounce(loadTasks, 500));

    // Add task button click
    $('#add-task-btn').on('click', function() {
        $('#task-id').val('');
        $('#task-form')[0].reset();
        $('#task-modal').show();
    });

    // Task form submission
    $('#task-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'cosign_add_task');
        formData.append('nonce', $('#cosign_nonce').val());

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Task saved successfully!');
                    closeTaskModal();
                    loadTasks();
                } else {
                    alert('Error saving task. Please try again.');
                }
            }
        });
    });

    function loadTasks() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cosign_get_tasks',
                nonce: $('#cosign_nonce').val(),
                priority: $('#filter-priority').val(),
                status: $('#filter-status').val(),
                assigned_to: $('#filter-assigned').val(),
                search: $('#search-tasks').val()
            },
            success: function(response) {
                if (response.success) {
                    renderTasks(response.data);
                }
            }
        });
    }

    function renderTasks(tasks) {
        var tbody = $('#tasks-table tbody');
        tbody.empty();

        tasks.forEach(function(task, index) {
            tbody.append(`
                <tr>
                    <td>${index + 1}</td>
                    <td>${formatDate(task.assigned_on)}</td>
                    <td>${task.assigned_by_name || '-'}</td>
                    <td><span class="cosign-priority ${task.priority.toLowerCase()}">${task.priority}</span></td>
                    <td>${task.title}</td>
                    <td>${task.description}</td>
                    <td>${formatDate(task.estimated_delivery_date)}</td>
                    <td>${formatDate(task.deadline_date)}</td>
                    <td>${task.file_path ? '<a href="' + task.file_path + '">View</a>' : '-'}</td>
                    <td><input type="checkbox" class="task-understood" data-task-id="${task.id}" ${task.task_understood ? 'checked' : ''}></td>
                    <td><input type="checkbox" class="task-done" data-task-id="${task.id}" ${task.task_done ? 'checked' : ''}></td>
                    <td>
                        <button class="cosign-btn small edit-task" data-task-id="${task.id}">Edit</button>
                        <button class="cosign-btn small secondary delete-task" data-task-id="${task.id}">Delete</button>
                    </td>
                </tr>
            `);
        });

        // Reinitialize task action handlers
        initializeTaskActions();
    }

    function initializeTaskActions() {
        $('.task-understood, .task-done').on('change', function() {
            var taskId = $(this).data('task-id');
            var field = $(this).hasClass('task-understood') ? 'task_understood' : 'task_done';
            
            updateTaskStatus(taskId, field, this.checked);
        });

        $('.edit-task').on('click', function() {
            var taskId = $(this).data('task-id');
            editTask(taskId);
        });

        $('.delete-task').on('click', function() {
            var taskId = $(this).data('task-id');
            if (confirm('Are you sure you want to delete this task?')) {
                deleteTask(taskId);
            }
        });
    }

    function updateTaskStatus(taskId, field, value) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cosign_update_task',
                nonce: $('#cosign_nonce').val(),
                task_id: taskId,
                field: field,
                value: value
            },
            success: function(response) {
                if (!response.success) {
                    alert('Error updating task status. Please try again.');
                    loadTasks(); // Reload to reset checkboxes
                }
            }
        });
    }

    function editTask(taskId) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cosign_get_task',
                nonce: $('#cosign_nonce').val(),
                task_id: taskId
            },
            success: function(response) {
                if (response.success) {
                    var task = response.data;
                    $('#task-id').val(task.id);
                    $('#task-title').val(task.title);
                    $('#task-description').val(task.description);
                    $('#task-priority').val(task.priority);
                    $('#task-assigned').val(task.assigned_to);
                    $('#task-client').val(task.client_id);
                    $('#task-delivery-date').val(task.estimated_delivery_date);
                    $('#task-deadline').val(task.deadline_date);
                    $('#task-modal').show();
                }
            }
        });
    }

    function deleteTask(taskId) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cosign_delete_task',
                nonce: $('#cosign_nonce').val(),
                task_id: taskId
            },
            success: function(response) {
                if (response.success) {
                    loadTasks();
                } else {
                    alert('Error deleting task. Please try again.');
                }
            }
        });
    }

    function closeTaskModal() {
        $('#task-modal').hide();
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString();
    }

    function debounce(func, wait) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }
});
</script>

<style>
.cosign-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 24px;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    z-index: 1000;
    max-width: 600px;
    width: 90%;
}

.cosign-form-row {
    margin-bottom: 20px;
}

.cosign-form-row label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.cosign-form-row input:not([type="file"]),
.cosign-form-row select,
.cosign-form-row textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #dfe3e8;
    border-radius: 4px;
    font-size: 14px;
}

.cosign-form-row textarea {
    min-height: 100px;
    resize: vertical;
}

.cosign-form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #dfe3e8;
}

.cosign-filters {
    margin: 20px 0;
    display: flex;
    gap: 15px;
    align-items: center;
    background: #f5f7fa;
    padding: 15px;
    border-radius: 8px;
}

.cosign-filters select,
.cosign-filters input {
    padding: 8px 12px;
    border: 1px solid #dfe3e8;
    border-radius: 4px;
    min-width: 150px;
    font-size: 14px;
}

.cosign-filters input {
    background: white url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>') no-repeat;
    background-position: center right 12px;
    padding-right: 35px;
}

.cosign-priority {
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    display: inline-block;
}

.cosign-priority.high {
    background: #ffeaea;
    color: #dc3545;
}

.cosign-priority.medium {
    background: #fff4e6;
    color: #fd7e14;
}

.cosign-priority.low {
    background: #e6f4ea;
    color: #28a745;
}

.cosign-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.cosign-table thead th {
    background: #002B5B;
    color: white;
    font-weight: 500;
    padding: 12px 16px;
    text-align: left;
    font-size: 14px;
    white-space: nowrap;
}

.cosign-table tbody td {
    padding: 12px 16px;
    border-bottom: 1px solid #dfe3e8;
    font-size: 14px;
    vertical-align: middle;
}

.cosign-table tbody tr:hover {
    background: #f8f9fa;
}

.cosign-btn.small {
    padding: 6px 12px;
    font-size: 13px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}

.cosign-btn.small:not(.secondary) {
    background: #0d6efd;
    color: white;
}

.cosign-btn.small:not(.secondary):hover {
    background: #0b5ed7;
}

.cosign-btn.small.secondary {
    background: #dc3545;
    color: white;
}

.cosign-btn.small.secondary:hover {
    background: #bb2d3b;
}

.cosign-table input[type="checkbox"] {
    width: 16px;
    height: 16px;
    border: 2px solid #dfe3e8;
    border-radius: 3px;
    cursor: pointer;
}
</style>