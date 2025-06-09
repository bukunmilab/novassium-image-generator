<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Register Gutenberg Blocks
 */
function nig_register_gutenberg_blocks() {
    wp_register_script(
        'nig-gutenberg-block',
        NIG_PLUGIN_URL . 'assets/js/gutenberg-block.js',
        array( 'wp-blocks', 'wp-element', 'wp-editor' ),
        '1.0',
        true
    );

    wp_register_style(
        'nig-gutenberg-block-css',
        NIG_PLUGIN_URL . 'assets/css/gutenberg-block.css',
        array(),
        '1.0'
    );

    register_block_type( 'nig/image-generator', array(
        'editor_script'   => 'nig-gutenberg-block',
        'editor_style'    => 'nig-gutenberg-block-css',
        'style'           => 'nig-gutenberg-block-css',
        'render_callback' => 'nig_render_gutenberg_block',
    ) );
}
add_action( 'init', 'nig_register_gutenberg_blocks' );

/**
 * Render Gutenberg Block
 */
function nig_render_gutenberg_block( $attributes ) {
    if ( ! is_user_logged_in() ) {
        return '<p>' . __( 'You must be logged in to generate images.', 'novassium-image-generator' ) . '</p>';
    }

    ob_start();
    ?>
    <div class="nig-gutenberg-block">
        <form class="nig-gutenberg-form">
            <label><?php _e( 'Prompt:', 'novassium-image-generator' ); ?></label>
            <textarea class="nig-gutenberg-prompt" rows="3" required></textarea>

            <button type="submit"><?php _e( 'Generate Image', 'novassium-image-generator' ); ?></button>
        </form>
        <div class="nig-gutenberg-result"></div>
    </div>
    <script>
    (function($){
        $(document).on('submit', '.nig-gutenberg-form', function(e){
            e.preventDefault();

            var prompt = $(this).find('.nig-gutenberg-prompt').val();

            var formData = {
                action: 'nig_generate_image_frontend',
                nonce: '<?php echo wp_create_nonce( 'nig_generate_image_frontend_nonce' ); ?>',
                prompt: prompt,
                style_preset: 'photographic',
                aspect_ratio: '16:9',
                samples: 1,
                output_format: 'jpeg',
            };

            $(this).next('.nig-gutenberg-result').html('<?php echo esc_js( __( "Generating image...", "novassium-image-generator" ) ); ?>');

            $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', formData, function(response){
                if(response.success){
                    var output = '';
                    response.data.images.forEach(function(url){
                        output += '<img src="' + url + '" style="max-width: 300px; margin:10px;" />';
                    });
                    $(this).next('.nig-gutenberg-result').html(output);
                } else {
                    $(this).next('.nig-gutenberg-result').html('<p style="color:red;">' + response.data.message + '</p>');
                }
            }.bind(this));
        });
    })(jQuery);
    </script>
    <?php
    return ob_get_clean();
}