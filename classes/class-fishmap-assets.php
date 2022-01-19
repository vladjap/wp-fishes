<?php
/**
 * Class for Fishmap_Assets assets.
 *
 * @package Fishmap_Assets/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Fishmap_Assets
 */
class Fishmap_Assets {

    /**
     * Fishmap_Assets constructor.
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_css' ) );
    }


    /**
     * Enqueue payment scripts.
     *
     * @hook wp_enqueue_scripts
     */
    public function enqueue_scripts() {
        wp_register_script(
            'fishmap',
            plugins_url( 'assets/js/fishmap.js', FISHMAP_MAIN_FILE ),
            array(),
            FISHMAP_PLUGIN_VERSION,
            true
        );

        wp_register_style(
            'fishmap-style',
            plugins_url( 'assets/css/fishmap.css', FISHMAP_MAIN_FILE ),
            array(),
            FISHMAP_PLUGIN_VERSION
        );

        $option = get_option('my_option', true);
        $data = [];
        if ($option === 'yes') {
            // is page
//         $data
        }
        if($_GET['data-wp'])


        $js_params = [
            'test' => 'yes it is',
            'data' => $data
        ];
        wp_localize_script( 'fishmap', 'fishmapParams', $js_params );
        wp_enqueue_script( 'fishmap' );

        wp_enqueue_style( 'fishmap-style' );
    }

    /**
     * Enqueues admin page scripts.
     *
     * @hook admin_enqueue_scripts
     */
    public function enqueue_admin_scripts() {
        wp_register_script( 'fishmap-admin', FISHMAP_PLUGIN_URL . '/assets/js/admin.fishmap.js', true, FISHMAP_PLUGIN_VERSION, true );
        wp_enqueue_script( 'fishmap-admin' );
    }

    /**
     * Loads admin CSS file.
     */
    public function enqueue_admin_css( $hook ) {

        wp_enqueue_style(
            'fishmap-admin',
            plugins_url( 'assets/css/fishmap-admin.css', FISHMAP_MAIN_FILE ),
            array(),
            FISHMAP_PLUGIN_VERSION
        );
    }

    public function add_wp_enqueue_scripts() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }


} new Fishmap_Assets();