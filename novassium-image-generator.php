<?php
/*
Plugin Name: NovassiumGen
Description: Generate images for posts, pages, and products using the Novassium API. Includes a dashboard, usage tracking, template library, batch processing, smart prompting, and integration with popular page builders.
Version: 2.0
Author: Proxyle
Author URI: https://proxyle.com
Plugin URI: https://proxyle.com/novassium
Text Domain: novassium-image-generator
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define Constants
define( 'NIG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NIG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include Required Files
require_once NIG_PLUGIN_DIR . 'includes/admin-settings.php';
require_once NIG_PLUGIN_DIR . 'includes/image-generation.php';
require_once NIG_PLUGIN_DIR . 'includes/dashboard.php';
require_once NIG_PLUGIN_DIR . 'includes/templates.php';
require_once NIG_PLUGIN_DIR . 'includes/batch-processing.php';
require_once NIG_PLUGIN_DIR . 'includes/smart-prompting.php';
require_once NIG_PLUGIN_DIR . 'includes/page-builder-integration.php';
require_once NIG_PLUGIN_DIR . 'includes/shortcodes.php';
require_once NIG_PLUGIN_DIR . 'includes/ajax-handlers.php';
require_once NIG_PLUGIN_DIR . 'includes/gutenberg-blocks.php'; // Ensure this line is present

// Activation Hook
register_activation_hook( __FILE__, 'nig_activate_plugin' );
function nig_activate_plugin() {
    // Actions to perform on activation
    add_option( 'nig_usage_credits', 0 );
}

// Dependency Checks
function nig_check_dependencies() {
    // Check for WooCommerce
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'nig_woocommerce_notice' );
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }


    // Check for Elementor
    if ( ! defined( 'ELEMENTOR_PATH' ) ) {
        add_action( 'admin_notices', 'nig_elementor_notice' );
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }
}
add_action( 'plugins_loaded', 'nig_check_dependencies' );

function nig_woocommerce_notice() {
    echo '<div class="notice notice-error"><p>' . __( 'Novassium Image Generator requires WooCommerce to be installed and active.', 'novassium-image-generator' ) . '</p></div>';
}

function nig_elementor_notice() {
    echo '<div class="notice notice-error"><p>' . __( 'Novassium Image Generator requires Elementor to be installed and active.', 'novassium-image-generator' ) . '</p></div>';
}

// Remove the Elementor not active notice
function nig_remove_elementor_notice() {
    remove_action('admin_notices', 'nig_elementor_not_active_notice');
}
add_action('init', 'nig_remove_elementor_notice', 5);
