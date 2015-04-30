<?php

namespace Autolink_Keywords;

if ( ! defined( 'AUTOLINK_KEYWORDS' ) ) exit;

class Autolink extends Plugin {
	private static $tracker_code = '--AUTOLINK_TRACK--';

	public function setup() {
		add_filter( 'the_content', [ &$this, 'add_autolinks' ], $this->get( 'priority' ) );
	}

	public function add_autolinks( $content ) {
		$default_max_links = $this->get('max_links');

		foreach ( Autolinks_Model::get_autolinks() as $autolink) {
			if ( ! self::is_same_page( $autolink ) ) {
				$keywords = self::convert_array_to_regex_selector( $autolink['keywords'] );
				$max_links = $autolink['max_links'] ? $autolink['max_links'] : $default_max_links;
				$content = $this->replace( $keywords, $autolink['link'], $content, $max_links );
			}
		}

		return $content;
	}

	private function replace( $keyword, $link, $content, $max_times ) {
		$step_1 = self::replace_keyword_with_tracker( $keyword, $content );

		if ( $step_1 == $content ) {
			return $step_1;
		}

		$disabled_tags = $this->get_disabled_tags();
		$step_2 = self::remove_tracker_from_disabled_tags( $disabled_tags, $step_1 );

		$step_3 = self::replace_trackers_with_links( $link, $step_2, $max_times );

		return $step_3;
	}

	private function get_disabled_tags() {
		return array_map( 'trim', explode( ',', $this->get( 'disabled_tags' ) ) );
	}

	private static function is_same_page( $autolink ) {
		if ( preg_match( '/href="(.+?)"/i', $autolink['link'], $matches ) ) {
			if ( $matches[1] == get_permalink() ) {
				return true;
			}
		}

		return false;
	}

	private static function replace_keyword_with_tracker( $keyword, $content ) {
		$find = '%(^|[.,/?"\'“” ‘’>(\s])(' . $keyword . ')([.,/?"\'“” ‘’<)\s]|$)%im';
		$replace = '${1}[' . self::$tracker_code . ' ${2}]${3}';
		return preg_replace( $find, $replace, $content );
	}

	private static function remove_tracker_from_disabled_tags( $disabled_tags, $content ) {
		foreach ( $disabled_tags as $disabled ) {
			$content = self::remove_tracker_from_disabled_tag( $disabled, $content );
		}

		return $content;
	}

	private static function remove_tracker_from_disabled_tag( $tag, $content ) {
		$find = '/<' . $tag . '([^a-z](.*?))?>(.*?)<\/' . $tag . '[\s+]*>/is';
		$callback = [ 'Autolink_Keywords\\Autolink', 'remove_tracker' ];
		return preg_replace_callback( $find, $callback, $content );
	}

	public static function remove_tracker( $matches ) {
		return preg_replace( '/\[' . self::$tracker_code . ' (.+?)]/', '${1}', $matches[0] );
	}

	public static function replace_trackers_with_links( $link, $content, $max_times ) {
		$callback = function ( $matches ) use ( $link, $max_times ) {
			static $count = 0;
			if ( ++$count <= $max_times ) {
				return self::process_link( $link, $matches[1] );
			} else {
				return self::remove_tracker( $matches );
			}
		};

		return preg_replace_callback( '/\[' . self::$tracker_code . ' (.+?)]/', $callback, $content );
	}

	private static function process_link( $link, $text ) {
		return do_shortcode( str_replace( '{text}', $text, $link ) );
	}

	private static function convert_array_to_regex_selector( $arr ) {
		$regex_selector = [];
		foreach ( explode( ',', $arr ) as $value ) {
			$regex_selector[] = preg_quote( trim( $value ) );
		}

		return implode( '|', $regex_selector );
	}

	public static function run() {
		add_action( 'init', [ instance( 'Autolink' ), 'setup' ] );
	}
}
