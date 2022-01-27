<?php
/**
 * SVG Support
 *
 * @package Wpinc Medi
 * @author Takuto Yanagida
 * @version 2022-01-28
 */

namespace wpinc\medi;

/**
 * Enables SVG file uploading.
 */
function enable_svg_uploading() {
	add_filter(
		'upload_mimes',
		function ( array $mimes ) {
			$mimes['svg']  = 'image/svg+xml';
			$mimes['svgz'] = 'image/svg+xml';
			return $mimes;
		}
	);
	add_filter(
		'wp_handle_upload_prefilter',
		function ( $file ) {
			if ( 'image/svg+xml' === $file['type'] ) {
				if ( ! check_svg_secure( $file['tmp_name'] ) ) {
					$file['error'] = __( 'Specified file was not be uploaded because it may contain security issues.' );
				}
			}
			return $file;
		}
	);
	add_filter(
		'wp_check_filetype_and_ext',
		function ( array $data, string $file, string $filename ) {
			$ext = $data['ext'] ?? '';
			if ( empty( $ext ) ) {
				$ext = strtolower( end( explode( '.', $filename ) ) );
			}
			if ( in_array( $ext, array( 'svg', 'svgz' ), true ) ) {
				$data['type'] = 'image/svg+xml';
				$data['ext']  = $ext;
			}
			return $data;
		},
		10,
		3
	);
	add_filter(
		'wp_prepare_attachment_for_js',
		function ( $res, $attachment ) {
			if ( 'image/svg+xml' === $res['mime'] ) {
				$ds = get_svg_size( get_attached_file( $attachment->ID ) );
				if ( $ds ) {
					$res = array_merge( $res, $ds );
				}
				$sizes = get_sizes( $ds );
				foreach ( $sizes as $name => &$a ) {
					$a['url']         = $res['url'];
					$a['orientation'] = $a['height'] > $a['width'] ? 'portrait' : 'landscape';
				}
				$res['sizes'] = $sizes;
				$res['icon']  = $res['url'];
			}
			return $res;
		},
		10,
		2
	);
	add_filter(
		'wp_generate_attachment_metadata',
		function ( $metadata, $attachment_id ) {
			if ( 'image/svg+xml' === get_post_mime_type( $attachment_id ) ) {
				$file = get_attached_file( $attachment_id );

				$ds = get_svg_size( $file );
				if ( empty( $ds ) ) {
					return array();
				}

				$rel_file = $file;
				$uploads  = wp_get_upload_dir();
				if ( 0 === strpos( $rel_file, $uploads['basedir'] ) ) {
					$rel_file = str_replace( $uploads['basedir'], '', $rel_file );
					$rel_file = ltrim( $rel_file, '/' );
				}
				// Default image meta.
				$metadata = array(
					'width'  => $ds['width'],
					'height' => $ds['height'],
					'file'   => $rel_file,
					'sizes'  => array(),
				);

				$sizes = get_sizes( $ds );
				foreach ( $sizes as $name => &$a ) {
					$a['file']      = wp_basename( $file );
					$a['mime-type'] = 'image/svg+xml';
				}
				$metadata['sizes']          = $sizes;
				$metadata['original_image'] = wp_basename( $file );
			}
			return $metadata;
		},
		10,
		2
	);
	add_filter(
		'wp_calculate_image_srcset_meta',
		function ( $image_meta, $size_array, $image_src, $attachment_id ) {
			if ( 'image/svg+xml' === get_post_mime_type( $attachment_id ) ) {
				unset( $image_meta['sizes'] );  // For removing srcset attribute.
			}
			return $image_meta;
		},
		10,
		4
	);
}

function check_svg_secure( $file ) {
	$cont = file_get_contents( $file );  // phpcs:ignore

	$is_zipped = ( 0 === mb_strpos( $contents, "\x1f" . "\x8b" . "\x08" ) );
	if ( $is_zipped ) {
		$cont = gzdecode( $cont );
		if ( false === $cont ) {
			return false;
		}
	}
	$svg = @simplexml_load_string( $cont, 'SimpleXMLIterator' );   // phpcs:ignore
	if ( $svg ) {
		return check_svg_tree( $svg );
	}
	return false;
}

function check_svg_tree( $sxi ) {
	$not_allowed_tag        = array( 'script', 'use', 'a' );
	$not_allowed_attr_start = array( 'on', 'href', 'xlink:href' );

	for ( $sxi->rewind(); $sxi->valid(); $sxi->next() ) {  // phpcs:ignore
		if ( in_array( $sxi->key(), $not_allowed_tag, true ) ) {
			return false;
		}
		$ats = $sxi->current()->attributes();
		if ( $ats ) {
			foreach ( $ats as $k => $v ) {
				$k = strtolower( $k );
				foreach ( $not_allowed_attr_start as $start ) {
					if ( 0 === strpos( $k, $start ) ) {
						return false;
					}
				}
				if ( has_remote_url( $v ) ) {
					return false;
				}
			}
		}
		if ( $sxi->hasChildren() ) {
			if ( ! check_svg_tree( $sxi->current() ) ) {
				return false;
			}
		}
	}
	return true;
}

function has_remote_url( $v ) {
	$v = trim( preg_replace( '/[^ -~]/xu', '', $v ) );

	$has_url = preg_match( '~^url\(\s*[\'"]\s*(.*)\s*[\'"]\s*\)$~xi', $v, $match );
	if ( ! $has_url ) {
		return false;
	}
	$v = trim( $match[1], '\'"' );
	return preg_match( '~^((https?|ftp|file):)?//~xi', $v );
}

function get_svg_size( $svg ) {
	$svg = @simplexml_load_file( $svg );   // phpcs:ignore
	$w   = 0;
	$h   = 0;
	if ( $svg ) {
		$ats = $svg->attributes();
		if ( isset( $ats->width, $ats->height ) && is_numeric( $ats->width ) && is_numeric( $ats->height ) ) {
			$w = floatval( $ats->width );
			$h = floatval( $ats->height );
		} elseif ( isset( $ats->viewBox ) ) {   // phpcs:ignore
			$ss = explode( ' ', $ats->viewBox );   // phpcs:ignore
			if ( isset( $ss[2], $ss[3] ) ) {
				$w = floatval( $ss[2] );
				$h = floatval( $ss[3] );
			}
		} else {
			return false;
		}
	}
	return array(
		'width'       => $w,
		'height'      => $h,
		'orientation' => ( $w > $h ) ? 'landscape' : 'portrait',
	);
}

function get_sizes( $ds ) {
	$sizes = array();
	$ais   = wp_get_additional_image_sizes();
	$names = apply_filters(
		'image_size_names_choose',
		array(
			'thumbnail' => __( 'Thumbnail' ),
			'medium'    => __( 'Medium' ),
			'large'     => __( 'Large' ),
			'full'      => __( 'Full Size' ),
		)
	);
	foreach ( $names as $name => $label ) {
		$def_w = 0;
		$def_h = 0;
		if ( $ds && 'full' === $name ) {
			$def_w = $ds['width'];
			$def_h = $ds['height'];
		}
		$w = get_option( "{$name}_size_w", $def_w );
		$h = get_option( "{$name}_size_h", $def_h );
		if ( ( 0 === $w || 0 === $h ) && isset( $ais[ $name ] ) ) {
			$w = $ais[ $name ]['width'];
			$h = $ais[ $name ]['height'];
		}
		if ( $w && $h ) {
			$sizes[ $name ] = array(
				'width'  => $w,
				'height' => $h,
			);
		}
	}
	return $sizes;
}
