<?php
/**
 * Top-level admin class: menu registration and asset enqueueing.
 *
 * @package ECFI_CF_Workflow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the CF Workflow admin menu and enqueues admin-only assets.
 */
class ECFI_CFW_Admin {

	/**
	 * Hook suffix returned by add_submenu_page() for the Collaborators screen.
	 *
	 * @var string
	 */
	private string $collaborators_hook = '';

	/**
	 * Sets up WordPress hooks. Only runs in the admin context.
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Registers the top-level CF Workflow menu and the Collaborators submenu.
	 *
	 * @return void
	 */
	public function register_menus(): void {
		add_menu_page(
			__( 'CF Workflow', 'ecfi-cf-workflow' ),
			__( 'CF Workflow', 'ecfi-cf-workflow' ),
			'manage_options',
			'ecfi-cfw',
			array( $this, 'render_top_level' ),
			'dashicons-groups',
			30
		);

		$this->collaborators_hook = add_submenu_page(
			'ecfi-cfw',
			__( 'Collaborators', 'ecfi-cf-workflow' ),
			__( 'Collaborators', 'ecfi-cf-workflow' ),
			'manage_options',
			'ecfi-cfw-collaborators',
			array( $this, 'render_collaborators_page' )
		);
	}

	/**
	 * Redirects the top-level menu click straight to the Collaborators screen.
	 *
	 * @return void
	 */
	public function render_top_level(): void {
		wp_redirect( admin_url( 'admin.php?page=ecfi-cfw-collaborators' ) );
		exit;
	}

	/**
	 * Instantiates the collaborators page controller and renders it.
	 *
	 * @return void
	 */
	public function render_collaborators_page(): void {
		( new ECFI_CFW_Collaborators_Page() )->render();
	}

	/**
	 * Enqueues admin CSS and JS only on the Collaborators screen.
	 *
	 * @param string $hook Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_assets( string $hook ): void {
		if ( $hook !== $this->collaborators_hook ) {
			return;
		}

		wp_enqueue_style(
			'ecfi-cfw-admin',
			ECFI_CF_WORKFLOW_URL . 'assets/admin.css',
			array(),
			ECFI_CF_WORKFLOW_VERSION
		);

		wp_enqueue_script(
			'ecfi-cfw-admin',
			ECFI_CF_WORKFLOW_URL . 'assets/admin.js',
			array(),
			ECFI_CF_WORKFLOW_VERSION,
			true
		);

		wp_localize_script(
			'ecfi-cfw-admin',
			'ecfiCfwAdmin',
			array(
				'editBaseUrl'    => home_url( '/edit/' ),
				'confirmRevoke'  => __( 'Are you sure you want to revoke this collaborator?', 'ecfi-cf-workflow' ),
				'confirmRegen'   => __( 'Are you sure? All existing share links for this collaborator will stop working immediately.', 'ecfi-cf-workflow' ),
				'copiedText'     => __( 'Copied!', 'ecfi-cf-workflow' ),
			)
		);
	}
}
