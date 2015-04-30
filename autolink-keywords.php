<?php
/*
 * Plugin Name: Autolink Keywords
 * Version: 1.0
 * Plugin URI: http://www.carobcherub.com/
 * Description: Automatically converts keywords to links.
 * Author: Robert Roose
 * Author URI: http://www.carobcherub.com/
 * Requires at least: 4.0
 * Tested up to: 4.2.1
 * Text Domain: autolink-keywords
 *
 * @package WordPress
 * @author Robert Roose
 * @since 1.0.0
 */

namespace Autolink_Keywords;

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'AUTOLINK_KEYWORDS', __FILE__ );

require 'autoload.php';

/**
 * Maintains a registry of instances for the plugin.
 *
 * @param string $name The name of the class to retrieve.
 * @return object The instantiated class.
 */
function instance( $name ) {
	static $instances = [];

	if ( ! isset( $instances[ $name ] ) ) {
		$namespaced = __NAMESPACE__ . '\\' . $name;
		$instances[ $name ] = new $namespaced;
	}

	return $instances[ $name ];
}

// Load plugin
Settings::load_settings();
Autolinks_Model::register_post_type();
Autolink::run();

// Load admin options
Admin\Settings_Page::install();
Admin\Posts_Management::install();
