<?php
/**
 * Data access layer for the cf_collaborators table.
 *
 * @package ECFI_CF_Workflow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Static CRUD methods for the wp_cf_collaborators table.
 */
class ECFI_CFW_Collaborator {

	const ALLOWED_ORDERBY = array( 'name', 'email', 'status', 'created_at' );

	/**
	 * Returns all collaborators, with optional filtering, sorting, and pagination.
	 *
	 * @param array $args {
	 *   @type string $orderby  Column to sort by. Default 'created_at'.
	 *   @type string $order    Sort direction: ASC or DESC. Default 'DESC'.
	 *   @type int    $per_page Rows per page. Default 20.
	 *   @type int    $paged    Page number (1-based). Default 1.
	 *   @type string $status   Filter by status: 'active', 'revoked', or 'all'. Default 'all'.
	 * }
	 * @return object[]
	 */
	public static function get_all( array $args = array() ): array {
		global $wpdb;

		$orderby  = in_array( $args['orderby'] ?? '', self::ALLOWED_ORDERBY, true )
			? $args['orderby'] : 'created_at';
		$order    = strtoupper( $args['order'] ?? 'DESC' ) === 'ASC' ? 'ASC' : 'DESC';
		$per_page = max( 1, (int) ( $args['per_page'] ?? 20 ) );
		$paged    = max( 1, (int) ( $args['paged'] ?? 1 ) );
		$offset   = ( $paged - 1 ) * $per_page;
		$status   = $args['status'] ?? 'all';

		$table = ECFI_CFW_Database::get_table_name( 'collaborators' );

		if ( in_array( $status, array( 'active', 'revoked' ), true ) ) {
			$sql = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM `{$table}` WHERE status = %s ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
				$status,
				$per_page,
				$offset
			);
		} else {
			$sql = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM `{$table}` ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
				$per_page,
				$offset
			);
		}

		return $wpdb->get_results( $sql ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Returns the total number of collaborators, optionally filtered by status.
	 *
	 * @param string $status 'active', 'revoked', or 'all'.
	 * @return int
	 */
	public static function count_all( string $status = 'all' ): int {
		global $wpdb;

		$table = ECFI_CFW_Database::get_table_name( 'collaborators' );

		if ( in_array( $status, array( 'active', 'revoked' ), true ) ) {
			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM `{$table}` WHERE status = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$status
				)
			);
		}

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Returns a single collaborator by primary key, or null if not found.
	 *
	 * @param int $id Row ID.
	 * @return object|null
	 */
	public static function get_by_id( int $id ): ?object {
		global $wpdb;

		$table = ECFI_CFW_Database::get_table_name( 'collaborators' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$id
			)
		) ?: null;
	}

	/**
	 * Returns a single collaborator by token, or null if not found.
	 *
	 * @param string $token 64-char hex token.
	 * @return object|null
	 */
	public static function get_by_token( string $token ): ?object {
		global $wpdb;

		$table = ECFI_CFW_Database::get_table_name( 'collaborators' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE token = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$token
			)
		) ?: null;
	}

	/**
	 * Creates a new collaborator and returns the new row ID, or false on failure.
	 *
	 * @param array $data Keys: name, email, position, organisation_name, notes, created_by.
	 * @return int|false
	 */
	public static function create( array $data ): int|false {
		global $wpdb;

		$table = ECFI_CFW_Database::get_table_name( 'collaborators' );
		$token = bin2hex( random_bytes( 32 ) );

		$result = $wpdb->insert(
			$table,
			array(
				'token'             => $token,
				'name'              => $data['name'] ?? '',
				'email'             => $data['email'] ?? '',
				'position'          => $data['position'] ?? null,
				'organisation_name' => $data['organisation_name'] ?? null,
				'notes'             => $data['notes'] ?? null,
				'created_by'        => $data['created_by'] ?? null,
				'status'            => 'active',
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
		);

		return $result !== false ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Updates editable fields for an existing collaborator. Never touches the token.
	 *
	 * @param int   $id   Row ID.
	 * @param array $data Keys: name, email, position, organisation_name, notes.
	 * @return bool
	 */
	public static function update( int $id, array $data ): bool {
		global $wpdb;

		$table = ECFI_CFW_Database::get_table_name( 'collaborators' );

		return $wpdb->update(
			$table,
			array(
				'name'              => $data['name'] ?? '',
				'email'             => $data['email'] ?? '',
				'position'          => $data['position'] ?? null,
				'organisation_name' => $data['organisation_name'] ?? null,
				'notes'             => $data['notes'] ?? null,
			),
			array( 'id' => $id ),
			array( '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		) !== false;
	}

	/**
	 * Sets the status of a collaborator to 'active' or 'revoked'.
	 *
	 * @param int    $id     Row ID.
	 * @param string $status 'active' or 'revoked'.
	 * @return bool
	 */
	public static function set_status( int $id, string $status ): bool {
		global $wpdb;

		if ( ! in_array( $status, array( 'active', 'revoked' ), true ) ) {
			return false;
		}

		$table = ECFI_CFW_Database::get_table_name( 'collaborators' );

		return $wpdb->update(
			$table,
			array( 'status' => $status ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		) !== false;
	}

	/**
	 * Generates a new token for a collaborator, invalidating the old one.
	 *
	 * @param int $id Row ID.
	 * @return string|false New token on success, false on failure.
	 */
	public static function regenerate_token( int $id ): string|false {
		global $wpdb;

		$token = bin2hex( random_bytes( 32 ) );
		$table = ECFI_CFW_Database::get_table_name( 'collaborators' );

		$result = $wpdb->update(
			$table,
			array( 'token' => $token ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);

		return $result !== false ? $token : false;
	}

	/**
	 * Deletes a collaborator row. Prefer set_status('revoked') for safety.
	 *
	 * @param int $id Row ID.
	 * @return bool
	 */
	public static function delete( int $id ): bool {
		global $wpdb;

		$table = ECFI_CFW_Database::get_table_name( 'collaborators' );

		return $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) ) !== false;
	}
}
