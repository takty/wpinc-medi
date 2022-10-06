<?php
/**
 * Media Sizes
 *
 * @package Wpinc Medi
 * @author Takuto Yanagida
 * @version 2022-10-06
 */

namespace wpinc\medi;

/**
 * Updates Image Size Options.
 */
function update_image_size_options() {
	update_option( 'thumbnail_size_w', 160 );
	update_option( 'thumbnail_size_h', 160 );
	update_option( 'thumbnail_crop', 1 );
	update_option( 'medium_size_w', 320 );
	update_option( 'medium_size_h', 9999 );
	update_option( 'medium_large_size_w', 512 );  // Assign this but usually not used.
	update_option( 'medium_large_size_h', 9999 );
	update_option( 'large_size_w', 768 );
	update_option( 'large_size_h', 9999 );
}

/**
 * Adds Custom Image Sizes.
 */
function add_custom_image_sizes() {
	remove_image_size( '1536x1536' );
	remove_image_size( '2048x2048' );
	add_image_size( 'small', 160, 9999 );
	add_image_size( 'medium-large', 512, 9999 );  // Add a size that is the same size but described differently, for use as CSS class name.
	add_image_size( 'x-large', 1024, 9999 );
	add_image_size( 'xx-large', 1536, 9999 );
	add_image_size( 'xxx-large', 2048, 9999 );

	add_filter(
		'image_size_names_choose',
		function ( $sizes ) {
			$ns = array();
			foreach ( $sizes as $name => $label ) {
				$ns[ $name ] = $label;
				if ( 'thumbnail' === $name ) {
					$ns['small'] = _x( 'Small', 'size', 'wpinc_medi' );
				}
				if ( 'medium' === $name ) {
					$ns['medium-large'] = _x( 'Medium Large', 'size', 'wpinc_medi' );
				}
			}
			return $ns;
		}
	);
}
