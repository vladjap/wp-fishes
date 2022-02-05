<?php // phpcs:ignore
/**
 * Plugin Name: Fishmap
 * Plugin URI: site.com
 * Description: Fishmap
 * Author: Ajvan
 * Author URI: https://site.com/
 * Version: 1.4.0
 * Text Domain: Fishmap
 * Domain Path: /languages
 *
 * Copyright (c) 2021 x-pro
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Constants
 */
define( 'FISHMAP_MAIN_FILE', __FILE__ );
define( 'FISHMAP_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'FISHMAP_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'FISHMAP_PLUGIN_VERSION', '1.4.0' );

if ( ! class_exists( 'Fishmap' ) ) {
    /**
     * Class Fishmap
     */
    class Fishmap {

        /**
         * The reference the *Singleton* instance of this class.
         *
         * @var $instance
         */
        protected static $instance;



        /**
         * Returns the *Singleton* instance of this class.
         *
         * @return self::$instance The *Singleton* instance.
         */
        public static function get_instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Notices (array)
         *
         * @var array
         */
        public $notices = array();

        /**
         * Protected constructor to prevent creating a new instance of the
         * *Singleton* via the `new` operator from outside of this class.
         */
        protected function __construct() {
            add_action( 'plugins_loaded', array( $this, 'init' ) );
            register_activation_hook( __FILE__, array( $this, 'generateTables' ) );

        }

        public function generateTables() {
            require_once __DIR__ . '/classes/class-fishmap-db.php';
            Fishmap_DB::generateTables();
        }

        /**
         * Init the plugin after plugins_loaded so environment variables are set.
         */
        public function init() {
            load_plugin_textdomain( 'fishmap', false, plugin_basename( __DIR__ ) . '/languages' );
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
            $this->include_files();
        }

        /**
         * Adds plugin action links
         *
         * @param array $links Plugin action link before filtering.
         *
         * @return array Filtered links.
         */
        public function plugin_action_links( $links ) {
//            $setting_link = "#";
            $plugin_links = array(
//                '<a href="' . $setting_link . '">' . __( 'Settings', 'wp-plugfish' ) . '</a>',
                '<a href="http://site.com/">' . __( 'Support', 'fishmap' ) . '</a>',
            );

            return array_merge( $plugin_links, $links );
        }

        /**
         * Includes the files for the plugin
         *
         * @return void
         */
        public function include_files() {
            include_once FISHMAP_PLUGIN_PATH . '/includes/utils.php';
            include_once FISHMAP_PLUGIN_PATH . '/classes/class-fishmap-assets.php';
            include_once FISHMAP_PLUGIN_PATH . '/classes/class-fishmap-db.php';
            include_once FISHMAP_PLUGIN_PATH . '/classes/class-fishmap-shortcode.php';
            include_once FISHMAP_PLUGIN_PATH . '/classes/class-fishmap-admin.php';

        }

    }
    Fishmap::get_instance();
}

/**
 * Main instance Fishmap.
 *
 * Returns the main instance of Fishmap.
 *
 * @return Fishmap
 */
function FM() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
    return Fishmap::get_instance();
}