<?php

namespace DC\EDD\Bkash;

/**
 * Scripts and Styles Class
 */
class Assets {
    /**
     * Assets constructor.
     */
    function __construct() {
        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'register' ], 5 );
        } else {
            add_action( 'wp_enqueue_scripts', [ $this, 'register' ], 5 );
        }
    }

    /**
     * Register our app scripts and styles
     *
     * @return void
     */
    public function register() {
        $this->register_scripts( $this->get_scripts() );
        $this->register_styles( $this->get_styles() );
    }

    /**
     * Register scripts
     *
     * @param array $scripts
     *
     * @return void
     */
    private function register_scripts( $scripts ) {
        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] ) ? $script['version'] : DC_EDD_BKASH_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
        }
    }

    /**
     * Register styles
     *
     * @param array $styles
     *
     * @return void
     */
    public function register_styles( $styles ) {
        foreach ( $styles as $handle => $style ) {
            $deps = isset( $style['deps'] ) ? $style['deps'] : false;

            wp_register_style( $handle, $style['src'], $deps, DC_EDD_BKASH_VERSION );
        }
    }

    /**
     * Get all registered scripts
     *
     * @return array
     */
    public function get_scripts() {
        $plugin_js_assets_path = DC_EDD_BKASH_ASSETS . '/js/';

        $scripts = [
            'edd-bkash-js' => [
                'src'       => $plugin_js_assets_path . 'edd_bkash.js',
                'version'   => filemtime( DC_EDD_BKASH_PATH . '/assets/js/edd_bkash.js' ),
                'deps'      => [],
                'in_footer' => true,
            ],
        ];

        return $scripts;
    }

    /**
     * Get registered styles
     *
     * @return array
     */
    public function get_styles() {
        $plugin_css_assets_path = DC_EDD_BKASH_ASSETS . '/css/';

        $styles = [
            'bkash-style' => [
                'src'     => $plugin_css_assets_path . 'bkash.css',
                'deps'    => [],
                'version' => filemtime( DC_EDD_BKASH_PATH . '/assets/css/bkash.css' ),
            ],
        ];

        return $styles;
    }
}
