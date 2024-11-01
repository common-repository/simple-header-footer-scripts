<?php
/**
 * Plugin Name: Simple Header Footer Scripts
 * Plugin URI: https://wordpress.org/plugins/simple-header-footer-scripts/
 * Description: Insert header footer scripts in your WordPress website easily.
 * Version: 1.0.0
 * Author: Bharat Mandava
 * Author URI: https://wpsquare.com/
 * Text Domain: simple-header-footer-scripts
 * License: GPL2
 * Domain Path: /languages
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>;.
 *
 * @package   simple-header-footer-scripts
 * @author    Bharat Mandava
 * @version   1.0.0
 * @copyright Copyright (c) 2018, WPSquare
 * 
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SHF_VERSION', '1.0.0' );
define( 'SHF_PLUGIN_NAME', 'Simple Header Footer Scripts' );
define( 'SHF_PLUGIN_SLUG', 'simple-header-footer-scripts' );
define( 'SHF_DIR', plugin_dir_path( __FILE__ ) );
define( 'SHF_URL', plugin_dir_url( __FILE__ ) );
define( 'SHF_PATH', plugin_basename( __FILE__ ) );
define( 'SHF_OPTIONS', 'simple_header_footer_scripts' );
define( 'SHF_WELCOME_DISMISS', 'simple_header_footer_scripts_welcome_dismiss' );

register_uninstall_hook( __FILE__, shf_uninstall_plugin );
/**
 * Uninstall - Remove plugin options
 *
 * @since 1.0.0
*/
function shf_uninstall_plugin() {
	delete_option( 'simple_header_footer_scripts' );
	delete_option( 'simple_header_footer_scripts_welcome_dismiss' );
}

/**
 * Include Class.
 */
include_once SHF_DIR . '/admin/class-shf-admin.php';