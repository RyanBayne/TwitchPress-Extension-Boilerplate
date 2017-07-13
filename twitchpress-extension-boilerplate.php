<?php 
/*
Plugin Name: TwitchPress Extension Boilerplate
Version: 1.0.0
Plugin URI: http://twitchpress.wordpress.com
Description: Create new TwitchPress extensions using this boilerplate.
Author: Ryan Bayne
Author URI: http://ryanbayne.wordpress.com
Text Domain: twitchpress-boilerplate
Domain Path: /languages
Copyright: © 2017 Ryan Bayne
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
 
GPL v3 

This program is free software downloaded from WordPress.org: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. This means
it can be provided for the sole purpose of being developed further
and we do not promise it is ready for any one persons specific needs.
See the GNU General Public License for more details.

See <http://www.gnu.org/licenses/>.


    Planning to create a TwitchPress extension like this one?

    Step 1: Read WordPress.org plugin development guidelines
    https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/

    Step 2: Read the TwitchPress extension development guidelines.
    Full guide coming soon!
    
    
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

/**
 * Check if TwitchPress is active, else avoid activation.
 **/
if ( !in_array( 'channel-solution-for-twitch/twitchpress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

/**
 * Required minimums and constants
 */
define( 'TWITCHPRESS_SYNC_VERSION', '3.2.0' );
define( 'TWITCHPRESS_SYNC_MIN_PHP_VER', '5.6.0' );
define( 'TWITCHPRESS_SYNC_MIN_WC_VER', '2.5.0' );
define( 'TWITCHPRESS_SYNC_MAIN_FILE', __FILE__ );
define( 'TWITCHPRESS_SYNC_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'TWITCHPRESS_SYNC_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

if ( ! class_exists( 'TwitchPress_Boilerplate' ) ) :

    class TwitchPress_Boilerplate {
        /**
         * @var Singleton
         */
        private static $instance;        

        /**
         * Get a *Singleton* instance of this class.
         *
         * @return Singleton The *Singleton* instance.
         * 
         * @version 1.0
         */
        public static function get_instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }
            return self::$instance;
        } 
        
        /**
         * Private clone method to prevent cloning of the instance of the
         * *Singleton* instance.
         *
         * @return void
         */
        private function __clone() {}

        /**
         * Private unserialize method to prevent unserializing of the *Singleton*
         * instance.
         *
         * @return void
         */
        private function __wakeup() {}    
        
        /**
         * Protected constructor to prevent creating a new instance of the
         * *Singleton* via the `new` operator from outside of this class.
         */
        protected function __construct() {
            
            $this->define_constants();
            $this->includes();
            $this->init_hooks();            

        }

        /**
         * Define TwitchPress Login Constants.
         * 
         * @version 1.0
         */
        private function define_constants() {
            
            $upload_dir = wp_upload_dir();
            
            // Main (package) constants.
            if ( ! defined( 'TWITCHPRESS_SYNC_ABSPATH' ) )  {  define( 'TWITCHPRESS_SYNC_ABSPATH', __FILE__ ); }
            if ( ! defined( 'TWITCHPRESS_SYNC_BASENAME' ) ) { define( 'TWITCHPRESS_SYNC_BASENAME', plugin_basename( __FILE__ ) ); }
            if ( ! defined( 'TWITCHPRESS_SYNC_DIR_PATH' ) ) { define( 'TWITCHPRESS_SYNC_DIR_PATH', plugin_dir_path( __FILE__ ) ); }
            
            // Constants for force hidden views to been seen for this plugin.
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_USERS' ) )    { define( 'TWITCHPRESS_SHOW_SETTINGS_USERS', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_BOT' ) )      { define( 'TWITCHPRESS_SHOW_SETTINGS_BOT', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_CHAT' ) )     { define( 'TWITCHPRESS_SHOW_SETTINGS_CHAT', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_JUKEBOX' ) )  { define( 'TWITCHPRESS_SHOW_SETTINGS_JUKEBOX', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_GAMES' ) )    { define( 'TWITCHPRESS_SHOW_SETTINGS_GAMES', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_COMMANDS' ) ) { define( 'TWITCHPRESS_SHOW_SETTINGS_COMMANDS', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_CONTENT' ) )  { define( 'TWITCHPRESS_SHOW_SETTINGS_CONTENT', true ); }      
        }  

        /**
         * Include required files.
         * 
         * @version 1.0
         */
        public function includes() {
            include_once( 'includes/function.twitchpress-sync-core.php' );
            
            if ( twitchpress_is_request( 'admin' ) ) {
                include_once( 'includes/class.twitchpress-sync-uninstall.php' );
            }      
        }

        /**
         * Hook into actions and filters.
         * 
         * @version 1.0
         */
        private function init() {
        
            // Load this extension after plugins loaded, we need TwitchPress core to load first mainly.
            add_action( 'plugins_loaded',      array( $this, 'init_hooks' ), 0 );

            register_activation_hook( __FILE__, array( 'TwitchPress_Boilerplate_Install', 'install' ) );
            
            // Do not confuse deactivation of a plugin with deletion of a plugin - two very different requests.
            register_deactivation_hook( __FILE__, array( 'TwitchPress_Boilerplate_Uninstall', 'deactivate' ) );
        }
                      
        /**
         * Init the plugin after plugins_loaded so environment variables are set.
         * 
         * @version 1.0
         */
        public function init_hooks() {
               
            // Other hooks.
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
            
            do_action( 'twitchpress_sync_loaded' );
        }
        
        public function init_filters() {

                        
        }
        
        /**
        * Styles for login page hooked by login_enqueue_scripts
        * 
        * @version 1.0
        */
        public function twitchpress_login_styles() {

        }
        
        /**
        * Add a new section to the User settings tab.
        * 
        * @param mixed $sections
        * 
        * @version 1.0
        */
        public function settings_add_section_users( $sections ) {  
            global $only_section;
            
            // We use this to apply this extensions settings as the default view...
            // i.e. when the tab is clicked and there is no "section" in URL. 
            if( empty( $sections ) ){ $only_section = true; } else { $only_section = false; }
            
            // Add sections to the User Settings tab. 
            $new_sections = array(
                'testsection'  => __( 'Test Section', 'twitchpress' ),
            );

            return array_merge( $sections, $new_sections );           
        }
        
        /**
        * Add options to this extensions own settings section.
        * 
        * @param mixed $settings
        * 
        * @version 1.0
        */
        public function settings_add_options_users( $settings ) {
            global $current_section, $only_section;
            
            $new_settings = array();
            
            // This first section is default if there are no other sections at all.
            if ( 'testsection' == $current_section || !$current_section && $only_section ) {
                $new_settings = apply_filters( 'twitchpress_testsection_users_settings', array(
     
                    array(
                        'title' => __( 'Testing New Settings', 'twitchpress-login' ),
                        'type'     => 'title',
                        'desc'     => 'Attempting to add new settings.',
                        'id'     => 'testingnewsettings',
                    ),

                    array(
                        'desc'            => __( 'Checkbox Three', 'twitchpress-login' ),
                        'id'              => 'loginsettingscheckbox3',
                        'default'         => 'yes',
                        'type'            => 'checkbox',
                        'checkboxgroup'   => '',
                        'show_if_checked' => 'yes',
                        'autoload'        => false,
                    ),
                            
                    array(
                        'type'     => 'sectionend',
                        'id'     => 'testingnewsettings'
                    ),

                ));   
                
            }
            
            return array_merge( $settings, $new_settings );         
        }
        
        /**
         * Adds plugin action links
         *
         * @since 1.0.0
         */
        public function plugin_action_links( $links ) {
            $plugin_links = array(

            );
            return array_merge( $plugin_links, $links );
        }        

        /**
         * Get the plugin url.
         * @return string
         */
        public function plugin_url() {                
            return untrailingslashit( plugins_url( '/', __FILE__ ) );
        }

        /**
         * Get the plugin path.
         * @return string
         */
        public function plugin_path() {              
            return untrailingslashit( plugin_dir_path( __FILE__ ) );
        }                                                         
    }
    
    $GLOBALS['twitchpress_boilerplate'] = TwitchPress_Boilerplate::get_instance();

endif;    

