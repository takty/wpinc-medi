<?php
/**
 * Custom Template Tags for Responsive Images
 *
 * @package Wpinc Medi
 * @author Takuto Yanagida
 * @version 2022-01-26
 */

namespace wpinc\medi;

function get_thumbnail_src( $size = 'large', $post_id = false, $meta_key = false ) {
	$tid = get_thumbnail_id( $post_id, $meta_key );
	if ( $tid === false ) {
		return '';
	}
	return get_attachment_src( $size, $tid );
}

function get_first_image_src( $size = 'large' ) {
	$fis = _scrape_first_image_src();
	if ( false === $fis ) {
		return '';
	}
	$aid = get_attachment_id( $fis );
	if ( false === $aid ) {
		return '';
	}
	return get_attachment_src( $size, $aid );
}

function get_attachment_src( $size = 'large', $aid ) {
	$ais = wp_get_attachment_image_src( $aid, $size );
	return false === $ais ? '' : $ais[0];
}

function get_thumbnail_id( $post_id = false, $meta_key = false ) {
	global $post;
	if ( false === $post_id ) {
		if ( ! $post ) {
			return false;
		}
		$post_id = $post->ID;
	}
	if ( false === $meta_key ) {
		if ( ! has_post_thumbnail( $post_id ) ) {
			return false;
		}
		return get_post_thumbnail_id( $post_id );
	}
	$pm = get_post_meta( $post_id, $meta_key, true );
	return empty( $pm ) ? false : $pm;
}

function get_attachment_id( $url ) {
	global $wpdb;
	preg_match( '/([^\/]+?)(-e\d+)?(-\d+x\d+)?(\.\w+)?$/', $url, $matches );
	$guid = str_replace( $matches[0], $matches[1] . $matches[4], $url );
	$v    = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE guid = %s", $guid ) );
	return null === $v ? false : ( (int) $v );
}

function get_first_image_id() {
	$fis = get_first_image_src();
	if ( false === $fis ) {
		return false;
	}
	$aid = get_attachment_id( $fis );
	if ( false === $aid ) {
		return false;
	}
	return $aid;
}

function _scrape_first_image_src() {
	if ( ! is_singular() ) {
		return false;
	}
	global $post;
	preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $ms );
	if ( empty( $ms[1][0] ) ) {
		return false;
	}
	$src = $ms[1][0];
	return $src;
}


// -----------------------------------------------------------------------------


function the_thumbnail_image( $size = 'large', $post_id = false, $meta_key = false ) {
	echo get_the_thumbnail_image( $size, $post_id, $meta_key );
}

function the_thumbnail_figure( $size = 'large', $post_id = false, $meta_key = false ) {
	echo get_the_thumbnail_figure( $size, $post_id, $meta_key );
}

function get_the_thumbnail_image( $size = 'large', $post_id = false, $meta_key = false ) {
	$tid = get_thumbnail_id( $post_id, $meta_key );
	if ( false === $tid ) {
		return '';
	}
	$ais = wp_get_attachment_image_src( $tid, $size );
	if ( false === $ais ) {
		return '';
	}
	$src = esc_attr( $ais[0] );
	return "<img class=\"size-$size\" src=\"$src\" alt=\"\" width=\"$ais[1]\" height=\"$ais[2]\">";
}

function get_the_thumbnail_figure( $size = 'large', $post_id = false, $meta_key = false ) {
	$tid = get_thumbnail_id( $post_id, $meta_key );
	if ( false === $tid ) {
		return '';
	}
	$ais = wp_get_attachment_image_src( $tid, $size );
	if ( false === $ais ) {
		return '';
	}
	$src = esc_attr( $ais[0] );
	$img = "<img class=\"size-$size\" src=\"$src\" alt=\"\" width=\"$ais[1]\" height=\"$ais[2]\">";

	$p   = get_post( $tid );
	$exc = empty( $p ) ? '' : $p->post_excerpt;
	return "<figure class=\"wp-caption\">$img<figcaption class=\"wp-caption-text\">$exc</figcaption></figure>";
}
