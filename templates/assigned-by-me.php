<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_user_id = get_current_user_id();
$users = \CoSignPlanner\cosign_get_users_list();
$clients = \CoSignPlanner\cosign_get_clients_list();
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

.wrap.cosign-wrap {
    max-width: 1400px;
    margin: 2rem auto;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border-color);
}

.cosign-header {
    background: linear-gradient(135deg, var(--primary-color), #000099);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: var(--radius) var(--radius) 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cosign-header h1 {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 600;
}

.cosign-filters {
    padding: 1.5rem 2rem;
    background: var(--light-gray);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.cosign-filters select,
.cosign-filters input {
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    font-size: 0.95rem;
}

.cosign-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.cosign-table th,
.cosign-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.cosign-table th {
    background-color: var(--primary-color);
    color: white;
    font-weight: 600;
    position: sticky;
    top: 0;
}

.cosign-table tbody tr:hover {
    background-color: rgba(146, 185, 42, 0.05);
}

.priority-high { color: #ef4444; font-weight: 600; }
.priority-medium { color: #f59e0b; font-weight: 600; }
.priority-low { color: #22c55e; font-weight: 600; }

.status-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
    display: inline-block;
}

.status-open { background-color: #fef3c7; color: #92400e; }
.status-in-progress { background-color: #e0f2fe; color: #0369a1; }
.status-completed { background-color: #dcfce7; color: #15803d; }

.action-btn {
    padding: 0.4rem 0.8rem;
    border-radius: 4px;
    cursor: pointer;
    transition: var(--transition);
    border: none;
    background: var(--secondary-color);
    color: white;
    margin-right: 0.5rem;
    font-size: 0.875rem;
}

.action-btn:hover {
    opacity: 0.9;
}

.action-btn.edit { background: #3b82f6; }
.action-btn.delete { background: #ef4444; }
</style>

<div class="wrap cosign-wrap">
    <div class="cosign-header">
        <h1><i class="fas fa-user-check"></i> Assigned by Me</h1>
        <div>
            <span style="font-size: 0.95rem; opacity: 0.9;">
                Total Tasks: <strong id="total-tasks-count">0</strong>
            </span>
        </div>
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

        <select id="filter-client">
            <option value="">All Clients</option>
            <?php foreach ($clients as $client): ?>
                <option value="<?php echo esc_attr($client['id']); ?>">
                    <?php echo esc_html($client['company_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" id="search-tasks" placeholder="Search tasks...">
    </div>

    <table class="cosign-table">
        <thead>
            <tr>
                <th>Sr. No.</th>
                <th>Assigned On</th>
                <th>Assigned To</th>
                <th>Priority</th>
                <th>Task</th>
                <th>Description</th>
                <th>Client</th>
                <th>Est. Delivery</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="tasks-tbody">
            <tr>
                <td colspan="10" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
                    <p>Loading tasks...</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    let currentPage = 1;
    let srNo = 1;

    // Load tasks assigned by current user
    function loadTasks() {
        $.ajax({
            url: cosignData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cosign_get_assigned_by_me',
                nonce: cosignData.nonce,
                priority: $('#filter-priority').val(),
                status: $('#filter-status').val(),
                client: $('#filter-client').val(),
                search: $('#search-tasks').val()
            },
            success: function(response) {
                if (response.success) {
                    displayTasks(response.data.tasks || []);
                    $('#total-tasks-count').text(response.data.total || 0);
                } else {
                    $('#tasks-tbody').html('<tr><td colspan="10" style="text-align: center; padding: 2rem;">No tasks found</td></tr>');
                }
            },
            error: function() {
                $('#tasks-tbody').html('<tr><td colspan="10" style="text-align: center; padding: 2rem; color: #ef4444;">Error loading tasks</td></tr>');
            }
        });
    }

    function displayTasks(tasks) {
        if (tasks.length === 0) {
            $('#tasks-tbody').html('<tr><td colspan="10" style="text-align: center; padding: 3rem;">No tasks assigned by you</td></tr>');
            return;
        }

        let html = '';
        srNo = 1;
        tasks.forEach(function(task) {
            const priorityClass = 'priority-' + task.priority.toLowerCase();
            const statusClass = 'status-' + task.status.replace(' ', '-').toLowerCase();
            const assignedDate = task.assigned_on ? new Date(task.assigned_on).toLocaleDateString() : '-';
            const estDelivery = task.estimated_delivery_date ? new Date(task.estimated_delivery_date).toLocaleDateString() : '-';
            
            html += `
                <tr>
                    <td>${srNo++}</td>
                    <td>${assignedDate}</td>
                    <td>${task.assigned_to_name || '-'}</td>
                    <td><span class="${priorityClass}">${task.priority}</span></td>
                    <td><strong>${task.title || '-'}</strong></td>
                    <td>${task.description ? (task.description.length > 50 ? task.description.substring(0, 50) + '...' : task.description) : '-'}</td>
                    <td>${task.client_name || '-'}</td>
                    <td>${estDelivery}</td>
                    <td><span class="status-badge ${statusClass}">${task.status}</span></td>
                    <td>
                        <button class="action-btn edit" onclick="editTask(${task.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete" onclick="deleteTask(${task.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#tasks-tbody').html(html);
    }

    // Filter handlers
    $('#filter-priority, #filter-status, #filter-client').on('change', loadTasks);
    $('#search-tasks').on('keyup', function() {
        clearTimeout($(this).data('timeout'));
        $(this).data('timeout', setTimeout(loadTasks, 500));
    });

    // Edit task
    window.editTask = function(id) {
        window.location.href = '<?php echo admin_url("admin.php?page=cosign-add-meeting&task_id="); ?>' + id;
    };

    // Delete task
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
                    alert(response.data.message || 'Task deleted successfully');
                    loadTasks();
                } else {
                    alert(response.data || 'Failed to delete task');
                }
            }
        });
    };

    // Initial load
    loadTasks();
});
</script>
