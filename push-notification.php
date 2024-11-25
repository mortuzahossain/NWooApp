<?php
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
    $notification_url = sanitize_text_field($_POST['notification_url']);
    $notification_image = sanitize_text_field($_POST['notification_image']);
    $notification_message = sanitize_textarea_field($_POST['notification_message']);
    $file_path = CUSTOM_PLUGIN_DIR . 'uploads/uploaded.json';
    if (!file_exists($file_path)){
        $_SESSION['notification_message'] = "Please complete the setting first and upload service_account.json file";
        $_SESSION['notification_status'] = "error";
    }
    else if (!empty($notification_topic) && !empty($notification_title) && !empty($notification_message)) {

        $body = array(
     			'message' => array(
    				'topic' => $notification_topic,
    				'notification' => array(
                        'title' => $notification_title,
                        'body' => $notification_message
    				),
    				'data' => array(
                        'title' => $notification_title,
                        'body' => $notification_message,
                        'url' => isset($notification_url) && !empty($notification_url) ? $notification_url : null,
                        'image_url' => isset($notification_image) && !empty($notification_image) ? $notification_image : null,
    				),
     			),
  		);

        if(sendFirebaseNotification($body)){
			$_SESSION['notification_message'] = "Notification sent successfully to topic:" . esc_html($notification_topic);
            $_SESSION['notification_status'] = "success";
        }
    } else {
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
                            <select name="notification_topic" id="notification_topic" class="regular-text"  >
                                <option value="">-- Select Topic --</option>
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
                            <input type="text" name="notification_title" id="notification_title" class="regular-text"  placeholder="Enter notification title"  required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="notification_message">Notification Message</label>
                        </th>
                        <td>
                            <textarea name="notification_message" id="notification_message" class="large-text" rows="5" placeholder="Enter notification message"  required></textarea>
                        </td>
                    </tr>
                     <tr>
                        <th scope="row">
                            <label for="notification_url">URL (Optional)</label>
                        </th>
                        <td>
                            <input type="url" name="notification_url" id="notification_url" class="regular-text"  placeholder="Enter url that you want to navigate after click"  >
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="notification_image">Notification Image</label>
                        </th>
                        <td>
                            <button type="button" id="upload_image_button" class="button">Select Image</button>
                            <input type="hidden" name="notification_image" id="notification_image" value="">
                            <img id="image_preview" src="" style="max-width: 100px; display: none; margin-top: 10px;" alt="Image Preview">
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
                                    <a href="?page=nwooapp&tab=push-notification&remove_topic=<?php echo urlencode($topic); ?>" class="button button-link-delete" style="color: red;">Remove</a>
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
