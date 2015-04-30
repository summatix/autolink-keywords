<?php

namespace Autolink_Keywords\Admin;
use \Autolink_Keywords\Plugin as Plugin;

if ( ! defined( 'AUTOLINK_KEYWORDS' ) ) exit;

class Settings_Page extends Plugin {
	public function __construct() {
		$this->name = __( 'Autolink Keywords', 'autolink-keywords' );
		$this->description = __( 'Automatically convert keywords to links', 'autolink-keywords' );
		$this->instructions = __( 'Set default settings', 'autolink-keywords' );
	}

	public function add_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=' . $this->type,
			__( 'Autolink Keywords Settings', 'autolink-keywords' ),
			__( 'Settings', 'autolink-keywords' ),
			'manage_options',
			$this->type,
			[ &$this, 'options_page' ]
		);
	}

	public function add_action_link( $links ) {
		$name = __( 'Settings', 'autolink-keywords' );
		$new_link = "<a href='edit.php?post_type={$this->type}&page={$this->type}'>{$name}</a>";
		return array_merge( [ $new_link ], $links );
	}

	public function init() {
		register_setting( $this->type, $this->type . '_settings', [ &$this, 'validate' ] );

		add_settings_section(
			$this->type . '_section',
			$this->description,
			[ &$this, 'ouput_instructions' ],
			$this->type
		);

		$this->add_settings_field(
			'max_links',
			__( 'Max Links', 'autolink-keywords' )
		);
		$this->add_settings_field(
			'disabled_tags',
			__( 'Disabled Tags (separated by commas)', 'autolink-keywords' )
		);
		$this->add_settings_field(
			'priority',
			__( 'Event priority', 'autolink-keywords' )
		);
	}

	public function ouput_instructions() {
		echo $this->instructions;
	}

	public function validate($input) {
		foreach ( $input as $key => $value ) {
			$input[ $key ] = apply_filters( "{$this->type}_validation_{$key}", $value );
		}

		return $input;
	}

	public function validate_max_links( $value ) {
		if ( ctype_digit( $value ) && $value > 0 ) {
			return $value;
		} else {
			return Settings::GetDefault( 'max_links' );
		}
	}

	public function validate_disabled_tags($value) {
		$tags = [];
		foreach ( explode( ',', $value ) as $tag ) {
			$tag = trim( $tag );
			if ( $tag ) {
				$tags[] = $tag;
			}
		}

		return implode( ', ', $tags );
	}

	public function validate_priority($value) {
		if ( ctype_digit( $value ) && $value >= 0 ) {
			return $value;
		}

		return Settings::GetDefault( 'priority' );
	}

	public function options_page() {
		?>
		<h3><?php echo $this->name; ?></h3>
		<form action='options.php' method='post'>
			<?php
				settings_fields( $this->type );
				do_settings_sections( $this->type );
				submit_button();
			?>
		</form>
		<?php
	}

	private function add_settings_field( $name, $description ) {
		add_filter( "{$this->type}_validation_{$name}", [ &$this, "validate_{$name}" ] );
		add_settings_field(
			"{$this->type}_{$name}",
			$description,
			[ &$this, "render_{$name}" ],
			$this->type,
			"{$this->type}_section"
		);
	}

	private function text_field_render( $name ) {
		$input_name = $this->type . '_settings[' . $name . ']';
		$value = esc_attr( \Autolink_Keywords\instance( 'Settings' )->get( $name ) );
		echo "<input type='text' name='$input_name' value='$value'>";
	}

	/**
	 * Magic method to render field.
	 */
	public function __call( $method, $arguments ) {
		$begins_with = 'render_';
		if ( $begins_with == substr( $method, 0, strlen( $begins_with ) ) ) {
			$name = substr( $method, strlen( $begins_with ) );
			if ( $name ) {
				return $this->text_field_render( $name );
			}
		} else {
			$class_method = __CLASS__ . '::' . $method;
			trigger_error( "Call to undefined method {$method}()", E_USER_ERROR );
		}
	}

	/**
	 * Installs the admin setting page.
	 */
	public static function install() {
		$self = \Autolink_Keywords\instance( 'Admin\\Settings_Page' );
		add_action( 'admin_menu', [ &$self, 'add_admin_menu' ] );
		add_action( 'admin_init', [ &$self, 'init' ] );

		$action_link = 'plugin_action_links_' . plugin_basename( AUTOLINK_KEYWORDS );
		add_filter( $action_link, [ &$self, 'add_action_link' ] );
	}
}
