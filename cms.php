<?php
/**
 * Plugin Name: CoSign Planner
 * Plugin URI: https://yoursite.com
 * Description: Complete CMS solution for signage company management.
 * Version: 2.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Neo Kun
 * Author URI: https://yoursite.com
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: cosign-planner
 * Domain Path: /languages
 */

namespace CoSignPlanner;

defined('ABSPATH') || exit;

/**
 * Define Plugin Constants
 */
define('COSIGN_VERSION', '2.0.0');
define('COSIGN_PLUGIN_FILE', __FILE__);
define('COSIGN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('COSIGN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('COSIGN_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
final class CoSign_Planner {

    /**
     * @var CoSign_Planner|null
     */
    private static ?CoSign_Planner $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->register_hooks();
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies(): void {
        $files = [
            'includes/db-table-creator-fixed.php',
            'includes/admin-menu.php',
            'includes/ajax-handlers.php',
            'includes/shared-data-handler.php',
            'includes/db-notice.php',
            'includes/pipeline-handlers.php',
        ];

        foreach ($files as $file) {
            $path = COSIGN_PLUGIN_DIR . $file;
            if (file_exists($path)) {
                require_once $path;
            } else {
                error_log("CoSign Planner: Missing dependency - {$file}");
            }
        }
    }

    /**
     * Register hooks and actions
     */
    private function register_hooks(): void {
        register_activation_hook(COSIGN_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(COSIGN_PLUGIN_FILE, [$this, 'deactivate']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_init', [$this, 'check_version']);
    }

    /**
     * Plugin activation callback
     */
    public function activate(): void {
        if (function_exists('mcp_create_custom_table')) {
            mcp_create_custom_table();
        }

        update_option('cosign_version', COSIGN_VERSION);
        update_option('cosign_activated', current_time('mysql'));

        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation callback
     */
    public function deactivate(): void {
        flush_rewrite_rules();
    }

    /**
     * Check plugin version and run necessary updates
     */
    public function check_version(): void {
        $current_version = get_option('cosign_version', '0');

        if (version_compare($current_version, COSIGN_VERSION, '<')) {
            $this->run_updates($current_version);
            update_option('cosign_version', COSIGN_VERSION);
        }
    }

    /**
     * Run version-specific updates
     */
    private function run_updates(string $from_version): void {
        if (version_compare($from_version, '2.0.0', '<') && function_exists('mcp_create_custom_table')) {
            mcp_create_custom_table();
        }
    }

    /**
     * Enqueue admin CSS/JS assets
     */
    public function enqueue_admin_assets(string $hook): void {
        if (!str_contains($hook, 'cosign') && !str_contains($hook, 'pipeline')) {
            return;
        }

        // Styles
        wp_enqueue_style(
            'cosign-font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
            [],
            '6.4.0'
        );

        wp_enqueue_style(
            'cosign-admin',
            COSIGN_PLUGIN_URL . 'assets/css/admin.css',
            [],
            COSIGN_VERSION
        );

        // jQuery UI
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style(
            'jquery-ui-css',
            'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css',
            [],
            '1.13.2'
        );

        // FullCalendar (only on calendar pages)
        if (str_contains($hook, 'calendar')) {
            wp_enqueue_script(
                'fullcalendar',
                'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js',
                [],
                '6.1.9',
                true
            );
        }

        // Admin JS
        wp_enqueue_script(
            'cosign-admin',
            COSIGN_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            COSIGN_VERSION,
            true
        );

        // Localized data
        $current_user = wp_get_current_user();
        wp_localize_script('cosign-admin', 'cosignData', [
            'ajaxUrl'          => admin_url('admin-ajax.php'),
            'nonce'            => wp_create_nonce('cosign_nonce'),
            'pluginUrl'        => COSIGN_PLUGIN_URL,
            'currentUser'      => $current_user->display_name,
            'currentUserEmail' => $current_user->user_email,
            'currentUserId'    => $current_user->ID,
            'strings' => [
                'confirmDelete'   => __('Are you sure you want to delete this item?', 'cosign-planner'),
                'saveSuccess'     => __('Saved successfully!', 'cosign-planner'),
                'saveError'       => __('Error saving. Please try again.', 'cosign-planner'),
                'requiredFields'  => __('Please fill in all required fields.', 'cosign-planner'),
            ],
        ]);
    }
}

/**
 * Initialize Plugin
 */
function cosign_planner_init(): CoSign_Planner {
    return CoSign_Planner::get_instance();
}
cosign_planner_init();

/**
 * Helper: Get clients list
 */
function cosign_get_clients_list(): array {
    global $wpdb;
    $table = $wpdb->prefix . 'clients';

    $query = $wpdb->prepare(
        "SELECT id, company_name, client_type, email 
         FROM $table 
         WHERE status = %s OR status IS NULL
         ORDER BY company_name ASC",
        'Active'
    );

    return $wpdb->get_results($query, ARRAY_A);
}

/**
 * Helper: Get WordPress users list
 */
function cosign_get_users_list(): array {
    $users = get_users([
        'role__in' => ['administrator', 'editor', 'author'],
        'orderby'  => 'display_name',
        'order'    => 'ASC',
    ]);

    return array_map(static function ($user) {
        return [
            'ID'           => $user->ID,
            'display_name' => $user->display_name,
            'user_email'   => $user->user_email,
        ];
    }, $users);
}

/**
 * Debug logger (only in WP_DEBUG mode)
 */
if (defined('WP_DEBUG') && WP_DEBUG) {
    function cosign_debug_log(string $message, $data = null): void {
        $output = 'CoSign Debug: ' . $message;
        if ($data !== null) {
            $output .= ' - ' . print_r($data, true);
        }
        error_log($output);
    }
}
