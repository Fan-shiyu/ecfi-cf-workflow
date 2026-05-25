<?php
/**
 * Database schema definition and management.
 *
 * @package ECFI_CF_Workflow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles creation and versioning of all custom plugin tables.
 */
class ECFI_CFW_Database {

	const SCHEMA_VERSION    = '1.0.0';
	const VERSION_OPTION    = 'ecfi_cfw_schema_version';

	/**
	 * Returns the schema version this code targets.
	 *
	 * @return string
	 */
	public static function get_schema_version(): string {
		return self::SCHEMA_VERSION;
	}

	/**
	 * Returns the schema version currently stored in wp_options.
	 *
	 * @return string Empty string if never installed.
	 */
	public static function get_installed_version(): string {
		return (string) get_option( self::VERSION_OPTION, '' );
	}

	/**
	 * Returns the prefixed table name for a given key.
	 *
	 * @param string $key One of: collaborators, collaborator_foundations, pending_changes, audit_log.
	 * @return string
	 */
	public static function get_table_name( string $key ): string {
		global $wpdb;
		$map = array(
			'collaborators'            => 'cf_collaborators',
			'collaborator_foundations' => 'cf_collaborator_foundations',
			'pending_changes'          => 'cf_pending_changes',
			'audit_log'                => 'cf_audit_log',
		);
		return isset( $map[ $key ] ) ? $wpdb->prefix . $map[ $key ] : '';
	}

	/**
	 * Runs dbDelta for all tables and updates the stored schema version.
	 *
	 * @return void
	 */
	public static function install(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = "DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

		$collaborators = $wpdb->prefix . 'cf_collaborators';
		$foundations   = $wpdb->prefix . 'cf_collaborator_foundations';
		$pending       = $wpdb->prefix . 'cf_pending_changes';
		$audit         = $wpdb->prefix . 'cf_audit_log';

		$sql = array();

		$sql[] = "CREATE TABLE {$collaborators} (
			id                BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			token             VARCHAR(64) NOT NULL,
			name              VARCHAR(255) NOT NULL,
			email             VARCHAR(255) NOT NULL,
			position          VARCHAR(255) DEFAULT NULL,
			organisation_name VARCHAR(255) DEFAULT NULL,
			status            ENUM('active', 'revoked') NOT NULL DEFAULT 'active',
			created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			last_used_at      DATETIME DEFAULT NULL,
			created_by        BIGINT(20) UNSIGNED DEFAULT NULL,
			notes             TEXT DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY token (token),
			KEY email (email),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$foundations} (
			id                BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			collaborator_id   BIGINT(20) UNSIGNED NOT NULL,
			foundation_id     BIGINT(20) UNSIGNED NOT NULL,
			assigned_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			assigned_by       BIGINT(20) UNSIGNED DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY collaborator_foundation (collaborator_id, foundation_id),
			KEY collaborator_id (collaborator_id),
			KEY foundation_id (foundation_id)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$pending} (
			id                BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			collaborator_id   BIGINT(20) UNSIGNED NOT NULL,
			foundation_id     BIGINT(20) UNSIGNED NOT NULL,
			submission_id     VARCHAR(36) NOT NULL,
			field_name        VARCHAR(100) NOT NULL,
			field_type        ENUM('acf', 'taxonomy', 'post_field') NOT NULL DEFAULT 'acf',
			old_value         LONGTEXT DEFAULT NULL,
			new_value         LONGTEXT DEFAULT NULL,
			status            ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
			submitted_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			reviewed_at       DATETIME DEFAULT NULL,
			reviewed_by       BIGINT(20) UNSIGNED DEFAULT NULL,
			review_note       TEXT DEFAULT NULL,
			PRIMARY KEY (id),
			KEY collaborator_id (collaborator_id),
			KEY foundation_id (foundation_id),
			KEY submission_id (submission_id),
			KEY status (status),
			KEY submitted_at (submitted_at)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$audit} (
			id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			action          VARCHAR(50) NOT NULL,
			actor_type      ENUM('admin', 'collaborator', 'system') NOT NULL,
			actor_id        BIGINT(20) UNSIGNED DEFAULT NULL,
			actor_name      VARCHAR(255) DEFAULT NULL,
			target_type     VARCHAR(50) DEFAULT NULL,
			target_id       BIGINT(20) UNSIGNED DEFAULT NULL,
			details         LONGTEXT DEFAULT NULL,
			occurred_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY action (action),
			KEY actor_type (actor_type),
			KEY actor_id (actor_id),
			KEY target_type_id (target_type, target_id),
			KEY occurred_at (occurred_at)
		) {$charset_collate};";

		foreach ( $sql as $statement ) {
			dbDelta( $statement );
		}

		update_option( self::VERSION_OPTION, self::SCHEMA_VERSION );
	}
}
