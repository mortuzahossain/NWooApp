<?php



/// ----------------------------- NOTIFICATION ACTION -----------------------------------------
// Send push notification when order status changes
add_action('woocommerce_order_status_changed', 'send_push_notification_on_order_status_change', 10, 3);

function send_push_notification_on_order_status_change($order_id, $old_status, $new_status)
{

    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();
    $fcm_token = get_user_meta($user_id, '_fcm_token', true);

    $data_dir = CUSTOM_PLUGIN_DIR . 'data/';
    $file_path = $data_dir . 'notification-messages.json';
    $default_messages = [
        'processing' => [
            'enabled' => true,
            'message' => 'Your order [order_id] is now processing.'
        ],
        'on-hold' => [
            'enabled' => true,
            'message' => 'Your order #[order_id] On hold.'
        ],
        'pending' => [
            'enabled' => true,
            'message' => 'Your order #[order_id] On pending.'
        ],
        'completed' => [
            'enabled' => true,
            'message' => 'Your #[order_id] is now completed!'
        ],
        'cancelled' => [
            'enabled' => true,
            'message' => 'Your order #[order_id] is Cancelled.'
        ],
        'refunded' => [
            'enabled' => true,
            'message' => 'Your order #[order_id] is Refunded.'
        ],
        'failed' => [
            'enabled' => true,
            'message' => 'Your order #[order_id] is Failed.'
        ],
    ];

    if (file_exists($file_path)) {
        $file_content = file_get_contents($file_path);
        $messages = json_decode($file_content, true) ?: $default_messages;
    } else {
        $messages = $default_messages;
        file_put_contents($file_path, json_encode($messages, JSON_PRETTY_PRINT));
    }
    if (isset($messages[$new_status]) &&  $messages[$new_status]['enabled']) {
        if (isset($messages[$new_status]) && isset($messages[$new_status]['message'])) {
            $message_template = $messages[$new_status]['message'];
            $message = str_replace('[order_id]', $order_id, $message_template);
        } else {
            $message = "Your order #{$order_id} status has changed to {$new_status}.";
        }
    } else {
        $message = "dscascda: $new_status" . $messages[$new_status];
    }

    if ($fcm_token && $message != "") {
        send_fcm_push_notification($fcm_token, $message);
    }
    send_fcm_push_notification("empty token", $message);
}


function send_fcm_push_notification($fcm_token, $message) {
    $notification_title = 'Order Update!!';
    $body = array(
        'message' => array(
            'token' => $fcm_token,
            'notification' => array(
                'title' => $notification_title,
                'body' => $message
            ),
            'data' => array(
                'title' => $notification_title,
                'body' => $message
            ),
        ),
    );
// logMe($body);
    $response = sendFirebaseNotification($body);
// logMe($response);
}

// Store FCM token upon user login
function store_fcm_token_on_login($user_login, $user) {
    if (isset($_POST['fcm_token']) && !empty($_POST['fcm_token'])) {
        $fcm_token = sanitize_text_field($_POST['fcm_token']);
        update_user_meta($user->ID, '_fcm_token', $fcm_token);
    }
}
add_action('wp_login', 'store_fcm_token_on_login', 10, 2);
