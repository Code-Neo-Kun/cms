<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX Handler: Save Menu
 */
add_action('wp_ajax_cosign_save_menu', 'cosign_handle_save_menu');

function cosign_handle_save_menu() {
    // Verify nonce
    check_ajax_referer('cosign_nonce', 'nonce');

    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    global $wpdb;

    // Get and sanitize menu data
    $menu_data = isset($_POST['menu_data']) ? $_POST['menu_data'] : [];
    
    if (empty($menu_data['menu_name'])) {
        wp_send_json_error('Menu name is required');
        return;
    }

    // Sanitize menu data
    $menu_name = sanitize_text_field($menu_data['menu_name']);
    $menu_type = sanitize_text_field($menu_data['menu_type'] ?? 'quote');
    $description = sanitize_textarea_field($menu_data['description'] ?? '');
    $client_id = !empty($menu_data['client_id']) ? intval($menu_data['client_id']) : null;
    $project_id = !empty($menu_data['project_id']) ? intval($menu_data['project_id']) : null;
    $items = isset($menu_data['items']) ? $menu_data['items'] : [];

    if (empty($items)) {
        wp_send_json_error('At least one menu item is required');
        return;
    }

    $menus_table = $wpdb->prefix . 'menus';
    $menu_items_table = $wpdb->prefix . 'menu_items';

    // Insert menu
    $menu_result = $wpdb->insert(
        $menus_table,
        [
            'menu_name' => $menu_name,
            'description' => $description,
            'menu_type' => $menu_type,
            'client_id' => $client_id,
            'project_id' => $project_id,
            'created_by' => get_current_user_id(),
            'status' => 'draft',
            'created_at' => current_time('mysql')
        ],
        ['%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s']
    );

    if ($menu_result === false) {
        wp_send_json_error('Failed to save menu: ' . $wpdb->last_error);
        return;
    }

    $menu_id = $wpdb->insert_id;

    // Insert menu items
    $item_order = 0;
    $errors = [];

    foreach ($items as $item) {
        $item_name = sanitize_text_field($item['name']);
        
        // Skip empty item names
        if (empty($item_name)) {
            continue;
        }
        
        $item_category = sanitize_text_field($item['category'] ?? '');
        $item_description = sanitize_textarea_field($item['description'] ?? '');
        $quantity = intval($item['quantity'] ?? 1);
        $unit_price = floatval($item['unit_price'] ?? 0);
        $total_price = floatval($item['total_price'] ?? ($quantity * $unit_price));

        $item_result = $wpdb->insert(
            $menu_items_table,
            [
                'menu_id' => $menu_id,
                'item_name' => $item_name,
                'item_description' => $item_description,
                'category' => $item_category,
                'quantity' => $quantity,
                'unit_price' => $unit_price,
                'total_price' => $total_price,
                'item_order' => $item_order++,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%s', '%d', '%f', '%f', '%d', '%s']
        );

        if ($item_result === false) {
            $errors[] = 'Failed to save menu item: ' . $item_name . ' - ' . $wpdb->last_error;
        }
    }

    if (!empty($errors)) {
        // If some items failed, delete the menu and return error
        $wpdb->delete($menus_table, ['id' => $menu_id], ['%d']);
        wp_send_json_error('Failed to save some menu items: ' . implode(', ', $errors));
        return;
    }

    wp_send_json_success([
        'message' => 'Menu saved successfully!',
        'menu_id' => $menu_id
    ]);
}

/**
 * AJAX Handler: Get tasks assigned by current user
 */
add_action('wp_ajax_cosign_get_assigned_by_me', 'cosign_handle_get_assigned_by_me');

function cosign_handle_get_assigned_by_me() {
    check_ajax_referer('cosign_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    global $wpdb;
    $tasks_table = $wpdb->prefix . 'tasks_full';
    $clients_table = $wpdb->prefix . 'clients';
    
    $current_user_id = get_current_user_id();
    $priority = isset($_POST['priority']) ? sanitize_text_field($_POST['priority']) : '';
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    $client = isset($_POST['client']) ? intval($_POST['client']) : 0;
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    
    $where = "WHERE t.created_by = %d";
    $params = [$current_user_id];
    
    if (!empty($priority)) {
        $where .= " AND t.priority = %s";
        $params[] = $priority;
    }
    
    if (!empty($status)) {
        $where .= " AND t.status = %s";
        $params[] = $status;
    }
    
    if (!empty($client)) {
        $where .= " AND t.client_id = %d";
        $params[] = $client;
    }
    
    if (!empty($search)) {
        $where .= " AND (t.title LIKE %s OR t.description LIKE %s)";
        $search_like = '%' . $wpdb->esc_like($search) . '%';
        $params[] = $search_like;
        $params[] = $search_like;
    }
    
    $query = "SELECT t.*, c.company_name as client_name, 
              u1.display_name as assigned_to_name, u2.display_name as created_by_name
              FROM $tasks_table t
              LEFT JOIN $clients_table c ON t.client_id = c.id
              LEFT JOIN {$wpdb->users} u1 ON t.assigned_to = u1.ID
              LEFT JOIN {$wpdb->users} u2 ON t.created_by = u2.ID
              $where
              ORDER BY t.assigned_on DESC, t.created_at DESC";
    
    $tasks = $wpdb->get_results($wpdb->prepare($query, $params));
    
    wp_send_json_success([
        'tasks' => $tasks,
        'total' => count($tasks)
    ]);
}

/**
 * AJAX Handler: Get dispatch items
 */
add_action('wp_ajax_cosign_get_dispatch_items', 'cosign_handle_get_dispatch_items');

function cosign_handle_get_dispatch_items() {
    check_ajax_referer('cosign_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    global $wpdb;
    $quotations_table = $wpdb->prefix . 'quotations';
    $clients_table = $wpdb->prefix . 'clients';
    
    $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'waiting-payment';
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $offset = ($page - 1) * $per_page;
    
    // Map tab to status
    $status_map = [
        'waiting-payment' => 'waiting_payment',
        'in-process' => 'in_process',
        'ready-dispatch' => 'ready_dispatch',
        'partial-dispatch' => 'partial_dispatch',
        'dispatch' => 'dispatch'
    ];
    
    $status = $status_map[$tab] ?? 'waiting_payment';
    
    $where = "WHERE q.status = %s";
    $params = [$status];
    
    if (!empty($search)) {
        $where .= " AND (q.quotation_number LIKE %s OR q.project_name LIKE %s)";
        $search_like = '%' . $wpdb->esc_like($search) . '%';
        $params[] = $search_like;
        $params[] = $search_like;
    }
    
    $query = "SELECT q.*, c.company_name as client_name, c.city
              FROM $quotations_table q
              LEFT JOIN $clients_table c ON q.client_id = c.id
              $where
              ORDER BY q.date_of_quotation DESC
              LIMIT %d OFFSET %d";
    
    $params[] = $per_page;
    $params[] = $offset;
    
    $items = $wpdb->get_results($wpdb->prepare($query, $params));
    
    wp_send_json_success([
        'items' => $items,
        'total' => count($items)
    ]);
}

/**
 * AJAX Handler: Get leave requests
 */
add_action('wp_ajax_cosign_get_leave_requests', 'cosign_handle_get_leave_requests');

function cosign_handle_get_leave_requests() {
    check_ajax_referer('cosign_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    global $wpdb;
    $leave_table = $wpdb->prefix . 'leave_requests';
    
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    $user = isset($_POST['user']) ? intval($_POST['user']) : 0;
    $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
    $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
    
    $where = "WHERE 1=1";
    $params = [];
    
    if (!empty($status)) {
        $where .= " AND lr.status = %s";
        $params[] = $status;
    }
    
    if (!empty($user)) {
        $where .= " AND lr.user_id = %d";
        $params[] = $user;
    }
    
    if (!empty($date_from)) {
        $where .= " AND lr.leave_date >= %s";
        $params[] = $date_from;
    }
    
    if (!empty($date_to)) {
        $where .= " AND lr.leave_date <= %s";
        $params[] = $date_to;
    }
    
    $query = "SELECT lr.*, u.display_name as user_name
              FROM $leave_table lr
              LEFT JOIN {$wpdb->users} u ON lr.user_id = u.ID
              $where
              ORDER BY lr.created_at DESC";
    
    $requests = empty($params) ? $wpdb->get_results($query) : $wpdb->get_results($wpdb->prepare($query, $params));
    
    wp_send_json_success([
        'requests' => $requests,
        'total' => count($requests)
    ]);
}

/**
 * AJAX Handler: Update leave status
 */
add_action('wp_ajax_cosign_update_leave_status', 'cosign_handle_update_leave_status');

function cosign_handle_update_leave_status() {
    check_ajax_referer('cosign_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    global $wpdb;
    $leave_table = $wpdb->prefix . 'leave_requests';
    
    $leave_id = isset($_POST['leave_id']) ? intval($_POST['leave_id']) : 0;
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    
    if (empty($leave_id) || empty($status)) {
        wp_send_json_error('Invalid parameters');
        return;
    }
    
    $result = $wpdb->update(
        $leave_table,
        ['status' => $status],
        ['id' => $leave_id],
        ['%s'],
        ['%d']
    );
    
    if ($result !== false) {
        wp_send_json_success(['message' => 'Leave request updated successfully']);
    } else {
        wp_send_json_error('Failed to update leave request');
    }
}

/**
 * AJAX Handler: Get unread comments
 */
add_action('wp_ajax_cosign_get_unread_comments', 'cosign_handle_get_unread_comments');

function cosign_handle_get_unread_comments() {
    check_ajax_referer('cosign_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    global $wpdb;
    $comments_table = $wpdb->prefix . 'task_comments';
    $tasks_table = $wpdb->prefix . 'tasks_full';
    
    $query = "SELECT tc.*, t.title as task_title, u.display_name as commented_by_name
              FROM $comments_table tc
              LEFT JOIN $tasks_table t ON tc.task_id = t.id
              LEFT JOIN {$wpdb->users} u ON tc.commented_by = u.ID
              WHERE tc.is_read = 0
              ORDER BY tc.created_at DESC";
    
    $comments = $wpdb->get_results($query);
    
    wp_send_json_success([
        'comments' => $comments,
        'total' => count($comments)
    ]);
}

/**
 * AJAX Handler: Mark comment as read
 */
add_action('wp_ajax_cosign_mark_comment_read', 'cosign_handle_mark_comment_read');

function cosign_handle_mark_comment_read() {
    check_ajax_referer('cosign_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    global $wpdb;
    $comments_table = $wpdb->prefix . 'task_comments';
    
    $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
    
    if (empty($comment_id)) {
        wp_send_json_error('Invalid comment ID');
        return;
    }
    
    $result = $wpdb->update(
        $comments_table,
        ['is_read' => 1],
        ['id' => $comment_id],
        ['%d'],
        ['%d']
    );
    
    if ($result !== false) {
        wp_send_json_success(['message' => 'Comment marked as read']);
    } else {
        wp_send_json_error('Failed to mark comment as read');
    }
}

/**
 * AJAX Handler: Save Lead
 */
add_action('wp_ajax_cosign_save_lead', 'cosign_handle_save_lead');

function cosign_handle_save_lead() {
    check_ajax_referer('cosign_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    global $wpdb;
    $leads_table = $wpdb->prefix . 'pipeline_leads';
    
    $lead_data = isset($_POST['lead_data']) ? $_POST['lead_data'] : [];
    $lead_id = isset($lead_data['lead_id']) ? intval($lead_data['lead_id']) : 0;
    $is_edit = $lead_id > 0;
    
    // Sanitize data
    $data = [
        'generated_by' => intval($lead_data['generated_by'] ?? get_current_user_id()),
        'client_name' => sanitize_text_field($lead_data['client_name'] ?? ''),
        'client_type' => sanitize_text_field($lead_data['client_type'] ?? ''),
        'contact_person' => sanitize_text_field($lead_data['contact_person'] ?? ''),
        'email' => sanitize_email($lead_data['email'] ?? ''),
        'phone' => sanitize_text_field($lead_data['phone'] ?? ''),
        'country' => sanitize_text_field($lead_data['country'] ?? ''),
        'zone' => sanitize_text_field($lead_data['zone'] ?? ''),
        'state' => sanitize_text_field($lead_data['state'] ?? ''),
        'city' => sanitize_text_field($lead_data['city'] ?? ''),
        'address' => sanitize_textarea_field($lead_data['address'] ?? '')
    ];
    
    if (empty($data['client_name']) || empty($data['email'])) {
        wp_send_json_error('Client name and email are required');
        return;
    }
    
    if ($is_edit) {
        // Update existing lead
        $result = $wpdb->update(
            $leads_table,
            $data,
            ['id' => $lead_id],
            ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'],
            ['%d']
        );
        
        if ($result !== false) {
            wp_send_json_success(['message' => 'Lead updated successfully!', 'id' => $lead_id]);
        } else {
            wp_send_json_error('Failed to update lead: ' . $wpdb->last_error);
        }
    } else {
        // Insert new lead
        $data['created_date'] = current_time('mysql');
        
        $result = $wpdb->insert(
            $leads_table,
            $data,
            ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
        
        if ($result !== false) {
            wp_send_json_success(['message' => 'Lead saved successfully!', 'id' => $wpdb->insert_id]);
        } else {
            wp_send_json_error('Failed to save lead: ' . $wpdb->last_error);
        }
    }
}

/**
 * AJAX Handler: Save Project
 */
add_action('wp_ajax_cosign_save_project', 'cosign_handle_save_project');

function cosign_handle_save_project() {
    check_ajax_referer('cosign_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    global $wpdb;
    $projects_table = $wpdb->prefix . 'pipeline_projects';
    $quotations_table = $wpdb->prefix . 'quotations';
    
    $project_data = isset($_POST['project_data']) ? $_POST['project_data'] : [];
    $project_id = isset($project_data['project_id']) ? intval($project_data['project_id']) : 0;
    $is_edit = $project_id > 0;
    
    // Sanitize data
    $data = [
        'name' => sanitize_text_field($project_data['project_name'] ?? ''),
        'project_type' => sanitize_text_field($project_data['project_type'] ?? ''),
        'client_id' => intval($project_data['client_id'] ?? 0),
        'quotation_number' => sanitize_text_field($project_data['quotation_number'] ?? ''),
        'date_of_quotation' => !empty($project_data['date_of_quotation']) ? sanitize_text_field($project_data['date_of_quotation']) : null,
        'expected_value' => !empty($project_data['expected_value']) ? floatval($project_data['expected_value']) : null,
        'expected_closure_date' => !empty($project_data['expected_closure_date']) ? sanitize_text_field($project_data['expected_closure_date']) : null,
        'stage' => sanitize_text_field($project_data['stage'] ?? ''),
        'status' => sanitize_text_field($project_data['status'] ?? 'draft'),
        'generated_by' => intval($project_data['generated_by'] ?? get_current_user_id()),
        'notes' => sanitize_textarea_field($project_data['notes'] ?? '')
    ];
    
    if (empty($data['name'])) {
        wp_send_json_error('Project name is required');
        return;
    }
    
    // Determine which table to use
    $use_quotations = !empty($data['quotation_number']) || !empty($data['date_of_quotation']);
    $table = $use_quotations ? $quotations_table : $projects_table;
    
    // Map project_name to the correct field
    if ($use_quotations) {
        $data['project_name'] = $data['name'];
        unset($data['name']);
    }
    
    // Prepare format array based on table
    if ($use_quotations) {
        $format = ['%s', '%s', '%d', '%s', '%s', '%f', '%s', '%s', '%s', '%d', '%s'];
    } else {
        $format = ['%s', '%s', '%d', '%s', '%s', '%f', '%s', '%s', '%s', '%d', '%s'];
    }
    
    if ($is_edit) {
        // Try both tables
        $result = false;
        $updated = false;
        
        // First try pipeline_projects
        $check_project = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$projects_table} WHERE id = %d",
            $project_id
        ));
        
        if ($check_project) {
            $result = $wpdb->update(
                $projects_table,
                $data,
                ['id' => $project_id],
                $format,
                ['%d']
            );
            $updated = true;
        } else {
            // Try quotations table
            $check_quotation = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM {$quotations_table} WHERE id = %d",
                $project_id
            ));
            
            if ($check_quotation) {
                // For quotations, use project_name
                $data['project_name'] = $data['name'];
                unset($data['name']);
                
                $result = $wpdb->update(
                    $quotations_table,
                    $data,
                    ['id' => $project_id],
                    $format,
                    ['%d']
                );
                $updated = true;
            }
        }
        
        if ($result !== false || $updated) {
            wp_send_json_success(['message' => 'Project updated successfully!', 'id' => $project_id]);
        } else {
            wp_send_json_error('Failed to update project: ' . $wpdb->last_error);
        }
    } else {
        // Insert new project
        $data['created_date'] = current_time('mysql');
        $format[] = '%s'; // Add created_date format
        
        // Default to pipeline_projects unless quotation number exists
        if ($use_quotations) {
            $data['project_name'] = $data['name'];
            unset($data['name']);
        }
        
        $result = $wpdb->insert(
            $table,
            $data,
            $format
        );
        
        if ($result !== false) {
            wp_send_json_success(['message' => 'Project saved successfully!', 'id' => $wpdb->insert_id]);
        } else {
            wp_send_json_error('Failed to save project: ' . $wpdb->last_error);
        }
    }
}
