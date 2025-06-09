<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Novassium_Image_Generator_Elementor_Widget extends Widget_Base {

    public function get_name() {
        return 'nig_image_generator';
    }

    public function get_title() {
        return __( 'Image Generator', 'novassium-image-generator' );
    }

    public function get_icon() {
        return 'eicon-image';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'novassium-image-generator' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'prompt',
            [
                'label' => __( 'Prompt', 'novassium-image-generator' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __( 'A beautiful sunset over the mountains', 'novassium-image-generator' ),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $prompt = $settings['prompt'];

        // Generate Image
        $args = array(
            'prompt'          => $prompt,
            'style_preset'    => 'photographic',
            'aspect_ratio'    => '16:9',
            'samples'         => 1,
            'negative_prompt' => '',
            'seed'            => 0,
            'output_format'   => 'jpeg',
            'width'           => 1024,
            'height'          => 576,
        );

        $response = nig_generate_image( $args );

        if ( isset( $response['success'] ) && $response['success'] ) {
            $image_url = $response['images'][0];
            echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $prompt ) . '" />';
        } else {
            echo '<p>' . esc_html( $response['message'] ) . '</p>';
        }
    }

    protected function _content_template() {}
}