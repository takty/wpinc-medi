<?php
/**
 * PDF Support
 *
 * @package Wpinc Medi
 * @author Takuto Yanagida
 * @version 2022-03-04
 */

namespace wpinc\medi;

require_once __DIR__ . '/assets/asset-url.php';

/**
 * Enables PDFs as post thumbnails.
 *
 * @param string $url_to (Optional) URL to the script.
 */
function enable_pdf_post_thumbnail( ?string $url_to = null ): void {
	if ( ! is_admin() ) {
		return;
	}
	add_filter( 'ajax_query_attachments_args', '\wpinc\medi\_cb_ajax_query_attachments_args', 11 );

	$url_to = untrailingslashit( $url_to ?? \wpinc\get_file_uri( __DIR__ ) );
	add_action(
		'admin_enqueue_scripts',
		function () use ( $url_to ) {
			_cb_admin_enqueue_scripts( $url_to );
		}
	);
}

/**
 * Callback function for 'ajax_query_attachments_args' filter.
 *
 * @access private
 *
 * @param array $query Query.
 * @return array Query.
 */
function _cb_ajax_query_attachments_args( array $query ): array {
	if ( 'image_pdf' === ( $query['post_mime_type'] ?? '' ) ) {
		$query['post_mime_type'] = array( 'image', 'application/pdf' );
	}
	return $query;
}

/**
 * Callback function for 'admin_enqueue_scripts' actions.
 *
 * @access private
 *
 * @param string $url_to URL to the script.
 */
function _cb_admin_enqueue_scripts( string $url_to ) {
	wp_enqueue_script( 'wpinc-medi-pdf-support', \wpinc\abs_url( $url_to, './assets/js/pdf-support.min.js' ), array(), '1.0', true );

	$translations = array(
		'label_image_pdf'      => __( 'Image' ) . ' & ' . __( 'PDF' ),
		'label_image'          => __( 'Image' ),
		'label_featured_image' => __( 'Featured Image' ),
	);
	wp_localize_script( 'wpinc-medi-pdf-support', 'wpinc_medi_pdf_support', $translations );
}
