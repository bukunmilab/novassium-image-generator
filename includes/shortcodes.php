<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Register Shortcode
 */
function nig_register_shortcodes() {
    add_shortcode( 'nig_image_generator', 'nig_image_generator_shortcode' );
}
add_action( 'init', 'nig_register_shortcodes' );

/**
 * Shortcode Handler - Direct API Version
 * 
 * Usage: [nig_image_generator]
 */
function nig_image_generator_shortcode($atts) {
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to generate images.</p>';
    }

    $atts = shortcode_atts(array(
        'prompt' => '', // Optional: pre-filled prompt
    ), $atts, 'nig_image_generator');
    
    // Get the API key from options
    $api_key = get_option('nig_api_key', '');
    
    // Define the API endpoint
    $api_endpoint = 'https://proxyle.com/api/novassium/v1/generate';
    
    ob_start();
    ?>
    <div id="nig-container" style="max-width: 800px; margin: 0 auto; padding: 20px; background: #fff; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h3>Novassium Image Generator</h3>
        
        <div style="background-color: #f0f8ff; border-left: 4px solid #00a0d2; padding: 15px; margin-bottom: 20px;">
            <p>Create unique AI-generated images based on your text descriptions.</p>
            <p><small>Images may take 30-60 seconds to generate depending on complexity.</small></p>
        </div>

        <form id="nig-generator-form" method="post">
            <?php wp_nonce_field('nig_generate_image', 'nig_nonce'); ?>
            <input type="hidden" name="nig_generate_image" value="1">
            
            <div style="margin-bottom: 20px;">
                <label for="nig_prompt" style="display: block; margin-bottom: 8px; font-weight: 500;">Enter your prompt:</label>
                <textarea 
                    id="nig_prompt" 
                    name="nig_prompt" 
                    rows="3" 
                    style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ddd;"
                    placeholder="Describe the image you want to create..."
                    required
                ><?php echo esc_textarea($atts['prompt']); ?></textarea>
            </div>
            
            <div style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 15px;">
                <div style="flex: 1 1 200px;">
                    <label for="nig_style_preset" style="display: block; margin-bottom: 8px; font-weight: 500;">Style:</label>
                    <select 
                        id="nig_style_preset" 
                        name="nig_style_preset" 
                        style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ddd;"
                        required
                    >
                        <option value="enhance">Enhance</option>
                        <option value="anime">Anime</option>
                        <option value="photographic">Photographic</option>
                        <option value="digital-art">Digital Art</option>
                        <option value="comic-book">Comic Book</option>
                        <option value="fantasy-art">Fantasy Art</option>
                        <option value="line-art">Line Art</option>
                        <option value="analog-film">Analog Film</option>
                        <option value="neon-punk">Neon Punk</option>
                        <option value="isometric">Isometric</option>
                        <option value="low-poly">Low Poly</option>
                        <option value="origami">Origami</option>
                        <option value="modeling-compound">Modeling Compound</option>
                        <option value="cinematic">Cinematic</option>
                        <option value="3d-model">3D Model</option>
                        <option value="pixel-art">Pixel Art</option>
                        <option value="tile-texture">Tile Texture</option>
                    </select>
                </div>
                
                <div style="flex: 1 1 200px;">
                    <label for="nig_samples" style="display: block; margin-bottom: 8px; font-weight: 500;">Number of Images:</label>
                    <select 
                        id="nig_samples" 
                        name="nig_samples" 
                        style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ddd;"
                        required
                    >
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
                
                <div style="flex: 1 1 200px;">
                    <label for="nig_aspect_ratio" style="display: block; margin-bottom: 8px; font-weight: 500;">Image Shape:</label>
                    <select 
                        id="nig_aspect_ratio" 
                        name="nig_aspect_ratio" 
                        style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ddd;"
                        required
                    >
                        <option value="1:1">Square (1:1)</option>
                        <option value="2:3">Portrait (2:3)</option>
                        <option value="16:9">Landscape (16:9)</option>
                    </select>
                </div>
            </div>
            
            <div style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 15px;">
                <div style="flex: 1 1 200px;">
                    <label for="nig_output_format" style="display: block; margin-bottom: 8px; font-weight: 500;">File Format:</label>
                    <select 
                        id="nig_output_format" 
                        name="nig_output_format" 
                        style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ddd;"
                        required
                    >
                        <option value="png">PNG</option>
                        <option value="jpeg">JPEG</option>
                        <option value="webp">WEBP</option>
                    </select>
                </div>
                
                <div style="flex: 1 1 200px;">
                    <label for="nig_seed" style="display: block; margin-bottom: 8px; font-weight: 500;">Seed (optional):</label>
                    <input 
                        type="number" 
                        id="nig_seed" 
                        name="nig_seed" 
                        min="0" 
                        max="4294967294" 
                        style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ddd;"
                        placeholder="For reproducibility"
                    >
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="nig_negative_prompt" style="display: block; margin-bottom: 8px; font-weight: 500;">Negative prompt (things to avoid):</label>
                <textarea 
                    id="nig_negative_prompt" 
                    name="nig_negative_prompt" 
                    rows="2" 
                    style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ddd;"
                    placeholder="Elements you don't want in the image..."
                ></textarea>
            </div>
            
            <div style="margin-top: 25px; text-align: left;">
                <input 
                    type="submit" 
                    id="nig-submit-btn"
                    value="Generate Images" 
                    style="background-color: #0073aa; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: 500;"
                >
            </div>
        </form>

        <?php
        // Handle form submission
        if (isset($_POST['nig_generate_image']) && wp_verify_nonce($_POST['nig_nonce'], 'nig_generate_image')) {
            // Validate API key
            if (empty($api_key)) {
                echo '<div style="margin-top: 20px; padding: 15px; background-color: #fde8e8; border-left: 4px solid #e53e3e; color: #c53030;">';
                echo '<p><strong>Error:</strong> API key is not configured. Please contact the administrator.</p>';
                echo '</div>';
            } else {
                // Get form data
                $prompt = sanitize_textarea_field($_POST['nig_prompt']);
                $negative_prompt = isset($_POST['nig_negative_prompt']) ? sanitize_textarea_field($_POST['nig_negative_prompt']) : '';
                $style_preset = sanitize_text_field($_POST['nig_style_preset']);
                $samples = isset($_POST['nig_samples']) ? intval($_POST['nig_samples']) : 1;
                $aspect_ratio = sanitize_text_field($_POST['nig_aspect_ratio']);
                $output_format = sanitize_text_field($_POST['nig_output_format']);
                $seed = isset($_POST['nig_seed']) && !empty($_POST['nig_seed']) ? intval($_POST['nig_seed']) : null;
                
                // Validate prompt
                if (empty($prompt)) {
                    echo '<div style="margin-top: 20px; padding: 15px; background-color: #fde8e8; border-left: 4px solid #e53e3e; color: #c53030;">';
                    echo '<p><strong>Error:</strong> Please enter a prompt for the image generation.</p>';
                    echo '</div>';
                } else {
                    // Make sure samples is within valid range (1-7)
                    $samples = max(1, min(7, $samples));
                    
                    // Note about multiple samples (per API documentation)
                    if ($samples > 1) {
                        echo '<div style="margin-top: 20px; padding: 15px; background-color: #fef9e6; border-left: 4px solid #f0ad4e;">';
                        echo '<p><strong>Note:</strong> You\'ve requested ' . $samples . ' images. According to the API documentation, only one image can be generated per request. We will make multiple requests for you.</p>';
                        echo '</div>';
                    }
                    
                    // Prepare the API request base data
                    $base_request_data = array(
                        'prompt' => $prompt,
                        'style_preset' => $style_preset,
                        'aspect_ratio' => $aspect_ratio,
                        'samples' => 1, // Using 1 as required by the API
                        'output_format' => $output_format,
                    );
                    
                    // Add optional parameters if provided
                    if (!empty($negative_prompt)) {
                        $base_request_data['negative_prompt'] = $negative_prompt;
                    }
                    
                    if (!is_null($seed)) {
                        $base_request_data['seed'] = $seed;
                    }
                    
                    // Set default width and height based on aspect ratio if not provided
                    $dimensions = get_dimensions_from_aspect_ratio($aspect_ratio);
                    if ($dimensions) {
                        $base_request_data['width'] = $dimensions['width'];
                        $base_request_data['height'] = $dimensions['height'];
                    }
                    
                    // Array to store all generated image URLs
                    $all_images = array();
                    $remaining_credits = null;
                    $errors = array();
                    
                    // Make API requests based on number of samples
                    for ($i = 0; $i < $samples; $i++) {
                        // Clone the request data for this iteration
                        $request_data = $base_request_data;
                        
                        // If seed is provided, increment it for each request to get different images
                        if (!is_null($seed)) {
                            $request_data['seed'] = $seed + $i;
                        }
                        
                        // Make the API request
                        $response = wp_remote_post($api_endpoint, array(
                            'method' => 'POST',
                            'headers' => array(
                                'Content-Type' => 'application/json',
                                'X-API-Key' => $api_key,
                            ),
                            'body' => json_encode($request_data),
                            'timeout' => 60, // Longer timeout for image generation
                        ));
                        
                        // Handle the response
                        if (is_wp_error($response)) {
                            // WP Error
                            $errors[] = $response->get_error_message();
                        } else {
                            $response_code = wp_remote_retrieve_response_code($response);
                            $response_body = json_decode(wp_remote_retrieve_body($response), true);
                            
                            if ($response_code === 200 && isset($response_body['success']) && $response_body['success'] === true && !empty($response_body['images'])) {
                                // Success - Add the image URL to our collection
                                $all_images = array_merge($all_images, $response_body['images']);
                                
                                // Update remaining credits
                                if (isset($response_body['remaining_credits'])) {
                                    $remaining_credits = $response_body['remaining_credits'];
                                }
                            } else {
                                // API Error
                                $error_message = isset($response_body['message']) ? $response_body['message'] : "Unknown error (HTTP Status: $response_code)";
                                $errors[] = $error_message;
                                
                                // Break the loop on error to prevent more failed requests
                                break;
                            }
                        }
                    }
                    
                    // Display any errors that occurred
                    if (!empty($errors)) {
                        echo '<div style="margin-top: 20px; padding: 15px; background-color: #fde8e8; border-left: 4px solid #e53e3e; color: #c53030;">';
                        echo '<p><strong>Errors occurred:</strong></p>';
                        echo '<ul style="margin-left: 20px; list-style-type: disc;">';
                        foreach ($errors as $error) {
                            echo '<li>' . esc_html($error) . '</li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                    }
                    
                    // Display generated images
                    if (!empty($all_images)) {
                        echo '<div style="margin-top: 30px;">';
                        echo '<h3>Generated Images</h3>';
                        
                        if (!is_null($remaining_credits)) {
                            echo '<p><strong>Remaining credits:</strong> ' . esc_html($remaining_credits) . '</p>';
                        }
                        
                        echo '<div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 15px;">';
                        
                        foreach ($all_images as $index => $image_url) {
                            echo '<div style="flex: 0 0 calc(50% - 10px); border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">';
                            echo '<div style="position: relative; overflow: hidden;">';
                            echo '<img src="' . esc_url($image_url) . '" alt="Generated image" style="display: block; width: 100%; height: auto;">';
                            echo '</div>';
                            echo '<div style="padding: 10px; display: flex; gap: 10px;">';
                            echo '<a href="' . esc_url($image_url) . '" target="_blank" style="flex: 1; display: block; text-align: center; padding: 8px 0; background-color: #f0f0f0; color: #333; text-decoration: none; border-radius: 4px;">View Full Size</a>';
                            echo '<a href="' . esc_url($image_url) . '" download="novassium-image-' . ($index + 1) . '.' . $output_format . '" style="flex: 1; display: block; text-align: center; padding: 8px 0; background-color: #0073aa; color: white; text-decoration: none; border-radius: 4px;">Download</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                        echo '</div>';
                        
                        // Display prompt information
                        echo '<div style="margin-top: 20px; padding: 15px; background-color: #f9f9f9; border-radius: 4px;">';
                        echo '<h4>Image Details</h4>';
                        echo '<p><strong>Prompt:</strong> ' . esc_html($prompt) . '</p>';
                        if (!empty($negative_prompt)) {
                            echo '<p><strong>Negative prompt:</strong> ' . esc_html($negative_prompt) . '</p>';
                        }
                        echo '<p><strong>Style:</strong> ' . esc_html($style_preset) . '</p>';
                        echo '<p><strong>Aspect ratio:</strong> ' . esc_html($aspect_ratio) . '</p>';
                        if (!is_null($seed)) {
                            echo '<p><strong>Starting seed:</strong> ' . esc_html($seed) . '</p>';
                        }
                        echo '</div>';
                    } elseif (empty($errors)) {
                        // No images and no errors (rare case)
                        echo '<div style="margin-top: 20px; padding: 15px; background-color: #fde8e8; border-left: 4px solid #e53e3e; color: #c53030;">';
                        echo '<p><strong>Error:</strong> No images were generated, but no specific errors were reported.</p>';
                        echo '</div>';
                    }
                }
            }
        }
        ?>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('nig-generator-form');
    var submitBtn = document.getElementById('nig-submit-btn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            var prompt = document.getElementById('nig_prompt').value.trim();
            
            if (!prompt) {
                alert('Please enter a prompt for image generation.');
                return false;
            }
            
            // Disable button and show loading state
            submitBtn.value = 'Generating...';
            submitBtn.disabled = true;
            
            // Store the original page title
            var originalTitle = document.title;
            
            // Change the page title to indicate processing
            document.title = 'Generating Images...';
            
            // Optionally: restore the original title after a long timeout 
            // (in case the form submission fails)
            setTimeout(function() {
                if (document.title === 'Generating Images...') {
                    document.title = originalTitle;
                }
            }, 120000); // 2 minutes timeout
            
            // Continue with form submission
            return true;
        });
        
        // Optionally: Restore the title when the page is focused
        // (useful when user switches to another tab during generation)
        window.addEventListener('focus', function() {
            if (document.title === 'Generating Images...') {
                // Only reset if it's still showing our generation message
                // and the form has been submitted (button is disabled)
                if (submitBtn && submitBtn.disabled) {
                    document.title = 'Image Generation in Progress...';
                }
            }
        });
    }
});
</script>

    <?php
    
    return ob_get_clean();
}

/**
 * Helper function to get image dimensions from aspect ratio
 */
function get_dimensions_from_aspect_ratio($aspect_ratio) {
    $dimensions = array(
        '16:9' => array('width' => 1344, 'height' => 768),
        '1:1' => array('width' => 1024, 'height' => 1024),
        '21:9' => array('width' => 1536, 'height' => 640),
        '2:3' => array('width' => 832, 'height' => 1216),
        '3:2' => array('width' => 1216, 'height' => 832),
        '4:5' => array('width' => 896, 'height' => 1088),
        '5:4' => array('width' => 1088, 'height' => 896),
        '9:16' => array('width' => 768, 'height' => 1344),
        '9:21' => array('width' => 640, 'height' => 1536),
    );
    
    return isset($dimensions[$aspect_ratio]) ? $dimensions[$aspect_ratio] : null;
}
