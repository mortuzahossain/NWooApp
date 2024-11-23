<?php
// Handle topic addition
if (isset($_POST['add_topic']) && !empty($_POST['topic_name'])) {
    $new_topic = sanitize_text_field($_POST['topic_name']);
    $saved_topics = get_option('push_notification_topics', []);
    if (!in_array($new_topic, $saved_topics)) {
        $saved_topics[] = $new_topic;
        update_option('push_notification_topics', $saved_topics);
    } else {
        $error_message = "Topic already exists!";
    }
}

// Handle topic removal
if (isset($_GET['remove_topic'])) {
    $topic_to_remove = sanitize_text_field($_GET['remove_topic']);
    $saved_topics = get_option('push_notification_topics', []);
    $saved_topics = array_filter($saved_topics, function ($topic) use ($topic_to_remove) {
        return $topic !== $topic_to_remove;
    });
    update_option('push_notification_topics', $saved_topics);
}

// Handle notification sending
if (isset($_POST['send_notification'])) {
    $notification_topic = sanitize_text_field($_POST['notification_topic']);
    $notification_title = sanitize_text_field($_POST['notification_title']);
    $notification_message = sanitize_textarea_field($_POST['notification_message']);
    // Add API call or functionality to send notifications here
     // Mock sending notification (replace with actual logic or API call)
    if (!empty($notification_topic) && !empty($notification_title) && !empty($notification_message)) {
        // Simulate successful notification
        $_SESSION['notification_message'] = "Notification sent successfully to topic: " . esc_html($notification_topic) . ".";
        $_SESSION['notification_status'] = "success";
    } else {
        // Simulate failure
        $_SESSION['notification_message'] = "Failed to send notification. Please check your input.";
        $_SESSION['notification_status'] = "error";
    }

}

// Fetch saved topics
$saved_topics = get_option('push_notification_topics', []);
?>

<div class="wrap">
    <h1>Push Notification Management</h1>
   <?php if (isset($_SESSION['notification_message'])) : ?>
        <div class="<?php echo esc_attr($_SESSION['notification_status'] === 'success' ? 'notice notice-success' : 'notice notice-error'); ?> is-dismissible">
            <p><?php echo esc_html($_SESSION['notification_message']); ?></p>
        </div>
        <?php
        // Clear the session message after displaying it
        unset($_SESSION['notification_message']);
        unset($_SESSION['notification_status']);
        ?>
    <?php endif; ?>
    <div style="display: flex; gap: 20px; align-items: flex-start;">
        <!-- Left Column: Send Notification -->
        <div style="flex: 2;">
            <h2>Send Notification</h2>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="notification_topic">Select Topic</label>
                        </th>
                        <td>
                            <select name="notification_topic" id="notification_topic" class="regular-text" required>
                                <option value="">-- Select Topic --</option>
                                <option value="/all">All</option>
                                <?php foreach ($saved_topics as $topic) : ?>
                                    <option value="<?php echo esc_attr($topic); ?>"><?php echo esc_html($topic); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="notification_title">Notification Title</label>
                        </th>
                        <td>
                            <input type="text" name="notification_title" id="notification_title" class="regular-text"  placeholder="Enter notification title" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="notification_message">Notification Message</label>
                        </th>
                        <td>
                            <textarea name="notification_message" id="notification_message" class="large-text" rows="5" placeholder="Enter notification message" required></textarea>
                        </td>
                    </tr>
                </table>
                <button type="submit" name="send_notification" class="button button-primary">Send Notification</button>
            </form>
        </div>

        <!-- Right Column: Topic Management -->
        <div style="flex: 1; border-left: 1px solid #ccc; padding-left: 20px;">
            <h2>Topic Management</h2>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <td>
                            <input type="text" name="topic_name" id="topic_name" class="regular-text" placeholder="Enter topic name" required>
                        </td>
                        <td><button type="submit" name="add_topic" class="button button-primary">Add Topic</button></td>
                    </tr>
                </table>
                <?php if (!empty($error_message)) : ?>
                    <p style="color: red;"><?php echo esc_html($error_message); ?></p>
                <?php endif; ?>
            </form>

            <?php if (!empty($saved_topics)) : ?>
                <h3>Existing Topics</h3>
                <table class="wp-list-table widefat fixed striped table-view-list">
                    <thead>
                        <tr>
                            <th>Topic Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($saved_topics as $topic) : ?>
                            <tr>
                                <td><?php echo esc_html($topic); ?></td>
                                <td>
                                    <a href="?page=custom-plugin-tabs&tab=push-notification&remove_topic=<?php echo urlencode($topic); ?>" class="button button-link-delete" style="color: red;">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No topics added yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
