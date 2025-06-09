<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Add Dashboard Menu Subpage
 */
function nig_add_dashboard_menu() {
    add_submenu_page(
        'nig_settings',
        __( 'Image Generator', 'novassium-image-generator' ),
        __( 'Image Generator', 'novassium-image-generator' ),
        'manage_options',
        'nig_image_generator',
        'nig_image_generator_page'
    );
}
add_action( 'admin_menu', 'nig_add_dashboard_menu' );

/**
 * Add a notice about potentially long processing time
 */
function nig_add_timeout_notice() {
    $screen = get_current_screen();
    if ($screen && strpos($screen->id, 'nig_image_generator') !== false) {
        ?>
        <div class="notice notice-info">
            <p><?php _e('Image generation may take 30-60 seconds per image depending on complexity. The process will continue one image at a time.', 'novassium-image-generator'); ?></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'nig_add_timeout_notice');

/**
 * Render Image Generator Page
 */
function nig_image_generator_page() {
    // Add this inside your function that displays the dashboard page, before you load scripts
    echo '<script>var pluginBaseUrl = "' . esc_js(NIG_PLUGIN_URL) . '";</script>';
    ?>
    <div class="wrap">
        <h1><?php _e( 'Novassium Image Generator Dashboard', 'novassium-image-generator' ); ?></h1>
        <div id="nig-image-generator">
            <form id="nig-image-generation-form" method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="nig_prompt"><?php _e( 'Enter your prompt:', 'novassium-image-generator' ); ?></label>
                        </th>
                        <td>
                            <textarea id="nig_prompt" name="prompt" rows="3" class="large-text" required></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="nig_style_preset"><?php _e( 'Select Style Preset:', 'novassium-image-generator' ); ?></label>
                        </th>
                        <td>
                            <select id="nig_style_preset" name="style_preset" required>
                                <option value="enhance"><?php _e( 'Enhance', 'novassium' ); ?></option>
                                <option value="anime"><?php _e( 'Anime', 'novassium' ); ?></option>
                                <option value="photographic"><?php _e( 'Photographic', 'novassium' ); ?></option>
                                <option value="digital-art"><?php _e( 'Digital Art', 'novassium' ); ?></option>
                                <option value="comic-book"><?php _e( 'Comic Book', 'novassium' ); ?></option>
                                <option value="fantasy-art"><?php _e( 'Fantasy Art', 'novassium' ); ?></option>
                                <option value="line-art"><?php _e( 'Line Art', 'novassium' ); ?></option>
                                <option value="analog-film"><?php _e( 'Analog Film', 'novassium' ); ?></option>
                                <option value="neon-punk"><?php _e( 'Neon Punk', 'novassium' ); ?></option>
                                <option value="isometric"><?php _e( 'Isometric', 'novassium' ); ?></option>
                                <option value="low-poly"><?php _e( 'Low Poly', 'novassium' ); ?></option>
                                <option value="origami"><?php _e( 'Origami', 'novassium' ); ?></option>
                                <option value="modeling-compound"><?php _e( 'Modeling Compound', 'novassium' ); ?></option>
                                <option value="cinematic"><?php _e( 'Cinematic', 'novassium' ); ?></option>
                                <option value="3d-model"><?php _e( '3D Model', 'novassium' ); ?></option>
                                <option value="pixel-art"><?php _e( 'Pixel Art', 'novassium' ); ?></option>
                                <option value="tile-texture"><?php _e( 'Tile Texture', 'novassium' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="nig_aspect_ratio"><?php _e( 'Image Size:', 'novassium-image-generator' ); ?></label>
                        </th>
                        <td>
                            <select id="nig_aspect_ratio" name="aspect_ratio" required>
                            <option value="1:1">Square (1024x1024)</option>
                            <option value="2:3">Portrait (832x1216)</option>
                            <option value="16:9">Landscape (1344x768)</option>
                            </select>

                            <p class="description"><?php _e( 'Select a size that best fits your needs. These ratios are optimized for generative AI compatibility.', 'novassium-image-generator' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="nig_samples"><?php _e( 'Number of Images to Generate:', 'novassium-image-generator' ); ?></label>
                        </th>
                        <td>
                            <select id="nig_samples" name="samples" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                            </select>
                            <p class="description"><?php _e( 'Each image will be processed one at a time to ensure reliability.', 'novassium-image-generator' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="nig_negative_prompt"><?php _e( 'Negative Prompt (optional):', 'novassium-image-generator' ); ?></label>
                        </th>
                        <td>
                            <textarea id="nig_negative_prompt" name="negative_prompt" rows="2" class="large-text" placeholder="Specify what you want to avoid in the image"><?php echo esc_textarea('text, watermark, signature, blurry, low quality, distorted, deformed'); ?></textarea>
                            <p class="description"><?php _e( 'Elements to avoid in the generated image.', 'novassium-image-generator' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="nig_seed"><?php _e( 'Seed (optional):', 'novassium-image-generator' ); ?></label>
                        </th>
                        <td>
                            <input type="number" id="nig_seed" name="seed" min="0" max="4294967294" />
                            <p class="description"><?php _e( 'Leave blank for random results. Use same seed for consistent results.', 'novassium-image-generator' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="nig_output_format"><?php _e( 'Output Format:', 'novassium-image-generator' ); ?></label>
                        </th>
                        <td>
                            <select id="nig_output_format" name="output_format" required>
                                <option value="png" selected>PNG</option>
                                <option value="jpeg">JPEG</option>
                                <option value="webp">WEBP</option>


                            </select>
                        </td>
                    </tr>
                    <!-- Hidden fields for width/height - will be set by JavaScript -->
                    <input type="hidden" id="nig_width" name="width" value="1024" />
                    <input type="hidden" id="nig_height" name="height" value="1024" />
                </table>
                
                <!-- Add nonce field -->
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('nig_nonce'); ?>">
                <input type="hidden" name="action" value="nig_generate_image">

                <button type="submit" class="button button-primary" id="nig-generate-button"><?php _e( 'Generate Images', 'novassium-image-generator' ); ?></button>
                <button type="button" class="button button-secondary" id="nig-open-templates"><?php _e( 'Use Template', 'novassium-image-generator' ); ?></button>
            </form>
            
            <div id="nig-api-status"></div>
            <div id="nig-generation-result" style="margin-top:20px;"></div>
        </div>

        <div class="usage-tracking" style="margin-top:40px;">
            <h2><?php _e( 'Usage Tracking', 'novassium-image-generator' ); ?></h2>
            <p><?php _e( 'Total Credits Used on this Website:', 'novassium-image-generator' ); ?> <span id="nig-total-credits"><?php echo esc_html( get_option( 'nig_usage_credits', 0 ) ); ?></span></p>
            <canvas id="nig-usage-chart" width="400" height="200" data-credits="<?php echo esc_attr( get_option( 'nig_usage_credits', 0 ) ); ?>"></canvas>
        </div>
    </div>

    <style>
    /* Custom styles for download button */
    .nig-view-btn {
        display: block;
        margin: 10px auto;
        padding: 8px 15px;
        background-color: #0073aa;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.3s;
    }
    .nig-view-btn:hover {
        background-color: #005177;
        color: white;
        text-decoration: none;
    }
    .nig-view-btn:active, .nig-view-btn:focus {
        background-color: #003f5e;
        color: white;
        text-decoration: none;
        outline: none;
        box-shadow: none;
    }
    
    /* New responsive image styles */
    .nig-image-item {
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 5px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .nig-image-item img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
    }
    
    .nig-images-grid {
        display: grid;
        grid-gap: 20px;
        margin-bottom: 20px;
    }
    
    @media screen and (min-width: 768px) {
        .nig-images-grid {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }
    }
    
    .nig-image-container {
        overflow: hidden;
        margin-bottom: 10px;
    }
    
    /* Spinner Animation */
    @keyframes nig-spin {
        to { transform: rotate(360deg); }
    }
    .nig-spinner {
        display: inline-block;
        width: 50px;
        height: 50px;
        border: 3px solid rgba(0,0,0,0.1);
        border-radius: 50%;
        border-top-color: #3498db;
        animation: nig-spin 1s ease-in-out infinite;
    }

    /* Button styles */
.nig-image-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
}

.nig-view-btn, .nig-download-btn {
    display: inline-block;
    padding: 8px 12px;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.3s;
    flex: 1;
}

.nig-view-btn {
    background-color: #0073aa;
}

.nig-download-btn {
    background-color: #46b450;
}

.nig-view-btn:hover {
    background-color: #005177;
    color: white;
    text-decoration: none;
}

.nig-download-btn:hover {
    background-color: #389e44;
    color: white;
    text-decoration: none;
}

.nig-view-btn:active, .nig-view-btn:focus, .nig-download-btn:active, .nig-download-btn:focus {
    color: white;
    text-decoration: none;
    outline: none;
    box-shadow: none;
}
    </style>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        console.log('Novassium Image Generator dashboard initialized');
        
        // Handle aspect ratio changes
        $('#nig_aspect_ratio').on('change', function() {
            var ratio = $(this).val();
            var width, height;
            
            switch(ratio) {
                case '1:1':
                    width = 1024; height = 1024;
                    break;
                case '4:3':
                    width = 1024; height = 768;
                    break;
                case '4:5':
                    width = 896; height = 1088;
                    break;
                case '16:9':
                    width = 1024; height = 576;
                    break;
                case '9:16':
                    width = 576; height = 1024;
                    break;
                case '2:3':
                    width = 683; height = 1024;
                    break;
                case '3:2':
                    width = 1024; height = 683;
                    break;
                default:
                    width = 1024; height = 1024;
            }
            
            $('#nig_width').val(width);
            $('#nig_height').val(height);
        });
        
        // Trigger aspect ratio change on load to set initial width/height
        $('#nig_aspect_ratio').trigger('change');
        
        // Initialize usage chart
        if(typeof Chart !== 'undefined') {
            var ctx = document.getElementById('nig-usage-chart').getContext('2d');
            var usageData = {
                labels: ['<?php esc_html_e( "Credits Used", "novassium-image-generator" ); ?>'],
                datasets: [{
                    label: '<?php esc_html_e( "Credits", "novassium-image-generator" ); ?>',
                    data: [<?php echo esc_js( get_option( 'nig_usage_credits', 0 ) ); ?>],
                    backgroundColor: ['rgba(75, 192, 192, 0.2)'],
                    borderColor: ['rgba(75, 192, 192, 1)'],
                    borderWidth: 1
                }]
            };
            
            var nigUsageChart = new Chart(ctx, {
                type: 'bar',
                data: usageData,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }
                }
            });
            
            window.updateUsageChart = function(newCredits) {
                nigUsageChart.data.datasets[0].data[0] = newCredits;
                nigUsageChart.update();
            };
        }
        
        // The rest of the JavaScript functionality will be handled by direct-api.js
    });
    </script>
    <?php
}

/**
 * Enqueue Admin Scripts and Styles
 */
function nig_enqueue_admin_scripts( $hook ) {
    // Check if we're on the Image Generator, Template Library, or Batch Processing pages
    if ( strpos( $hook, 'nig_image_generator' ) === false &&
         strpos( $hook, 'nig_template_library' ) === false &&
         strpos( $hook, 'nig_batch_processing' ) === false ) {
        return;
    }

    // Enqueue CSS
    wp_enqueue_style( 'nig-admin-css', NIG_PLUGIN_URL . 'assets/css/admin.css', array(), '1.0.1' );

    // Enqueue Chart.js if needed
    wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.7.0', true );

    // Enqueue jQuery
    wp_enqueue_script( 'jquery' );

    // Then enqueue the direct API script
    wp_enqueue_script('nig-direct-api', NIG_PLUGIN_URL . 'assets/js/direct-api.js', array('jquery'), '1.0.2', true);
    
    // Pass AJAX URL and nonce to the script
    wp_localize_script('nig-direct-api', 'nig_ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('nig_nonce')
    ));
}
add_action( 'admin_enqueue_scripts', 'nig_enqueue_admin_scripts' );

/**
 * Handle AJAX Request for Image Generation
 * Note: This is kept for compatibility, but we'll be primarily using direct-api.php
 */
function nig_handle_image_generation_ajax() {
    // Increase PHP execution time for this request
    set_time_limit(300); // 5 minutes
    
    check_ajax_referer( 'nig_generate_image_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'novassium-image-generator' ) ) );
    }

    // Sanitize and prepare arguments
    $args = array(
        'prompt'          => isset($_POST['prompt']) ? sanitize_text_field( $_POST['prompt'] ) : '',
        'style_preset'    => isset($_POST['style_preset']) ? sanitize_text_field( $_POST['style_preset'] ) : 'enhance',
        'samples'         => isset($_POST['samples']) ? intval( $_POST['samples'] ) : 1,
        'negative_prompt' => isset($_POST['negative_prompt']) ? sanitize_textarea_field( $_POST['negative_prompt'] ) : '',
        'seed'            => isset($_POST['seed']) && !empty($_POST['seed']) ? intval( $_POST['seed'] ) : 0,
        'output_format'   => isset($_POST['output_format']) ? sanitize_text_field( $_POST['output_format'] ) : 'png',
        'width'           => isset($_POST['width']) ? intval( $_POST['width'] ) : 1024,
        'height'          => isset($_POST['height']) ? intval( $_POST['height'] ) : 1024,
    );

    // Generate the image using the Novassium API
    $response = nig_generate_image( $args );

    if ( isset( $response['success'] ) && $response['success'] ) {
        $images = $response['images'];
        $remaining_credits = isset( $response['remaining_credits'] ) ? intval( $response['remaining_credits'] ) : 0;

        // Save Images to Media Library
        $saved_images = array();
        foreach ( $images as $image_url ) {
            $image_data = file_get_contents( $image_url );
            if ( ! $image_data ) {
                continue;
            }
            $filename = basename( $image_url );
            $saved = nig_save_image_to_media_library( $image_data, $filename );
            if ( ! is_wp_error( $saved ) ) {
                $saved_images[] = $saved;
            }
        }

        // Update Usage Credits
        $current_credits = get_option( 'nig_usage_credits', 0 );
        $current_credits += $args['samples']; // Assuming each sample costs 1 credit
        update_option( 'nig_usage_credits', $current_credits );

        wp_send_json_success( array(
            'images'            => $saved_images,
            'remaining_credits' => $remaining_credits,
        ) );
    } else {
        $message = isset( $response['message'] ) ? $response['message'] : __( 'An error occurred.', 'novassium-image-generator' );
        wp_send_json_error( array( 'message' => $message ) );
    }
}
add_action( 'wp_ajax_nig_generate_image', 'nig_handle_image_generation_ajax' );
