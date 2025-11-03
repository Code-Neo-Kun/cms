<?php
if (!defined('ABSPATH')) {
    exit;
}

function cosign_register_admin_menu() {
    // Add top level menu
    add_menu_page(
        'CoSign Planner', 
        'CoSign Planner',
        'manage_options',
        'cosign-planner',
        'cosign_dashboard_page',
        'dashicons-calendar-alt',
        30
    );

    // Add submenu pages
    add_submenu_page('cosign-planner', 'Dashboard', 'Dashboard', 'manage_options', 'dashboard.php', 'cosign_dashboard_page');
    add_submenu_page('cosign-planner', 'Calendar', 'Calendar', 'manage_options', 'cosign-calendar', 'cosign_calendar_page');
    add_submenu_page('cosign-planner', 'Add Meeting', 'Add Meeting', 'manage_options', 'cosign-add-meeting', 'cosign_add_meeting_page');
    add_submenu_page('cosign-planner', 'Create Menu', 'Create Menu', 'manage_options', 'cosign-create-menu', 'cosign_create_menu_page');
    add_submenu_page('cosign-planner', 'Daily Closing', 'Daily Closing', 'manage_options', 'cosign-daily-closing', 'cosign_daily_closing_page');
    add_submenu_page('cosign-planner', 'Todo List', 'Todo List', 'manage_options', 'cosign-todo-list', 'cosign_todo_list_page');
    add_submenu_page('cosign-planner', 'Task List', 'Task List', 'manage_options', 'cosign-task-list', 'cosign_task_list_page');
    add_submenu_page('cosign-planner', 'Assigned by Me', 'Assigned by Me', 'manage_options', 'cosign-assigned-by-me', 'cosign_assigned_by_me_page');
    add_submenu_page('cosign-planner', 'Dispatch Stage', 'Dispatch Stage', 'manage_options', 'cosign-dispatch-stage', 'cosign_dispatch_stage_page');
    add_submenu_page('cosign-planner', 'Pipeline Projects', 'Pipeline Projects', 'manage_options', 'cosign-pipeline', 'cosign_pipeline_projects_page');
    add_submenu_page('cosign-planner', 'Lead Details', 'Lead Details', 'manage_options', 'cosign-lead-details', 'cosign_lead_details_page');
    add_submenu_page('cosign-planner', 'Project Details', 'Project Details', 'manage_options', 'cosign-project-details', 'cosign_project_details_page');
    add_submenu_page('cosign-planner', 'On Leave', 'On Leave', 'manage_options', 'cosign-on-leave', 'cosign_on_leave_page');
    add_submenu_page('cosign-planner', 'Unread Comments', 'Unread Comments', 'manage_options', 'cosign-unread-comments', 'cosign_unread_comments_page');
    add_submenu_page('cosign-planner', 'Price List', 'Price List', 'manage_options', 'cosign-price-list', 'cosign_price_list_page');
    add_submenu_page('cosign-planner', 'Reports', 'Reports', 'manage_options', 'cosign-reports', 'cosign_reports_page');
}
add_action('admin_menu', 'cosign_register_admin_menu');

// Page callback functions
function cosign_dashboard_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/dashboard.php';
}

function cosign_calendar_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/calendar.php';
}

function cosign_add_meeting_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/add-meeting.php';
}

function cosign_create_menu_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/create-menu.php';
}

function cosign_daily_closing_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/daily-closing.php';
}

function cosign_todo_list_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/todo-list.php';
}

function cosign_task_list_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/task-list.php';
}

function cosign_pipeline_projects_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/pipeline-projects.php';
}

function cosign_price_list_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/price-list.php';
}

function cosign_reports_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/reports.php';
}

function cosign_assigned_by_me_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/assigned-by-me.php';
}

function cosign_dispatch_stage_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/dispatch-stage.php';
}

function cosign_on_leave_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/on-leave.php';
}

function cosign_unread_comments_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/unread-comments.php';
}

function cosign_lead_details_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/lead-details.php';
}

function cosign_project_details_page() {
    require_once COSIGN_PLUGIN_DIR . 'templates/project-details.php';
}