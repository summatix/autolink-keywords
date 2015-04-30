<?php

namespace Autolink_Keywords\Admin;
use \Autolink_Keywords\Plugin as Plugin;
use \Autolink_Keywords\Autolinks_Model as Autolinks_Model;

if ( ! defined( 'AUTOLINK_KEYWORDS' ) ) exit;

class Posts_Management extends Plugin {
	public function setup() {
		add_action( 'add_meta_boxes', [ &$this, 'add_extra_fields' ] );
		add_action( 'save_post', [ &$this, 'save' ] );
		add_filter( "manage_{$this->type}_posts_columns", [ &$this, 'add_link_column_after_title' ] );
		add_action( "manage_{$this->type}_posts_custom_column", [ &$this, 'add_link_column_body' ], 10, 2 );
		add_filter( 'post_row_actions', [ &$this, 'remove_quick_edit' ], 10, 2 );
	}

	public function add_link_column_after_title( $defaults ) {
		$columns = [];
		foreach ( $defaults as $name => $value ) {
			$columns[ $name ] = $value;
			if ( $name == 'title' ) {
				$columns['link'] = __( 'Link', 'autolink-keywords' );
			}
		}

		return $columns;
	}

	public function add_link_column_body( $column_name, $post_id ) {
		if ( 'link' == $column_name ) {
			echo htmlspecialchars( Autolinks_Model::get_value( $post_id, 'link' ) );
		}
	}

	public function remove_quick_edit( $actions, $post ) {
		if ( $post->post_type == $this->type ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		return $actions;
	}

	public function add_extra_fields() {
		add_meta_box(
			$this->type,
			__( 'Autolink', 'autolink-keywords' ),
			[ &$this, 'print_fields' ],
			$this->type
		);
	}

	public function print_fields( $post ) {
		wp_nonce_field( $this->type, "{$this->type}_nonce" );
		$this->print_field(
			'keywords',
			__( 'Keywords to autolink (separate by commas)', 'autolink-keywords' ),
			$post
		);
		$this->print_field(
			'link',
			__( 'Link to replace keywords with', 'autolink-keywords' ),
			$post
		);
		$this->print_field(
			'max_links',
			__( 'Maximum number of times to autolink in a page', 'autolink-keywords' ),
			$post
		);
	}

	public function save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! $this->verify_nounce( $name, $post_id ) ) {
			return;
		}

		$this->save_field( 'keywords', $post_id );
		$this->save_field( 'link', $post_id );
		$this->save_field( 'max_links', $post_id );
		$this->sync_title_and_keywords( $post_id );
	}

	public function validate_field_max_links( $value ) {
		return ctype_digit( $value ) && $value > 0 ? $value : '';
	}

	private function sync_title_and_keywords( $post_id ) {
		$avoid_infinite_loop = [ &$this, 'save' ];
		remove_action( 'save_post', $avoid_infinite_loop );
		wp_update_post( [
			'ID'         => $post_id,
			'post_title' => Autolinks_Model::get_value( $post_id, 'keywords' )
		] );
		add_action( 'save_post', $avoid_infinite_loop );
	}

	private function print_field( $name, $description, $post ) {
		$field_name = "{$this->type}_{$name}";
		$value = htmlspecialchars( Autolinks_Model::get_value( $post_id, $name ) );
		echo "<p><label for='$field_name'>$description</label> ";
		echo "<textarea id='$field_name' name='$field_name'>$value</textarea></p>";
	}

	private function save_field( $name, $post_id ) {
		$field_value = $_POST[ "{$this->type}_{$name}" ];
		$field_value = $this->validate_field( $name, $field_value );

		Autolinks_Model::save_validated_value( $post_id, $name, $field_value );
	}

	private function validate_field( $name, $value ) {
		$validate_method_name = "validate_field_{$name}";
		if ( method_exists( $this, $validate_method_name ) ) {
			return $this->$validate_method_name( $value );
		} else {
			return $value;
		}
	}

	private function verify_nounce( $name, $post_id ) {
		if ( isset( $_POST[ "{$this->type}_nonce" ] ) ) {
			return wp_verify_nonce( $_POST[ "{$this->type}_nonce" ], $this->type );
		} else {
			return false;
		}
	}

	public static function install() {
		\Autolink_Keywords\instance( 'Admin\\Posts_Management' )->setup();
	}
}
