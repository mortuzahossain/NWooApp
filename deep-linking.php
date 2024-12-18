<?php
// Define the file path for storing the data.
$well_known_dir = ABSPATH . '.well-known';
if (!file_exists($well_known_dir)) {
    mkdir($well_known_dir, 0755, true);
}

$assetlinks_file_path = $well_known_dir . '/assetlinks.json';
// Handle form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_package']) && isset($_POST['sha256_key'])) {
    $package_name = sanitize_text_field($_POST['app_package']);
    $sha256_key = sanitize_text_field($_POST['sha256_key']);


    $assetlinks_content = json_encode([
        [
            'relation' => ['delegate_permission/common.handle_all_urls', 'delegate_permission/common.get_login_creds'],
            'target' => [
                'namespace' => 'android_app',
                'package_name' => $package_name,
                'sha256_cert_fingerprints' => [$sha256_key]
            ]
        ]
    ]);

    // Save the file in .well-known directory

    file_put_contents($assetlinks_file_path, $assetlinks_content);
    echo '<div class="updated"><p>Data saved successfully!</p></div>';
}

// Retrieve the saved data if it exists.
$saved_data = [];
if (file_exists($assetlinks_file_path)) {
    $saved_data = json_decode(file_get_contents($assetlinks_file_path), true);
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
                           value="<?php echo isset($saved_data[0]['target']) ? esc_attr($saved_data[0]['target']['package_name']) : ''; ?>"
                           required />
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="sha256_key">SHA256 Key</label></th>
                <td>
                    <input type="text" name="sha256_key" id="sha256_key" class="regular-text"
                           value="<?php echo isset($saved_data[0]['target']) ? esc_attr($saved_data[0]['target']['sha256_cert_fingerprints'][0]) : ''; ?>"
                           required />
                </td>
            </tr>
        </table>
        <p class="submit">
            <button type="submit" class="button button-primary">Save</button>
        </p>
    </form>
</div>
