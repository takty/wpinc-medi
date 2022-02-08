<?php
/**
 * Shortcode
 *
 * @package Wpinc Medi
 * @author Takuto Yanagida
 * @version 2022-02-08
 */

namespace wpinc\medi;

/**
 * Adds shortcode for YouTube movies.
 */
function add_youtube_shortcode() {
	if ( ! is_admin() ) {
		add_shortcode( 'youtube', '\wpinc\medi\_sc_youtube' );
	}
}

/**
 * Adds shortcode for Vimeo movies.
 */
function add_vimeo_shortcode() {
	if ( ! is_admin() ) {
		add_shortcode( 'vimeo', '\wpinc\medi\_sc_vimeo' );
	}
}

/**
 * Adds shortcode for Instagram.
 */
function add_instagram_shortcode() {
	if ( ! is_admin() ) {
		add_shortcode( 'instagram', '\wpinc\medi\_sc_instagram' );
		add_action( 'wp_enqueue_scripts', '\wpinc\medi\_cb_wp_enqueue_scripts__instagram_shortcode' );
	}
}


// -----------------------------------------------------------------------------


/**
 * Callback function for shortcode 'youtube'.
 *
 * @access private
 *
 * @param array $atts Attributes.
 * @return string Result of the shortcode.
 */
function _sc_youtube( array $atts ): string {
	return _make_video_frame(
		$atts,
		'<iframe src="https://www.youtube.com/embed/%s" width="%s" height="%s" frameborder="0" allow="autoplay;encrypted-media;fullscreen;picture-in-picture"></iframe>'
	);
}

/**
 * Callback function for shortcode 'vimeo'.
 *
 * @access private
 *
 * @param array $atts Attributes.
 * @return string Result of the shortcode.
 */
function _sc_vimeo( array $atts ): string {
	return _make_video_frame(
		$atts,
		'<iframe src="https://player.vimeo.com/video/%s" width="%s" height="%s" frameborder="0" allow="autoplay;fullscreen"></iframe>'
	);
}

/**
 * Makes video frame.
 *
 * @access private
 *
 * @param array  $atts Attributes.
 * @param string $tag  Iframe tag.
 * @return string Video frame markup.
 */
function _make_video_frame( array $atts, string $tag ): string {
	$atts = shortcode_atts(
		array(
			'id'     => '',
			'width'  => '',
			'aspect' => '16:9',
		),
		$atts
	);
	if ( empty( $atts['id'] ) ) {
		return '';
	}
	list( $w, $h ) = _extract_aspect_size( $atts['aspect'] );

	ob_start();
	if ( ! empty( $atts['width'] ) ) {
		echo '<div style="max-width:' . esc_attr( $atts['width'] ) . 'px">' . "\n";
	}
	printf( "\t$tag\n", esc_attr( $id ), esc_attr( $w ), esc_attr( $h ) );  // phpcs:ignore
	if ( ! empty( $atts['width'] ) ) {
		echo "</div>\n";
	}
	return ob_get_clean();
}

/**
 * Extracts aspect size.
 *
 * @access private
 *
 * @param string $aspect Aspect ration attribute.
 * @param int    $base   Base width.
 * @return array Array of width and height.
 */
function _extract_aspect_size( string $aspect, int $base = 1920 ): array {
	$as = array( 16, 9 );
	if ( ! empty( $aspect ) ) {
		$ts = explode( ':', $aspect );
		if ( count( $ts ) === 2 ) {
			$w = (float) $ts[0];
			$h = (float) $ts[1];
			if ( 0 !== $w && 0 !== $h ) {
				$as = array( $w, $h );
			}
		}
	}
	return array( $base, (int) ( $base * $as[1] / $as[0] ) );
}


// -----------------------------------------------------------------------------


/**
 * Callback function for shortcode 'instagram'.
 *
 * @access private
 *
 * @param array $atts Attributes.
 * @return string Result of the shortcode.
 */
function _sc_instagram( array $atts ): string {
	$atts = shortcode_atts(
		array(
			'url'   => '',
			'width' => '',
		),
		$atts
	);
	ob_start();
	if ( ! empty( $atts['width'] ) ) {
		echo '<div style="max-width:' . esc_attr( $atts['width'] ) . 'px">' . "\n";
		echo "\t<style>iframe.instagram-media{min-width:initial!important;}</style>\n";
	}
	echo "\t" . '<blockquote class="instagram-media" data-instgrm-version="12" style="max-width:99.5%;min-width:300px;width:calc(100% - 2px);display:none;">' . "\n";
	echo "\t\t" . '<a href="' . esc_url( $url ) . '"></a>' . "\n";
	echo "\t" . '</blockquote>' . "\n";
	if ( ! empty( $atts['width'] ) ) {
		echo "</div>\n";
	}
	return ob_get_clean();
}

/**
 * Callback function for 'wp_enqueue_scripts' action.
 *
 * @access private
 */
function _cb_wp_enqueue_scripts__instagram_shortcode() {
	global $post;
	if ( $post && has_shortcode( $post->post_content, 'instagram' ) ) {
		wp_enqueue_script( 'instagram', '//platform.instagram.com/en_US/embeds.js', array(), 1.0, true );
	}
}
