<?php
/**
 * URL of Assets Files.
 *
 * @package Wpinc Meta
 * @author Takuto Yanagida
 * @version 2022-01-27
 */

namespace wpinc\medi;

/**
 * Gets the URL of the file.
 *
 * @param string $path The path of a file.
 * @return string The URL.
 */
function get_file_uri( string $path ): string {
	$path = wp_normalize_path( $path );

	if ( is_child_theme() ) {
		$theme_path = wp_normalize_path( defined( 'CHILD_THEME_PATH' ) ? CHILD_THEME_PATH : get_stylesheet_directory() );
		$theme_uri  = get_stylesheet_directory_uri();

		// When child theme is used, and libraries exist in the parent theme.
		$len_t = strlen( $theme_path );
		$len   = strlen( $path );
		if ( $len_t >= $len || 0 !== strncmp( $theme_path . $path[ $len_t ], $path, $len_t + 1 ) ) {
			$theme_path = wp_normalize_path( defined( 'THEME_PATH' ) ? THEME_PATH : get_template_directory() );
			$theme_uri  = get_template_directory_uri();
		}
		return str_replace( $theme_path, $theme_uri, $path );
	} else {
		$theme_path = wp_normalize_path( defined( 'THEME_PATH' ) ? THEME_PATH : get_stylesheet_directory() );
		$theme_uri  = get_stylesheet_directory_uri();
		return str_replace( $theme_path, $theme_uri, $path );
	}
}

/**
 * Gets the absolute URL of the relative URL.
 *
 * @param string $base A base URL.
 * @param string $rel  A relative URL.
 * @return string The absolute URL.
 */
function abs_url( string $base, string $rel ): string {
	$scheme = wp_parse_url( $rel, PHP_URL_SCHEME );
	if ( false === $scheme || null !== $scheme ) {
		return $rel;
	}
	$base = trailingslashit( $base );
	if ( '#' === $rel[0] || '?' === $rel[0] ) {
		return $base . $rel;
	}
	$pu = wp_parse_url( $base );
	// phpcs:disable
	$scheme = isset( $pu['scheme'] ) ? $pu['scheme'] . '://' : '';
	$host   = isset( $pu['host'] )   ? $pu['host']           : '';
	$port   = isset( $pu['port'] )   ? ':' . $pu['port']     : '';
	$path   = isset( $pu['path'] )   ? $pu['path']           : '';
	// phpcs:enable
	$path = preg_replace( '#/[^/]*$#', '', $path );
	if ( '/' === $rel[0] ) {
		$path = '';
	}
	$abs = "$host$port$path/$rel";
	$re  = array( '#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#' );
	for ( $n = 1; $n > 0; $abs = preg_replace( $re, '/', $abs, -1, $n ) ) {}  // phpcs:ignore
	return $scheme . $abs;
}
