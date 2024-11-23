<?php
// Define the file path for storing the links.
$data_dir = CUSTOM_PLUGIN_DIR . 'data/';
$data_file = $data_dir . 'external-links.json';

// Ensure the directory exists.
if (!is_dir($data_dir)) {
    mkdir($data_dir, 0755, true);
}

// Handle form submission to add a new link.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_link'])) {
    $new_link = sanitize_text_field($_POST['external_link']);
    $saved_links = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];
    $saved_links[] = $new_link;

    file_put_contents($data_file, json_encode($saved_links));
    echo '<div class="updated"><p>Link added successfully!</p></div>';
}

// Handle request to remove a link.
if (isset($_GET['remove_link'])) {
    $index_to_remove = intval($_GET['remove_link']);
    $saved_links = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];

    if (isset($saved_links[$index_to_remove])) {
        unset($saved_links[$index_to_remove]);
        $saved_links = array_values($saved_links); // Reindex the array.
        file_put_contents($data_file, json_encode($saved_links));
        echo '<div class="updated"><p>Link removed successfully!</p></div>';
    }
}

// Retrieve the saved links.
$saved_links = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];
?>
<style>
.wp-list-table th.actions-column,
.wp-list-table td.actions-column {
    width: 15%;
    text-align: right;
}

 </style>

<div class="manage-external-links-tab">
    <h2>Manage External Links</h2>
    <div style="display: flex; gap: 20px;">
        <!-- Left Section: Add and Manage Links -->
        <div style="flex: 2;">
            <h3>Add a New Link</h3>
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="external_link">External Link</label></th>
                        <td>
                            <input type="text" name="external_link" id="external_link" class="regular-text" placeholder="https://example.com" required />
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" name="add_link" class="button button-primary">Add Link</button>
                </p>
            </form>

           <?php if (!empty($saved_links)) : ?>
            <h3>Saved Links</h3>
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th scope="col">External Link</th>
                        <th scope="col" style="width: 15%; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($saved_links as $index => $link) : ?>
                        <tr>
                            <td><a href="<?php echo esc_url($link); ?>" target="_blank"><?php echo esc_html($link); ?></a></td>
                            <td style="text-align: right;">
                                <a href="?page=custom-plugin-tabs&tab=manage-external-link&remove_link=<?php echo $index; ?>" class="button button-link-delete" style="color: red;">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No links added yet.</p>
        <?php endif; ?>

        </div>

        <!-- Right Section: Notes -->
        <div style="flex: 1; border: 1px solid #ccc; padding: 15px; background: #f9f9f9;">
            <h3>Notes</h3>
            <p><strong>URL Samples:</strong></p>
            <ul>
                <li><code>https://example.com</code> - Standard URL.</li>
                <li><code>https://example.com/page</code> - Specific page on the domain.</li>
                <li><code>https://sub.example.com</code> - Subdomain URL.</li>
            </ul>
            <p>Please ensure the URL starts with <code>http://</code> or <code>https://</code>.</p>
        </div>
    </div>
</div>
