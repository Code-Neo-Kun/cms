<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'waiting-payment';
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = isset($_GET['entries']) ? intval($_GET['entries']) : 10;
$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
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

.dispatch-tabs {
    background: var(--light-gray);
    padding: 0;
    border-bottom: 2px solid var(--border-color);
}

.tab-navigation {
    display: flex;
    gap: 0;
}

.tab-link {
    padding: 1rem 2rem;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-weight: 500;
    color: var(--text-color);
    transition: var(--transition);
}

.tab-link:hover {
    background: rgba(0, 0, 102, 0.05);
}

.tab-link.active {
    background: white;
    border-bottom-color: var(--secondary-color);
    color: var(--primary-color);
    font-weight: 600;
}

.tab-content {
    display: none;
    padding: 2rem;
}

.tab-content.active {
    display: block;
}

.cosign-filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: var(--light-gray);
    border-radius: var(--radius);
}

.cosign-filters input,
.cosign-filters select {
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    font-size: 0.95rem;
}

.cosign-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
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

.status-waiting-payment { background-color: #fef3c7; color: #92400e; }
.status-in-process { background-color: #e0f2fe; color: #0369a1; }
.status-ready-dispatch { background-color: #dcfce7; color: #15803d; }
.status-partial-dispatch { background-color: #fef3c7; color: #92400e; }
.status-dispatch { background-color: #dcfce7; color: #15803d; }

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
.action-btn.view { background: #6366f1; }
</style>

<div class="wrap cosign-wrap">
    <div class="cosign-header">
        <h1><i class="fas fa-truck"></i> Dispatch Stage</h1>
    </div>

    <div class="dispatch-tabs">
        <div class="tab-navigation">
            <button type="button" class="tab-link <?php echo $current_tab === 'waiting-payment' ? 'active' : ''; ?>" 
                    data-tab="waiting-payment" onclick="switchTab('waiting-payment')">
                Waiting Payment
            </button>
            <button type="button" class="tab-link <?php echo $current_tab === 'in-process' ? 'active' : ''; ?>" 
                    data-tab="in-process" onclick="switchTab('in-process')">
                In Process
            </button>
            <button type="button" class="tab-link <?php echo $current_tab === 'ready-dispatch' ? 'active' : ''; ?>" 
                    data-tab="ready-dispatch" onclick="switchTab('ready-dispatch')">
                Ready to Dispatch
            </button>
            <button type="button" class="tab-link <?php echo $current_tab === 'partial-dispatch' ? 'active' : ''; ?>" 
                    data-tab="partial-dispatch" onclick="switchTab('partial-dispatch')">
                Partial Dispatch
            </button>
            <button type="button" class="tab-link <?php echo $current_tab === 'dispatch' ? 'active' : ''; ?>" 
                    data-tab="dispatch" onclick="switchTab('dispatch')">
                Dispatched
            </button>
        </div>
    </div>

    <div id="tab-content-<?php echo esc_attr($current_tab); ?>" class="tab-content active">
        <div class="cosign-filters">
            <input type="text" id="search-items" placeholder="Search by quotation number or project name..." 
                   value="<?php echo esc_attr($search); ?>" style="flex: 1;">
            <select id="filter-entries" onchange="loadItems()">
                <option value="10" <?php selected($per_page, 10); ?>>10 per page</option>
                <option value="25" <?php selected($per_page, 25); ?>>25 per page</option>
                <option value="50" <?php selected($per_page, 50); ?>>50 per page</option>
            </select>
        </div>

        <table class="cosign-table">
            <thead>
                <tr>
                    <th>Sr. No.</th>
                    <th>Quotation #</th>
                    <th>Project Name</th>
                    <th>Client</th>
                    <th>City</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="dispatch-items-tbody">
                <tr>
                    <td colspan="8" style="text-align: center; padding: 3rem;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
                        <p>Loading items...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let currentTab = '<?php echo esc_js($current_tab); ?>';

    function loadItems() {
        const search = $('#search-items').val();
        const entries = $('#filter-entries').val();

        $.ajax({
            url: cosignData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cosign_get_dispatch_items',
                nonce: cosignData.nonce,
                tab: currentTab,
                search: search,
                per_page: entries,
                page: 1
            },
            success: function(response) {
                if (response.success) {
                    displayItems(response.data.items || []);
                } else {
                    $('#dispatch-items-tbody').html('<tr><td colspan="8" style="text-align: center; padding: 2rem;">No items found</td></tr>');
                }
            },
            error: function() {
                $('#dispatch-items-tbody').html('<tr><td colspan="8" style="text-align: center; padding: 2rem; color: #ef4444;">Error loading items</td></tr>');
            }
        });
    }

    function displayItems(items) {
        if (items.length === 0) {
            $('#dispatch-items-tbody').html('<tr><td colspan="8" style="text-align: center; padding: 3rem;">No items in this stage</td></tr>');
            return;
        }

        let html = '';
        items.forEach(function(item, index) {
            const statusClass = 'status-' + (item.status || 'waiting-payment').replace('_', '-');
            const date = item.date_of_quotation ? new Date(item.date_of_quotation).toLocaleDateString() : '-';
            
            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${item.quotation_number || '-'}</strong></td>
                    <td>${item.project_name || '-'}</td>
                    <td>${item.client_name || '-'}</td>
                    <td>${item.city || '-'}</td>
                    <td>${date}</td>
                    <td><span class="status-badge ${statusClass}">${(item.status || 'waiting_payment').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span></td>
                    <td>
                        <button class="action-btn view" onclick="viewDetails(${item.id})" title="View Details">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="action-btn edit" onclick="updateStatus(${item.id})" title="Update Status">
                            <i class="fas fa-edit"></i> Update
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#dispatch-items-tbody').html(html);
    }

    window.switchTab = function(tab) {
        currentTab = tab;
        $('.tab-link').removeClass('active');
        $(`.tab-link[data-tab="${tab}"]`).addClass('active');
        loadItems();
    };

    window.viewDetails = function(id) {
        window.location.href = '<?php echo admin_url("admin.php?page=cosign-pipeline&project_id="); ?>' + id;
    };

    window.updateStatus = function(id) {
        // Open status update modal
        alert('Status update functionality will be implemented');
    };

    // Search handler
    $('#search-items').on('keyup', function() {
        clearTimeout($(this).data('timeout'));
        $(this).data('timeout', setTimeout(loadItems, 500));
    });

    // Initial load
    loadItems();
});
</script>
