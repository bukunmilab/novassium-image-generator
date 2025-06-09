<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Enhance prompt based on post content with improved context awareness
 *
 * @param string $title Post/Page/Product title.
 * @param string $content Post/Page/Product content.
 * @param string $type Content type (post, page, product).
 * @return string Enhanced prompt.
 */
function nig_smart_prompting( $title, $content, $type = 'post' ) {
    // Basic content summary
    $summary = wp_trim_words( wp_strip_all_tags( $content ), 20, '...' );
    
    // Start with a base prompt
    $prompt = $title;
    
    // Add type-specific enhancements
    switch( $type ) {
        case 'post':
            $prompt .= ' - Blog article visual showing ' . $summary;
            break;
            
        case 'page':
            $prompt .= ' - Professional page header image representing ' . $summary;
            $prompt .= ' - Wide format with balanced composition suitable for page headers';
            break;
            
        case 'product':
            $prompt .= ' - Product showcase image highlighting ' . $summary;
            $prompt .= ' - Clear product details on neutral background';
            break;
            
        default:
            $prompt .= ' - ' . $summary;
    }

    // Universal enhancements
    $prompt .= ' - High quality, professional style';
    $prompt .= ' - SEO optimized image';
    
    return sanitize_text_field( $prompt );
}

/**
 * Detect content type automatically
 * 
 * @param int $post_id The post ID to analyze
 * @return string The detected content type (post, page, product)
 */
function nig_detect_content_type( $post_id ) {
    $post_type = get_post_type( $post_id );
    
    if ( $post_type === 'product' ) {
        return 'product';
    } elseif ( $post_type === 'page' ) {
        return 'page';
    } else {
        return 'post'; // default for regular posts and custom post types
    }
}
