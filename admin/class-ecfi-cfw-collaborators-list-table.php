<?php
/**
 * WP_List_Table subclass for the collaborators admin screen.
 *
 * @package ECFI_CF_Workflow
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Renders the collaborators list table.
 */
class ECFI_CFW_Collaborators_List_Table extends WP_List_Table {

	/**
	 * @inheritdoc
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'collaborator',
				'plural'   => 'collaborators',
				'ajax'     => false,
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function ajax_user_can(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @inheritdoc
	 */
	public function get_columns(): array {
		return array(
			'cb'                => '<input type="checkbox" />',
			'name'              => __( 'Name', 'ecfi-cf-workflow' ),
			'email'             => __( 'Email', 'ecfi-cf-workflow' ),
			'position'          => __( 'Position', 'ecfi-cf-workflow' ),
			'organisation_name' => __( 'Organisation', 'ecfi-cf-workflow' ),
			'status'            => __( 'Status', 'ecfi-cf-workflow' ),
			'created_at'        => __( 'Created', 'ecfi-cf-workflow' ),
			'last_used_at'      => __( 'Last Used', 'ecfi-cf-workflow' ),
		);
	}

	/**
	 * @inheritdoc
	 */
	protected function get_sortable_columns(): array {
		return array(
			'name'       => array( 'name', false ),
			'email'      => array( 'email', false ),
			'status'     => array( 'status', false ),
			'created_at' => array( 'created_at', true ),
		);
	}

	/**
	 * @inheritdoc
	 */
	protected function get_default_primary_column_name(): string {
		return 'name';
	}

	/**
	 * @inheritdoc
	 */
	protected function get_bulk_actions(): array {
		return array(
			'bulk_revoke' => __( 'Revoke', 'ecfi-cf-workflow' ),
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_views(): array {
		$current = sanitize_key( $_REQUEST['status'] ?? 'all' );
		$base    = admin_url( 'admin.php?page=ecfi-cfw-collaborators' );

		$counts = array(
			'all'     => ECFI_CFW_Collaborator::count_all( 'all' ),
			'active'  => ECFI_CFW_Collaborator::count_all( 'active' ),
			'revoked' => ECFI_CFW_Collaborator::count_all( 'revoked' ),
		);

		$views = array();

		foreach ( array( 'all', 'active', 'revoked' ) as $key ) {
			$label = ucfirst( $key );
			$url   = $key === 'all' ? $base : add_query_arg( 'status', $key, $base );
			$class = $current === $key ? ' class="current"' : '';
			/* translators: 1: Tab label, 2: Count */
			$views[ $key ] = sprintf(
				'<a href="%s"%s>%s <span class="count">(%s)</span></a>',
				esc_url( $url ),
				$class,
				esc_html( $label ),
				esc_html( number_format_i18n( $counts[ $key ] ) )
			);
		}

		return $views;
	}

	/**
	 * @inheritdoc
	 */
	public function prepare_items(): void {
		$per_page = 20;
		$orderby  = in_array( $_REQUEST['orderby'] ?? '', ECFI_CFW_Collaborator::ALLOWED_ORDERBY, true )
			? sanitize_key( $_REQUEST['orderby'] )
			: 'created_at';
		$order    = strtoupper( $_REQUEST['order'] ?? 'DESC' ) === 'ASC' ? 'ASC' : 'DESC';
		$paged    = $this->get_pagenum();
		$status   = in_array( $_REQUEST['status'] ?? '', array( 'active', 'revoked' ), true )
			? sanitize_key( $_REQUEST['status'] )
			: 'all';

		$total = ECFI_CFW_Collaborator::count_all( $status );

		$this->items = ECFI_CFW_Collaborator::get_all(
			array(
				'orderby'  => $orderby,
				'order'    => $order,
				'per_page' => $per_page,
				'paged'    => $paged,
				'status'   => $status,
			)
		);

		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
			)
		);

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
			$this->get_default_primary_column_name(),
		);
	}

	/**
	 * Checkbox column.
	 *
	 * @param object $item Row data.
	 * @return string
	 */
	public function column_cb( $item ): string {
		return sprintf(
			'<input type="checkbox" name="collaborator_ids[]" value="%d" />',
			absint( $item->id )
		);
	}

	/**
	 * Name column with row actions.
	 *
	 * @param object $item Row data.
	 * @return string
	 */
	public function column_name( $item ): string {
		$id       = absint( $item->id );
		$base_url = admin_url( 'admin.php?page=ecfi-cfw-collaborators' );

		$edit_url = add_query_arg( array( 'action' => 'edit', 'id' => $id ), $base_url );

		$name_html = sprintf(
			'<strong><a href="%s">%s</a></strong>',
			esc_url( $edit_url ),
			esc_html( $item->name )
		);

		// Edit action (GET).
		$actions['edit'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_url ), __( 'Edit', 'ecfi-cf-workflow' ) );

		// Revoke / Reactivate action (POST form styled as link).
		if ( $item->status === 'active' ) {
			$actions['revoke'] = $this->row_action_form(
				$id,
				'revoke_collaborator',
				'ecfi_cfw_revoked_' . $id,
				__( 'Revoke', 'ecfi-cf-workflow' ),
				__( 'Are you sure you want to revoke this collaborator?', 'ecfi-cf-workflow' )
			);
		} else {
			$actions['reactivate'] = $this->row_action_form(
				$id,
				'reactivate_collaborator',
				'ecfi_cfw_active_' . $id,
				__( 'Reactivate', 'ecfi-cf-workflow' ),
				null
			);
		}

		// Regenerate token action (POST form).
		$actions['regenerate_token'] = $this->row_action_form(
			$id,
			'regenerate_token',
			'ecfi_cfw_regen_' . $id,
			__( 'Regenerate Token', 'ecfi-cf-workflow' ),
			__( 'Are you sure? All existing share links for this collaborator will stop working immediately.', 'ecfi-cf-workflow' )
		);

		// Copy link (JS only).
		$actions['copy_link'] = sprintf(
			'<button type="button" class="button-link ecfi-copy-link" data-token="%s">%s</button>',
			esc_attr( $item->token ),
			esc_html__( 'Copy Link', 'ecfi-cf-workflow' )
		);

		return $name_html . $this->row_actions( $actions );
	}

	/**
	 * Status column — renders a coloured badge.
	 *
	 * @param object $item Row data.
	 * @return string
	 */
	public function column_status( $item ): string {
		$status = esc_attr( $item->status );
		$label  = ucfirst( $item->status );
		return sprintf(
			'<span class="ecfi-cfw-badge ecfi-cfw-badge--%s">%s</span>',
			$status,
			esc_html( $label )
		);
	}

	/**
	 * Created date column.
	 *
	 * @param object $item Row data.
	 * @return string
	 */
	public function column_created_at( $item ): string {
		if ( empty( $item->created_at ) ) {
			return '—';
		}
		return esc_html( wp_date( get_option( 'date_format' ), strtotime( $item->created_at ) ) );
	}

	/**
	 * Last used date column.
	 *
	 * @param object $item Row data.
	 * @return string
	 */
	public function column_last_used_at( $item ): string {
		if ( empty( $item->last_used_at ) ) {
			return '<em>' . esc_html__( 'Never', 'ecfi-cf-workflow' ) . '</em>';
		}
		return esc_html( wp_date( get_option( 'date_format' ), strtotime( $item->last_used_at ) ) );
	}

	/**
	 * Fallback for columns without a dedicated method.
	 *
	 * @param object $item        Row data.
	 * @param string $column_name Column key.
	 * @return string
	 */
	public function column_default( $item, $column_name ): string {
		$value = $item->$column_name ?? '';
		return $value !== '' ? esc_html( $value ) : '—';
	}

	/**
	 * @inheritdoc
	 */
	public function no_items(): void {
		esc_html_e( 'No collaborators found.', 'ecfi-cf-workflow' );
	}

	/**
	 * Renders a mini inline POST form styled as a row action link.
	 *
	 * @param int         $collaborator_id Row ID.
	 * @param string      $action          Value for the hidden `action` field.
	 * @param string      $nonce_action    Nonce action string.
	 * @param string      $label           Visible link text.
	 * @param string|null $confirm         JS confirm message, or null for no confirm.
	 * @return string
	 */
	private function row_action_form(
		int $collaborator_id,
		string $action,
		string $nonce_action,
		string $label,
		?string $confirm
	): string {
		$data_confirm = $confirm
			? ' data-confirm="' . esc_attr( $confirm ) . '"'
			: '';

		ob_start();
		?>
		<form method="post" class="ecfi-cfw-row-action-form"<?php echo $data_confirm; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
			<input type="hidden" name="collaborator_id" value="<?php echo absint( $collaborator_id ); ?>">
			<?php wp_nonce_field( $nonce_action, '_wpnonce', false ); ?>
			<button type="submit" class="button-link"><?php echo esc_html( $label ); ?></button>
		</form>
		<?php
		return ob_get_clean();
	}
}
