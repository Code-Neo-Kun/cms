<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get today's meetings
 */
function cosign_get_todays_meetings() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'meetings';
    $today = current_time('Y-m-d');
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE DATE(start_time) = %s ORDER BY start_time ASC",
        $today
    ));
    
    return $results ? $results : [];
}

/**
 * Get today's tasks
 */
function cosign_get_todays_tasks() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tasks_full';
    $today = current_time('Y-m-d');
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE DATE(assigned_on) = %s OR DATE(deadline_date) = %s ORDER BY priority DESC, deadline_date ASC",
        $today,
        $today
    ));
    
    return $results ? $results : [];
}

/**
 * Get task statistics
 */
function cosign_get_task_statistics() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tasks_full';
    
    $stats = array(
        'total' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name"),
        'open' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'open' OR status = ''"),
        'in_progress' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'in-progress' OR status = 'in_progress'"),
        'completed' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'completed' OR task_done = 1"),
        'overdue' => (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE deadline_date < %s AND (status != 'completed' AND task_done != 1)",
            current_time('Y-m-d')
        ))
    );
    
    return $stats;
}

/**
 * Get meeting statistics
 */
function cosign_get_meeting_statistics() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'meetings';
    
    $stats = array(
        'total' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name"),
        'scheduled' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'scheduled' OR status = ''"),
        'completed' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'completed'"),
        'cancelled' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'cancelled'")
    );
    
    return $stats;
}
