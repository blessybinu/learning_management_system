<?php
/**
 * @package TutorLMS/Templates
 * @version 1.7.5
 */

defined( 'ABSPATH' ) || exit;
?>
<h3><?php esc_html_e( 'Settings', 'edumall' ) ?></h3>

<div class="tutor-dashboard-content-inner">
	<div class="tutor-dashboard-inline-links">
		<?php tutor_load_template( 'dashboard.settings.nav-bar', [ 'active_setting_nav' => 'reset_password' ] ); ?>
	</div>

	<div class="tutor-reset-password-form-wrap">
		<?php
		$success_msg = tutor_utils()->get_flash_msg( 'success' );
		if ( $success_msg ) { ?>
			<div class="tutor-success-msg">
				<?php echo esc_html( $success_msg ); ?>
			</div>
		<?php } ?>

		<form action="" method="post" enctype="multipart/form-data"
		      class="dashboard-settings-form dashboard-settings-reset-password-form">
			<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
			<input type="hidden" value="tutor_reset_password" name="tutor_action"/>

			<?php
			$errors = apply_filters( 'tutor_reset_password_validation_errors', array() );
			if ( is_array( $errors ) && count( $errors ) ) {
				echo '<div class="tutor-alert-warning tutor-mb-10"><ul class="tutor-required-fields">';
				foreach ( $errors as $error_key => $error_value ) {
					echo "<li>{$error_value}</li>";
				}
				echo '</ul></div>';
			}
			?>

			<?php do_action( 'tutor_reset_password_input_before' ) ?>

			<div class="row">
				<div class="col-md-12 col-lg-6">
					<div class="dashboard-content-box">
						<h4 class="dashboard-content-box-title"><?php esc_html_e( 'Reset Password', 'edumall' ); ?></h4>

						<div class="tutor-form-group">
							<label> <?php esc_html_e( 'Current Password', 'edumall' ); ?> </label>
							<input type="password" name="previous_password">
						</div>

						<div class="row">
							<div class="col-md-6">
								<div class="tutor-form-group">
									<label><?php esc_html_e( 'New Password', 'edumall' ); ?></label>
									<input type="password" name="new_password">
								</div>
							</div>
							<div class="col-md-6">
								<div class="tutor-form-group">
									<label><?php esc_html_e( 'Confirm New Password', 'edumall' ); ?></label>
									<input type="password" name="confirm_new_password">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="tutor-form-group form-submit-wrap">
				<button type="submit" class="tutor-button"
				        name="tutor_password_reset_btn"><?php esc_html_e( 'Reset Password', 'edumall' ); ?></button>
			</div>

			<?php do_action( 'tutor_reset_password_input_after' ) ?>

		</form>
	</div>
</div>




