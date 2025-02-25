<?php

/**
 * Plugin Name: NWooApp
 * Description: A plugin to make your wordpress app into natieve webview app with deeplinking & push notification
 * Version: 1.0.1
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

function custom_plugin_add_menu()
{
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
function nwooapp_main_page()
{
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
function custom_plugin_get_active_tab($tab_name)
{
    return isset($_GET['tab']) && $_GET['tab'] === $tab_name ? 'nav-tab-active' : '';
}



function nwooapp_enqueue_media_uploader($hook_suffix)
{

    wp_enqueue_media();
    wp_enqueue_script(
        'nwooapp-media-uploader',
        plugin_dir_url(__FILE__) . 'media-uploader.js',
        ['jquery'], // Dependencies
        '1.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'nwooapp_enqueue_media_uploader');




// Activate the plugin - init database
function wcn_activate()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'nwooapp_user_notifications';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        message TEXT NOT NULL,
        order_status VARCHAR(50) DEFAULT 'pending',
        status ENUM('unread', 'read') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'wcn_activate');


// API for getting the data
function wcn_get_notifications() {
    if (!is_user_logged_in()) wp_die();

    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'nwooapp_user_notifications';

    // Fetch last 30 notifications
    $notifications = $wpdb->get_results(
        $wpdb->prepare("SELECT id, message, order_status, status, created_at FROM $table_name WHERE user_id = %d ORDER BY id DESC LIMIT 30", $user_id),
        ARRAY_A
    );

    // Mark notifications as seen when retrieved
    $wpdb->query(
        $wpdb->prepare("UPDATE $table_name SET status = 'read' WHERE user_id = %d AND status = 'unread'", $user_id)
    );

    // Return JSON response
    wp_send_json($notifications);
}
add_action('wp_ajax_wcn_get_notifications', 'wcn_get_notifications');


// - SORTCODE
function wcn_notification_shortcode() {
    if (!is_user_logged_in()) return '';

    ob_start();
    ?>

    <style>
    div#wcn-notification {
        display: block;
        padding: 10px;
        margin: 10px;
        width: 20px;
        height: 20px;
    }
    div#wcn-notification>i.dashicons.dashicons-bell {
        font-size: 30px;
    }
    </style>

    <div id="wcn-notification" style="cursor:pointer;position:relative;" >
        <i class="dashicons dashicons-bell"></i>
        <span id="wcn-count" style="position:absolute;top:-5px;right:-5px;background:black;color:white;border-radius:50%;padding:3px 6px;font-size:12px;"></span>
    </div>

    <div id="wcn-popup" style="display:none;position:absolute;top:40px;right:0;background:white;padding:10px;border:1px solid #ddd;box-shadow:0 0 10px rgba(0,0,0,0.1);">
        <ul id="wcn-list" style="list-style:none;padding:0;margin:0;"></ul>
    </div>

    <script>
        function getOrderIcon(status) {
            switch (status) {
                case 'processing': return '🔄'; // Processing
                case 'completed': return '✅'; // Completed
                case 'cancelled': return '❌'; // Cancelled
                case 'on-hold': return '⏳'; // On-Hold
                default: return '📦'; // Default
            }
        }

        function fetchNotifications() {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=wcn_get_notifications')
                .then(response => response.json())
                .then(data => {
                    let list = document.getElementById('wcn-list');
                    list.innerHTML = '';

                    if (data.length > 0) {
                        let unreadCount = data.filter(notification => notification.status === "unread").length;
                        if (unreadCount === 0) {
                            document.getElementById('wcn-count').style.display = "none";
                        } else {
                            document.getElementById('wcn-count').style.display = "inline-block";
                            document.getElementById('wcn-count').textContent = unreadCount;
                        }
                    } else {
                        document.getElementById('wcn-count').textContent = '';
                        list.innerHTML = '<li>No notifications</li>';
                    }

                    data.forEach(notif => {
                        let li = document.createElement('li');
                        li.style.padding = '5px 0';
                        li.innerHTML = `${getOrderIcon(notif.order_status)} <strong>${notif.message}</strong> <br> <small>${notif.created_at}</small>`;
                        list.appendChild(li);
                    });
                });
        }

        document.getElementById('wcn-notification').addEventListener('click', function() {
            let popup = document.getElementById('wcn-popup');
            popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
            fetchNotifications();
        });

        document.addEventListener('click', function(event) {
            if (!document.getElementById('wcn-notification').contains(event.target) &&
                !document.getElementById('wcn-popup').contains(event.target)) {
                document.getElementById('wcn-popup').style.display = 'none';
            }
        });

        fetchNotifications(); // Fetch notifications on page load
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('user_notifications', 'wcn_notification_shortcode');
