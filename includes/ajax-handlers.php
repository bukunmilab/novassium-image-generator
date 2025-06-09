<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Register AJAX handlers
 */
function nig_register_ajax_handlers() {
    add_action('wp_ajax_nig_generate_image_ajax', 'nig_generate_image_ajax_handler');
    add_action('wp_ajax_nig_test_connection', 'nig_test_connection_ajax_handler');
}
add_action('init', 'nig_register_ajax_handlers');

/**
 * AJAX handler for image generation
 */
function nig_generate_image_ajax_handler() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nig_generate_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    // Check if user has permission
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    
    // Get parameters from request
    $prompt = isset($_POST['prompt']) ? sanitize_textarea_field($_POST['prompt']) : '';
    $samples = isset($_POST['samples']) ? intval($_POST['samples']) : 1;
    $size = isset($_POST['size']) ? sanitize_text_field($_POST['size']) : '512x512';
    $style_preset = isset($_POST['style_preset']) ? sanitize_text_field($_POST['style_preset']) : 'photographic';
    
    // Validate required parameters
    if (empty($prompt)) {
        wp_send_json_error('Prompt is required');
    }
    
    // Parse the size parameter
    $size_parts = explode('x', $size);
    $width = isset($size_parts[0]) ? intval($size_parts[0]) : 512;
    $height = isset($size_parts[1]) ? intval($size_parts[1]) : 512;
    
    // Prepare parameters for API call
    $args = array(
        'prompt' => $prompt,
        'samples' => $samples,
        'width' => $width,
        'height' => $height,
        'style_preset' => $style_preset,
        'output_format' => 'png',
        // aspect_ratio will be calculated from width/height in the nig_generate_image function
    );
    
    // Log the request for debugging
    error_log('Starting image generation with args: ' . json_encode($args));
    
    // Call the image generation function
    $result = nig_generate_image($args);
    
    // Log the result for debugging
    error_log('Image generation result: ' . json_encode(array_slice($result, 0, 3))); // Limit log size
    
    if (isset($result['success']) && $result['success']) {
        // Process the successful result
        $processed_images = array();
        
        // Handle image saving to media library if needed
        if (isset($result['images']) && is_array($result['images'])) {
            error_log('Found images array in result: ' . count($result['images']));
            
            foreach ($result['images'] as $index => $image_data) {
                // For direct image URLs in the main array
                if (is_string($image_data)) {
                    $processed_images[] = array(
                        'id' => $index,
                        'url' => $image_data,
                    );
                    continue;
                }
                
                // For base64 encoded images (OpenAI format)
                if (isset($image_data['b64_json']) && !empty($image_data['b64_json'])) {
                    $binary_data = base64_decode($image_data['b64_json']);
                    $filename = 'novassium-' . time() . '-' . ($index + 1) . '.png';
                    $image_url = nig_save_image_to_media_library($binary_data, $filename);
                    
                    if (!is_wp_error($image_url)) {
                        $processed_images[] = array(
                            'id' => $index,
                            'url' => $image_url,
                        );
                    }
                }
                // For base64 encoded images (alternative key)
                elseif (isset($image_data['base64']) && !empty($image_data['base64'])) {
                    $binary_data = base64_decode($image_data['base64']);
                    $filename = 'novassium-' . time() . '-' . ($index + 1) . '.png';
                    $image_url = nig_save_image_to_media_library($binary_data, $filename);
                    
                    if (!is_wp_error($image_url)) {
                        $processed_images[] = array(
                            'id' => $index,
                            'url' => $image_url,
                        );
                    }
                }
                // For image URLs
                elseif (isset($image_data['url']) && !empty($image_data['url'])) {
                    $processed_images[] = array(
                        'id' => $index,
                        'url' => $image_data['url'],
                    );
                }
                // For image URLs with different key names
                elseif (isset($image_data['image_url']) && !empty($image_data['image_url'])) {
                    $processed_images[] = array(
                        'id' => $index,
                        'url' => $image_data['image_url'],
                    );
                }
            }
        }
        
        // If no images were processed but API reported success, check raw response
        if (empty($processed_images) && isset($result['raw_response'])) {
            error_log('No images processed from standard format. Checking alternative formats in raw_response');
            
            // Log the full raw response structure to understand its format
            error_log('Raw response structure: ' . json_encode(array_keys($result['raw_response'])));
            
            // Try to extract images from different response formats
            
            // Format 1: data array with URLs
            if (isset($result['raw_response']['data']) && is_array($result['raw_response']['data'])) {
                error_log('Found data array in raw response: ' . count($result['raw_response']['data']));
                
                foreach ($result['raw_response']['data'] as $index => $item) {
                    // Check if item is a string (direct URL)
                    if (is_string($item)) {
                        $processed_images[] = array(
                            'id' => $index,
                            'url' => $item,
                        );
                    }
                    // Check for url in the item
                    elseif (is_array($item) && isset($item['url'])) {
                        $processed_images[] = array(
                            'id' => $index,
                            'url' => $item['url'],
                        );
                    }
                }
            }
            
            // Format 2: direct output field
            if (empty($processed_images) && isset($result['raw_response']['output']) && is_array($result['raw_response']['output'])) {
                error_log('Found output array in raw response');
                
                foreach ($result['raw_response']['output'] as $index => $output_url) {
                    if (is_string($output_url)) {
                        $processed_images[] = array(
                            'id' => $index,
                            'url' => $output_url,
                        );
                    }
                }
            }
            
            // Format 3: output.data field
            if (empty($processed_images) && isset($result['raw_response']['output']['data']) && is_array($result['raw_response']['output']['data'])) {
                error_log('Found output.data array in raw response');
                
                foreach ($result['raw_response']['output']['data'] as $index => $item) {
                    if (is_string($item)) {
                        $processed_images[] = array(
                            'id' => $index,
                            'url' => $item,
                        );
                    } elseif (is_array($item) && isset($item['url'])) {
                        $processed_images[] = array(
                            'id' => $index,
                            'url' => $item['url'],
                        );
                    }
                }
            }
            
            // Format 4: direct results array
            if (empty($processed_images) && isset($result['raw_response']['results']) && is_array($result['raw_response']['results'])) {
                error_log('Found results array in raw response');
                
                foreach ($result['raw_response']['results'] as $index => $item) {
                    if (isset($item['url'])) {
                        $processed_images[] = array(
                            'id' => $index,
                            'url' => $item['url'],
                        );
                    } elseif (isset($item['image_url'])) {
                        $processed_images[] = array(
                            'id' => $index,
                            'url' => $item['image_url'],
                        );
                    }
                }
            }
            
            // Format 5: generations (like in OpenAI API)
            if (empty($processed_images) && isset($result['raw_response']['generations']) && is_array($result['raw_response']['generations'])) {
                error_log('Found generations array in raw response');
                
                foreach ($result['raw_response']['generations'] as $index => $item) {
                    if (isset($item['url'])) {
                        $processed_images[] = array(
                            'id' => $index,
                            'url' => $item['url'],
                        );
                    }
                }
            }
        }
        
        // If we still have no images, return an error with details about the response format
        if (empty($processed_images)) {
            $response_info = '';
            if (isset($result['raw_response'])) {
                $response_info = ' Response format: ' . json_encode(array_keys($result['raw_response']));
                if (isset($result['raw_response']['data'])) {
                    $response_info .= ', Data format: ' . json_encode(array_slice($result['raw_response']['data'], 0, 1));
                }
            }
            
            wp_send_json_error('API returned success but no images were found in the response.' . $response_info);
        } else {
            wp_send_json_success(array(
                'message' => 'Images generated successfully',
                'images' => $processed_images,
                'count' => count($processed_images)
            ));
        }
    } else {
        // Handle error
        $error_message = isset($result['message']) ? $result['message'] : 'Image generation failed';
        
        // Add debugging info if available
        if (isset($result['debug_data'])) {
            $error_message .= ' Debug data: ' . json_encode($result['debug_data']);
        }
        
        wp_send_json_error($error_message);
    }
}

/**
 * AJAX handler for API connection test
 */
function nig_test_connection_ajax_handler() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nig_test_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    
    $result = nig_test_api_connection();
    
    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        $error_message = isset($result['message']) ? $result['message'] : 'API connection failed';
        
        // Add response content if available
        if (isset($result['response'])) {
            $error_message .= ' Response: ' . $result['response'];
        }
        
        wp_send_json_error(array(
            'message' => $error_message,
            'detail' => isset($result['response']) ? $result['response'] : null
        ));
    }
}
