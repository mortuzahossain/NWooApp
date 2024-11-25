<?php
function curl($url, $req, $header = '')
{
	$post_params = json_encode($req);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	//curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$respData = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return $respData;
}

function getProjectId($serviceAccountPath)
{
    try {
    	$serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
    	return $serviceAccount['project_id'];
	} catch (Exception $e) {
        return "-1";
    }
}

function getOAuthToken($serviceAccountPath, $scopes)
{
    try {
    	$authUrl = 'https://oauth2.googleapis.com/token';
    	$jwtHeader = base64_encode(json_encode(array('alg' => 'RS256', 'typ' => 'JWT')));
    	$serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
    	$jwtClaim = base64_encode(json_encode(array(
    		'iss' => $serviceAccount['client_email'],
    		'scope' => $scopes,
    		'aud' => 'https://oauth2.googleapis.com/token',
    		'exp' => time() + 3600,
    		'iat' => time(),
    	)));

    	$privateKey = $serviceAccount['private_key'];
    	$signature = '';
    	openssl_sign($jwtHeader . '.' . $jwtClaim, $signature, $privateKey, 'SHA256');
    	$jwtSignature = base64_encode($signature);

    	$jwt = $jwtHeader . '.' . $jwtClaim . '.' . $jwtSignature;

    	$response = file_get_contents($authUrl, false, stream_context_create(array(
    		'http' => array(
    			'header' => 'Content-Type: application/x-www-form-urlencoded',
    			'method' => 'POST',
    			'content' => http_build_query(array(
    				'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    				'assertion' => $jwt,
    			)),
    		),
    	)));
    	return  json_decode($response, true)['access_token'];
	} catch (Exception $e) {
        return "-1";
    }
}



function sendFirebaseNotification($body)
{

    $file_path = CUSTOM_PLUGIN_DIR . 'uploads/uploaded.json';
    $scopes = 'https://www.googleapis.com/auth/firebase.messaging';
    $token = getOAuthToken($file_path, $scopes);

    if ($token == "-1") {
        $_SESSION['notification_message'] = "Failed to send notification. Problem in token generation. Check and verify your setting.";
        $_SESSION['notification_status'] = "error";
        return false;
    }

    $header = array(
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
    );


    $projectId = getProjectId($file_path);
    $API = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";

    // Sending notification via cURL
    $resA = curl($API, $body, $header);
logMe($body);
logMe($resA);
    if ($resA) {
        $obj = json_decode($resA);
        if (isset($obj->name)) {
            return true;
        } else {
            $_SESSION['notification_message'] = "Failed to send notification. Something went wrong!";
            $_SESSION['notification_status'] = "error";
            return false;
        }
    }

    $_SESSION['notification_message'] = "Failed to send notification. Unknown error occurred.";
    $_SESSION['notification_status'] = "error";
    return false;
}



function logMe($body) {
    $log_file = CUSTOM_PLUGIN_DIR . '/uploads/fcm_push_notification_log.txt';

    // Check if the custom plugin directory is writable
    if (!is_writable(CUSTOM_PLUGIN_DIR)) {
        // Use the WP upload directory if the custom plugin directory isn't writable
        $upload_dir = wp_upload_dir();
        $log_file = $upload_dir['basedir'] . '/uploads/fcm_push_notification_log.txt';
    }

    // Prepare the log data
    $log_data = [
        'Date' => current_time('Y-m-d H:i:s'),
        'Message' => is_array($body) ? json_encode($body, JSON_PRETTY_PRINT) : $body,
    ];

    // Create the log string
    $log_string = "==== Push Notification Log ====\n";
    foreach ($log_data as $key => $value) {
        $log_string .= $key . ': ' . $value . "\n";
    }
    $log_string .= "\n==============================\n\n";

    // Write the log string to the file
    $result = file_put_contents($log_file, $log_string, FILE_APPEND);

    // Optionally return success or failure
    return $result !== false;
}
