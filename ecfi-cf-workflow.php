<?php
/**
 * Plugin Name: ECFI CF Workflow
 * Plugin URI:  https://github.com/felicityfan/ecfi-cf-workflow
 * Description: Collaborative data update workflow for the ECFI Atlas community foundation directory.
 * Version:     0.1.0
 * Author:      Fan Shiyu
 * Author URI:  https://github.com/felicityfan
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: ecfi-cf-workflow
 * Domain Path: /languages
 *
 * @package ECFI_CF_Workflow
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants.
define( 'ECFI_CF_WORKFLOW_VERSION', '0.1.0' );
define( 'ECFI_CF_WORKFLOW_DIR', plugin_dir_path( __FILE__ ) );
define( 'ECFI_CF_WORKFLOW_URL', plugin_dir_url( __FILE__ ) );

require_once ECFI_CF_WORKFLOW_DIR . 'includes/class-ecfi-cfw-database.php';
require_once ECFI_CF_WORKFLOW_DIR . 'includes/class-ecfi-cfw-activator.php';
require_once ECFI_CF_WORKFLOW_DIR . 'includes/class-ecfi-cfw-collaborator.php';
require_once ECFI_CF_WORKFLOW_DIR . 'includes/class-ecfi-cfw-audit-log.php';
require_once ECFI_CF_WORKFLOW_DIR . 'admin/class-ecfi-cfw-admin.php';
require_once ECFI_CF_WORKFLOW_DIR . 'admin/class-ecfi-cfw-collaborators-page.php';
require_once ECFI_CF_WORKFLOW_DIR . 'admin/class-ecfi-cfw-collaborators-list-table.php';

/**
 * Runs on plugin activation.
 *
 * @return void
 */
function ecfi_cf_workflow_activate(): void {
	ECFI_CFW_Activator::activate();
}
register_activation_hook( __FILE__, 'ecfi_cf_workflow_activate' );

/**
 * Runs on plugin deactivation.
 *
 * @return void
 */
function ecfi_cf_workflow_deactivate(): void {
	// Data is intentionally preserved on deactivation.
}
register_deactivation_hook( __FILE__, 'ecfi_cf_workflow_deactivate' );

add_action(
	'plugins_loaded',
	static function () {
		new ECFI_CFW_Admin();
	}
);
