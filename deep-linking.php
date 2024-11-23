<?php
// Define the file path for storing the data.
$data_dir = CUSTOM_PLUGIN_DIR . 'data/';
$data_file = $data_dir . 'deep-linking.json';

// Ensure the directory exists.
if (!is_dir($data_dir)) {
    mkdir($data_dir, 0755, true);
}

// Handle form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_package']) && isset($_POST['sha256_key'])) {
    $app_package = sanitize_text_field($_POST['app_package']);
    $sha256_key = sanitize_text_field($_POST['sha256_key']);

    $data = [
        'app_package' => $app_package,
        'sha256_key' => $sha256_key,
    ];

    file_put_contents($data_file, json_encode($data));

    echo '<div class="updated"><p>Data saved successfully!</p></div>';
}

// Retrieve the saved data if it exists.
$saved_data = [];
if (file_exists($data_file)) {
    $saved_data = json_decode(file_get_contents($data_file), true);
}
?>

<div class="deep-linking-tab">
    <h2>Deep Linking</h2>
    <form method="post">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="app_package">App Package Name</label></th>
                <td>
                    <input type="text" name="app_package" id="app_package" class="regular-text" 
                           value="<?php echo isset($saved_data['app_package']) ? esc_attr($saved_data['app_package']) : ''; ?>" 
                           required />
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="sha256_key">SHA256 Key</label></th>
                <td>
                    <input type="text" name="sha256_key" id="sha256_key" class="regular-text" 
                           value="<?php echo isset($saved_data['sha256_key']) ? esc_attr($saved_data['sha256_key']) : ''; ?>" 
                           required />
                </td>
            </tr>
        </table>
        <p class="submit">
            <button type="submit" class="button button-primary">Save</button>
        </p>
    </form>
</div>
