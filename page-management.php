<?php

/**
 * Plugin Name: Page Management Test
 * Description: A simple plugin to manage pages/posts with title, content & status.
 * Version: 1.0
 * Author: Ruhul Siddiki
 */

if (!defined('ABSPATH')) {
    exit; // denayed direct acces
}

// activation hook
function pm_activate_plugin()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'page_management';

    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id int(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        content text NOT NULL,
        status varchar(20) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'pm_activate_plugin');

define("PLUGIN_DIR_PATH", plugin_dir_path(__FILE__));
define("PLUGIN_DIR_URL", plugin_dir_url(__FILE__));
define("PLUGIN_VERSION", "1.0.0");
define("PLUGIN_BASENAME", plugin_basename(__FILE__));

class PageManagementPlugin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'first_menu_plugin'));
    }

    public function first_menu_plugin()
    {
        add_menu_page('Page Management', 'Page Management', 'manage_options', 'page-management', array($this, 'page_management_callback_func'), 'dashicons-camera-alt', 80);
        add_submenu_page('page-management', 'Add New Page', 'Add New Page', 'manage_options', 'add-new-page', array($this, 'submenu_func'));
        add_submenu_page('page-management', 'Main Page', 'Main Page', 'manage_options', 'main-page', array($this, 'submenu_func2'));
    }

    public function submenu_func()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'page_management';

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pm_submit'])) {
            $title = sanitize_text_field($_POST['pm_title']);
            $content = sanitize_textarea_field($_POST['pm_content']);
            $status = sanitize_text_field($_POST['pm_status']);
            $id = isset($_POST['pm_id']) ? intval($_POST['pm_id']) : 0;

            if ($id > 0) {
                $wpdb->update($table_name, ['title' => $title, 'content' => $content, 'status' => $status], ['id' => $id]);
            } else {
                $wpdb->insert($table_name, ['title' => $title, 'content' => $content, 'status' => $status]);
            }
        }

        if (isset($_GET['delete_id'])) {
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_page_nonce')) {
                die('Security check failed');
            }
            $id = intval($_GET['delete_id']);
            $wpdb->delete($table_name, ['id' => $id]);
        }

        $pages = $wpdb->get_results("SELECT * FROM $table_name");

        $edit_page = null;
        if (isset($_GET['edit_id'])) {
            $id = intval($_GET['edit_id']);
            $edit_page = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
        }

        include_once PLUGIN_DIR_PATH . 'views/submenu.php';
    }

    public function submenu_func2()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'page_management';
        $pages = $wpdb->get_results("SELECT * FROM $table_name");
        $edit_page = null;
        if (isset($_GET['edit_id'])) {
            $id = intval($_GET['edit_id']);
            $edit_page = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
        }

        include_once PLUGIN_DIR_PATH . 'views/submenu2.php';
    }

    public function page_management_callback_func()
    {
        echo "<h2>Main Page</h2>";
        echo "Hello";
    }
}

new PageManagementPlugin();
