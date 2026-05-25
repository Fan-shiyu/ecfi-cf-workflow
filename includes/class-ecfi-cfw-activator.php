<?php
/**
 * Plugin activation handler.
 *
 * @package ECFI_CF_Workflow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fired during plugin activation.
 */
class ECFI_CFW_Activator {

	/**
	 * Runs all activation routines.
	 *
	 * Creates or upgrades custom database tables and records the activation
	 * event in the audit log.
	 *
	 * @return void
	 */
	public static function activate(): void {
		ECFI_CFW_Database::install();

		// TODO: insert audit log entry for 'system.plugin_activated' once
		// a helper method exists on ECFI_CFW_Database (or a dedicated audit
		// class) that safely runs after the table is guaranteed to exist.
	}
}
