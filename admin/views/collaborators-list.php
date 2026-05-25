<?php
/**
 * Collaborators list view template.
 *
 * Available variables:
 *   $table  ECFI_CFW_Collaborators_List_Table — already had prepare_items() called.
 *
 * @package ECFI_CF_Workflow
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Collaborators', 'ecfi-cf-workflow' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=ecfi-cfw-collaborators&action=add' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Add New', 'ecfi-cf-workflow' ); ?>
	</a>
	<hr class="wp-header-end">

	<?php if ( isset( $_GET['ecfi_updated'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Collaborator saved successfully.', 'ecfi-cf-workflow' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['ecfi_error'] ) && 'validation' === $_GET['ecfi_error'] ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Name and email are required fields.', 'ecfi-cf-workflow' ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post">
		<input type="hidden" name="page" value="ecfi-cfw-collaborators">
		<?php
		$table->views();
		$table->display();
		?>
	</form>
</div>
