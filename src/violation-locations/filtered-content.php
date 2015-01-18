<?php

class MCD_Violation_Location_Filtered_Content extends MCD_Violation_Location_Base {
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
	 * Determine if the blocked URI is present in the content.
	 *
	 * @since  1.2.0.
	 *
	 * @param  array    $violation    The violation data.
	 * @return bool                   True if the URI was found; false if it was not.
	 */
	public function result( $violation ) {
		// Ensure that the content can even be searched first
		if ( false !== $this->_is_content_searchable( $violation ) ) {
			// Get the content for searching
			$post_id = $this->_get_post_id( $this->_get_violation_part( $violation, 'document-uri' ) );
			$content = $this->_get_post_content( $post_id );

			// Search for the blocked URI in the content
			$blocked_uri  = $this->_get_violation_part( $violation, 'blocked-uri' );
			$uri_variants = $this->_get_uri_variants( $blocked_uri );

			// Search for each variant. If any one variant matches, return true.
			foreach ( $uri_variants as $variant ) {
				if ( ! empty( $variant ) ) {
					if ( false !== strpos( $content, $variant ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get a post ID from a URL.
	 *
	 * This is merely a wrapper for url_to_postid and is added in case we want to do something else to find the ID in
	 * the future.
	 *
	 * @since  1.2.0.
	 *
	 * @param  string    $url    URL of the resource to find the ID for.
	 * @return int               Post ID corresponding to the URL.
	 */
	private function _get_post_id( $url ) {
		return url_to_postid( $url );
	}

	/**
	 * Get a posts's unfiltered content.
	 *
	 * @since  1.2.0.
	 *
	 * @param  int       $id    The post ID.
	 * @return string           The post content.
	 */
	private function _get_post_content( $id ) {
		$post    = get_post( $id );
		$content = '';

		if ( ! is_null( $post ) && ! empty( $post->post_content ) ) {
			$content = $post->post_content;
			$content = apply_filters( 'the_content', $content );
			$content = str_replace( ']]>', ']]&gt;', $content );
		}

		return $content;
	}

	/**
	 * Determine if content is searchable for a particular violation.
	 *
	 * @since  1.2.0.
	 *
	 * @param  array    $violation    Collection of violation data.
	 * @return bool                   True if content is searchable; false if it is not.
	 */
	private function _is_content_searchable( $violation ) {
		/**
		 * Only if the document URI and the referrer are identical can we search for content. If the referrer is
		 * different than the document URI, we know that something else in the document loaded the content, ruling out
		 * the source of the violation coming from the content.
		 */
		return ( $this->_get_violation_part( $violation, 'document-uri' ) === $this->_get_violation_part( $violation, 'referrer' ) );
	}
}