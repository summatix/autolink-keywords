<?php

namespace Autolink_Keywords;

if ( ! defined( 'AUTOLINK_KEYWORDS' ) ) exit;

class Autolinks_Model extends Plugin {
	public static function get_autolinks() {
		$autolinks = [];
		$query = new \WP_Query( [
			'post_type'   => self::get_type(),
			'post_status' => 'publish',
			'nopaging'    => true,
		] );

		while ( $query->have_posts() ) {
			$query->the_post();
			$id = get_the_ID();
			$keywords = self::get_value( $id, 'keywords' );
			$link = self::get_value( $id, 'link' );

			if ( ! empty( $keywords ) && ! empty( $link ) ) {
				$autolinks[] = [
					'keywords'  => $keywords,
					'link'      => $link,
					'max_links' => self::get_value( $id, 'max_links' )
				];
			}
		}

		wp_reset_query();

		return $autolinks;
	}

	public static function get_value( $id, $name ) {
		return get_post_meta( $id, '_' . self::get_type() . '_' . $name, true );
	}

	public static function save_validated_value( $id, $name, $value ) {
		update_post_meta( $post_id, '_' . self::get_type() . '_' . $name, $value );
	}

	private static function get_type() {
		static $type = null;

		if ( null == $type ) {
			$type = instance( 'Plugin' )->type;
		}

		return $type;
	}

	public static function register_post_type() {
		$labels = [
			'name'               => __( 'Autokeywords', 'autolink-keywords' ),
			'singular_name'      => __( 'Autokeywords', 'autolink-keywords' ),
			'add_new'            => __( 'New Autokeywords', 'autolink-keywords' ),
			'add_new_item'       => __( 'Add New Autokeywords', 'autolink-keywords' ),
			'edit_item'          => __( 'Edit Autokeywords', 'autolink-keywords' ),
			'new_item'           => __( 'New Autokeywords', 'autolink-keywords' ),
			'search_items'       => __( 'Search Autokeywords', 'autolink-keywords' ),
			'not_found'          => __( 'No Autokeywords found', 'autolink-keywords' ),
			'not_found_in_trash' => __( 'No Autokeywords found in Trash', 'autolink-keywords' )
		];

		register_post_type( self::get_type(), [
			'labels'              => $labels,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'menu_icon'           => 'dashicons-admin-links',
			'query_var'           => false,
			'rewrite'             => false,
			'supports'            => false
		] );
	}
}
