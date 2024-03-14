<?php
/**
 * Shortcode
 *
 * @package Wpinc Medi
 * @author Takuto Yanagida
 * @version 2024-03-14
 */

declare(strict_types=1);

namespace wpinc\medi;

/**
 * Adds shortcode for YouTube movies.
 */
function add_youtube_shortcode(): void {
	if ( ! is_admin() ) {
		add_shortcode( 'youtube', '\wpinc\medi\_sc_youtube' );
	}
}

/**
 * Adds shortcode for Vimeo movies.
 */
function add_vimeo_shortcode(): void {
	if ( ! is_admin() ) {
		add_shortcode( 'vimeo', '\wpinc\medi\_sc_vimeo' );
	}
}

/**
 * Adds shortcode for Instagram.
 */
function add_instagram_shortcode(): void {
	if ( ! is_admin() ) {
		add_shortcode( 'instagram', '\wpinc\medi\_sc_instagram' );
		add_action( 'wp_enqueue_scripts', '\wpinc\medi\_cb_wp_enqueue_scripts__instagram_shortcode', 10, 0 );
	}
}

/**
 * Adds shortcode for Google calendars.
 */
function add_google_calendar_shortcode(): void {
	if ( ! is_admin() ) {
		add_shortcode( 'google_calendar', '\wpinc\medi\_sc_google_calendar' );
		add_shortcode( 'gcal', '\wpinc\medi\_sc_google_calendar' );
	}
}


// -----------------------------------------------------------------------------


/**
 * Callback function for shortcode 'youtube'.
 *
 * @access private
 *
 * @param array<string, string>|string $atts Attributes.
 * @return string Result of the shortcode.
 */
function _sc_youtube( $atts ): string {
	return _make_video_frame(
		$atts,
		'<iframe class="wpinc-medi-youtube" src="https://www.youtube.com/embed/%s" width="%s" height="%s" frameborder="0" allow="autoplay;encrypted-media;fullscreen;picture-in-picture"></iframe>'
	);
}

/**
 * Callback function for shortcode 'vimeo'.
 *
 * @access private
 *
 * @param array<string, string>|string $atts Attributes.
 * @return string Result of the shortcode.
 */
function _sc_vimeo( $atts ): string {
	return _make_video_frame(
		$atts,
		'<iframe class="wpinc-medi-vimeo" src="https://player.vimeo.com/video/%s" width="%s" height="%s" frameborder="0" allow="autoplay;fullscreen"></iframe>'
	);
}

/**
 * Makes video frame.
 *
 * @access private
 *
 * @param array<string, string>|string $atts Attributes.
 * @param string                       $tag  Iframe tag.
 * @return string Video frame markup.
 */
function _make_video_frame( $atts, string $tag ): string {
	$atts = shortcode_atts(
		array(
			'id'     => '',
			'width'  => '',
			'aspect' => '16:9',
		),
		(array) $atts
	);
	if ( '' === $atts['id'] ) {
		return '';
	}
	list( $w, $h ) = _extract_aspect_size( $atts['aspect'] );

	ob_start();
	if ( is_string( $atts['width'] ) && '' !== $atts['width'] ) {  // Check for non-empty-string.
		printf( "<div style=\"max-width:%spx\">\n", esc_attr( $atts['width'] ) );
		printf( "\t$tag\n", esc_attr( $atts['id'] ), esc_attr( (string) $w ), esc_attr( (string) $h ) );  // phpcs:ignore
		echo "</div>\n";
	} else {
		printf( "$tag\n", esc_attr( $atts['id'] ), esc_attr( (string) $w ), esc_attr( (string) $h ) );  // phpcs:ignore
	}
	return (string) ob_get_clean();
}

/**
 * Extracts aspect size.
 *
 * @access private
 *
 * @param string $aspect Aspect ration attribute.
 * @param int    $base   Base width.
 * @return int[] Array of width and height.
 */
function _extract_aspect_size( string $aspect, int $base = 1920 ): array {
	$as = array( 16, 9 );
	if ( '' !== $aspect ) {
		$ts = explode( ':', $aspect );
		if ( count( $ts ) === 2 ) {
			$w = (float) $ts[0];
			$h = (float) $ts[1];
			if ( 0.0 !== $w && 0.0 !== $h ) {
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
 * @param array<string, string>|string $atts Attributes.
 * @return string Result of the shortcode.
 */
function _sc_instagram( $atts ): string {
	$atts = shortcode_atts(
		array(
			'url'   => '',
			'width' => '',
		),
		(array) $atts
	);

	$is = 'max-width:99.5%;min-width:300px;width:calc(100% - 2px);display:none;';
	$ls = array(
		'<blockquote class="instagram-media" data-instgrm-version="12" style="%s">',
		"\t<a href=\"%s\"></a>",
		'</blockquote>',
	);
	ob_start();
	if ( is_string( $atts['width'] ) && '' !== $atts['width'] ) {  // Check for non-empty-string.
		printf( "<div style=\"max-width:%spx\">\n", esc_attr( $atts['width'] ) );
		echo "\t<style>iframe.instagram-media{min-width:initial!important;}</style>\n";
		printf( "\t" . implode( "\n\t", $ls ) . "\n", esc_attr( $is ), esc_url( $atts['url'] ) );  // phpcs:ignore
		echo "</div>\n";
	} else {
		printf( implode( "\n", $ls ) . "\n", esc_attr( $is ), esc_url( $atts['url'] ) );  // phpcs:ignore
	}
	return (string) ob_get_clean();
}

/**
 * Callback function for 'wp_enqueue_scripts' action.
 *
 * @access private
 * @global \WP_Post|null $post
 */
function _cb_wp_enqueue_scripts__instagram_shortcode(): void {
	global $post;
	if ( $post && has_shortcode( $post->post_content, 'instagram' ) ) {
		wp_enqueue_script( 'instagram', '//platform.instagram.com/en_US/embeds.js', array(), '1.0', true );
	}
}


// -----------------------------------------------------------------------------


/**
 * Callback function for shortcode 'google-calendar' and 'gcal'.
 *
 * @access private
 *
 * @param array<string, string>|string $atts Attributes.
 * @return string Result of the shortcode.
 */
function _sc_google_calendar( $atts ): string {
	static $count = 0;

	$atts = array_change_key_case( (array) $atts );

	$mk = array(
		'wkst'   => 'weekstart',
		'hl'     => 'lang',
		'ctz'    => 'timezone',
		'showtz' => 'showtimezone',
	);
	foreach ( $mk as $from => $to ) {
		if ( isset( $atts[ $from ] ) ) {
			$atts[ $to ] = $atts[ $from ];
		}
	}
	if ( in_array( 'responsive', $atts, true ) ) {
		$atts['responsive'] = '1';
	}
	$atts = shortcode_atts(
		array(
			'id'            => '',
			'width'         => '',
			'aspect'        => '1:1',
			'responsive'    => '0',
			'mobilewidth'   => '600',
			'mode'          => 'MONTH',  // One of these: WEEK, MONTH, AGENDA.
			'weekstart'     => '1',      // One of these: 1 (Sun), 2 (Mon), 7 (Sat).
			'lang'          => 'ja',     // en.
			'timezone'      => 'Asia/Tokyo',
			'showtitle'     => '1',
			'shownav'       => '1',
			'showdate'      => '1',
			'showprint'     => '1',
			'showtabs'      => '1',
			'showcalendars' => '1',
			'showtimezone'  => '1',
		),
		$atts
	);
	if ( '' === $atts['id'] ) {
		return '';
	}
	list( $w, $h ) = _extract_aspect_size( $atts['aspect'] );
	$is_responsive = ( '1' === $atts['responsive'] && 'AGENDA' !== $atts['mode'] );

	$frm = '<iframe class="wpinc-medi-gcal" id="wpinc-medi-gcal-%s" src="%s" width="%s" height="%s" frameborder="0" scrolling="no"></iframe>';
	$url = 'https://calendar.google.com/calendar/embed?';

	$qps = array(
		'src'           => $atts['id'],
		'mode'          => $atts['mode'],
		'wkst'          => $atts['weekstart'],
		'hl'            => $atts['lang'],
		'ctz'           => $atts['timezone'],
		'showTz'        => $atts['showtimezone'],
		'showNav'       => $atts['shownav'],
		'showDate'      => $atts['showdate'],
		'showTabs'      => $atts['showtabs'],
		'showPrint'     => $atts['showprint'],
		'showTitle'     => $atts['showtitle'],
		'showCalendars' => $atts['showcalendars'],
	);
	$tag = sprintf( $frm, $count, esc_url( $url . http_build_query( $qps ) ), esc_attr( (string) $w ), esc_attr( (string) $h ) );

	$tag_m = null;
	$sty   = null;
	if ( $is_responsive ) {
		$qps['mode']          = 'AGENDA';
		$qps['showTz']        = '0';
		$qps['showPrint']     = '0';
		$qps['showCalendars'] = '0';

		$tag_m = sprintf( $frm, "m-$count", esc_url( $url . http_build_query( $qps ) ), esc_attr( (string) $w ), esc_attr( (string) $h ) );

		$sty = array(
			"#wpinc-medi-gcal-m-$count { display: none; }",
			'@media screen and (max-width: ' . ( (int) $atts['mobilewidth'] ) . 'px) {',
			"#wpinc-medi-gcal-$count { display: none; }",
			"#wpinc-medi-gcal-m-$count { display: initial; }",
			'}',
		);
	}
	++$count;

	ob_start();
	if ( is_string( $atts['width'] ) && '' !== $atts['width'] ) {  // Check for non-empty-string.
		echo '<div style="max-width:' . esc_attr( $atts['width'] ) . 'px">' . "\n";
		echo "\t$tag\n" . ( is_string( $tag_m ) ? "\t$tag_m\n" : '' );  // phpcs:ignore
		echo "</div>\n";
	} else {
		echo "$tag\n" . ( $is_responsive ? "$tag_m\n" : '' );  // phpcs:ignore
	}
	if ( is_array( $sty ) ) {
		echo '<style>' . esc_html( implode( ' ', $sty ) ) . '</style>';
	}
	return (string) ob_get_clean();
}
