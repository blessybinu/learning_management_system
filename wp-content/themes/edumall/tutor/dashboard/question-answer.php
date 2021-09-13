<?php
/**
 * @package       TutorLMS/Templates
 * @version       1.6.4
 *
 * @theme-since   1.0.0
 * @theme-version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

$per_page     = 10;
$current_page = max( 1, tutils()->avalue_dot( 'current_page', $_GET ) );
$offset       = ( $current_page - 1 ) * $per_page;

$total_items = tutils()->get_total_qa_question();
$questions   = tutils()->get_qa_questions( $offset, $per_page );
?>
	<h3><?php esc_html_e( 'Question & Answer', 'edumall' ); ?></h3>

<?php if ( tutils()->count( $questions ) ) : ?>
	<div class="dashboard-question-n-answer-table dashboard-table-wrapper dashboard-table-responsive">
		<div class="dashboard-table-container">
			<table class="dashboard-table">
				<thead>
				<tr>
					<th><?php esc_html_e( 'Question', 'edumall' ); ?></th>
					<th><?php esc_html_e( 'Student', 'edumall' ); ?></th>
					<th><?php esc_html_e( 'Course', 'edumall' ); ?></th>
					<th><?php esc_html_e( 'Answer', 'edumall' ); ?></th>
					<th></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ( $questions as $question ) : ?>
					<?php
					$questioner_profile_url = tutor_utils()->profile_url( $question->user_id );
					?>
					<tr id="<?php echo 'tutor-dashboard-question-' . $question->comment_ID; ?>">
						<td>
							<div class="heading col-heading-mobile col-heading-block"><?php esc_html_e( 'Question:', 'edumall' ); ?></div>
							<h3 class="question-title"><a class="link-in-title"
							                              href="<?php echo tutils()->get_tutor_dashboard_page_permalink( 'question-answer/answers?question_id=' . $question->comment_ID ); ?>">
									<?php echo esc_html( $question->question_title ); ?>
								</a></h3>

						</td>
						<td>
							<div class="heading col-heading-mobile col-heading-block"><?php esc_html_e( 'Student:', 'edumall' ); ?></div>
							<div class="questioner-info">
								<div class="tutor-question-avatar">
									<a href="<?php echo esc_url( $questioner_profile_url ); ?>">
										<?php echo edumall_get_avatar( $question->user_id, 52 ); ?>
									</a>
								</div>
								<div class="question-info">
									<h5 class="questioner-name"><?php echo esc_html( $question->display_name ); ?></h5>
									<span
										class="question-post-date"><?php echo esc_html( sprintf( __( '%s ago', 'edumall' ), human_time_diff( strtotime
										( $question->comment_date ) ) ) ); ?></span>
								</div>
							</div>
						</td>
						<td>
							<div class="heading col-heading-mobile"><?php esc_html_e( 'Course:', 'edumall' ); ?></div>
							<h3 class="course-title"><?php echo esc_html( $question->post_title ); ?></h3>
						</td>
						<td>
							<div class="heading col-heading-mobile"><?php esc_html_e( 'Answer:', 'edumall' ); ?></div>
							<span
								class="question-answer-count"><?php echo esc_html( number_format_i18n( $question->answer_count ) ); ?></span>
						</td>
						<td>
							<a href="#tutor-question-delete"
							   class="tutor-dashboard-element-delete-btn dashboard-action-btn delete-action-btn"
							   data-id="<?php echo esc_attr( $question->comment_ID ); ?>">
								<i class="fal fa-trash-alt"></i><?php esc_html_e( 'Delete', 'edumall' ) ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="tutor-pagination">
		<?php
		Edumall_Templates::render_paginate_links( [
			'format'  => '?current_page=%#%',
			'current' => $current_page,
			'total'   => ceil( $total_items / $per_page ),
		] );
		?>
	</div>

	<div class="tutor-frontend-modal" data-popup-rel="#tutor-question-delete" style="display: none">
		<div class="tutor-frontend-modal-overlay"></div>
		<div class="tutor-frontend-modal-content">
			<button class="tm-close tutor-icon-line-cross"></button>
			<div class="tutor-modal-body tutor-course-delete-popup">
				<img src="<?php echo tutor()->url . 'assets/images/delete-icon.png' ?>"
				     alt="<?php esc_attr_e( 'Delete this question?', 'edumall' ); ?>">
				<h3 class="popup-title"><?php esc_html_e( 'Delete This Question?', 'edumall' ); ?></h3>
				<p class="popup-description"><?php esc_html_e( 'You are going to delete this question, it can\'t be undone', 'edumall' ); ?></p>
				<div class="tutor-modal-button-group">
					<form action="" id="tutor-dashboard-delete-element-form">
						<input type="hidden" name="action" value="tutor_delete_dashboard_question">
						<input type="hidden" name="question_id" id="tutor-dashboard-delete-element-id" value="">
						<button type="button"
						        class="tutor-modal-btn-cancel"><?php esc_html_e( 'Cancel', 'edumall' ) ?></button>
						<button type="submit"
						        class="tutor-danger tutor-modal-element-delete-btn"><?php esc_html_e( 'Yes, Delete Question', 'edumall' ) ?></button>
					</form>
				</div>
			</div>

		</div>
	</div>
<?php else : ?>
	<div class="dashboard-no-content-found">
		<?php esc_html_e( 'No question is available.', 'edumall' ); ?>
	</div>
<?php endif;
