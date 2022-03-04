<?php
/**
 * Utilities for Images
 *
 * @package Wpinc Medi
 * @author Takuto Yanagida
 * @version 2022-03-04
 */

namespace wpinc\medi;

/**
 * Displays an HTML img element of post thumbnail image.
 *
 * @param \WP_Post|array|null $post     (Optional) Post ID or post object. Default global $post.
 * @param string|int[]        $size     (Optional) Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'large'.
 * @param string              $meta_key (Optional) Meta key.
 */
function the_thumbnail_image( $post = null, $size = 'large', string $meta_key = '_thumbnail_id' ): void {
	echo get_the_thumbnail_image( $post, $size, $meta_key );  // phpcs:ignore
}

/**
 * Displays an HTML figure element of post thumbnail image.
 *
 * @param \WP_Post|array|null $post     (Optional) Post ID or post object. Default global $post.
 * @param string|int[]        $size     (Optional) Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'large'.
 * @param string              $meta_key (Optional) Meta key.
 */
function the_thumbnail_figure( $post = null, $size = 'large', string $meta_key = '_thumbnail_id' ): void {
	echo get_the_thumbnail_figure( $post, $size, $meta_key );  // phpcs:ignore
}

/**
 * Gets an HTML img element of post thumbnail image.
 *
 * @param \WP_Post|array|null $post     (Optional) Post ID or post object. Default global $post.
 * @param string|int[]        $size     (Optional) Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'large'.
 * @param string              $meta_key (Optional) Meta key.
 * @return string HTML img element or empty string on failure.
 */
function get_the_thumbnail_image( $post = null, $size = 'large', string $meta_key = '_thumbnail_id' ): string {
	$aid = get_thumbnail_id( $post, $meta_key );
	if ( null === $aid ) {
		return '';
	}
	return wp_get_attachment_image( $aid, $size );
}

/**
 * Gets an HTML figure element of post thumbnail image.
 *
 * @param \WP_Post|array|null $post     (Optional) Post ID or post object. Default global $post.
 * @param string|int[]        $size     (Optional) Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'large'.
 * @param string              $meta_key (Optional) Meta key.
 * @return string HTML figure element or empty string on failure.
 */
function get_the_thumbnail_figure( $post = null, $size = 'large', string $meta_key = '_thumbnail_id' ): string {
	$aid = get_thumbnail_id( $post, $meta_key );
	if ( null === $aid ) {
		return '';
	}
	$img = wp_get_attachment_image( $aid, $size );

	$p   = get_post( $aid );
	$cap = empty( $p ) ? '' : "<figcaption class=\"wp-caption-text\">$p->post_excerpt</figcaption>";
	return "<figure class=\"wp-caption\">$img$cap</figure>";
}


// -----------------------------------------------------------------------------


/**
 * Retrieves thumbnail image ID
 *
 * @param \WP_Post|array|null $post     (Optional) Post ID or post object. Default global $post.
 * @param string              $meta_key (Optional) Post meta key.
 * @return int|null Attachment ID if the thumbnail is found, or null.
 */
function get_thumbnail_id( $post = null, string $meta_key = '_thumbnail_id' ): ?int {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}
	$id = get_post_meta( $post->ID, $meta_key, true );
	return empty( $id ) ? null : (int) $id;
}

/**
 * Retrieves attachment ID of the first image src from post contents.
 *
 * @param \WP_Post|array|null $post (Optional) Post ID or post object. Default global $post.
 * @return int|null Attachment ID if the image is found, or null.
 */
function get_first_image_id( $post = null ): ?int {
	$src = _scrape_first_image_src();
	if ( empty( $src ) ) {
		return null;
	}
	return url_to_attachment_id( $src );
}

/**
 * Retrieves attachment ID from URL.
 *
 * @param string $url URL of an attachment.
 * @return int|null Attachment ID if the attachment is found, or null.
 */
function url_to_attachment_id( string $url ): ?int {
	$dir = \wp_get_upload_dir();
	if ( 0 !== strpos( $url, $dir['baseurl'] ) ) {
		return null;
	}
	$id = \attachment_url_to_postid( $url );
	if ( $id ) {
		return $id;
	}
	$full_url = preg_replace( '/(-\d+x\d+)(\.[^.]+){0,1}$/i', '${2}', $url );
	if ( $url === $full_url ) {
		return null;
	}
	return \attachment_url_to_postid( $full_url );
}

/**
 * Scrapes the first image src from post contents.
 *
 * @access private
 *
 * @param \WP_Post|array|null $post (Optional) Post ID or post object. Default global $post.
 * @return string URL if the first image is found, or '';
 */
function _scrape_first_image_src( $post = null ): string {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $ms );
	if ( empty( $ms[1][0] ) ) {
		return '';
	}
	$src = $ms[1][0];
	return $src;
}
