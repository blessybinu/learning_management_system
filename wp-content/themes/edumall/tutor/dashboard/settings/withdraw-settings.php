<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

defined( 'ABSPATH' ) || exit;

$tutor_withdrawal_methods = tutor_withdrawal_methods();
?>
<h3><?php esc_html_e( 'Settings', 'edumall' ) ?></h3>

<div class="tutor-dashboard-content-inner">
	<div class="tutor-dashboard-inline-links">
		<?php tutor_load_template( 'dashboard.settings.nav-bar', [ 'active_setting_nav' => 'withdrawal' ] ); ?>
	</div>

	<form id="tutor-withdraw-account-set-form" action="" method="post"
	      class="dashboard-settings-form dashboard-settings-withdraw-form">
		<div class="dashboard-content-box dashboard-content-box-small">
			<h4 class="dashboard-content-box-title"><?php esc_html_e( 'Select a withdraw method', 'edumall' ); ?></h4>
			<?php if ( tutor_utils()->count( $tutor_withdrawal_methods ) ) : ?>
				<?php
				$saved_account  = tutor_utils()->get_user_withdraw_method();
				$old_method_key = tutor_utils()->avalue_dot( 'withdraw_method_key', $saved_account );

				$min_withdraw_amount = tutor_utils()->get_option( 'min_withdraw_amount' );
				?>
				<div class="withdraw-method-select-wrap">
					<?php foreach ( $tutor_withdrawal_methods as $method_id => $method ) : ?>
						<div class="withdraw-method-select <?php echo 'withdraw-method-' . $method_id; ?>"
						     data-withdraw-method="<?php echo esc_attr( $method_id ); ?>">
							<input type="radio" id="withdraw_method_select_<?php echo esc_attr( $method_id ); ?>"
							       class="withdraw-method-select-input"
							       name="tutor_selected_withdraw_method" value="<?php echo esc_attr( $method_id ); ?>"
							       style="display: none;"
								<?php checked( $method_id, $old_method_key ); ?> >

							<label for="withdraw_method_select_<?php echo esc_attr( $method_id ); ?>">
								<span
									class="method-name"><?php echo tutor_utils()->avalue_dot( 'method_name', $method ); ?></span>
								<span
									class="method-amount"><?php esc_html_e( 'Min withdraw', 'edumall' ); ?><?php echo tutor_utils()->tutor_price( $min_withdraw_amount );
									?></span>
							</label>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="dashboard-withdraw-method-forms-wrap">
					<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
					<input type="hidden" value="tutor_save_withdraw_account" name="action"/>

					<?php do_action( 'tutor_withdraw_set_account_form_before' ); ?>

					<?php foreach ( $tutor_withdrawal_methods as $method_id => $method ) : ?>
						<?php $form_fields = tutor_utils()->avalue_dot( 'form_fields', $method ); ?>

						<div id="withdraw-method-form-<?php echo esc_attr( $method_id ); ?>"
						     class="withdraw-method-form withdraw-method-form-<?php echo esc_attr( $method_id );
						     ?>" style="display: none;">


							<?php do_action( "tutor_withdraw_set_account_{$method_id}_before" ); ?>

							<?php
							if ( tutor_utils()->count( $form_fields ) ) {
								foreach ( $form_fields as $field_name => $field ) {
									?>
									<div
										class="withdraw-method-field-wrap tutor-form-group <?php echo 'withdraw-method-field-' . $field_name . ' ' . $field['type']; ?> ">
										<?php
										if ( ! empty( $field['label'] ) ) {
											echo "<label for='field_{$method_id}_$field_name'>{$field['label']}</label>";
										}

										$passing_data = apply_filters( 'tutor_withdraw_account_field_type_data', array(
											'method_id'  => $method_id,
											'method'     => $method,
											'field_name' => $field_name,
											'field'      => $field,
											'old_value'  => null,
										) );
										$old_value    = tutor_utils()->avalue_dot( $field_name . ".value", $saved_account );
										if ( $old_value ) {
											$passing_data['old_value'] = $old_value;
										}

										tutor_load_template( "dashboard.withdraw-method-fields.{$field['type']}", $passing_data );

										if ( ! empty( $field['desc'] ) ) {
											echo "<p class='form-description'>{$field['desc']}</p>";
										}
										?>
									</div>
									<?php
								}
							}
							?>

							<?php do_action( "tutor_withdraw_set_account_{$method_id}_after" ); ?>
						</div>
					<?php endforeach; ?>

					<?php do_action( 'tutor_withdraw_set_account_form_after' ); ?>

				</div>
			<?php endif; ?>
		</div>
		<?php if ( tutor_utils()->count( $tutor_withdrawal_methods ) ) : ?>
			<div class="withdraw-account-save-btn-wrap">
				<button type="submit" class="tutor_set_withdraw_account_btn tutor-btn"
				        name="withdraw_btn_submit"><?php esc_html_e( 'Save Withdraw Account', 'edumall' ); ?></button>
			</div>
		<?php endif; ?>
	</form>
</div>
