<?php

if ( ! function_exists( 'mcd_get_violation_wp_query' ) ) :
/**
 * Get the violations that are currently logged in the form of a WP_Query.
 *
 * @since  1.1.0.
 *
 * @param  int         $num    The number of violations to query.
 * @return WP_Query            The WP_Query containing the violations
 */
function mcd_get_violation_wp_query( $num = 999 ) {
	// Determine number of violations to display
	$num = ( ! empty( $num ) ) ? intval( $num ) : 999; // Use intval to allow -1 if desired

	// Query for all violations
	$violations = new WP_Query( array(
		'post_type' => 'csp-report',
		'posts_per_page' => $num,
		'no_found_rows' => true,
	) );

	return $violations;
}
endif;

if ( ! function_exists( 'mcd_get_violation_data' ) ) :
/**
 * Return CSP Report data in an easy to use manner.
 *
 * Note that none of the returned data is escaped. Since the data will need to be escaped depending on the
 * particular situation in which is it used, it is the responsibility of the caller to handle escaping.
 *
 * Returned data is in the form of:
 *
 *   array(
 *     'blocked-uri'        => '',
 *     'document-uri'       => '',
 *     'referrer'           => '',
 *     'violated-directive' => '',
 *     'original-policy'    => '',
 *     'resolved'           => '',
 *   )
 *
 * @since  1.1.0.
 *
 * @param  int      $num    The number of violations to get.
 * @return array            The data for the violations.
 */
function mcd_get_violation_data( $num = 999 ) {
	// Set a data collector
	$data = array();

	// Query for the violations
	$violation_wp_query = mcd_get_violation_wp_query( $num );

	// Package up the important data
	if ( $violation_wp_query->have_posts() ) {
		while ( $violation_wp_query->have_posts() ) {
			$violation_wp_query->the_post();

			$referrer = get_post_meta( get_the_ID(), 'document-uri', true );
			$referrer = ( ! empty( $referrer ) ) ? $referrer : __( 'N/A', 'zdt-mcd' );

			$v_directive = get_post_meta( get_the_ID(), 'violated-directive', true );
			$v_directive = ( ! empty( $v_directive ) ) ? $v_directive : __( 'N/A', 'zdt-mcd' );

			$original_policy = get_post_meta( get_the_ID(), 'original-policy', true );
			$original_policy = ( ! empty( $original_policy ) ) ? $original_policy : __( 'N/A', 'zdt-mcd' );

			$data[ get_the_ID() ] = array(
				'id'                 => get_the_ID(),
				'blocked-uri'        => get_the_title(),
				'document-uri'       => get_post_meta( get_the_ID(), 'document-uri', true ),
				'referrer'           => $referrer,
				'violated-directive' => $v_directive,
				'original-policy'    => $original_policy,
				'resolved'           => absint( get_post_meta( get_the_ID(), 'resolved', true ) ),
			);
		}
	}

	wp_reset_postdata();

	return $data;
}
endif;

if ( ! function_exists( 'mcd_mark_all_violations_resolved' ) ) :
/**
 * Mark all CSP Reports as resolved.
 *
 * @since  1.1.0.
 *
 * @return int    The number of posts resolved.
 */
function mcd_mark_all_violations_resolved() {
	$violation_data = mcd_get_violation_data( -1 );
	$resolutions    = 0;

	foreach ( $violation_data as $post_id => $data ) {
		if ( false !== mcd_mark_violation_resolved( $post_id ) ) {
			$resolutions++;
		}
	}

	return $resolutions;
}
endif;

if ( ! function_exists( 'mcd_mark_violation_resolved' ) ) :
/**
 * Mark a single CSP Report as resolved.
 *
 * @since  1.1.0.
 *
 * @param  int                   $id    The ID of the report to resolve.
 * @return array|bool|WP_Post           The result of the resolution.
 */
function mcd_mark_violation_resolved( $id ) {
	return update_post_meta( $id, 'resolved', 1 );
}
endif;