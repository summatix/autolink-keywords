<?php

namespace Autolink_Keywords;

if ( ! defined( 'AUTOLINK_KEYWORDS' ) ) exit;

class Settings {
	private $settings = [
		'max_links'     => 3,
		'disabled_tags' => 'a, form',
		'priority'      => 10000
	];

	/**
	 * Initializes a new instance with the provided settings.
	 *
	 * @param array $settings The settings for the instance.
	 */
	public function __construct( $settings = [] ) {
		$this->load( $settings );
	}

	/**
	 * Loads the object with the provided settings.
	 *
	 * @param array $settings The settings for the instance.
	 */
	public function load( $settings ) {
		foreach ( $this->settings as $key => $value ) {
			if ( isset( $settings[ $key ] ) ) {
				$this->settings[ $key ] = $settings[ $key ];
			}
		}
	}

	/**
	 * Retrieves a setting value.
	 *
	 * @param string $setting The name of the setting to retrieve.
	 * @param mixed (optional) $default The default value of the setting if not set. Default is false.
	 * @return mixed The value of the retrieved setting.
	 */
	public function get( $setting, $default = false ) {
		return isset( $this->settings[ $setting ] ) ? $this->settings[ $setting ] : $default;
	}

	/**
	 * Gets the default value for a given setting.
	 *
	 * @param string $setting The name of the setting to retrieve default value for.
	 * @return mixed
	 */
	public static function get_default( $setting ) {
		$inst = new Settings;
		return $inst->get( $setting );
	}

	/**
	 * Loads the global settings from the database.
	 */
	public static function load_settings() {
		$option = instance( 'Plugin' )->type . '_settings';
		$settings = get_option( $option );
		if ( is_array( $settings ) ) {
			instance( 'Settings' )->load( $settings );
		}
	}
}
