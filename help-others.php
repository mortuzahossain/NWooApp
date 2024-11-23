<?php
    $site_url = get_site_url();
    $api_url = $site_url . '/wp-json/nwooapp/v1/topics-and-links';
?>


<h2>Help & Others</h2>
<div class="wrap">
        <p>Use the following API to access the topics and external links data.</p>
        <h3>API URL - (GET)</h3>
        <pre><code><?php echo esc_html($api_url); ?></code></pre>
        
        <h3>Sample Response</h3>
        <pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ddd;">{
    "topics": [
        "topic1",
        "topic2",
        "topic3"
    ],
    "external_links": [
        "https://link1.com",
        "https://link2.com"
    ]
}</pre>
    </div>