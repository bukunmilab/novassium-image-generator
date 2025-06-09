<?php
/**
 * Direct API Integration for Novassium Image Generator
 * This file handles direct API calls, bypassing WordPress AJAX system
 */

// Load WordPress
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

// Security checks
if (!is_user_logged_in() || !current_user_can('upload_files')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Verify nonce
if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nig_nonce')) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Security verification failed']);
    exit;
}

// Process the request
$action = isset($_POST['nig_action']) ? $_POST['nig_action'] : '';

switch ($action) {
    case 'generate_image':
        handle_generate_image();
        break;
        
    case 'check_api':
        handle_check_api();
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

/**
 * Handle image generation
 */
function handle_generate_image() {
    // Get parameters from request
    $prompt = isset($_POST['prompt']) ? sanitize_text_field($_POST['prompt']) : '';
    $style_preset = isset($_POST['style_preset']) ? sanitize_text_field($_POST['style_preset']) : 'photographic';
    $aspect_ratio = isset($_POST['aspect_ratio']) ? sanitize_text_field($_POST['aspect_ratio']) : '1:1';
    $samples = 1; // Always generate 1 image at a time as per API documentation
    $negative_prompt = isset($_POST['negative_prompt']) ? sanitize_textarea_field($_POST['negative_prompt']) : '';
    $seed = isset($_POST['seed']) && intval($_POST['seed']) > 0 ? intval($_POST['seed']) : null;
    $output_format = isset($_POST['output_format']) ? sanitize_text_field($_POST['output_format']) : 'jpeg';
    $width = isset($_POST['width']) ? intval($_POST['width']) : 1024;
    $height = isset($_POST['height']) ? intval($_POST['height']) : 1024;
    
    if (empty($prompt)) {
        echo json_encode(['success' => false, 'message' => 'Prompt is required']);
        exit;
    }
    
    // Get API key
    $api_key = get_option('nig_api_key', '');
    
    if (empty($api_key)) {
        echo json_encode(['success' => false, 'message' => 'API key is not configured']);
        exit;
    }
    
    // Prepare API request
    $api_url = 'https://proxyle.com/api/novassium/v1/generate';
    
    $request_data = [
        'prompt' => $prompt,
        'style_preset' => $style_preset,
        'aspect_ratio' => $aspect_ratio,
        'samples' => $samples,
        'output_format' => $output_format,
        'width' => $width,
        'height' => $height
    ];
    
    // Add optional parameters
    if (!empty($negative_prompt)) {
        $request_data['negative_prompt'] = $negative_prompt;
    }
    
    if ($seed !== null) {
        $request_data['seed'] = $seed;
    }
    
    // Make API request with the new headers according to the documentation
    $response = wp_remote_post(
        $api_url,
        [
            'method' => 'POST',
            'timeout' => 90,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-Key' => $api_key
            ],
            'body' => json_encode($request_data),
        ]
    );
    
    // Handle API response
    if (is_wp_error($response)) {
        echo json_encode([
            'success' => false, 
            'message' => 'API request failed: ' . $response->get_error_message()
        ]);
        exit;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    $result = json_decode($response_body, true);
    
    // Debug - log the response
    error_log('Novassium API Response: ' . print_r($result, true));
    
    if ($response_code !== 200) {
        $error_message = isset($result['message']) ? $result['message'] : 'Unknown API error';
        echo json_encode([
            'success' => false, 
            'message' => 'API error: ' . $error_message, 
            'code' => $response_code,
            'response' => $result
        ]);
        exit;
    }
    
    // Check if the response has the expected format
    if (!isset($result['success']) || !$result['success'] || !isset($result['images']) || !is_array($result['images'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid API response format', 
            'response' => $result
        ]);
        exit;
    }
    
    // Extract image URLs
    $image_urls = $result['images'];
    $processed_images = [];
    
    // Process each image URL
    foreach ($image_urls as $image_url) {
        // Download and save to media library
        $image_response = wp_remote_get($image_url);
        
        if (!is_wp_error($image_response) && wp_remote_retrieve_response_code($image_response) === 200) {
            $image_content = wp_remote_retrieve_body($image_response);
            
            // Save to media library
            $filename = 'nig-' . sanitize_title($prompt) . '-' . uniqid() . '.' . $output_format;
            $attachment_id = save_image_to_media_library($image_content, $filename, $prompt);
            
            if ($attachment_id) {
                $processed_images[] = wp_get_attachment_url($attachment_id);
            } else {
                // If saving to media library fails, use original URL
                $processed_images[] = $image_url;
            }
        } else {
            // If download fails, use original URL
            $processed_images[] = $image_url;
        }
    }
    
    // Update usage stats
    $current_credits = get_option('nig_usage_credits', 0);
    update_option('nig_usage_credits', $current_credits + count($processed_images));
    
    // Return results
    echo json_encode([
        'success' => true,
        'images' => $processed_images,
        'remaining_credits' => isset($result['remaining_credits']) ? $result['remaining_credits'] : null
    ]);
    exit;
}

/**
 * Check API connectivity
 */
function handle_check_api() {
    // Get API key
    $api_key = get_option('nig_api_key', '');
    
    if (empty($api_key)) {
        echo json_encode([
            'success' => false,
            'message' => 'API key is not configured',
            'config_status' => 'missing_key'
        ]);
        exit;
    }
    
    // Use a simple request to test the API
    $test_url = 'https://proxyle.com/api/novassium/v1/generate';
    $test_data = [
        'prompt' => 'Test connection',
        'style_preset' => 'enhance',
        'aspect_ratio' => '1:1',
        'samples' => 1,
        'output_format' => 'jpeg',
    ];
    
    // Just do a simplified minimal request to check auth
    $response = wp_remote_post(
        $test_url, 
        [
            'method' => 'POST',
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-Key' => $api_key
            ],
            'body' => json_encode($test_data),
        ]
    );
    
    if (is_wp_error($response)) {
        echo json_encode([
            'success' => false,
            'message' => 'API connection failed: ' . $response->get_error_message(),
            'config_status' => 'connection_failed'
        ]);
        exit;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    
    // We don't need a successful image generation, just to check auth works
    if ($response_code === 401 || $response_code === 403) {
        echo json_encode([
            'success' => false,
            'message' => 'API authentication failed. Please check your API key.',
            'config_status' => 'auth_failed',
            'http_code' => $response_code
        ]);
        exit;
    }
    
    if ($response_code >= 500) {
        echo json_encode([
            'success' => false,
            'message' => 'API server error. Please try again later.',
            'config_status' => 'server_error',
            'http_code' => $response_code
        ]);
        exit;
    }
    
    // If we get here, the connection is probably working
    echo json_encode([
        'success' => true,
        'message' => 'API connection successful',
        'config_status' => 'success'
    ]);
    exit;
}

/**
 * Save image to media library
 */
function save_image_to_media_library($image_data, $filename, $title) {
    $upload_dir = wp_upload_dir();
    
    if (!is_dir($upload_dir['path'])) {
        wp_mkdir_p($upload_dir['path']);
    }
    
    $file_path = $upload_dir['path'] . '/' . sanitize_file_name($filename);
    
    if (!file_put_contents($file_path, $image_data)) {
        return false;
    }
    
    $filetype = wp_check_filetype($filename, null);
    
    $attachment = [
        'guid' => $upload_dir['url'] . '/' . basename($filename),
        'post_mime_type' => $filetype['type'],
        'post_title' => sanitize_text_field($title),
        'post_content' => '',
        'post_status' => 'inherit'
    ];
    
    $attachment_id = wp_insert_attachment($attachment, $file_path, 0);
    
    if (is_wp_error($attachment_id)) {
        return false;
    }
    
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
    wp_update_attachment_metadata($attachment_id, $attachment_data);
    
    return $attachment_id;
}
