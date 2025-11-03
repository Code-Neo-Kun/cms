<?php
if (!defined('ABSPATH')) {
    exit;
}

function mcp_create_custom_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // Create menus table
    $menus_table = $wpdb->prefix . 'menus';
    
    $sql = "CREATE TABLE $menus_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        menu_name varchar(255) NOT NULL,
        description text,
        menu_type varchar(100),
        client_id bigint(20),
        project_id bigint(20),
        created_by bigint(20),
        status varchar(50) DEFAULT 'draft',
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY client_id (client_id),
        KEY project_id (project_id),
        KEY created_by (created_by)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Create menu_items table
    $menu_items_table = $wpdb->prefix . 'menu_items';
    
    $sql = "CREATE TABLE $menu_items_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        menu_id bigint(20) NOT NULL,
        item_name varchar(255) NOT NULL,
        item_description text,
        quantity int(11) DEFAULT 1,
        unit_price decimal(10,2) DEFAULT 0.00,
        total_price decimal(10,2) DEFAULT 0.00,
        category varchar(100),
        item_order int(11) DEFAULT 0,
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        updated_at timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY menu_id (menu_id)
    ) $charset_collate;";
    
    dbDelta($sql);
}
