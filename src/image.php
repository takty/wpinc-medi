<?php
/**
 * Utilities for Images
 *
 * @package Wpinc Medi
 * @author Takuto Yanagida
 * @version 2024-03-14
 */

declare(strict_types=1);

namespace wpinc\medi;

/**
 * Displays an HTML img element of post thumbnail image.
 *
 * @param int|\WP_Post|null $post     (Optional) Post ID or post object. Default global $post.
 * @param string|int[]      $size     (Optional) Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'large'.
 * @param string            $meta_key (Optional) Meta key.
 */
function the_thumbnail_image( $post = null, $size = 'large', string $meta_key = '_thumbnail_id' ): void {
	echo get_the_thumbnail_image( $post, $size, $meta_key );  // phpcs:ignore
}

/**
 * Displays an HTML figure element of post thumbnail image.
 *
 * @param int|\WP_Post|null $post     (Optional) Post ID or post object. Default global $post.
 * @param string|int[]      $size     (Optional) Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'large'.
 * @param string            $meta_key (Optional) Meta key.
 */
function the_thumbnail_figure( $post = null, $size = 'large', string $meta_key = '_thumbnail_id' ): void {
	echo get_the_thumbnail_figure( $post, $size, $meta_key );  // phpcs:ignore
}

/**
 * Gets an HTML img element of post thumbnail image.
 *
 * @param int|\WP_Post|null $post     (Optional) Post ID or post object. Default global $post.
 * @param string|int[]      $size     (Optional) Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'large'.
 * @param string            $meta_key (Optional) Meta key.
 * @return string HTML img element or empty string on failure.
 */
function get_the_thumbnail_image( $post = null, $size = 'large', string $meta_key = '_thumbnail_id' ): string {
	$aid = get_thumbnail_id( $post, $meta_key );
	if ( 0 === $aid ) {
		return '';
	}
	return wp_get_attachment_image( $aid, $size );
}

/**
 * Gets an HTML figure element of post thumbnail image.
 *
 * @param int|\WP_Post|null $post     (Optional) Post ID or post object. Default global $post.
 * @param string|int[]      $size     (Optional) Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'large'.
 * @param string            $meta_key (Optional) Meta key.
 * @return string HTML figure element or empty string on failure.
 */
function get_the_thumbnail_figure( $post = null, $size = 'large', string $meta_key = '_thumbnail_id' ): string {
	$aid = get_thumbnail_id( $post, $meta_key );
	if ( 0 === $aid ) {
		return '';
	}
	$img = wp_get_attachment_image( $aid, $size );

	$p = get_post( $aid );
	if ( $p instanceof \WP_Post ) {
		$cap = "<figcaption class=\"wp-caption-text\">$p->post_excerpt</figcaption>";
	} else {
		$cap = '';
	}
	return "<figure class=\"wp-caption\">$img$cap</figure>";
}


// -----------------------------------------------------------------------------


/**
 * Retrieves thumbnail image ID
 *
 * @param int|\WP_Post|null $post     (Optional) Post ID or post object. Default global $post.
 * @param string            $meta_key (Optional) Post meta key.
 * @return int Attachment ID if the thumbnail is found, or 0.
 */
function get_thumbnail_id( $post = null, string $meta_key = '_thumbnail_id' ): int {
	$post = get_post( $post );
	if ( ! ( $post instanceof \WP_Post ) ) {
		return 0;
	}
	$id = get_post_meta( $post->ID, $meta_key, true );
	return ( ! is_numeric( $id ) ) ? 0 : (int) $id;
}

/**
 * Retrieves attachment ID of the first image src from post contents.
 *
 * @param int|\WP_Post|null $post (Optional) Post ID or post object. Default global $post.
 * @return int Attachment ID if the image is found, or 0.
 */
function get_first_image_id( $post = null ): int {
	$src = _scrape_first_image_src( $post );
	if ( '' === $src ) {
		return 0;
	}
	return url_to_attachment_id( $src );
}

/**
 * Retrieves attachment ID from URL.
 *
 * @param string $url URL of an attachment.
 * @return int Attachment ID if the attachment is found, or 0.
 */
function url_to_attachment_id( string $url ): int {
	$dir = \wp_get_upload_dir();
	if ( 0 !== strpos( $url, $dir['baseurl'] ) ) {
		return 0;
	}
	$id = \attachment_url_to_postid( $url );
	if ( $id ) {
		return $id;
	}
	$full_url = preg_replace( '/(-\d+x\d+)(\.[^.]+){0,1}$/i', '${2}', $url ) ?? $url;
	if ( $url === $full_url ) {
		return 0;
	}
	return \attachment_url_to_postid( $full_url );
}

/**
 * Scrapes the first image src from post contents.
 *
 * @access private
 *
 * @param int|\WP_Post|null $post (Optional) Post ID or post object. Default global $post.
 * @return string URL if the first image is found, or '';
 */
function _scrape_first_image_src( $post = null ): string {
	$post = get_post( $post );
	if ( ! ( $post instanceof \WP_Post ) ) {
		return '';
	}
	preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $ms );
	return $ms[1][0] ?? '';
}
