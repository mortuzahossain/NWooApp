<?php
// Handle file upload.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['json_file'])) {
    $upload_dir = CUSTOM_PLUGIN_DIR . 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $uploaded_file = $_FILES['json_file'];
    $file_path = $upload_dir . 'uploaded.json';

    if ($uploaded_file['type'] === 'application/json') {
        move_uploaded_file($uploaded_file['tmp_name'], $file_path);
        echo '<div class="updated"><p>JSON file uploaded successfully!</p></div>';
    } else {
        echo '<div class="error"><p>Please upload a valid JSON file.</p></div>';
    }
}

// Read and display the uploaded JSON file's content.
$file_path = CUSTOM_PLUGIN_DIR . 'uploads/uploaded.json';
$json_content = '';
if (file_exists($file_path)) {
    $json_content = file_get_contents($file_path);
}
?>

<div class="settings-tab">
    <h2>Settings</h2>
    <form method="post" enctype="multipart/form-data">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="json_file">Upload JSON File</label></th>
                <td>
                    <input type="file" name="json_file" id="json_file" accept=".json" />
                    <button type="submit" class="button button-primary">Upload</button>
                </td>
            </tr>
        </table>
    </form>

    <?php if (!empty($json_content)) : ?>
        <h3>Uploaded JSON Content</h3>
        <pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ddd;"><?php echo esc_html($json_content); ?></pre>
    <?php endif; ?>
</div>
