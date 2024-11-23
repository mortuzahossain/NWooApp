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
        'NWooApp',           // Page title
        'NWooApp',                  // Menu title
        'manage_options',               // Capability
        'custom-plugin-tabs',           // Menu slug
        'custom_plugin_render_page',    // Callback function
        'dashicons-admin-generic',      // Icon
        100                              // Position
    );
}




// Regertering the API

// Include the API logic from the external file
require_once plugin_dir_path(__FILE__) . 'api.php';


// Render the plugin admin page.
function custom_plugin_render_page() {
    ?>
    <div class="wrap">
        <h1>Welcome to <b>NWooApp</b></h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=custom-plugin-tabs&tab=settings" class="nav-tab <?php echo custom_plugin_get_active_tab('settings'); ?>">Settings</a>
            <!-- <a href="?page=custom-plugin-tabs&tab=splash-screen" class="nav-tab <?php echo custom_plugin_get_active_tab('splash-screen'); ?>">Splash Screen</a> -->
            <a href="?page=custom-plugin-tabs&tab=order-notification" class="nav-tab <?php echo custom_plugin_get_active_tab('order-notification'); ?>">Order Notification</a>
            <a href="?page=custom-plugin-tabs&tab=push-notification" class="nav-tab <?php echo custom_plugin_get_active_tab('push-notification'); ?>">Push Notification</a>
            <a href="?page=custom-plugin-tabs&tab=deep-linking" class="nav-tab <?php echo custom_plugin_get_active_tab('deep-linking'); ?>">Deep Linking</a>
            <a href="?page=custom-plugin-tabs&tab=manage-external-link" class="nav-tab <?php echo custom_plugin_get_active_tab('manage-external-link'); ?>">Manage External Link</a>
            <a href="?page=custom-plugin-tabs&tab=help-others" class="nav-tab <?php echo custom_plugin_get_active_tab('help-others'); ?>">Help & Others</a>
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




/// ----------------------------- NOTIFICATION ACTION -----------------------------------------
// Send push notification when order status changes
add_action('woocommerce_order_status_changed', 'send_push_notification_on_order_status_change', 10, 3);

function send_push_notification_on_order_status_change($order_id, $old_status, $new_status)
{

    $order = wc_get_order($order_id); 
    $user_id = $order->get_user_id(); 
    $fcm_token = get_user_meta($user_id, '_fcm_token', true);
     $status_messages = [
            'processing' => 'Your order is now processing.',
            'completed' => 'Your order has been completed!',
            'on-hold' => 'Your order is on hold.',
            'cancelled' => 'Your order has been cancelled.',
            'refunded' => 'Your order has been refunded.',
            'failed' => 'Your order failed.',
        ];
        
        // Get the message for the new order status
        $message = isset($status_messages[$new_status]) ? $status_messages[$new_status] : 'Your order status has changed.';
        
    if ($fcm_token) {
        // Prepare the message to send
       
        // Send the notification (This is a placeholder for your FCM push notification logic)
        send_fcm_push_notification($fcm_token, $message);
    }
    send_fcm_push_notification("empty token", $message);
}


function send_fcm_push_notification($fcm_token, $message) {
    $log_file = CUSTOM_PLUGIN_DIR . '/uploads/fcm_push_notification_log.txt';

    // Fallback to uploads directory if plugin directory is not writable
    if (!is_writable(CUSTOM_PLUGIN_DIR)) {
        $upload_dir = wp_upload_dir();
        $log_file = $upload_dir['basedir'] . '/uploads/fcm_push_notification_log.txt';
    }

    $log_data = [
        'Date' => current_time('Y-m-d H:i:s'),
        'FCM Token' => $fcm_token,
        'Message' => $message,
    ];

    $log_string = "==== Push Notification Log ====\n";
    foreach ($log_data as $key => $value) {
        $log_string .= $key . ': ' . $value . "\n";
    }
    $log_string .= "\n==============================\n\n";

    $result = file_put_contents($log_file, $log_string, FILE_APPEND);

    if ($result === false) {
        error_log('Failed to write to log file: ' . $log_file);
    } else {
        error_log('Successfully wrote to log file: ' . $log_file);
    }
}

// Store FCM token upon user login
function store_fcm_token_on_login($user_login, $user) {
    if (isset($_POST['fcm_token']) && !empty($_POST['fcm_token'])) {
        $fcm_token = sanitize_text_field($_POST['fcm_token']);
        update_user_meta($user->ID, '_fcm_token', $fcm_token);
    }
}
add_action('wp_login', 'store_fcm_token_on_login', 10, 2);
