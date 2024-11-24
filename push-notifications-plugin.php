<?php
/**
 * Plugin Name: NWooApp
 * Description: A plugin to make your wordpress app into natieve webview app with deeplinking & push notification
 * Version: 1.0
 * Author: Md Mortuza Hossain
 * Requires Plugins: woocommerce
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

define('CUSTOM_PLUGIN_DIR', plugin_dir_path(__FILE__));


// Register the admin menu.
add_action('admin_menu', 'custom_plugin_add_menu');

function custom_plugin_add_menu() {
    add_menu_page(
        'NWooApp', // Page title
        'NWooApp', // Menu title
        'manage_options', // Capability
        'nwooapp',  // Menu slug
        'nwooapp_main_page', // Callback function
        'dashicons-bell', // Icon
        100  // Position
    );
}


// Regestering the helper files
require_once plugin_dir_path(__FILE__) . 'api.php';
require_once plugin_dir_path(__FILE__) . 'helper.php';
require_once plugin_dir_path(__FILE__) . 'trigger_actions.php';


// Render the plugin admin page.
function nwooapp_main_page() {
    ?>
    <div class="wrap">
        <h1>Welcome to <b>NWooApp</b></h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=nwooapp&tab=settings" class="nav-tab <?php echo custom_plugin_get_active_tab('settings'); ?>">Settings</a>
            <!-- <a href="?page=nwooapp&tab=splash-screen" class="nav-tab <?php echo custom_plugin_get_active_tab('splash-screen'); ?>">Splash Screen</a> -->
            <a href="?page=nwooapp&tab=order-notification" class="nav-tab <?php echo custom_plugin_get_active_tab('order-notification'); ?>">Order Notification</a>
            <a href="?page=nwooapp&tab=push-notification" class="nav-tab <?php echo custom_plugin_get_active_tab('push-notification'); ?>">Push Notification</a>
            <a href="?page=nwooapp&tab=deep-linking" class="nav-tab <?php echo custom_plugin_get_active_tab('deep-linking'); ?>">Deep Linking</a>
            <a href="?page=nwooapp&tab=manage-external-link" class="nav-tab <?php echo custom_plugin_get_active_tab('manage-external-link'); ?>">Manage External Link</a>
            <a href="?page=nwooapp&tab=help-others" class="nav-tab <?php echo custom_plugin_get_active_tab('help-others'); ?>">Help & Others</a>
        </h2>

        <div class="tab-content">
            <?php
            $tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';
            switch ($tab) {
                case 'splash-screen':
                    echo '<h2>Splash Screen</h2><p>Content for Splash Screen.</p>';
                    break;
                case 'order-notification':
                    include_once CUSTOM_PLUGIN_DIR . 'order-notification-settings.php';
                    break;
                case 'push-notification':
                    include_once CUSTOM_PLUGIN_DIR . 'push-notification.php';
                    break;
                case 'deep-linking':
                    include_once CUSTOM_PLUGIN_DIR . 'deep-linking.php';
                    break;
                case 'manage-external-link':
                    include_once CUSTOM_PLUGIN_DIR . 'manage-external-links.php';
                    break;
                case 'help-others':
                    include_once CUSTOM_PLUGIN_DIR . 'help-others.php';
                    break;
                default:
                    include_once CUSTOM_PLUGIN_DIR . 'settings.php';
            }
            ?>
        </div>
    </div>
    <?php
}

// Get active tab CSS class.
function custom_plugin_get_active_tab($tab_name) {
    return isset($_GET['tab']) && $_GET['tab'] === $tab_name ? 'nav-tab-active' : '';
}
