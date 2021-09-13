<?php
/**
 * @package TutorLMS/Templates
 * @version 1.7.5
 */

defined( 'ABSPATH' ) || exit;

$earning_sum                   = tutor_utils()->get_earning_sum();
$min_withdraw                  = tutor_utils()->get_option( 'min_withdraw_amount' );
$formatted_min_withdraw_amount = tutor_utils()->tutor_price( $min_withdraw );

$saved_account        = tutor_utils()->get_user_withdraw_method();
$withdraw_method_name = tutor_utils()->avalue_dot( 'withdraw_method_name', $saved_account );

$user_id = get_current_user_id();

$balance_formatted     = tutor_utils()->tutor_price( $earning_sum->balance );
$is_balance_sufficient = $earning_sum->balance >= $min_withdraw;
$all_histories         = tutor_utils()->get_withdrawals_history( $user_id, array(
	'status' => array(
		'pending',
		'approved',
		'rejected',
	),
) );

$image_base   = tutor()->url . 'assets/images/';
$method_icons = array(
	'bank_transfer_withdraw' => $image_base . 'icon-bank.svg',
	'echeck_withdraw'        => $image_base . 'icon-echeck.svg',
	'paypal_withdraw'        => $image_base . 'icon-paypal.svg',
);

$status_message = array(
	'rejected' => esc_html__( 'Please contact the site administrator for more information.', 'edumall' ),
	'pending'  => esc_html__( 'Withdrawal request is pending for approval, please hold tight.', 'edumall' ),
);

$currency_symbol = '';
if ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
	$currency_symbol = get_woocommerce_currency_symbol();
} else if ( function_exists( 'edd_currency_symbol' ) ) {
	$currency_symbol = edd_currency_symbol();
}
?>
<div class="tutor-dashboard-content-inner">

	<div class="dashboard-content-box withdraw-page-current-balance">
		<h4 class="dashboard-content-box-title"><?php esc_html_e( 'Current Balance', 'edumall' ); ?></h4>

		<div class="withdraw-balance-row">
			<p class="withdraw-balance-col">
				<?php if ( $earning_sum->balance >= $min_withdraw ) : ?>
					<?php echo sprintf(
						esc_html__( 'You currently have %s ready to withdraw', 'edumall' ),
						'<strong class="available_balance">' . $balance_formatted . '</strong>'
					); ?>
				<?php else : ?>
					<?php echo sprintf(
						esc_html__( 'You currently have %s and this is insufficient balance to withdraw.', 'edumall' ),
						'<strong class="available_balance">' . $balance_formatted . '</strong>'
					); ?>
				<?php endif; ?>
			</p>
		</div>

		<div class="current-withdraw-account-wrap withdrawal-preference inline-image-text">
			<span class="far fa-question-circle primary-color"></span>
			<span>
	            <?php
	            $my_profile_url = tutor_utils()->get_tutor_dashboard_page_permalink( 'settings/withdraw-settings' );
	            echo $withdraw_method_name ? sprintf( esc_html__( 'The preferred payment method is selected as %s. ', 'edumall' ), $withdraw_method_name ) : '';
	            echo sprintf( esc_html__( 'You can change your %s withdrawal preference %s', 'edumall' ), '<a href="' . esc_url( $my_profile_url ) . '" class="link-transition-02">', '</a>' );
	            ?>
            </span>
		</div>

		<?php if ( $is_balance_sufficient && $withdraw_method_name ) : ?>
			<div class="making-withdraw-wrap">
				<?php
				Edumall_Templates::render_button( [
					'link'        => [
						'url' => 'javascript:void(0);',
					],
					'text'        => esc_html__( 'Make a withdraw', 'edumall' ),
					'extra_class' => 'open-withdraw-form-btn',
					'size'        => 'xs',
				] );
				?>
				<div class="tutor-earning-withdraw-form-wrap" style="display: none;">
					<div>
						<div class="tutor-withdrawal-pop-up-success">
							<div>
								<span class="close-withdraw-form-btn" data-reload="yes"><i
										class="fal fa-times"></i></span>
								<div style="text-align:center">
									<img src="<?php echo $image_base; ?>icon-cheers.svg"/>
									<h3 class="popup-withdraw-heading"><?php esc_html_e( 'Your withdrawal request has been successfully accepted', 'edumall' ); ?></h3>
									<p class="popup-withdraw-description"><?php esc_html_e( 'Please check your transaction notification on your connected withdrawal method', 'edumall' ); ?></p>
								</div>
								<div class="tutor-withdraw-form-response"></div>
							</div>
						</div>

						<div class="tutor-withdrawal-op-up-frorm">
							<div>
								<span class="close-withdraw-form-btn"><i class="fal fa-times"></i></span>
								<h3 class="popup-withdraw-heading"><?php esc_html_e( 'Make a Withdrawal', 'edumall' ); ?></h3>
								<p class="popup-withdraw-description"><?php esc_html_e( 'Please enter withdrawal amount and click the submit request button.', 'edumall' ); ?></p>
								<table>
									<tbody>
									<tr>
										<td>
											<span><?php esc_html_e( 'Current Balance', 'edumall' ); ?></span><br/>
											<b><?php echo $balance_formatted; ?></b>
										</td>
										<td>
											<span><?php esc_html_e( 'Selected Payment Method', 'edumall' ); ?></span><br/>
											<b><?php echo $withdraw_method_name; ?></b>
										</td>
									</tr>
									</tbody>
								</table>
							</div>
							<form id="tutor-earning-withdraw-form" action="" method="post">
								<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
								<input type="hidden" value="tutor_make_an_withdraw" name="action"/>
								<?php do_action( 'tutor_withdraw_form_before' ); ?>
								<div class="withdraw-form-field-row">
									<label
										for="tutor_withdraw_amount"
										class="form-label"><?php esc_html_e( 'Amount', 'edumall' ); ?></label>
									<div class="withdraw-form-field-amount">
										<span
											class="withdraw-currency"><?php echo esc_html( $currency_symbol ); ?></span>
										<input type="number" min="1" name="tutor_withdraw_amount"
										       id="tutor_withdraw_amount"/>
									</div>
									<div class="inline-image-text">
										<span class="far fa-question-circle primary-color"></span>
										<span><?php echo esc_html( sprintf( __( 'Minimum withdraw amount is %1$s', 'edumall' ), strip_tags( $formatted_min_withdraw_amount ) ) ); ?></span>
									</div>
								</div>

								<div class="tutor-withdraw-button-container">
									<button class="tutor-btn" type="submit" id="tutor-earning-withdraw-btn"
									        name="withdraw-form-submit"><?php esc_html_e( 'Submit Request', 'edumall' ); ?></button>
									<button
										class="tutor-btn tutor-btn-secondary close-withdraw-form-btn"><?php esc_html_e( 'Cancel', 'edumall' ); ?></button>
								</div>

								<div class="tutor-withdraw-form-response"></div>

								<?php do_action( 'tutor_withdraw_form_after' ); ?>
							</form>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<div class="dashboard-content-box withdraw-history-table-wrap">
		<h4 class="dashboard-content-box-title"><?php esc_html_e( 'Withdrawal History', 'edumall' ); ?></h4>
		<?php if ( tutor_utils()->count( $all_histories->results ) ) : ?>

			<div class="dashboard-table-wrapper dashboard-table-responsive">
				<div class="dashboard-table-container">
					<table class="withdrawals-history tutor-table dashboard-table">
						<thead>
						<tr>
							<th><?php esc_html_e( 'Withdrawal Method', 'edumall' ); ?></th>
							<th width="30%"><?php esc_html_e( 'Requested On', 'edumall' ); ?></th>
							<th width="15%"><?php esc_html_e( 'Amount', 'edumall' ); ?></th>
							<th width="15%"><?php esc_html_e( 'Status', 'edumall' ); ?></th>
							<th></th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ( $all_histories->results as $withdraw_history ) : ?>
							<tr>
								<td>
									<?php
									$method_data  = maybe_unserialize( $withdraw_history->method_data );
									$method_key   = $method_data['withdraw_method_key'];
									$method_title = '';

									switch ( $method_key ) {
										case 'bank_transfer_withdraw':
											$method_title = $method_data['account_number']['value'];
											$method_title = substr_replace( $method_title, '****', 2, strlen( $method_title ) - 4 );
											break;
										case 'paypal_withdraw':
											$method_title = $method_data['paypal_email']['value'];
											$email_base   = substr( $method_title, 0, strpos( $method_title, '@' ) );
											$method_title = substr_replace( $email_base, '****', 2, strlen( $email_base ) - 3 ) . substr( $method_title, strpos( $method_title, '@' ) );
											break;
									}
									?>
									<div class="inline-image-text is-inline-block">
										<?php if ( isset( $method_icons[ $method_key ] ) ) : ?>
											<img src="<?php echo esc_url( $method_icons[ $method_key ] ); ?>"/>
										<?php endif; ?>
										&nbsp;
										<span>
                                        <?php
                                        echo '<strong class="withdraw-method-name">', tutor_utils()->avalue_dot( 'withdraw_method_name', $method_data ), '</strong>';
                                        echo '<small>', $method_title, '</small>';
                                        ?>
                                    </span>
									</div>
								</td>
								<td>
									<?php
									echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $withdraw_history->created_at ) );
									?>
								</td>
								<td>
									<strong><?php echo tutor_utils()->tutor_price( $withdraw_history->amount ); ?></strong>
								</td>
								<td>
                                    <span
	                                    class="withdraw-status tutor-status-text <?php echo 'status-' . $withdraw_history->status; ?>">
                                        <?php echo __( ucfirst( $withdraw_history->status ), 'edumall' ); ?>
                                    </span>
								</td>
								<td>
									<?php if ( $withdraw_history->status !== 'approved' && isset( $status_message[ $withdraw_history->status ] ) ) : ?>
										<span class="hint--left hint--bounce hint--primary"
										      aria-label="<?php echo esc_attr( $status_message[ $withdraw_history->status ] ); ?>">
                                            <i class="far fa-question-circle primary-color"></i>
                                        </span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		<?php else: ?>
			<p><?php esc_html_e( 'No withdrawal yet', 'edumall' ); ?></p>
		<?php endif; ?>
	</div>
</div>
