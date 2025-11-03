<?php
if (!defined('ABSPATH')) {
    exit;
}

$users = \CoSignPlanner\cosign_get_users_list();
?>
<style>
:root {
    --primary-color: #000066;
    --secondary-color: #92b92a;
    --text-color: #2c3e50;
    --light-gray: #f8f9fa;
    --border-color: #e2e8f0;
    --shadow: 0 2px 4px rgba(0,0,0,0.1);
    --transition: all 0.3s ease;
    --radius: 8px;
}

.wrap.cosign-wrap {
    max-width: 1400px;
    margin: 2rem auto;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
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
}

.cosign-table tbody tr:hover {
    background-color: rgba(146, 185, 42, 0.05);
}

.status-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
    display: inline-block;
}

.status-pending { background-color: #fef3c7; color: #92400e; }
.status-approved { background-color: #dcfce7; color: #15803d; }
.status-rejected { background-color: #fee2e2; color: #991b1b; }

.action-btn {
    padding: 0.4rem 0.8rem;
    border-radius: 4px;
    cursor: pointer;
    transition: var(--transition);
    border: none;
    color: white;
    margin-right: 0.5rem;
    font-size: 0.875rem;
}

.action-btn.approve { background: #22c55e; }
.action-btn.reject { background: #ef4444; }
.action-btn:hover { opacity: 0.9; }
</style>

<div class="wrap cosign-wrap">
    <div class="cosign-header">
        <h1><i class="fas fa-calendar-minus"></i> On Leave</h1>
        <button class="cosign-btn" onclick="requestLeave()" style="background: var(--secondary-color); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: var(--radius); cursor: pointer;">
            <i class="fas fa-plus"></i> Request Leave
        </button>
    </div>

    <div class="cosign-filters">
        <select id="filter-status">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
        </select>

        <select id="filter-user">
            <option value="">All Users</option>
            <?php foreach ($users as $user): ?>
                <option value="<?php echo esc_attr($user['ID']); ?>">
                    <?php echo esc_html($user['display_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="date" id="filter-date-from" placeholder="From Date">
        <input type="date" id="filter-date-to" placeholder="To Date">
    </div>

    <table class="cosign-table">
        <thead>
            <tr>
                <th>Sr. No.</th>
                <th>User</th>
                <th>Leave Date</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Requested On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="leave-requests-tbody">
            <tr>
                <td colspan="7" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
                    <p>Loading leave requests...</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    function loadLeaveRequests() {
        $.ajax({
            url: cosignData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cosign_get_leave_requests',
                nonce: cosignData.nonce,
                status: $('#filter-status').val(),
                user: $('#filter-user').val(),
                date_from: $('#filter-date-from').val(),
                date_to: $('#filter-date-to').val()
            },
            success: function(response) {
                if (response.success) {
                    displayLeaveRequests(response.data.requests || []);
                } else {
                    $('#leave-requests-tbody').html('<tr><td colspan="7" style="text-align: center; padding: 2rem;">No leave requests found</td></tr>');
                }
            },
            error: function() {
                $('#leave-requests-tbody').html('<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #ef4444;">Error loading leave requests</td></tr>');
            }
        });
    }

    function displayLeaveRequests(requests) {
        if (requests.length === 0) {
            $('#leave-requests-tbody').html('<tr><td colspan="7" style="text-align: center; padding: 3rem;">No leave requests found</td></tr>');
            return;
        }

        let html = '';
        requests.forEach(function(req, index) {
            const statusClass = 'status-' + (req.status || 'pending');
            const leaveDate = req.leave_date ? new Date(req.leave_date).toLocaleDateString() : '-';
            const requestedOn = req.created_at ? new Date(req.created_at).toLocaleDateString() : '-';
            
            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${req.user_name || '-'}</strong></td>
                    <td>${leaveDate}</td>
                    <td>${req.reason || '-'}</td>
                    <td><span class="status-badge ${statusClass}">${(req.status || 'pending').charAt(0).toUpperCase() + (req.status || 'pending').slice(1)}</span></td>
                    <td>${requestedOn}</td>
                    <td>
                        ${req.status === 'pending' ? `
                            <button class="action-btn approve" onclick="updateLeaveStatus(${req.id}, 'approved')" title="Approve">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="action-btn reject" onclick="updateLeaveStatus(${req.id}, 'rejected')" title="Reject">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        ` : ''}
                    </td>
                </tr>
            `;
        });

        $('#leave-requests-tbody').html(html);
    }

    window.requestLeave = function() {
        window.location.href = '<?php echo admin_url("admin.php?page=cosign-planner"); ?>';
    };

    window.updateLeaveStatus = function(id, status) {
        if (!confirm(`Are you sure you want to ${status} this leave request?`)) {
            return;
        }

        $.ajax({
            url: cosignData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cosign_update_leave_status',
                nonce: cosignData.nonce,
                leave_id: id,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Leave request updated successfully');
                    loadLeaveRequests();
                } else {
                    alert(response.data || 'Failed to update leave request');
                }
            }
        });
    };

    // Filter handlers
    $('#filter-status, #filter-user').on('change', loadLeaveRequests);
    $('#filter-date-from, #filter-date-to').on('change', loadLeaveRequests);

    // Initial load
    loadLeaveRequests();
});
</script>
