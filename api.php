<?php
// File: api.php

// Register the API endpoint
function nwooapp_register_api_endpoint() {
    register_rest_route('nwooapp/v1', '/topics-and-links', [
        'methods' => 'GET',
        'callback' => 'nwooapp_get_topics_and_links',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'nwooapp_register_api_endpoint');

// Callback function to fetch topics and external links
function nwooapp_get_topics_and_links(WP_REST_Request $request) {

    $data_dir = CUSTOM_PLUGIN_DIR . 'data/';
    $data_file = $data_dir . 'external-links.json';
    $saved_data = [];
    if (file_exists($data_file)) {
        $saved_data = json_decode(file_get_contents($data_file), true);
    }

    $topics = get_option('push_notification_topics', []);
    $saved_links = $saved_data;
    
    // Return the data in the response
    return new WP_REST_Response([
        'topics' => $topics,
        'external_links' => $saved_links,
    ], 200);
}
?>
