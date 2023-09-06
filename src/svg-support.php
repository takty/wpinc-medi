<?php
/**
 * SVG Support
 *
 * @package Wpinc Medi
 * @author Takuto Yanagida
 * @version 2023-09-06
 */

namespace wpinc\medi;

/**
 * Enables SVG file supports.
 *
 * @param string|null $capability Capability name.
 */
function enable_svg_support( ?string $capability = 'manage_options' ): void {
	if ( is_admin() ) {
		if ( $capability && current_user_can( $capability ) ) {
			add_filter( 'upload_mimes', '\wpinc\medi\_cb_upload_mimes__svg' );
			add_filter( 'wp_check_filetype_and_ext', '\wpinc\medi\_cb_wp_check_filetype_and_ext__svg', 10, 3 );
			add_filter( 'wp_handle_upload_prefilter', '\wpinc\medi\_cb_wp_handle_upload_prefilter__svg' );
			add_filter( 'wp_prepare_attachment_for_js', '\wpinc\medi\_cb_wp_prepare_attachment_for_js__svg', 10, 2 );
			add_filter( 'wp_generate_attachment_metadata', '\wpinc\medi\_cb_wp_generate_attachment_metadata__svg', 10, 2 );
		}
	} else {
		add_filter( 'wp_calculate_image_srcset_meta', '\wpinc\medi\_cb_wp_calculate_image_srcset_meta__svg', 10, 4 );
	}
}

/**
 * Callback function for 'upload_mimes' filter.
 *
 * @param array<string, string> $mimes Mime types keyed by the file extension.
 * @return array<string, string> Mimes.
 */
function _cb_upload_mimes__svg( array $mimes ): array {
	$mimes['svg']  = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';
	return $mimes;
}

/**
 * Callback function for 'wp_check_filetype_and_ext' filter.
 *
 * @param array<string, string|false> $data     Values for the extension, mime type, and corrected filename.
 * @param string                      $file     Full path to the file.
 * @param string                      $filename The name of the file.
 * @return array<string, string|false> Data.
 */
function _cb_wp_check_filetype_and_ext__svg( array $data, string $file, string $filename ): array {
	$ext = $data['ext'] ?? '';
	if ( empty( $ext ) ) {
		$cs  = explode( '.', $filename );
		$ext = strtolower( end( $cs ) );
	}
	if ( in_array( $ext, array( 'svg', 'svgz' ), true ) ) {
		$data['type'] = 'image/svg+xml';
		$data['ext']  = $ext;
	}
	return $data;
}

/**
 * Callback function for 'wp_handle_upload_prefilter' filter.
 *
 * @param array<string, mixed> $file An array of data for a single file.
 * @return array<string, mixed> File data.
 */
function _cb_wp_handle_upload_prefilter__svg( array $file ): array {
	if ( 'image/svg+xml' === $file['type'] ) {
		if ( ! _check_svg_secure( $file['tmp_name'] ) ) {
			$file['error'] = _x( 'Specified file was not be uploaded because it may contain security issues.', 'svg support', 'wpinc_medi' );
		}
	}
	return $file;
}

/**
 * Callback function for 'wp_prepare_attachment_for_js' filter.
 * For showing size selector on image dialog.
 *
 * @param array<string, mixed> $res        Array of prepared attachment data.
 * @param \WP_Post             $attachment Attachment object.
 * @return array<string, mixed> Filtered data.
 */
function _cb_wp_prepare_attachment_for_js__svg( array $res, \WP_Post $attachment ): array {
	if ( 'image/svg+xml' === $res['mime'] ) {
		$file = get_attached_file( $attachment->ID );
		if ( false === $file ) {
			return $res;
		}
		$ds = _get_svg_size( $file );
		if ( ! $ds ) {
			return $res;
		}
		$res   = array_merge( $res, $ds );
		$sizes = _get_possible_sizes( $ds );
		foreach ( $sizes as $name => &$a ) {
			$a['url']         = $res['url'];
			$a['orientation'] = $a['height'] > $a['width'] ? 'portrait' : 'landscape';
		}
		$res['sizes'] = $sizes;
		$res['icon']  = $res['url'];
	}
	return $res;
}

/**
 * Callback function for 'wp_generate_attachment_metadata' filter.
 * For generating metadata of SVG images.
 *
 * @param array<string, mixed> $metadata      An array of attachment meta data.
 * @param int                  $attachment_id Current attachment ID.
 * @return array<string, mixed> Filtered metadata.
 */
function _cb_wp_generate_attachment_metadata__svg( array $metadata, int $attachment_id ): array {
	if ( 'image/svg+xml' === get_post_mime_type( $attachment_id ) ) {
		$file = get_attached_file( $attachment_id );
		if ( false === $file ) {
			return $metadata;
		}
		$ds = _get_svg_size( $file );
		if ( ! $ds ) {
			return $metadata;
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

		$sizes = _get_possible_sizes( $ds );
		foreach ( $sizes as $name => &$a ) {
			$a['file']      = wp_basename( $file );
			$a['mime-type'] = 'image/svg+xml';
		}
		$metadata['sizes']          = $sizes;
		$metadata['original_image'] = wp_basename( $file );
	}
	return $metadata;
}

/**
 * Callback function for 'wp_calculate_image_srcset_meta' filter.
 * For removing srcset attributes of SVG images.
 *
 * @param array<string, mixed> $image_meta    The image meta data as returned by 'wp_get_attachment_metadata()'.
 * @param int[]                $size_array    An array of requested width and height values.
 * @param string               $image_src     The 'src' of the image.
 * @param int                  $attachment_id The image attachment ID or 0 if not supplied.
 * @return array<string, mixed> Filtered metadata.
 */
function _cb_wp_calculate_image_srcset_meta__svg( array $image_meta, array $size_array, string $image_src, int $attachment_id ): array {
	if ( 'image/svg+xml' === get_post_mime_type( $attachment_id ) ) {
		unset( $image_meta['sizes'] );
	}
	return $image_meta;
}


// -----------------------------------------------------------------------------


/**
 * Check whether a SVG file is secure.
 *
 * @access private
 *
 * @param string $path File path.
 * @return bool True if the file is secure.
 */
function _check_svg_secure( string $path ): bool {
	$cont = file_get_contents( $path );  // phpcs:ignore
	if ( false === $cont ) {
		return false;
	}
	if ( 0 === mb_strpos( $cont, "\x1f\x8b\x08" ) ) {
		$cont = gzdecode( $cont );
		if ( false === $cont ) {
			return false;
		}
	}
	$svg = @simplexml_load_string( $cont, 'SimpleXMLIterator' );  // phpcs:ignore
	if ( $svg instanceof \SimpleXMLIterator ) {
		return _check_svg_tree( $svg );
	}
	return false;
}

/**
 * Check SVG file recursively.
 *
 * @access private
 *
 * @param \SimpleXMLIterator $sxi Iterator of XML nodes.
 * @return bool True if the current node is secure.
 */
function _check_svg_tree( \SimpleXMLIterator $sxi ): bool {
	$ng_tag        = array( 'script', 'use', 'a' );
	$ng_attr_start = array( 'on', 'href', 'xlink:href' );

	for ( $sxi->rewind(); $sxi->valid(); $sxi->next() ) {  // phpcs:ignore
		if ( in_array( $sxi->key(), $ng_tag, true ) ) {
			return false;
		}
		$ats = $sxi->current()->attributes();
		if ( $ats ) {
			foreach ( $ats as $k => $v ) {
				if ( ! is_string( $k ) ) {
					continue;
				}
				$k = strtolower( $k );
				foreach ( $ng_attr_start as $start ) {
					if ( 0 === strpos( $k, $start ) ) {
						return false;
					}
				}
				if ( _has_remote_url( $v ) ) {
					return false;
				}
			}
		}
		if ( $sxi->hasChildren() ) {
			$e = $sxi->current();
			if ( ! $e || ! _check_svg_tree( $e ) ) {
				return false;
			}
		}
	}
	return true;
}

/**
 * Check whether a string contains a remote URL.
 *
 * @access private
 *
 * @param string $v String.
 * @return bool True if it contains a remote URL.
 */
function _has_remote_url( string $v ): bool {
	$v = trim( preg_replace( '/[^ -~]/xu', '', $v ) ?? $v );

	$has_url = preg_match( '~^url\(\s*[\'"]\s*(.*)\s*[\'"]\s*\)$~xi', $v, $match );
	if ( ! $has_url ) {
		return false;
	}
	$v = trim( $match[1], '\'"' );
	return (bool) preg_match( '~^((https?|ftp|file):)?//~xi', $v );
}

/**
 * Retrieves the size of SVG image.
 *
 * @access private
 *
 * @param string $path File path.
 * @return array<string, mixed>|null Array of dimension.
 */
function _get_svg_size( string $path ): ?array {
	$cont = file_get_contents( $path );  // phpcs:ignore
	if ( false === $cont ) {
		return null;
	}
	if ( 0 === mb_strpos( $cont, "\x1f\x8b\x08" ) ) {
		$cont = gzdecode( $cont );
		if ( false === $cont ) {
			return null;
		}
	}
	$svg = @simplexml_load_string( $cont );  // phpcs:ignore
	$w   = 0;
	$h   = 0;
	if ( $svg ) {
		$ats = $svg->attributes();
		if ( isset( $ats->width, $ats->height ) && is_numeric( (string) $ats->width ) && is_numeric( (string) $ats->height ) ) {
			$w = (float) $ats->width;
			$h = (float) $ats->height;
		} elseif ( isset( $ats->viewBox ) ) {  // phpcs:ignore
			$ss = explode( ' ', $ats->viewBox );  // phpcs:ignore
			if ( isset( $ss[2], $ss[3] ) ) {
				$w = (float) $ss[2];
				$h = (float) $ss[3];
			}
		} else {
			return null;
		}
	}
	return array(
		'width'       => $w,
		'height'      => $h,
		'orientation' => ( $w > $h ) ? 'landscape' : 'portrait',
	);
}

/**
 * Undocumented function
 *
 * @access private
 *
 * @param array<string, mixed> $ds Dimension of image.
 * @return array<string|int, array<string, int>> Array of size name to sizes.
 */
function _get_possible_sizes( array $ds ): array {
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
			$ow = $ds['width'] ?? 1;
			$oh = $ds['height'] ?? 1;
			if ( 'full' !== $name ) {
				if ( $w < $h ) {  // Portrait.
					if ( $ow / $oh < $w / $h ) {  // Original is vertically narrower.
						$w = $ow * $h / $oh;
					} else {
						$h = $oh * $w / $ow;
					}
				} else {  // Landscape.
					if ( $w / $h < $ow / $oh ) {  // Original is horizontally narrower.
						$h = $oh * $w / $ow;
					} else {
						$w = $ow * $h / $oh;
					}
				}
			}
			$sizes[ $name ] = array(
				'width'  => absint( $w ),
				'height' => absint( $h ),
			);
		}
	}
	return $sizes;
}
