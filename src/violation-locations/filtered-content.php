<?php

class MCD_Violation_Location_Filtered_Content extends MCD_Violation_Location_Content_Base {
	/**
	 * The ID of the violation location.
	 *
	 * @since  1.2.0.
	 *
	 * @return string    The ID for the violation location.
	 */
	public function get_location_id() {
		return 'mcd-filtered-content';
	}

	/**
	 * The name of the violation location.
	 *
	 * @since  1.2.0.
	 *
	 * @return string    The name for the violation location.
	 */
	public function get_location_name() {
		return __( 'Filtered Content', 'zdt-mcd' );
	}

	/**
	 * The hint for the violation location.
	 *
	 * @since  1.2.0.
	 *
	 * @return string    The hint for the violation location.
	 */
	public function get_location_hint() {
		return __( 'The violation report originated from filtered post content. This violation was flagged only after the post content was filtered. A "the_content" filter most likely produced this violation report.', 'zdt-mcd' );
	}

	/**
	 * Get a posts's unfiltered content.
	 *
	 * @since  1.2.0.
	 *
	 * @param  int       $id    The post ID.
	 * @return string           The post content.
	 */
	protected function _get_post_content( $id ) {
		$post    = get_post( $id );
		$content = '';

		if ( ! is_null( $post ) && ! empty( $post->post_content ) ) {
			$content = $post->post_content;
			$content = apply_filters( 'the_content', $content );
			$content = str_replace( ']]>', ']]&gt;', $content );
		}

		return $content;
	}
}