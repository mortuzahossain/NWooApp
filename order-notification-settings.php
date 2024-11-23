<?php

// Define the file path for storing the data.
$data_dir = CUSTOM_PLUGIN_DIR . 'data/';
$file_path = $data_dir . 'notification-messages.json';
$default_messages = [
    'processing' => [
        'enabled' => true,
        'message' => 'Your order [order_id] is now processing.'
    ],
    'on_hold' => [
        'enabled' => false,
        'message' => 'Custom message for On hold.'
    ],
    'completed' => [
        'enabled' => true,
        'message' => 'Your #[order_id] is now completed!'
    ],
    'cancelled' => [
        'enabled' => false,
        'message' => 'Custom message for Cancelled.'
    ],
    'refunded' => [
        'enabled' => false,
        'message' => 'Custom message for Refunded.'
    ],
    'failed' => [
        'enabled' => false,
        'message' => 'Custom message for Failed.'
    ],
];

// Load messages from the file or use default
if (file_exists($file_path)) {
    $file_content = file_get_contents($file_path);
    $messages = json_decode($file_content, true) ?: $default_messages;
} else {
    $messages = $default_messages;
    file_put_contents($file_path, json_encode($messages, JSON_PRETTY_PRINT));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['on_save_notifications'])) {
    $updated_messages = [];
    foreach ($messages as $status => $data) {
        $updated_messages[$status] = [
            'enabled' => isset($_POST['notification_messages'][$status]['enabled']),
            'message' => sanitize_text_field($_POST['notification_messages'][$status]['message']),
        ];
    }

    // Save updated data to the file
    file_put_contents($file_path, json_encode($updated_messages, JSON_PRETTY_PRINT));

    // Update $messages with the latest data
    $messages = $updated_messages;

    // Show success message
    echo '<div class="updated notice"><p>Notification settings saved successfully.</p></div>';
}
?>


<div class="wrap">
    <h1>Order Notification Settings</h1>
    <p>Use <code>[order_id]</code> as a placeholder in your message to include the order number.</p>
    <form method="post">
        <table class="form-table">
            <thead>
                <tr>
                    <th scope="col" style="width: 0px; text-align: left;">Order Status</th>
                    <th scope="col">Message</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $status => $data): ?>
                    <tr>
                        <th scope="row" style="width: 0px; text-align: left;">
                            <label for="message-<?php echo esc_attr($status); ?>">
                                <?php echo ucfirst($status); ?>
                            </label>
                        </th>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input 
                                    type="checkbox" 
                                    name="notification_messages[<?php echo esc_attr($status); ?>][enabled]" 
                                    value="1" 
                                    <?php checked(!empty($data['enabled'])); ?> 
                                />
                                <input 
                                    type="text" 
                                    id="message-<?php echo esc_attr($status); ?>" 
                                    name="notification_messages[<?php echo esc_attr($status); ?>][message]" 
                                    value="<?php echo esc_attr($data['message']); ?>" 
                                    style="flex: 1;" 
                                />
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p>
            <input type="submit" name="on_save_notifications" value="Save Changes" class="button button-primary" />
        </p>
    </form>
</div>
