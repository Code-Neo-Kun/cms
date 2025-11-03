<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'sales';

// Get current page number
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

// Get number of entries per page
$per_page = isset($_GET['entries']) ? intval($_GET['entries']) : 10;

// Get search term
$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Initialize database connection
global $wpdb;

// Function to get data based on tab
function get_pipeline_data($tab, $search, $page, $per_page)
{
    global $wpdb;
    $offset = ($page - 1) * $per_page;

    $where = '';
    if (!empty($search)) {
        $where = $wpdb->prepare(
            "WHERE client_name LIKE %s 
            OR quotation_number LIKE %s 
            OR project_name LIKE %s",
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%'
        );
    }

    switch ($tab) {
        case 'sales':
            $table = $wpdb->prefix . 'pipeline_sales';
            $query = "SELECT * FROM {$table} {$where} ORDER BY created_date DESC LIMIT %d OFFSET %d";
            $count_query = "SELECT COUNT(*) FROM {$table} {$where}";
            break;
        case 'leads':
            $table = $wpdb->prefix . 'pipeline_leads';
            $query = "SELECT * FROM {$table} {$where} ORDER BY created_date DESC LIMIT %d OFFSET %d";
            $count_query = "SELECT COUNT(*) FROM {$table} {$where}";
            break;
        case 'projects':
            $table = $wpdb->prefix . 'pipeline_projects';
            $query = "SELECT * FROM {$table} {$where} ORDER BY created_date DESC LIMIT %d OFFSET %d";
            $count_query = "SELECT COUNT(*) FROM {$table} {$where}";
            break;
    }

    $total_items = $wpdb->get_var($count_query);
    $items = $wpdb->get_results($wpdb->prepare($query, $per_page, $offset));

    return array(
        'items' => $items,
        'total' => $total_items
    );
}

// Get data for current tab
$data = get_pipeline_data($current_tab, $search, $page, $per_page);
$total_pages = ceil($data['total'] / $per_page);

// Header structure
?>
<div class="wrap">
    <div class="cosign-header">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div class="header-actions">
            <button type="button" class="button-primary" onclick="openAddForm()">
                <?php _e('Add New', 'cosign-planner'); ?>
            </button>
        </div>
    </div>

    <div class="pipeline-tabs">
        <div class="tab-navigation">
            <button type="button" class="tab-link <?php echo $current_tab === 'sales' ? 'active' : ''; ?>"
                data-tab="sales">
                Sales Pipeline
            </button>
            <button type="button" class="tab-link <?php echo $current_tab === 'leads' ? 'active' : ''; ?>"
                data-tab="leads">
                Leads
            </button>
            <button type="button" class="tab-link <?php echo $current_tab === 'projects' ? 'active' : ''; ?>"
                data-tab="projects">
                Projects
            </button>
        </div>
    </div>

    <div id="tab-content-sales" class="tab-content <?php echo $current_tab === 'sales' ? 'active' : ''; ?>"
        data-tab="sales">
        <!-- Sales Pipeline content will be loaded here -->
    </div>
    <div id="tab-content-leads" class="tab-content <?php echo $current_tab === 'leads' ? 'active' : ''; ?>"
        data-tab="leads">
        <!-- Leads content will be loaded here -->
    </div>
    <div id="tab-content-projects" class="tab-content <?php echo $current_tab === 'projects' ? 'active' : ''; ?>"
        data-tab="projects">
        <!-- Projects content will be loaded here -->
    </div>

    <div class="pipeline-controls">
        <div class="entries-control">
            <label>Show
                <select id="entries-count" onchange="updateEntries(this.value)">
                    <?php $entries_options = array(10, 25, 50, 100); ?>
                    <?php foreach ($entries_options as $option): ?>
                        <option value="<?php echo $option; ?>" <?php selected($per_page, $option); ?>>
                            <?php echo $option; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                entries
            </label>
        </div>
        <div class="search-control">
            <form method="get" action="" id="search-form">
                <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">
                <input type="hidden" name="tab" value="<?php echo esc_attr($current_tab); ?>">
                <input type="hidden" name="entries" value="<?php echo esc_attr($per_page); ?>">
                <label>Search:
                    <input type="text" name="search" value="<?php echo esc_attr($search); ?>" placeholder="Search..."
                        onkeyup="handleSearch(this)">
                </label>
            </form>
        </div>
    </div>

    <div class="pipeline-table-container">
        <table class="pipeline-table">
            <thead>
                <tr>
                    <?php
                    echo '<th>Sr.No.</th>';

                    if ($current_tab === 'sales') {
                        echo '<th>Quotation Number</th>';
                        echo '<th>Date of Quotation</th>';
                        echo '<th>Client Name</th>';
                        echo '<th>Project</th>';
                        echo '<th>Project Type</th>';
                        echo '<th>Project Value</th>';
                        echo '<th>Conversion Belief</th>';
                        echo '<th>Time Belief</th>';
                    } elseif ($current_tab === 'leads') {
                        echo '<th>Client Name</th>';
                        echo '<th>Client Type</th>';
                        echo '<th>Generated By</th>';
                        echo '<th>Created Date</th>';
                    } else {
                        echo '<th>Project Name</th>';
                        echo '<th>Generated By</th>';
                        echo '<th>Created Date</th>';
                        echo '<th>Stakeholder List</th>';
                    }

                    echo '<th>Action</th>';
                    ?>
                </tr>
            </thead>
            <?php
            echo '<tbody>';
            if (!empty($data['items'])) {
                $counter = ($page - 1) * $per_page + 1;
                foreach ($data['items'] as $item) {
                    echo '<tr>';
                    echo '<td>' . $counter++ . '</td>';

                    if ($current_tab === 'sales') {
                        echo '<td>' . esc_html($item->quotation_number) . '</td>';
                        echo '<td>' . esc_html(date('d-m-Y', strtotime($item->quotation_date))) . '</td>';
                        echo '<td>' . esc_html($item->client_name) . '</td>';
                        echo '<td>' . esc_html($item->project) . '</td>';
                        echo '<td>' . esc_html($item->project_type) . '</td>';
                        echo '<td>' . esc_html($item->project_value) . '</td>';
                        echo '<td>' . esc_html($item->conversion_belief) . '%</td>';
                        echo '<td>' . esc_html($item->time_belief) . '%</td>';
                    } elseif ($current_tab === 'leads') {
                        echo '<td>' . esc_html($item->client_name) . '</td>';
                        echo '<td>' . esc_html($item->client_type) . '</td>';
                        echo '<td>' . esc_html($item->generated_by) . '</td>';
                        echo '<td>' . esc_html(date('d-m-Y H:i', strtotime($item->created_date))) . '</td>';
                    } else {
                        echo '<td>' . esc_html($item->project_name) . '</td>';
                        echo '<td>' . esc_html($item->generated_by) . '</td>';
                        echo '<td>' . esc_html(date('d-m-Y H:i', strtotime($item->created_date))) . '</td>';
                        echo '<td>' . esc_html($item->stakeholders) . '</td>';
                    }
                    echo '<td class="action-buttons">';
                    if ($current_tab === 'sales') {
                        printf('
                                <button class="btn generate-pi" onclick="generatePI(%d)">Generate PI</button>
                                <button class="btn generate-po" onclick="generatePO(%d)">Generate PO</button>
                                <button class="btn view" onclick="viewItem(%d)">View</button>
                                <button class="btn edit" onclick="editItem(%d)">Edit</button>
                                <button class="btn confirm" onclick="confirmItem(%d)">Confirm</button>
                                <button class="btn delete" onclick="deleteItem(%d)">Delete</button>',
                            esc_js($item->id),
                            esc_js($item->id),
                            esc_js($item->id),
                            esc_js($item->id),
                            esc_js($item->id),
                            esc_js($item->id)
                        );
                    } else {
                        printf('
                                <button class="btn pipeline-guide" onclick="pipelineGuide(%d)">Pipeline Guide</button>
                                <button class="btn add-comment" onclick="addComment(%d)">Add Comment</button>
                                <button class="btn comment-history" onclick="viewCommentHistory(%d)">Comment History</button>
                                <button class="btn delete" onclick="deleteItem(%d)">Delete</button>',
                            esc_js($item->id),
                            esc_js($item->id),
                            esc_js($item->id),
                            esc_js($item->id)
                        );
                    }
                    echo '</td>';
                    echo '</tr>';
                }
            } else {
                $colspan = $current_tab === 'sales' ? '10' : ($current_tab === 'leads' ? '5' : '6');
                echo '<tr><td colspan="' . $colspan . '" class="no-records">No records found</td></tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            if ($total_pages > 1) {
                echo '<div class="pipeline-pagination">';
                echo '<div class="pagination-controls">';

                if ($page > 1) {
                    printf(
                        '<a href="%s" class="btn-page">«</a>',
                        esc_url(add_query_arg('paged', $page - 1))
                    );
                }

                for ($i = 1; $i <= $total_pages; $i++) {
                    printf(
                        '<a href="%s" class="btn-page %s">%d</a>',
                        esc_url(add_query_arg('paged', $i)),
                        $i === $page ? 'active' : '',
                        $i
                    );
                }

                if ($page < $total_pages) {
                    printf(
                        '<a href="%s" class="btn-page">»</a>',
                        esc_url(add_query_arg('paged', $page + 1))
                    );
                }

                echo '</div>';
                echo '</div>';
            }

            ?>
    </div>

    <?php include_once dirname(__FILE__) . '/lead-form.php'; ?>

    <?php
    // Add nonce for security
    $nonce = wp_create_nonce('pipeline_actions_nonce');
    ?>
    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function () {
            const tabButtons = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const tabName = button.getAttribute('data-tab');

                    // Update active states
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));

                    button.classList.add('active');
                    document.querySelector(`#tab-content-${tabName}`).classList.add('active');

                    // Load tab content via AJAX
                    loadTabContent(tabName);
                });
            });
        });

        function loadTabContent(tabName) {
            jQuery.post(ajaxurl, {
                action: 'get_pipeline_tab_content',
                tab: tabName,
                nonce: '<?php echo esc_js($nonce); ?>'
            }, function (response) {
                if (response.success) {
                    document.querySelector(`#tab-content-${tabName}`).innerHTML = response.data;
                } else {
                    alert('Error loading tab content: ' + response.data);
                }
            });
        }

        function updateEntries(value) {
            const searchParams = new URLSearchParams(window.location.search);
            searchParams.set('entries', value);
            searchParams.delete('paged'); // Reset to first page
            loadTabContent(getCurrentTab());
        }

        function handleSearch(input) {
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(() => {
                document.getElementById('search-form').submit();
            }, 500);
        }

        function addComment(id) {
            const comment = prompt('Enter your comment:');
            if (comment) {
                jQuery.post(ajaxurl, {
                    action: 'add_pipeline_comment',
                    id: id,
                    comment: comment,
                    tab: '<?php echo esc_js($current_tab); ?>',
                    nonce: '<?php echo esc_js($nonce); ?>'
                }, function (response) {
                    if (response.success) {
                        alert('Comment added successfully');
                        location.reload();
                    } else {
                        alert('Error adding comment: ' + response.data);
                    }
                });
            }
        }

        function viewCommentHistory(id) {
            jQuery.post(ajaxurl, {
                action: 'get_pipeline_comments',
                id: id,
                tab: '<?php echo esc_js($current_tab); ?>',
                nonce: '<?php echo esc_js($nonce); ?>'
            }, function (response) {
                if (response.success) {
                    const comments = response.data;
                    let message = 'Comment History:\n\n';
                    comments.forEach(comment => {
                        message += `${comment.date}\n${comment.comment}\nBy: ${comment.user}\n\n`;
                    });
                    alert(message);
                } else {
                    alert('Error fetching comments: ' + response.data);
                }
            });
        }

        function deleteItem(id) {
            if (confirm('Are you sure you want to delete this item?')) {
                jQuery.post(ajaxurl, {
                    action: 'delete_pipeline_item',
                    id: id,
                    tab: '<?php echo esc_js($current_tab); ?>',
                    nonce: '<?php echo esc_js($nonce); ?>'
                }, function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error deleting item: ' + response.data);
                    }
                });
            }
        }

        function openAddForm() {
            const modal = document.getElementById('lead-form-modal');
            modal.style.display = 'block';

            // Handle close button
            const closeBtn = modal.querySelector('.close');
            closeBtn.onclick = function() {
                modal.style.display = 'none';
            }

            // Handle cancel button
            const cancelBtn = modal.querySelector('.btn.cancel');
            cancelBtn.onclick = function() {
                modal.style.display = 'none';
            }

            // Close on outside click
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        }

        // Handle form submission
        document.addEventListener('DOMContentLoaded', function() {
            const leadForm = document.getElementById('leadForm');
            if (leadForm) {
                leadForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(leadForm);
                    formData.append('action', 'save_lead');
                    formData.append('nonce', '<?php echo esc_js($nonce); ?>');

                    jQuery.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                alert('Lead saved successfully');
                                document.getElementById('lead-form-modal').style.display = 'none';
                                leadForm.reset();
                                loadTabContent('leads');
                            } else {
                                alert('Error saving lead: ' + response.data);
                            }
                        },
                        error: function() {
                            alert('Error saving lead. Please try again.');
                        }
                    });
                });
            }

            // Handle zone, state, and city dropdowns
            const zoneSelect = document.getElementById('zone');
            const stateSelect = document.getElementById('state');
            const citySelect = document.getElementById('city');

            if (zoneSelect && stateSelect) {
                zoneSelect.addEventListener('change', function() {
                    // Load states based on selected zone
                    jQuery.post(ajaxurl, {
                        action: 'get_states',
                        zone: this.value,
                        nonce: '<?php echo esc_js($nonce); ?>'
                    }, function(response) {
                        if (response.success) {
                            stateSelect.innerHTML = '<option value="">Select State</option>' + 
                                response.data.map(state => 
                                    `<option value="${state.id}">${state.name}</option>`
                                ).join('');
                            citySelect.innerHTML = '<option value="">Select City</option>';
                        }
                    });
                });
            }

            if (stateSelect && citySelect) {
                stateSelect.addEventListener('change', function() {
                    // Load cities based on selected state
                    jQuery.post(ajaxurl, {
                        action: 'get_cities',
                        state: this.value,
                        nonce: '<?php echo esc_js($nonce); ?>'
                    }, function(response) {
                        if (response.success) {
                            citySelect.innerHTML = '<option value="">Select City</option>' + 
                                response.data.map(city => 
                                    `<option value="${city.id}">${city.name}</option>`
                                ).join('');
                        }
                    });
                });
            }
        });

        function generatePI(id) {
            jQuery.post(ajaxurl, {
                action: 'generate_pi',
                id: id,
                nonce: '<?php echo esc_js($nonce); ?>'
            }, function (response) {
                if (response.success) {
                    window.location.href = response.data.url;
                } else {
                    alert('Error generating PI: ' + response.data);
                }
            });
        }

        function generatePO(id) {
            jQuery.post(ajaxurl, {
                action: 'generate_po',
                id: id,
                nonce: '<?php echo esc_js($nonce); ?>'
            }, function (response) {
                if (response.success) {
                    window.location.href = response.data.url;
                } else {
                    alert('Error generating PO: ' + response.data);
                }
            });
        }

        function viewItem(id) {
            jQuery.post(ajaxurl, {
                action: 'get_item_details',
                id: id,
                tab: '<?php echo esc_js($current_tab); ?>',
                nonce: '<?php echo esc_js($nonce); ?>'
            }, function (response) {
                if (response.success) {
                    // Implementation for viewing details
                    console.log(response.data);
                } else {
                    alert('Error fetching details: ' + response.data);
                }
            });
        }

        function editItem(id) {
            window.location.href = '<?php echo esc_js(admin_url("admin.php")); ?>?page=<?php echo esc_js($_GET["page"]); ?>&action=edit&id=' + id;
        }

        function confirmItem(id) {
            if (confirm('Are you sure you want to confirm this item?')) {
                jQuery.post(ajaxurl, {
                    action: 'confirm_item',
                    id: id,
                    tab: '<?php echo esc_js($current_tab); ?>',
                    nonce: '<?php echo esc_js($nonce); ?>'
                }, function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error confirming item: ' + response.data);
                    }
                });
            }
        }

        function pipelineGuide(id) {
            jQuery.post(ajaxurl, {
                action: 'get_pipeline_guide',
                id: id,
                nonce: '<?php echo esc_js($nonce); ?>'
            }, function (response) {
                if (response.success) {
                    // Implementation for showing pipeline guide
                    console.log(response.data);
                } else {
                    alert('Error fetching pipeline guide: ' + response.data);
                }
            });
        }
    </script>

    <style>
        .wrap {
            padding: 20px;
            background: #f5f7fa;
            min-height: 100vh;
        }

        .cosign-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            background: white;
            padding: 16px 24px;
            border-radius: 8px 8px 0 0;
            border-bottom: 1px solid #dfe3e8;
        }

        .pipeline-tabs {
            background: white;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 0 0 8px 8px;
        }

        .tab-navigation {
            display: flex;
            padding: 0 24px;
            border-bottom: 1px solid #dfe3e8;
        }

        .tab-link {
            padding: 16px 24px;
            color: #495057;
            text-decoration: none;
            font-weight: 500;
            position: relative;
            transition: color 0.2s;
            background: none;
            border: none;
            cursor: pointer;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .tab-link:hover {
            color: #002B5B;
        }

        .tab-link.active {
            color: #0d6efd;
        }

        .tab-link.active:after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background: #0d6efd;
        }

        .pipeline-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: white;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .entries-control select {
            margin: 0 8px;
            padding: 4px 8px;
            border: 1px solid #dfe3e8;
            border-radius: 4px;
        }

        .search-control input {
            padding: 6px 12px;
            border: 1px solid #dfe3e8;
            border-radius: 4px;
            width: 200px;
        }

        .pipeline-table-container {
            background: white;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        .pipeline-table {
            width: 100%;
            border-collapse: collapse;
        }

        .pipeline-table th,
        .pipeline-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #dfe3e8;
        }

        .pipeline-table th {
            background: #002B5B;
            color: white;
            font-weight: 500;
            white-space: nowrap;
        }

        .pipeline-table tbody tr:hover {
            background: #f8f9fa;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            white-space: nowrap;
        }

        .btn {
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: background-color 0.2s;
            margin: 0 2px;
        }

        .btn.generate-pi,
        .btn.generate-po {
            background: #00bcd4;
            color: white;
        }

        .btn.view {
            background: #0d6efd;
            color: white;
        }

        .btn.edit {
            background: #1a237e;
            color: white;
        }

        .btn.confirm {
            background: #28a745;
            color: white;
        }

        .btn.delete {
            background: #dc3545;
            color: white;
        }

        .btn.pipeline-guide {
            background: #0d6efd;
            color: white;
        }

        .btn.add-comment {
            background: #00bcd4;
            color: white;
        }

        .btn.comment-history {
            background: #28a745;
            color: white;
        }

        /* Hover states */
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .pipeline-pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            background: white;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .pagination-controls {
            display: flex;
            gap: 8px;
        }

        .btn-page {
            padding: 6px 12px;
            border: 1px solid #dfe3e8;
            background: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-page.active {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .btn-page:hover:not(.active) {
            background: #f8f9fa;
        }

        .notice p {
            margin: 0;
            color: #495057;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .button-primary {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s;
        }

        .button-primary:hover {
            background: #0b5ed7;
            color: white;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-top: 24px;
        }

        .card h2 {
            margin-top: 0;
            color: #002B5B;
            font-size: 1.5em;
            margin-bottom: 16px;
        }

        .card p {
            color: #495057;
            margin-bottom: 20px;
        }

        .migration-status {
            margin-top: 24px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #dfe3e8;
        }

        .migration-status h3 {
            margin: 0 0 16px 0;
            color: #002B5B;
            font-size: 1.2em;
        }

        .migration-status ul {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .migration-status li {
            margin: 12px 0;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .migration-status li:before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
        }

        .migration-status li:last-child:before {
            content: "→";
            color: #0d6efd;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #666;
        }

        .close:hover {
            color: #000;
        }

        /* Form Styles */
        .lead-form {
            padding: 20px 0;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
            min-width: 250px;
        }

        .form-group.full-width {
            flex: 0 0 100%;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .required {
            color: #dc3545;
        }

        .required-note {
            font-size: 14px;
            color: #666;
            font-weight: normal;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #dfe3e8;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #0d6efd;
            outline: none;
            box-shadow: 0 0 0 2px rgba(13,110,253,0.25);
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dfe3e8;
        }

        .btn.submit {
            background: #28a745;
            color: white;
        }

        .btn.reset {
            background: #1a237e;
            color: white;
        }

        .btn.cancel {
            background: #dc3545;
            color: white;
        }
    </style>