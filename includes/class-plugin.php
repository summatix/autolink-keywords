<?php

namespace Autolink_Keywords;

if ( ! defined( 'AUTOLINK_KEYWORDS' ) ) exit;

/**
 * Base class to access settings.
 */
class Plugin {
	public $type = 'autolink_keywords';

	protected $settings;

	/**
	 * Initialize the instance with the optional settings.
	 *
	 * @param array (optional) $settings The settings to initialize the
	 * instance with. If not set, global settings are loaded.
	 */
	public function __construct( $settings = false ) {
		$this->settings = $settings ? $settings : instance( 'Settings' );
	}

	/**
	 * Shorthand method to $this->settings->get
	 */
	protected function get( $setting, $default = false ) {
		return $this->settings->get( $setting, $default );
	}
}
