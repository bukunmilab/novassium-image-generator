<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Add Admin Menu
 */
function nig_add_admin_menu() {
    add_menu_page(
        __( 'Novassium Image Generator', 'novassium-image-generator' ),
        __( 'NovassiumGen', 'novassium-image-generator' ),
        'manage_options',
        'nig_settings',
        'nig_settings_page',
        'dashicons-format-image',
        56
    );
}
add_action( 'admin_menu', 'nig_add_admin_menu' );

/**
 * Register Settings
 */
function nig_register_settings() {
    register_setting( 'nig_settings_group', 'nig_api_key', 'sanitize_text_field' );

    add_settings_section(
        'nig_api_section',
        __( 'API Configuration', 'novassium-image-generator' ),
        'nig_api_section_callback',
        'nig_settings_group'
    );

    add_settings_field(
        'nig_api_key',
        __( 'Novassium API Key', 'novassium-image-generator' ),
        'nig_api_key_render',
        'nig_settings_group',
        'nig_api_section'
    );
}
add_action( 'admin_init', 'nig_register_settings' );

/**
 * Settings Section Callback
 */
function nig_api_section_callback() {
    echo '<p>' . __('Enter your Novassium API Key to enable image generation.', 'novassium-image-generator') . '</p>';
    echo '<p>' . __('Your API key can be found in your Novassium dashboard. The API key should be entered in the format provided by Proxyle/Novassium.', 'novassium-image-generator') . '</p>';
    echo '<p>' . __('After saving your API key, use the "Test API Connection" button to verify it works correctly.', 'novassium-image-generator') . '</p>';
}

/**
 * API Key Field Render
 */
function nig_api_key_render() {
    $api_key = get_option( 'nig_api_key' );
    echo '<input type="text" name="nig_api_key" value="' . esc_attr( $api_key ) . '" size="50" />';
    echo '<p class="description">' . __('Enter the API key exactly as provided, including any hyphens or special characters.', 'novassium-image-generator') . '</p>';
}

/**
 * Test API Connection
 */
function nig_test_api_connection() {
    $api_key = get_option('nig_api_key');
    
    if (empty($api_key)) {
        return array(
            'success' => false,
            'message' => __('API Key is not set', 'novassium-image-generator')
        );
    }
    
    // A minimal test request to validate connection and API key
    $test_data = array(
        'prompt' => 'API connection test',
        'style_preset' => 'photographic',
        'aspect_ratio' => '1:1',
        'samples' => 1,
        'output_format' => 'jpeg'
    );
    
    $response = wp_remote_post('https://proxyle.com/api/novassium/v1/generate', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-API-Key' => $api_key
        ),
        'body' => json_encode($test_data),
        'timeout' => 15,
        'sslverify' => true,
    ));
    
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => $response->get_error_message()
        );
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    $response_data = json_decode($response_body, true);
    
    // Check authentication-specific error codes
    if ($response_code === 401 || $response_code === 403) {
        return array(
            'success' => false,
            'message' => __('API Key is invalid or unauthorized', 'novassium-image-generator')
        );
    }
    
    // Check for other error codes
    if ($response_code >= 400) {
        $error_message = isset($response_data['message']) ? $response_data['message'] : __('Unknown error', 'novassium-image-generator');
        return array(
            'success' => false,
            'message' => sprintf(__('API error: %s (Code: %d)', 'novassium-image-generator'), $error_message, $response_code)
        );
    }
    
    // If we get here, the connection is successful
    $credits = isset($response_data['remaining_credits']) ? $response_data['remaining_credits'] : __('unknown', 'novassium-image-generator');
    
    return array(
        'success' => true,
        'message' => sprintf(__('Connection successful! Available credits: %s', 'novassium-image-generator'), $credits)
    );
}

/**
 * AJAX handler for testing API connection
 */
function nig_test_connection_ajax() {
    check_ajax_referer('nig_test_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Permission denied', 'novassium-image-generator'));
    }
    
    $connection_test = nig_test_api_connection();
    
    if ($connection_test['success']) {
        wp_send_json_success($connection_test);
    } else {
        wp_send_json_error($connection_test);
    }
}
add_action('wp_ajax_nig_test_connection', 'nig_test_connection_ajax');

/**
 * Settings Page Content
 */
function nig_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'Novassium Image Generator Settings', 'novassium-image-generator' ); ?></h1>
        
        <!-- API Connection Status Banner -->
        <div id="nig-connection-status" style="margin-bottom: 20px;"></div>
        
        <!-- API Settings Section -->
        <div class="nig-api-settings card" style="max-width: 800px; padding: 20px; margin-bottom: 20px;">
            <h2><?php _e('API Configuration', 'novassium-image-generator'); ?></h2>
            <p><?php _e('Configure your Novassium API credentials below to start generating images.', 'novassium-image-generator'); ?></p>
            
            <form action="options.php" method="post">
                <?php
                settings_fields( 'nig_settings_group' );
                do_settings_sections( 'nig_settings_group' );
                submit_button(__('Save API Key', 'novassium-image-generator'));
                ?>
            </form>
            
            <div class="nig-api-test" style="margin-top: 20px;">
                <button id="nig_test_connection" class="button button-secondary"><?php _e('Test API Connection', 'novassium-image-generator'); ?></button>
                <span id="nig_test_result" style="margin-left: 10px; display: inline-block;"></span>
            </div>
            
            <!-- API Information Box -->
            <div class="nig-api-info" style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #007cba; border-radius: 4px;">
                <h3 style="margin-top: 0;"><?php _e('About the Novassium API', 'novassium-image-generator'); ?></h3>
                <p><?php _e('The Novassium API allows you to generate high-quality images using AI. Each image generation consumes API credits from your account.', 'novassium-image-generator'); ?></p>
                <p><strong><?php _e('API Endpoints:', 'novassium-image-generator'); ?></strong></p>
                <ul style="list-style-type: disc; margin-left: 20px;">
                    <li><?php _e('Image Generation: https://proxyle.com/api/novassium/v1/generate', 'novassium-image-generator'); ?></li>
                </ul>
                <p><strong><?php _e('Authentication:', 'novassium-image-generator'); ?></strong> <?php _e('The API uses an API key that should be included in the X-API-Key header.', 'novassium-image-generator'); ?></p>
                <p><strong><?php _e('Shortcode:', 'novassium-image-generator'); ?></strong> <?php _e(' [nig_image_generator] is the shortcode— a smart way you may want to resell image generation on your website.', 'novassium-image-generator'); ?></p>
            </div>
        </div>
        
        <!-- Quick Links Section -->
        <div class="nig-quick-links card" style="max-width: 800px; padding: 20px; margin-bottom: 20px;">
            <h2><?php _e('Quick Links', 'novassium-image-generator'); ?></h2>
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=nig_image_generator')); ?>" class="button button-primary"><?php _e('Go to Image Generator Dashboard', 'novassium-image-generator'); ?></a>
                <?php if (function_exists('nig_batch_processing_page')): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=nig_batch_processing')); ?>" class="button"><?php _e('Batch Process Images', 'novassium-image-generator'); ?></a>
                <?php endif; ?>
                <?php if (function_exists('nig_template_library_page')): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=nig_template_library')); ?>" class="button"><?php _e('Prompt Template Library', 'novassium-image-generator'); ?></a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Getting Started Guide -->
        <div class="nig-getting-started card" style="max-width: 800px; padding: 20px;">
            <h2><?php _e('Getting Started Guide', 'novassium-image-generator'); ?></h2>
            <ol style="margin-left: 20px; line-height: 1.6;">
                <li><?php _e('<strong>Enter your API Key</strong> in the field above and click "Save API Key".', 'novassium-image-generator'); ?></li>
                <li><?php _e('<strong>Test the connection</strong> using the "Test API Connection" button.', 'novassium-image-generator'); ?></li>
                <li><?php _e('<strong>Navigate to the Image Generator</strong> using the link above or from the left menu.', 'novassium-image-generator'); ?></li>
                <li><?php _e('<strong>Enter a prompt</strong> describing the image you want to create.', 'novassium-image-generator'); ?></li>
                <li><?php _e('<strong>Select your options</strong> like style preset and image size.', 'novassium-image-generator'); ?></li>
                <li><?php _e('<strong>Generate your image</strong> and use it on your site!', 'novassium-image-generator'); ?></li>
            </ol>
            <p><?php _e('For more detailed instructions, please refer to the <a href="https://proxyle.com/api-documentation/" target="_blank">Novassium API Documentation</a>.', 'novassium-image-generator'); ?></p>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($){
        // Check API connection on page load
        function checkInitialApiStatus() {
            var apiKey = '<?php echo esc_js(get_option('nig_api_key')); ?>';
            
            if (apiKey) {
                $('#nig-connection-status').html('<div class="notice notice-info is-dismissible"><p><?php _e('Checking API connection status...', 'novassium-image-generator'); ?></p></div>');
                
                var data = {
                    action: 'nig_test_connection',
                    nonce: '<?php echo wp_create_nonce('nig_test_nonce'); ?>'
                };
                
                $.post(ajaxurl, data, function(response) {
                    if (response.success) {
                        $('#nig-connection-status').html('<div class="notice notice-success is-dismissible"><p><strong><?php _e('API Status:', 'novassium-image-generator'); ?></strong> ' + response.data.message + '</p></div>');
                    } else {
                        $('#nig-connection-status').html('<div class="notice notice-error is-dismissible"><p><strong><?php _e('API Status:', 'novassium-image-generator'); ?></strong> ' + response.data.message + '</p><p><?php _e('Please check your API key and try again.', 'novassium-image-generator'); ?></p></div>');
                    }
                }).fail(function() {
                    $('#nig-connection-status').html('<div class="notice notice-error is-dismissible"><p><strong><?php _e('API Status:', 'novassium-image-generator'); ?></strong> <?php _e('Could not check API connection.', 'novassium-image-generator'); ?></p></div>');
                });
            } else {
                $('#nig-connection-status').html('<div class="notice notice-warning is-dismissible"><p><?php _e('API Key is not configured. Please enter your Novassium API Key to start using the plugin.', 'novassium-image-generator'); ?></p></div>');
            }
        }
        
        // Run the initial check
        checkInitialApiStatus();
        
        // Test API connection button
        $('#nig_test_connection').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $result = $('#nig_test_result');
            
            $button.prop('disabled', true);
            $result.html('<span style="color:#0073aa;"><?php _e('Testing connection...', 'novassium-image-generator'); ?></span>');
            
            var data = {
                action: 'nig_test_connection',
                nonce: '<?php echo wp_create_nonce('nig_test_nonce'); ?>'
            };
            
            $.post(ajaxurl, data, function(response) {
                $button.prop('disabled', false);
                
                if (response.success) {
                    $result.html('<span style="color: green;">' + response.data.message + '</span>');
                    $('#nig-connection-status').html('<div class="notice notice-success is-dismissible"><p><strong><?php _e('API Status:', 'novassium-image-generator'); ?></strong> ' + response.data.message + '</p></div>');
                } else {
                    $result.html('<span style="color: red;">' + response.data.message + '</span>');
                    $('#nig-connection-status').html('<div class="notice notice-error is-dismissible"><p><strong><?php _e('API Status:', 'novassium-image-generator'); ?></strong> ' + response.data.message + '</p></div>');
                }
            }).fail(function() {
                $button.prop('disabled', false);
                $result.html('<span style="color: red;"><?php _e('Connection test failed. Please check your server configuration.', 'novassium-image-generator'); ?></span>');
            });
        });
        
        // Make notices dismissible
        $(document).on('click', '.notice-dismiss', function() {
            $(this).parent().fadeOut(300, function() { $(this).remove(); });
        });
    });
    </script>
    <?php
}
