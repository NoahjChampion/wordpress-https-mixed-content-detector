<?php
if ( ! class_exists( 'MCD_Command' ) ) :
/**
 * Define the "mcd" WP CLI command.
 *
 * @since 1.1.0.
 */
class MCD_Command extends WP_CLI_Command {
	/**
	 * List the violations.
	 *
	 * @since  1.1.0.
	 *
	 * @subcommand list
	 *
	 * @return void
	 */
	public function _list() {
		// Set up check and x marks
		$resolved   = \cli\Colors::colorize( "%G✓%n", true );
		$unresolved = \cli\Colors::colorize( "%R✖%n", true );

		$data = mcd_get_violation_data();

		// Reformat data for table display
		$final_data = array();

		foreach ( $data as $key => $report ) {
			foreach ( $report as $subkey => $value ) {
				if ( 'resolved' === $subkey ) {
					$value = ( 1 === $value ) ? $resolved : $unresolved;
				}

				$final_data[ $key ][] = $value;
			}
		}

		wp_reset_postdata();

		if ( count( $data ) > 0 ) {
			// Display results
			$table = new \cli\Table();

			$table->setHeaders( array(
				__( 'Report ID', 'zdt-mdc' ),
				__( 'Blocked URI', 'zdt-mdc' ),
				__( 'Document URI', 'zdt-mdc' ),
				__( 'Referrer', 'zdt-mdc' ),
				__( 'Violated Directive', 'zdt-mdc' ),
				__( 'Original Policy', 'zdt-mdc' ),
				__( 'Resolved', 'zdt-mdc' ),
			) );

			$table->setRows( $final_data );
			$table->display();
		} else {
			WP_CLI::warning( 'There are no CSP violations logged.' );
		}
	}

	/**
	 * Resolve a single or multiple CSP Reports.
	 *
	 * ## OPTIONS
	 *
	 * [<id>]
	 * : The ID of the CSP Report to resolve.
	 *
	 * [--all]
	 * : Resolve all CSP Reports.
	 *
	 * ## EXAMPLES
	 *
	 *     # Resolve CSP Report with post ID of 35
	 *     wp mcd resolve 35
	 *
	 *     # Remove all CSP Reports
	 *     wp mcd resolve --all
	 *
	 * @since 1.1.0.
	 *
	 * @subcommand resolve
	 *
	 * @param  array    $args          List of arguments passed to command.
	 * @param  array    $assoc_args    List of flags passed to command
	 * @return void
	 */
	public function _resolve( $args, $assoc_args ) {
		$id  = ( ! empty( $args[0] ) ) ? absint( $args[0] ) : 0;
		$all = ( isset( $assoc_args['all'] ) );

		$resolutions = 0;

		if ( 0 !== $id ) {
			if ( false !== mcd_mark_violation_resolved( $id ) ) {
				$resolutions++;
			}
		} elseif ( true === $all ) {
			$resolutions = mcd_mark_all_violations_resolved();
		}

		if ( $resolutions === 1 ) {
			$message = '1 report marked as resolved';
		} else if ( $resolutions > 1 ) {
			$message = absint( $resolutions ) . ' reports marked as resolved';
		} else {
			$message = 'No reports marked as resolved';
		}

		WP_CLI::success( $message );
	}

	/**
	 * Remove a single or multiple CSP Reports.
	 *
	 * ## OPTIONS
	 *
	 * [<id>]
	 * : The ID of the CSP Report to remove.
	 *
	 * [--all]
	 * : Remove all CSP Reports.
	 *
	 * ## EXAMPLES
	 *
	 *     # Resolve CSP Report with post ID of 35
	 *     wp mcd remove 35
	 *
	 *     # Remove all CSP Reports
	 *     wp mcd remove --all
	 *
	 * @since 1.1.0.
	 *
	 * @subcommand remove
	 *
	 * @param  array    $args          List of arguments passed to command.
	 * @param  array    $assoc_args    List of flags passed to command
	 * @return void
	 */
	public function _remove( $args, $assoc_args ) {
		$id  = ( ! empty( $args[0] ) ) ? absint( $args[0] ) : 0;
		$all = ( isset( $assoc_args['all'] ) );

		$removals = 0;

		if ( 0 !== $id ) {
			if ( false !== mcd_remove_violation( $id ) ) {
				$removals++;
			}
		} elseif ( true === $all ) {
			$removals = mcd_remove_all_violations();
		}

		if ( $removals === 1 ) {
			$message = '1 report removed';
		} else if ( $removals > 1 ) {
			$message = absint( $removals ) . ' reports removed';
		} else {
			$message = 'No reports removed';
		}

		WP_CLI::success( $message );
	}
}
endif;

WP_CLI::add_command( 'mcd', 'MCD_Command' );