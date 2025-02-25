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
    // $wpdb->query(
    //     $wpdb->prepare("UPDATE $table_name SET status = 'read' WHERE user_id = %d AND status = 'unread'", $user_id)
    // );

    // Return JSON response
    wp_send_json($notifications);
}
add_action('wp_ajax_wcn_get_notifications', 'wcn_get_notifications');


function wcn_update_notifications() {
    if (!is_user_logged_in()) wp_die();

    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'nwooapp_user_notifications';
    

    $wpdb->query(
        $wpdb->prepare("UPDATE $table_name SET status = 'read' WHERE user_id = %d AND status = 'unread'", $user_id)
    ); 

    
    wp_send_json_success("mark message as read");
}
add_action('wp_ajax_wcn_update_notifications', 'wcn_update_notifications');


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

    <div id="wcn-popup" style="display:none;position:fixed;top:30%;left:50%;transform:translate(-50%, -50%);background:white;padding:20px;border:1px solid #ddd;box-shadow:0 0 10px rgba(0,0,0,0.1);z-index:1000;width:60%;max-width:600px;">
        <button id="close-wcn" style="position:absolute;top:5px;right:5px;border:none;background:red;color:white;width:20px;height:20px;cursor:pointer;font-size:14px;">×</button>
        <ul id="wcn-list" style="list-style:none;padding:0;margin:0;"></ul>
    </div>

    <style>
        @media (max-width: 768px) { /* Tablet */
            #wcn-popup {
                width: 80%;
            }
        }
        @media (max-width: 480px) { /* Mobile */
            #wcn-popup {
                width: 90%;
            }
        }
    </style>
    <script>
       function getOrderIcon(status) {
            switch (status) {
                case 'processing': return '🔄'; // Processing
                case 'completed': return '✅'; // Completed
                case 'cancelled': return '❌'; // Cancelled
                case 'on-hold': return '⏳'; // On-Hold
                case 'pending': return '⏱️'; // Pending
                case 'refunded': return '💸'; // Refunded
                case 'failed': return '❗'; // Failed
                default: return '📦'; // Default
            }
        }

        document.getElementById('close-wcn').addEventListener('click', function() {
            document.getElementById('wcn-popup').style.display = 'none';
        });

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

                    data.forEach((notif, index) => {
                        let li = document.createElement('li');
                        li.style.display = 'flex';
                        li.style.alignItems = 'center';
                        li.style.gap = '15px';
                        li.style.padding = '10px 0';

                        // Icon container with circle background
                        let iconContainer = document.createElement('div');
                        iconContainer.style.width = '40px';
                        iconContainer.style.height = '40px';
                        iconContainer.style.borderRadius = '50%';
                        iconContainer.style.backgroundColor = '#f0f0f0'; // Light gray background
                        iconContainer.style.display = 'flex';
                        iconContainer.style.justifyContent = 'center';
                        iconContainer.style.alignItems = 'center';
                        iconContainer.style.flexShrink = '0';

                        let icon = document.createElement('span');
                        icon.innerText = getOrderIcon(notif.order_status); // Get emoji based on order status
                        icon.style.fontSize = '20px'; // Adjust emoji size here


                        iconContainer.appendChild(icon);

                        // Text container
                        let textContainer = document.createElement('div');
                        textContainer.style.display = 'flex';
                        textContainer.style.flexDirection = 'column';

                        // Format the date as "time ago"
                        let formattedDate = timeAgo(notif.created_at);
                        let dateElement = document.createElement('small');
                        dateElement.style.color = '#888';
                        dateElement.style.marginBottom = '4px';
                        dateElement.innerText = formattedDate;

                        let messageElement = document.createElement('strong');
                        messageElement.innerText = notif.message;

                        textContainer.appendChild(dateElement);
                        textContainer.appendChild(messageElement);

                        li.appendChild(iconContainer);
                        li.appendChild(textContainer);
                        list.appendChild(li);

                        // Add HR only if it's not the last item
                        if (index !== data.length - 1) {
                            let hr = document.createElement('hr');
                            hr.style.border = '0';
                            hr.style.borderTop = '1px solid #ddd';
                            hr.style.margin = '5px 0';
                            list.appendChild(hr);
                        }
                    });
                });
        }

        function updateNotifications() {
             fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=wcn_update_notifications')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('wcn-count').style.display = "none";
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });

        }

        document.getElementById('wcn-notification').addEventListener('click', function() {
            let popup = document.getElementById('wcn-popup');
            popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
            // fetchNotifications();
            console.log("Checking");
            
            updateNotifications();
        });

        document.addEventListener('click', function(event) {
            if (!document.getElementById('wcn-notification').contains(event.target) &&
                !document.getElementById('wcn-popup').contains(event.target)) {
                document.getElementById('wcn-popup').style.display = 'none';
            }
        });

        function timeAgo(dateString) {
            let date = new Date(dateString);
            let seconds = Math.floor((new Date() - date) / 1000);
            let intervals = {
                year: 31536000,
                month: 2592000,
                day: 86400,
                hour: 3600,
                minute: 60
            };

            for (let [unit, value] of Object.entries(intervals)) {
                let count = Math.floor(seconds / value);
                if (count > 0) {
                    return `${count} ${unit}${count > 1 ? 's' : ''} ago`;
                }
            }
            return 'Just now';
        }

        fetchNotifications(); // Fetch notifications on page load
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('user_notifications', 'wcn_notification_shortcode');
