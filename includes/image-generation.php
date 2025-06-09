<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Generate Image via Novassium API with improved timeout handling
 *
 * @param array $args Parameters for image generation.
 * @return array API response.
 */
function nig_generate_image( $args ) {
    $api_key = get_option( 'nig_api_key' );

    if ( empty( $api_key ) ) {
        return array(
            'success' => false,
            'message' => __( 'Novassium API Key is not set.', 'novassium-image-generator' )
        );
    }

    $endpoint = 'https://proxyle.com/api/novassium/v1/generate';
    
    // Add required parameters if not already set
    if (!isset($args['style_preset'])) {
        $args['style_preset'] = 'photographic'; // Default preset - can be changed
    }
    
    if (!isset($args['output_format'])) {
        $args['output_format'] = 'png'; // Default format
    }
    
    // Calculate aspect_ratio from width and height if not provided
    if (!isset($args['aspect_ratio']) && isset($args['width']) && isset($args['height'])) {
        $width = $args['width'];
        $height = $args['height'];
        
        // Common aspect ratios
        if ($width == $height) {
            $args['aspect_ratio'] = '1:1';
        } elseif ($width == 1024 && $height == 512) {
            $args['aspect_ratio'] = '2:1';
        } elseif ($width == 512 && $height == 1024) {
            $args['aspect_ratio'] = '1:2';
        } else {
            // Calculate the ratio and find the simplest form
            $gcd = function($a, $b) use (&$gcd) {
                return $b ? $gcd($b, $a % $b) : $a;
            };
            
            $divisor = $gcd($width, $height);
            $args['aspect_ratio'] = ($width / $divisor) . ':' . ($height / $divisor);
        }
    }
    
    // Debugging info
    error_log('Preparing API request to Novassium with args: ' . json_encode($args));
    
    // Set samples parameter correctly
    if (isset($args['samples']) && $args['samples'] > 1) {
        // Try all these parameter names to increase compatibility
        $args['n'] = $args['samples'];
        $args['batch_size'] = $args['samples'];
        $args['num_images'] = $args['samples'];
        $args['num_outputs'] = $args['samples'];
    }

    $headers = array(
        'Content-Type' => 'application/json',
        'X-API-Key' => $api_key,
        'Accept' => 'application/json',
    );

    // Log the request for debugging
    error_log('Novassium API Request: ' . json_encode($args));

    // Increase timeout to 60 seconds initially
    $response = wp_remote_post( $endpoint, array(
        'headers' => $headers,
        'body' => json_encode( $args ),
        'timeout' => 60, 
        'blocking' => true,
        'sslverify' => true,
    ));

    // Check for timeout or other error
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_code = $response->get_error_code();
        
        // If it's a timeout error, try again with a longer timeout
        if ($error_code === 'http_request_failed' && strpos($error_message, 'timed out') !== false) {
            error_log("API request timed out. Retrying with a longer timeout.");
            
            // Retry with a longer timeout
            $response = wp_remote_post($endpoint, array(
                'headers' => $headers,
                'body' => json_encode($args),
                'timeout' => 180, // 3 minutes
                'blocking' => true,
                'sslverify' => true,
            ));
            
            // If still an error, return the error
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $error_code = $response->get_error_code();
                
                error_log("Novassium API Error (retry): $error_code - $error_message");
                
                return array(
                    'success' => false,
                    'message' => sprintf(
                        __('API request failed after retry: %s. Try generating fewer images or a simpler prompt.', 'novassium-image-generator'),
                        $error_message
                    ),
                    'error_code' => $error_code
                );
            }
        } else {
            // Return original error
            error_log("Novassium API Error: $error_code - $error_message");
            
            return array(
                'success' => false,
                'message' => sprintf(
                    __('API request failed: %s.', 'novassium-image-generator'),
                    $error_message
                ),
                'error_code' => $error_code
            );
        }
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    // Log response information
    error_log("Novassium API Response Code: $response_code");
    error_log("Novassium API Response Body (first 1000 chars): " . substr($body, 0, 1000));
    
    // Parse the JSON response
    $data = json_decode($body, true);
    
    // Check if JSON is valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Invalid JSON response from API: " . json_last_error_msg());
        return array(
            'success' => false,
            'message' => __('Invalid response format from API.', 'novassium-image-generator'),
            'raw_response' => substr($body, 0, 1000)
        );
    }

    // Handle error responses with more detail
    if ($response_code >= 400 || !isset($data['success']) || $data['success'] === false) {
        $message = isset($data['message']) ? $data['message'] : 'Unknown API error';
        
        // Special handling for the "Invalid parameter(s): samples" error
        if (strpos($message, 'Invalid parameter') !== false || strpos($message, 'Missing parameter') !== false) {
            $message .= ' - This might be due to an incorrect parameter name or value limit. Please try generating one image at a time.';
            
            if (isset($data['details'])) {
                $message .= ' Details: ' . (is_array($data['details']) ? json_encode($data['details']) : $data['details']);
            }
            
            if (isset($data['data']) && isset($data['data']['params']) && is_array($data['data']['params'])) {
                $message .= ' Missing parameters: ' . implode(', ', $data['data']['params']);
            }
        }
        
        return array(
            'success' => false,
            'message' => $message,
            'response_code' => $response_code,
            'debug_data' => $data
        );
    }

    // Add additional debugging
    error_log("Response format received: " . json_encode(array_keys($data)));
    
    // Ensure the response has a consistent format for images
    if (!isset($data['images']) && isset($data['data'])) {
        $data['images'] = $data['data'];
    }
       
    // If no images array but we have data, handle that
    if ((!isset($data['images']) || !is_array($data['images'])) && isset($data['data']) && is_array($data['data'])) {
        // We'll use the data field as images
        error_log("Using 'data' field for images: " . json_encode(array_slice($data['data'], 0, 1)));
        
        return array(
            'success' => true,
            'images' => $data['data'],
            'raw_response' => $data
        );
    }
    
    // Verify the response has the expected format but allow empty images array
    if (!isset($data['images']) && !isset($data['data']) && !isset($data['output']) && !isset($data['results'])) {
        error_log("API response did not contain image data in any expected format. Response: " . json_encode(array_slice($data, 0, 3)));
        
        return array(
            'success' => false,
            'message' => __('API response did not contain image data in the expected format.', 'novassium-image-generator'),
            'debug_data' => $data
        );
    }
    
    return array(
        'success' => true,
        'images' => isset($data['images']) ? $data['images'] : [],
        'raw_response' => $data
    );
}

/**
 * Save Generated Images to Media Library
 *
 * @param string $image_data Binary image data.
 * @param string $filename Desired filename.
 * @return mixed Attachment URL or WP_Error.
 */
function nig_save_image_to_media_library( $image_data, $filename ) {
    $upload_dir = wp_upload_dir();

    if ( ! is_writable( $upload_dir['path'] ) ) {
        return new WP_Error( 'upload_error', __( 'Upload directory is not writable.', 'novassium-image-generator' ) );
    }

    $file_path = $upload_dir['path'] . '/' . sanitize_file_name( $filename );

    if ( ! file_put_contents( $file_path, $image_data ) ) {
        return new WP_Error( 'file_put_error', __( 'Failed to write image data to file.', 'novassium-image-generator' ) );
    }

    $filetype = wp_check_filetype( $filename, null );

    $attachment = array(
        'guid'           => $upload_dir['url'] . '/' . basename( $filename ),
        'post_mime_type' => $filetype['type'],
        'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
        'post_content'   => '',
        'post_status'    => 'inherit',
    );

    $attach_id = wp_insert_attachment( $attachment, $file_path );

    if ( is_wp_error( $attach_id ) ) {
        return $attach_id;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
    wp_update_attachment_metadata( $attach_id, $attach_data );

    return wp_get_attachment_url( $attach_id );
}
