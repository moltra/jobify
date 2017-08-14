<?php
/**
 * Plugin Name: JobifyPlugin
 * Plugin URI: https://benmarshall.me/jobifyPlugin
 * Description: Official fully-featured job board plugin that seamlessly integrates with GitHub Jobs, Indeed &amp; more!
 * Version: 1.4.4
 * Author: Ben Marshall
 * Text Domain: jobifyPlugin
 * Domain Path: /languages
 * Author URI: https://benmarshall.me
 * License: GPL2
 */

/*  Copyright 2015  Ben Marshall  (email : me@benmarshall.me)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Security Note: Blocks direct access to the plugin PHP files.
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Define constants.
if( ! defined( 'JobifyPlugin_ROOT ' ) ) {
  define( 'JobifyPlugin_ROOT', plugin_dir_path( __FILE__ ) );
}

if( ! defined( 'JobifyPlugin_PLUGIN ' ) ) {
  define( 'JobifyPlugin_PLUGIN', __FILE__ );
}

// Define globals.
$jobifyPluginAPIs = array();

/**
 * Include the TGM_Plugin_Activation class.
 */
require_once JobifyPlugin_ROOT . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-tgm-plugin-activation.php';

/**
 * Used to detect installed plugins.
 */
require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Include the plugin helpers.
 */
require_once JobifyPlugin_ROOT . 'src' . DIRECTORY_SEPARATOR . 'helpers.php';

/**
 * Include widgets.
 */
require_once JobifyPlugin_ROOT . 'src' . DIRECTORY_SEPARATOR . 'JobifyPlugin/JobsWidget.class.php';

spl_autoload_register( 'jobifyPlugin_autoloader' );
function jobifyPlugin_autoloader( $class_name ) {
  if ( false !== strpos( $class_name, 'JobifyPlugin' ) ) {
    $classes_dir = JobifyPlugin_ROOT . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
    $class_file = str_replace( '_', DIRECTORY_SEPARATOR, $class_name ) . '.php';
    require_once $classes_dir . $class_file;
  }
}

function jobifyPlugin_githubjobs_api()
{
  global $jobifyPluginAPIs;

  // Load the plugin features.
  $plugin                  = new JobifyPlugin_Plugin();
  $plugin['scripts']       = new JobifyPlugin_Scripts();
  $plugin['custom_fields'] = new JobifyPlugin_CustomFields();
  $plugin['widgets']       = new JobifyPlugin_Widgets();
  $plugin['shortcodes']    = new JobifyPlugin_Shortcodes();
  $plugin['admin']         = new JobifyPlugin_Admin();

  if ( ! $plugin->settings['job_post_type'] )
  {
    require_once JobifyPlugin_ROOT . 'src' . DIRECTORY_SEPARATOR . 'APIs' . DIRECTORY_SEPARATOR . 'jobifyPlugin.php';
    $plugin['job_post_type'] = new JobifyPlugin_JobPostType();
  }

  // Initialize the plugin.
  $plugin->run();
}
add_action( 'plugins_loaded', 'jobifyPlugin_githubjobs_api' );

/**
 * Include APIs.
 */
require_once JobifyPlugin_ROOT . 'src' . DIRECTORY_SEPARATOR . 'APIs' . DIRECTORY_SEPARATOR . 'github.php';
require_once JobifyPlugin_ROOT . 'src' . DIRECTORY_SEPARATOR . 'APIs' . DIRECTORY_SEPARATOR . 'indeed.php';
require_once JobifyPlugin_ROOT . 'src' . DIRECTORY_SEPARATOR . 'APIs' . DIRECTORY_SEPARATOR . 'usajobs.php';
require_once JobifyPlugin_ROOT . 'src' . DIRECTORY_SEPARATOR . 'APIs' . DIRECTORY_SEPARATOR . 'careerjet.php';
