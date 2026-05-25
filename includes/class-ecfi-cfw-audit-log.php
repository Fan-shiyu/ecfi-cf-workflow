<?php
/**
 * Data access layer for the cf_audit_log table.
 *
 * @package ECFI_CF_Workflow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Writes structured audit log entries to wp_cf_audit_log.
 */
class ECFI_CFW_Audit_Log {

	/**
	 * Inserts an audit log entry.
	 *
	 * @param string $action      Action identifier, e.g. 'collaborator.created'.
	 * @param array  $details     Arbitrary key/value context, JSON-encoded in storage.
	 * @param int    $target_id   Primary key of the affected row.
	 * @param string $target_type Table/entity type, default 'collaborator'.
	 * @return bool True on success.
	 */
	public static function log(
		string $action,
		array $details,
		int $target_id,
		string $target_type = 'collaborator'
	): bool {
		global $wpdb;

		$user  = wp_get_current_user();
		$table = ECFI_CFW_Database::get_table_name( 'audit_log' );

		$result = $wpdb->insert(
			$table,
			array(
				'action'      => $action,
				'actor_type'  => 'admin',
				'actor_id'    => $user->ID ?: null,
				'actor_name'  => $user->display_name ?: null,
				'target_type' => $target_type,
				'target_id'   => $target_id,
				'details'     => wp_json_encode( $details ),
				'occurred_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s' )
		);

		return $result !== false;
	}
}
