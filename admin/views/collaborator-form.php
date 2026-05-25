<?php
/**
 * Add/Edit collaborator form template.
 *
 * Available variables:
 *   $collaborator  stdClass|null — populated row for edit mode, null for add mode.
 *
 * @package ECFI_CF_Workflow
 */

defined( 'ABSPATH' ) || exit;

$is_edit = $collaborator !== null;
$title   = $is_edit
	? __( 'Edit Collaborator', 'ecfi-cf-workflow' )
	: __( 'Add Collaborator', 'ecfi-cf-workflow' );

$val = static function ( string $field ) use ( $collaborator ): string {
	return $collaborator ? esc_attr( $collaborator->$field ?? '' ) : '';
};
?>
<div class="wrap">
	<h1><?php echo esc_html( $title ); ?></h1>

	<p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ecfi-cfw-collaborators' ) ); ?>">
			&larr; <?php esc_html_e( 'Back to Collaborators', 'ecfi-cf-workflow' ); ?>
		</a>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=ecfi-cfw-collaborators' ) ); ?>">
		<?php wp_nonce_field( 'ecfi_cfw_collaborator_form' ); ?>
		<input type="hidden" name="action" value="save_collaborator">
		<input type="hidden" name="collaborator_id" value="<?php echo $is_edit ? absint( $collaborator->id ) : 0; ?>">

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="ecfi-name">
						<?php esc_html_e( 'Name', 'ecfi-cf-workflow' ); ?>
						<span class="required" aria-hidden="true">*</span>
					</label>
				</th>
				<td>
					<input
						type="text"
						id="ecfi-name"
						name="name"
						class="regular-text"
						value="<?php echo $val( 'name' ); ?>"
						required
					>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="ecfi-email">
						<?php esc_html_e( 'Email', 'ecfi-cf-workflow' ); ?>
						<span class="required" aria-hidden="true">*</span>
					</label>
				</th>
				<td>
					<input
						type="email"
						id="ecfi-email"
						name="email"
						class="regular-text"
						value="<?php echo $val( 'email' ); ?>"
						required
					>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="ecfi-position"><?php esc_html_e( 'Position', 'ecfi-cf-workflow' ); ?></label>
				</th>
				<td>
					<input
						type="text"
						id="ecfi-position"
						name="position"
						class="regular-text"
						value="<?php echo $val( 'position' ); ?>"
					>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="ecfi-organisation"><?php esc_html_e( 'Organisation', 'ecfi-cf-workflow' ); ?></label>
				</th>
				<td>
					<input
						type="text"
						id="ecfi-organisation"
						name="organisation_name"
						class="regular-text"
						value="<?php echo $val( 'organisation_name' ); ?>"
					>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="ecfi-notes"><?php esc_html_e( 'Notes', 'ecfi-cf-workflow' ); ?></label>
				</th>
				<td>
					<textarea
						id="ecfi-notes"
						name="notes"
						class="large-text"
						rows="5"
					><?php echo $is_edit ? esc_textarea( $collaborator->notes ?? '' ) : ''; ?></textarea>
				</td>
			</tr>
		</table>

		<?php
		submit_button(
			$is_edit
				? __( 'Update Collaborator', 'ecfi-cf-workflow' )
				: __( 'Add Collaborator', 'ecfi-cf-workflow' )
		);
		?>
	</form>
</div>
