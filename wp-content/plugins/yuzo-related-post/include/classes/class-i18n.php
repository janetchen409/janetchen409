<?php
/**
 * @since       6.0         2019-04-13 17:30:48     Release
 * @package     YUZO
 * @subpackage  YUZO/Core
 */
// ───────────────────────────
namespace YUZO\Core;
// ───────────────────────────

if( ! class_exists( 'Yuzo_i18n' ) ){
    /*
    |--------------------------------------------------------------------------
    | Define the internationalization functionality
    |--------------------------------------------------------------------------
    |
    | Loads and defines the internationalization files for this plugin
    | so that it is ready for translation.
    |
    */
    class Yuzo_i18n {
        /**
          * Load the plugin text domain for translation.
          *
          * @since   6.0   2019-04-13 17:31:00     Release
          * @return  void
          */
        public function load_plugin_textdomain() {
            load_plugin_textdomain(
                YUZO_TEXTDOMAIN,
                false,
                YUZO_PATH . '/languages/'
            );
        }
    }
}?>