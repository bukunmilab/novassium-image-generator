<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Add Template Library Section to Dashboard
 */
function nig_add_template_library() {
    add_submenu_page(
        'nig_settings',
        __( 'Template Library', 'novassium-image-generator' ),
        __( 'Template Library', 'novassium-image-generator' ),
        'manage_options',
        'nig_template_library',
        'nig_template_library_page'
    );
}
add_action( 'admin_menu', 'nig_add_template_library' );

/**
 * Render Template Library Page
 */
function nig_template_library_page() {
    // Define templates
    $templates = array(
        'blog_post' => array(
            'title' => __( 'Blog Post Image', 'novassium-image-generator' ),
            'prompt' => __( 'A captivating image representing the blog [POST_TITLE]', 'novassium-image-generator' ),
        ),
        'ecommerce_product' => array(
            'title' => __( 'E-commerce Product Image', 'novassium-image-generator' ),
            'prompt' => __( 'A high-quality image of [PRODUCT_NAME] suitable for online store display', 'novassium-image-generator' ),
        ),
        'page_header' => array(
            'title' => __( 'Page Header Image', 'novassium-image-generator' ),
            'prompt' => __( 'A professional wide banner image for a webpage about [PAGE_TOPIC] with balanced composition suitable for text overlay', 'novassium-image-generator' ),
        ),
        'hero_section' => array(
            'title' => __( 'Hero Section Background', 'novassium-image-generator' ),
            'prompt' => __( 'A striking hero section background image for [TOPIC] with subtle patterns and adequate space for text overlay. Widescreen format.', 'novassium-image-generator' ),
        ),
        'about_page' => array(
            'title' => __( 'About Page Team Photo', 'novassium-image-generator' ),
            'prompt' => __( 'A professional, friendly team photo of diverse, modern and collaborative business people in an office setting, suitable for an About Us page', 'novassium-image-generator' ),
        ),
        // Add more templates as needed
    );
    
    // Add custom CSS for better button styling
    ?>
    <style>
        .nig-template-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            margin-top: 20px;
            border: 1px solid #ccd0d4;
            background-color: #fff;
            box-shadow: 0 1px 1px rgba(0,0,0,0.04);
        }
        .nig-template-table th {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ccd0d4;
            font-weight: bold;
        }
        .nig-template-table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        .nig-template-table tr:last-child td {
            border-bottom: none;
        }
        .nig-template-table tr:hover {
            background-color: #f9f9f9;
        }
        .nig-select-template {
            background-color: #0085ba;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .nig-select-template:hover {
            background-color: #006799;
        }
    </style>
    
    <div class="wrap">
        <h1><?php _e( 'Template Library', 'novassium-image-generator' ); ?></h1>
        <p><?php _e('Select a template to use as a starting point for your image generation prompt.', 'novassium-image-generator'); ?></p>
        
        <table class="nig-template-table">
            <thead>
                <tr>
                    <th width="25%"><?php _e( 'Template Name', 'novassium-image-generator' ); ?></th>
                    <th width="60%"><?php _e( 'Prompt', 'novassium-image-generator' ); ?></th>
                    <th width="15%"><?php _e( 'Action', 'novassium-image-generator' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $templates as $key => $template ) : ?>
                    <tr>
                        <td><?php echo esc_html( $template['title'] ); ?></td>
                        <td><?php echo esc_html( $template['prompt'] ); ?></td>
                        <td>
                            <button class="nig-select-template" data-prompt="<?php echo esc_attr( $template['prompt'] ); ?>"><?php _e( 'Use Template', 'novassium-image-generator' ); ?></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="nig-template-instructions" style="margin-top: 20px;">
            <h3><?php _e('How to use templates:', 'novassium-image-generator'); ?></h3>
            <ol>
                <li><?php _e('Click "Use Template" to select a predefined prompt', 'novassium-image-generator'); ?></li>
                <li><?php _e('The template will be added to the prompt field in the generator', 'novassium-image-generator'); ?></li>
                <li><?php _e('Replace placeholder text like [PRODUCT_NAME] or [PAGE_TOPIC] with your specific content', 'novassium-image-generator'); ?></li>
                <li><?php _e('Customize the prompt further if needed before generating images', 'novassium-image-generator'); ?></li>
            </ol>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($){
        // Make template buttons work
        $('.nig-select-template').on('click', function(e){
            e.preventDefault();
            var prompt = $(this).data('prompt');
            
            // Check if we're in a popup window
            if (window.opener && !window.opener.closed) {
                // If we're in a popup, send data to the opener and close
                window.opener.jQuery('#nig_prompt').val(prompt);
                window.close();
            } else {
                // If not in a popup, use localStorage to store the selected prompt
                localStorage.setItem('nig_selected_prompt', prompt);
                // Redirect to the main settings page
                window.location.href = '<?php echo esc_url(admin_url('admin.php?page=nig_settings')); ?>';
            }
        });
    });
    </script>
    <?php
}
