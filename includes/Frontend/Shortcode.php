<?php

namespace DC\EDD\Bkash\Frontend;

/**
 * Class Shortcode
 * @package DC\EDD\Bkash\Frontend
 */
class Shortcode {

    public function __construct() {
        add_shortcode( 'dc_edd_bkash_payment', [ $this, 'render_frontend' ] );
    }

    /**
     * Render frontend app
     *
     * @param array $atts
     * @param string $content
     *
     * @return string
     */
    public function render_frontend( $atts, $content = '' ) {
        // wp_enqueue_style( 'frontend' );
        // wp_enqueue_script( 'frontend' );

        $content .= 'Hello World!';

        return $content;
    }
}
