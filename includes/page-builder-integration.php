<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Elementor\Plugin as ElementorPlugin;

/**
 * Register Elementor Widget
 */
function nig_register_elementor_widget() {
    require_once NIG_PLUGIN_DIR . 'includes/elementor-widget.php';
    
    // Ensure Elementor is loaded before registering the widget
    if (defined('ELEMENTOR_PATH') && class_exists('Elementor\Widget_Base')) {
        ElementorPlugin::instance()->widgets_manager->register(new \Novassium_Image_Generator_Elementor_Widget());
    }
}

/**
 * Check if Elementor is active and then register the widget
 */
function nig_integration_with_elementor() {
    // Check if Elementor is active using the 'elementor/version' option
    if ( defined( 'ELEMENTOR_PATH' ) && class_exists( 'Elementor\Widget_Base' ) ) {
        add_action( 'elementor/widgets/register', 'nig_register_elementor_widget' );
    } else {
        // Display admin notice if Elementor is not active
        add_action( 'admin_notices', 'nig_elementor_not_active_notice' );
    }
}
add_action( 'plugins_loaded', 'nig_integration_with_elementor' );


/** This notice is unnecessary so it has been removed since the plugin also provides a short code to use 
 * Admin Notice for Missing Elementor
 */
 /*
function nig_elementor_not_active_notice() {
    echo '<div class="notice notice-error"><p>' . __( 'Novassium Image Generator requires Elementor to be installed and active for page builder integration.', 'novassium-image-generator' ) . '</p></div>';
} 
*/