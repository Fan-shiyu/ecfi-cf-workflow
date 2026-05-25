<?php
/**
 * Controller for the Collaborators admin page.
 *
 * @package ECFI_CF_Workflow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles form submissions, row/bulk actions, and view rendering for the
 * Collaborators screen.
 */
class ECFI_CFW_Collaborators_Page {

	/**
	 * Entry point — called directly by the add_submenu_page() callback.
	 *
	 * POST requests are dispatched and redirected (PRG) before any output
	 * is produced. GET requests render the appropriate view.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'ecfi-cf-workflow' ) );
		}

		// Handle POST before any output (PRG pattern).
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			$action = sanitize_key( $_POST['action'] ?? '' );

			switch ( $action ) {
				case 'save_collaborator':
					$this->handle_save();
					break;
				case 'revoke_collaborator':
					$this->handle_status_change( 'revoked' );
					break;
				case 'reactivate_collaborator':
					$this->handle_status_change( 'active' );
					break;
				case 'regenerate_token':
					$this->handle_regenerate_token();
					break;
				case 'bulk_revoke':
					$this->handle_bulk_revoke();
					break;
			}
		}

		// GET dispatch.
		$get_action = sanitize_key( $_GET['action'] ?? '' );

		switch ( $get_action ) {
			case 'add':
				$this->render_form( null );
				break;
			case 'edit':
				$this->render_form( absint( $_GET['id'] ?? 0 ) );
				break;
			default:
				$this->render_list();
				break;
		}
	}

	// -------------------------------------------------------------------------
	// POST handlers
	// -------------------------------------------------------------------------

	/**
	 * Handles create and update form submissions.
	 *
	 * @return void
	 */
	private function handle_save(): void {
		check_admin_referer( 'ecfi_cfw_collaborator_form' );

		$id   = absint( $_POST['collaborator_id'] ?? 0 );
		$name  = sanitize_text_field( $_POST['name'] ?? '' );
		$email = sanitize_email( $_POST['email'] ?? '' );

		if ( '' === $name || '' === $email ) {
			wp_redirect(
				add_query_arg(
					'ecfi_error',
					'validation',
					$this->list_url()
				)
			);
			exit;
		}

		$data = array(
			'name'              => $name,
			'email'             => $email,
			'position'          => sanitize_text_field( $_POST['position'] ?? '' ),
			'organisation_name' => sanitize_text_field( $_POST['organisation_name'] ?? '' ),
			'notes'             => sanitize_textarea_field( $_POST['notes'] ?? '' ),
		);

		if ( $id > 0 ) {
			ECFI_CFW_Collaborator::update( $id, $data );
			ECFI_CFW_Audit_Log::log( 'collaborator.updated', $data, $id );
		} else {
			$data['created_by'] = get_current_user_id();
			$new_id             = ECFI_CFW_Collaborator::create( $data );
			if ( $new_id ) {
				ECFI_CFW_Audit_Log::log( 'collaborator.created', $data, $new_id );
			}
		}

		wp_redirect( add_query_arg( 'ecfi_updated', '1', $this->list_url() ) );
		exit;
	}

	/**
	 * Handles revoke and reactivate row actions.
	 *
	 * @param string $status 'revoked' or 'active'.
	 * @return void
	 */
	private function handle_status_change( string $status ): void {
		$id = absint( $_POST['collaborator_id'] ?? 0 );
		check_admin_referer( "ecfi_cfw_{$status}_{$id}" );

		ECFI_CFW_Collaborator::set_status( $id, $status );

		$action = 'revoked' === $status ? 'collaborator.revoked' : 'collaborator.reactivated';
		ECFI_CFW_Audit_Log::log( $action, array(), $id );

		wp_redirect( add_query_arg( 'ecfi_updated', '1', $this->list_url() ) );
		exit;
	}

	/**
	 * Handles token regeneration row action.
	 *
	 * @return void
	 */
	private function handle_regenerate_token(): void {
		$id = absint( $_POST['collaborator_id'] ?? 0 );
		check_admin_referer( "ecfi_cfw_regen_{$id}" );

		ECFI_CFW_Collaborator::regenerate_token( $id );
		ECFI_CFW_Audit_Log::log( 'collaborator.token_regenerated', array(), $id );

		wp_redirect( add_query_arg( 'ecfi_updated', '1', $this->list_url() ) );
		exit;
	}

	/**
	 * Handles bulk revoke action from the list table.
	 *
	 * @return void
	 */
	private function handle_bulk_revoke(): void {
		check_admin_referer( 'bulk-collaborators' );

		$ids   = array_map( 'absint', (array) ( $_POST['collaborator_ids'] ?? array() ) );
		$count = 0;

		foreach ( $ids as $id ) {
			if ( $id > 0 ) {
				ECFI_CFW_Collaborator::set_status( $id, 'revoked' );
				ECFI_CFW_Audit_Log::log( 'collaborator.revoked', array(), $id );
				++$count;
			}
		}

		wp_redirect( add_query_arg( 'ecfi_updated', $count, $this->list_url() ) );
		exit;
	}

	// -------------------------------------------------------------------------
	// View renderers
	// -------------------------------------------------------------------------

	/**
	 * Renders the collaborators list view.
	 *
	 * @return void
	 */
	private function render_list(): void {
		$table = new ECFI_CFW_Collaborators_List_Table();
		$table->prepare_items();

		include ECFI_CF_WORKFLOW_DIR . 'admin/views/collaborators-list.php';
	}

	/**
	 * Renders the add/edit form.
	 *
	 * @param int|null $id Collaborator ID for edit mode, or null for add mode.
	 * @return void
	 */
	private function render_form( ?int $id ): void {
		$collaborator = null;

		if ( $id > 0 ) {
			$collaborator = ECFI_CFW_Collaborator::get_by_id( $id );
			if ( ! $collaborator ) {
				wp_die( esc_html__( 'Collaborator not found.', 'ecfi-cf-workflow' ) );
			}
		}

		include ECFI_CF_WORKFLOW_DIR . 'admin/views/collaborator-form.php';
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Returns the canonical URL for the collaborators list screen.
	 *
	 * @return string
	 */
	private function list_url(): string {
		return admin_url( 'admin.php?page=ecfi-cfw-collaborators' );
	}
}
