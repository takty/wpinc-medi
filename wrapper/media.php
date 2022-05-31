<?php
/**
 * Media
 *
 * @package Sample
 * @author Takuto Yanagida
 * @version 2022-05-31
 */

namespace sample;

require_once __DIR__ . '/medi/image.php';
require_once __DIR__ . '/medi/shortcode.php';
require_once __DIR__ . '/medi/size.php';
require_once __DIR__ . '/medi/pdf-support.php';
require_once __DIR__ . '/medi/svg-support.php';

/**
 * Displays an HTML img element of post thumbnail image.
 *
 * @param \WP_Post|array|null $post     (Optional) Post ID or post object. Default global $post.
 * @param string|int[]        $size     (Optional) Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'large'.
 * @param string              $meta_key (Optional) Meta key.
 */
function the_thumbnail_image( $post = null, $size = 'large', string $meta_key = '_thumbnail_id' ): void {
	\wpinc\medi\the_thumbnail_image( $post, $size, $meta_key );
}

/**
 * Displays an HTML figure element of post thumbnail image.
 *
 * @param \WP_Post|array|null $post     (Optional) Post ID or post object. Default global $post.
 * @param string|int[]        $size     (Optional) Image size. Accepts any registered image size name, or an array of width and height values in pixels (in that order). Default 'large'.
 * @param string              $meta_key (Optional) Meta key.
 */
function the_thumbnail_figure( $post = null, $size = 'large', string $meta_key = '_thumbnail_id' ): void {
	\wpinc\medi\the_thumbnail_figure( $post, $size, $meta_key );
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
	return \wpinc\medi\get_the_thumbnail_image( $post, $size, $meta_key );
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
	return \wpinc\medi\get_the_thumbnail_figure( $post, $size, $meta_key );
}

/**
 * Retrieves thumbnail image ID
 *
 * @param \WP_Post|array|null $post     (Optional) Post ID or post object. Default global $post.
 * @param string              $meta_key (Optional) Post meta key.
 * @return int|null Attachment ID if the thumbnail is found, or null.
 */
function get_thumbnail_id( $post = null, string $meta_key = '_thumbnail_id' ): ?int {
	return \wpinc\medi\get_thumbnail_id( $post, $meta_key );
}

/**
 * Retrieves attachment ID of the first image src from post contents.
 *
 * @param \WP_Post|array|null $post (Optional) Post ID or post object. Default global $post.
 * @return int|null Attachment ID if the image is found, or null.
 */
function get_first_image_id( $post = null ): ?int {
	return \wpinc\medi\get_first_image_id( $post );
}

/**
 * Retrieves attachment ID from URL.
 *
 * @param string $url URL of an attachment.
 * @return int|null Attachment ID if the attachment is found, or null.
 */
function url_to_attachment_id( string $url ): ?int {
	return \wpinc\medi\url_to_attachment_id( $url );
}


// -----------------------------------------------------------------------------


/**
 * Adds shortcode for YouTube movies.
 */
function add_youtube_shortcode(): void {
	\wpinc\medi\add_youtube_shortcode();
}

/**
 * Adds shortcode for Vimeo movies.
 */
function add_vimeo_shortcode(): void {
	\wpinc\medi\add_vimeo_shortcode();
}

/**
 * Adds shortcode for Instagram.
 */
function add_instagram_shortcode(): void {
	\wpinc\medi\add_instagram_shortcode();
}

/**
 * Adds shortcode for Google Calendars.
 */
function add_google_calendar_shortcode() {
	\wpinc\medi\add_google_calendar_shortcode();
}


// -----------------------------------------------------------------------------


/**
 * Updates Image Size Options.
 */
function update_image_size_options(): void {
	\wpinc\medi\update_image_size_options();
}

/**
 * Adds Custom Image Sizes.
 */
function add_custom_image_sizes(): void {
	\wpinc\medi\add_custom_image_sizes();
}


// -----------------------------------------------------------------------------


/**
 * Enables PDFs as post thumbnails.
 *
 * @param string $url_to (Optional) URL to the script.
 */
function enable_pdf_post_thumbnail( ?string $url_to = null ): void {
	\wpinc\medi\enable_pdf_post_thumbnail( $url_to );
}


// -----------------------------------------------------------------------------


/**
 * Enables SVG file supports.
 */
function enable_svg_support(): void {
	\wpinc\medi\enable_svg_support();
}
