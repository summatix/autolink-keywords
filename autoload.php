<?php

namespace Autolink_Keywords;

if ( ! defined( 'AUTOLINK_KEYWORDS' ) ) exit;

function autoload( $classname ) {
	if ( __NAMESPACE__ == substr( $classname, 0, strlen( __NAMESPACE__ ) ) ) {
		$classname = substr( $classname, strlen( __NAMESPACE__ . '/' ) );
		$classname = strtolower( $classname );
		$classname = str_replace( '_', '-', $classname );

		$include_path = '/includes';
		$paths = explode( '\\', $classname );
		if ( count($paths) > 1 ) {
			$include_path .= '/' . implode( '/', array_slice( $paths, 0, -1 ) );
		}

		$classname = end( $paths );
		$class_path = "{$include_path}/class-{$classname}.php";
		$class_path = realpath( dirname( __FILE__ ) . $class_path );

		if ( file_exists( $class_path ) ) {
			require $class_path;
		}
	}
}

spl_autoload_register( __NAMESPACE__ . '\\autoload' );
