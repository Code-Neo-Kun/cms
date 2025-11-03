<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$current_date = current_time('Y-m-d');
?>

<div class="wrap cosign-wrap">
    <div class="cosign-header">
        <h1>
            <i class="fas fa-tasks"></i>
            To Do List: <?php echo date('d M Y', strtotime($current_date)); ?>
        </h1>
        <div class="header-actions">
            <button type="button" class="cosign-btn" id="add-todo-btn">
                <i class="fas fa-plus"></i>
                Add Meeting/Task
            </button>
        </div>
    </div>

    <div class="daily-actions">
        <a href="<?php echo esc_url(admin_url('admin.php?page=cosign-daily-closing')); ?>" class="cosign-btn">
            <i class="fas fa-check-circle"></i>
            Daily Closing
        </a>
        <button type="button" class="cosign-btn secondary">
            <i class="fas fa-calendar-minus"></i>
            On Leave
        </button>
    </div>

    <!-- Scheduled Tasks Section -->
    <div>
        <div class="section-header">Scheduled Tasks</div>
        <table class="cosign-table" id="scheduled-tasks">
                <thead>
                    <tr>
                        <th>Priority</th>
                        <th>Task Name</th>
                        <th>Client Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Task Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Tasks will be loaded here via AJAX -->
                </tbody>
            </table>
        </div>

        <!-- Completed Tasks Section -->
        <div class="section-header completed">Completed Tasks</div>
        <table class="cosign-table" id="completed-tasks">
            <thead>
                <tr>
                    <th>Priority</th>
                    <th>Task Name</th>
                    <th>Client Name</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Task Description</th>
                    <th>Status</th>
                    <th>View Details</th>
                </tr>
            </thead>
            <tbody>
                <!-- Completed tasks will be loaded here via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Todo Modal -->
<div id="todo-modal" style="display:none;" class="cosign-modal">
    <form id="todo-form" class="cosign-form">
        <?php wp_nonce_field('cosign_nonce', 'cosign_nonce'); ?>
        <input type="hidden" name="todo_id" id="todo-id">

        <div class="cosign-form-row">
            <label for="todo-type">Task Type *</label>
            <select id="todo-type" name="task_type" required>
                <option value="">--Select Type--</option>
                <option value="meeting">Meeting</option>
                <option value="call">Phone Call</option>
                <option value="task">General Task</option>
            </select>
        </div>

        <div class="cosign-form-row">
            <label for="todo-title">Task Name *</label>
            <input type="text" id="todo-title" name="title" required>
        </div>

        <div class="cosign-form-row">
            <label for="todo-description">Task Description *</label>
            <textarea id="todo-description" name="description" required></textarea>
        </div>

        <div class="cosign-form-row">
            <label for="todo-priority">Priority *</label>
            <select id="todo-priority" name="priority" required>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
        </div>

        <div class="cosign-form-row">
            <label for="todo-date">Date *</label>
            <input type="date" id="todo-date" name="task_date" required>
        </div>

        <div class="cosign-form-row">
            <label for="todo-time">Time *</label>
            <input type="time" id="todo-time" name="task_time" required>
        </div>

        <div class="cosign-form-row">
            <label for="todo-client">Client</label>
            <select id="todo-client" name="client_id">
                <option value="">Select Client</option>
                <?php 
                $clients = cosign_get_clients_list();
                foreach ($clients as $client): 
                ?>
                    <option value="<?php echo esc_attr($client->id); ?>">
                        <?php echo esc_html($client->company_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="cosign-form-actions">
            <button type="submit" class="cosign-btn">Save Task</button>
            <button type="button" class="cosign-btn secondary" onclick="closeTodoModal()">Cancel</button>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Load tasks on page load
    loadTasks();

    // Add todo button click
    $('#add-todo-btn').on('click', function() {
        $('#todo-id').val('');
        $('#todo-form')[0].reset();
        $('#todo-date').val('<?php echo esc_js($current_date); ?>');
        $('#todo-modal').show();
    });

    // Todo form submission
    $('#todo-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'cosign_add_todo');
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
                    closeTodoModal();
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
                action: 'cosign_get_todos',
                nonce: $('#cosign_nonce').val(),
                date: '<?php echo esc_js($current_date); ?>'
            },
            success: function(response) {
                if (response.success) {
                    renderTasks(response.data);
                }
            }
        });
    }

    function renderTasks(data) {
        var scheduledTbody = $('#scheduled-tasks tbody');
        var completedTbody = $('#completed-tasks tbody');
        
        scheduledTbody.empty();
        completedTbody.empty();

        data.scheduled.forEach(function(task) {
            scheduledTbody.append(createTaskRow(task, true));
        });

        data.completed.forEach(function(task) {
            completedTbody.append(createTaskRow(task, false));
        });

        // Reinitialize task action handlers
        initializeTaskActions();
    }

    function createTaskRow(task, isScheduled) {
        var row = `
            <tr>
                <td><span class="cosign-priority ${task.priority.toLowerCase()}">${task.priority}</span></td>
                <td>${task.title}</td>
                <td>${task.client_name || '-'}</td>
                <td>${formatDate(task.task_date)}</td>
                <td>${task.task_time || '-'}</td>
                <td>${task.description}</td>`;
        
        if (isScheduled) {
            row += `
                <td>
                    <select class="task-status" data-task-id="${task.id}">
                        <option value="pending" ${task.status === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="in-progress" ${task.status === 'in-progress' ? 'selected' : ''}>In Progress</option>
                        <option value="completed" ${task.status === 'completed' ? 'selected' : ''}>Completed</option>
                    </select>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="action-btn edit edit-todo" data-task-id="${task.id}">
                            <i class="fas fa-edit"></i>
                            Edit
                        </button>
                        <button class="action-btn delete delete-todo" data-task-id="${task.id}">
                            <i class="fas fa-trash"></i>
                            Delete
                        </button>
                    </div>
                </td>`;
        } else {
            row += `
                <td><span class="status-completed">Completed</span></td>
                <td>
                    <button class="action-btn edit view-todo" data-task-id="${task.id}">
                        <i class="fas fa-eye"></i>
                        View Details
                    </button>
                </td>`;
        }
        
        row += '</tr>';
        return row;
    }

    function initializeTaskActions() {
        $('.task-status').on('change', function() {
            var taskId = $(this).data('task-id');
            updateTaskStatus(taskId, this.value);
        });

        $('.edit-todo').on('click', function() {
            var taskId = $(this).data('task-id');
            editTodo(taskId);
        });

        $('.delete-todo').on('click', function() {
            var taskId = $(this).data('task-id');
            if (confirm('Are you sure you want to delete this task?')) {
                deleteTodo(taskId);
            }
        });

        $('.view-todo').on('click', function() {
            var taskId = $(this).data('task-id');
            viewTodoDetails(taskId);
        });
    }

    function updateTaskStatus(taskId, status) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cosign_update_todo_status',
                nonce: $('#cosign_nonce').val(),
                task_id: taskId,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    loadTasks();
                } else {
                    alert('Error updating task status. Please try again.');
                }
            }
        });
    }

    function editTodo(taskId) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cosign_get_todo',
                nonce: $('#cosign_nonce').val(),
                task_id: taskId
            },
            success: function(response) {
                if (response.success) {
                    var todo = response.data;
                    $('#todo-id').val(todo.id);
                    $('#todo-type').val(todo.task_type);
                    $('#todo-title').val(todo.title);
                    $('#todo-description').val(todo.description);
                    $('#todo-priority').val(todo.priority);
                    $('#todo-date').val(todo.task_date);
                    $('#todo-time').val(todo.task_time);
                    $('#todo-client').val(todo.client_id);
                    $('#todo-modal').show();
                }
            }
        });
    }

    function deleteTodo(taskId) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cosign_delete_todo',
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

    function viewTodoDetails(taskId) {
        // Implement view details functionality
        // Could show a modal with full task details and history
    }

    function closeTodoModal() {
        $('#todo-modal').hide();
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString();
    }
});
</script>

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

.wrap.cosign-wrap {
    margin: 20px;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.cosign-header {
    padding: 20px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
}

.cosign-header h1 {
    margin: 0;
    font-size: 1.5rem;
    color: var(--text-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header {
    background: #f03e3e;
    color: white;
    padding: 12px 20px;
    font-size: 1rem;
    font-weight: 600;
}

.section-header.completed {
    background: #82c91e;
}

.cosign-table {
    width: 100%;
    border-collapse: collapse;
}

.cosign-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: var(--text-color);
    border-bottom: 2px solid var(--border-color);
}

.cosign-table td {
    padding: 12px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-color);
    font-size: 0.9rem;
}

.cosign-table tr:hover td {
    background: #f8f9fa;
}

.cosign-priority {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
    text-align: center;
    display: inline-block;
}

.cosign-priority.high {
    background: #fff5f5;
    color: #e03131;
}

.cosign-priority.medium {
    background: #fff9db;
    color: #f08c00;
}

.cosign-priority.low {
    background: #ebfbee;
    color: #2b8a3e;
}

.task-status {
    padding: 6px 12px;
    border-radius: var(--radius);
    border: 1px solid var(--border-color);
    background: white;
    font-size: 0.9rem;
    cursor: pointer;
}

.status-completed {
    color: #2b8a3e;
    font-weight: 500;
}

.btn-group {
    display: flex;
    gap: 8px;
}

.cosign-btn {
    padding: 8px 16px;
    border-radius: var(--radius);
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
    text-decoration: none;
    background: var(--secondary-color);
    color: white;
}

.cosign-btn.secondary {
    background: #e9ecef;
    color: var(--text-color);
}

.cosign-btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.cosign-btn.small {
    padding: 4px 8px;
    font-size: 0.8rem;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.action-btn {
    padding: 4px 8px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.action-btn.edit {
    background: #e9ecef;
    color: var(--text-color);
}

.action-btn.delete {
    background: #ffe3e3;
    color: #e03131;
}

.header-actions {
    display: flex;
    gap: 12px;
}

.daily-actions {
    display: flex;
    gap: 8px;
    padding: 12px 20px;
    background: #f8f9fa;
}

.daily-actions .cosign-btn {
    font-size: 0.9rem;
}

@media (max-width: 1200px) {
    .wrap.cosign-wrap {
        margin: 10px;
    }

    .cosign-header {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }

    .header-actions {
        width: 100%;
    }

    .cosign-btn {
        flex: 1;
        justify-content: center;
    }

    .cosign-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
}</style>