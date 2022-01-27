<?php
/**
 * Media Options
 *
 * @package Wpinc Medi
 * @author Takuto Yanagida
 * @version 2022-01-26
 */

namespace wpinc\medi;

function update_media_options() {
	update_option( 'thumbnail_size_w', 320 );
	update_option( 'thumbnail_size_h', 320 );
	update_option( 'thumbnail_crop', 1 );
	update_option( 'medium_size_w', 640 );
	update_option( 'medium_size_h', 9999 );
	update_option( 'medium_large_size_w', 960 );
	update_option( 'medium_large_size_h', 9999 );
	update_option( 'large_size_w', 1280 );
	update_option( 'large_size_h', 9999 );
	update_option( 'uploads_use_yearmonth_folders', 1 );
}

function enable_default_image_sizes( $add_medium_small = true ) {
	add_image_size( 'small', 320, 9999 );
	add_image_size( 'huge', 2560, 9999 );
	if ( $add_medium_small ) {
		add_image_size( 'medium-small', 480, 9999 );
	}
	add_filter(
		'image_size_names_choose',
		function ( $sizes ) use ( $add_medium_small ) {
			$is_ja = preg_match( '/^ja/', get_locale() );
			$ns    = array();
			foreach ( $sizes as $idx => $s ) {
				$ns[ $idx ] = $s;
				if ( 'thumbnail' === $idx ) {
					$ns['small'] = ( $is_ja ? '小' : 'Small' );
					if ( $add_medium_small ) {
						$ns['medium-small'] = ( $is_ja ? 'やや小' : 'Medium Small' );
					}
				}
				if ( 'medium' === $idx ) {
					$ns['medium_large'] = ( $is_ja ? 'やや大' : 'Medium Large' );
				}
			}
			return $ns;
		}
	);
}
