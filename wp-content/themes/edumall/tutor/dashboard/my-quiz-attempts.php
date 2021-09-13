<?php
/**
 * Quiz Attempts, I attempted to courses
 *
 * @since   v.1.1.2
 *
 * @author  Themeum
 * @url https://themeum.com
 *
 *
 * @package TutorLMS/Templates
 * @version 1.6.4
 */

defined( 'ABSPATH' ) || exit;

$previous_attempts = tutor_utils()->get_all_quiz_attempts_by_user();
$attempted_count   = is_array( $previous_attempts ) ? count( $previous_attempts ) : 0;
?>
	<h3><?php esc_html_e( 'My Quiz Attempts', 'edumall' ); ?></h3>

<?php if ( $attempted_count ) : ?>
	<div class="dashboard-quiz-attempt-history dashboard-table-wrapper dashboard-table-responsive">
		<div class="dashboard-table-container">
			<table class="dashboard-table">
				<thead>
				<tr>
					<th class="col-course-info"><?php esc_html_e( 'Course Info', 'edumall' ); ?></th>
					<th class="col-correct-answer"><?php esc_html_e( 'Correct Answer', 'edumall' ); ?></th>
					<th class="col-incorrect-answer"><?php esc_html_e( 'Incorrect Answer', 'edumall' ); ?></th>
					<th class="col-earned-marks"><?php esc_html_e( 'Earned Marks', 'edumall' ); ?></th>
					<th class="col-result"><?php esc_html_e( 'Result', 'edumall' ); ?></th>
					<th class="col-detail-link"></th>
					<?php do_action( 'tutor_quiz/my_attempts/table/thead/col' ); ?>
				</tr>
				</thead>
				<tbody>
				<?php foreach ( $previous_attempts as $attempt ) : ?>
					<?php
					$attempt_action    = tutor_utils()->get_tutor_dashboard_page_permalink( 'my-quiz-attempts/attempts-details/?attempt_id=' . $attempt->attempt_id );
					$earned_percentage = $attempt->earned_marks > 0 ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;
					$passing_grade     = (int) tutor_utils()->get_quiz_option( $attempt->quiz_id, 'passing_grade', 0 );
					$answers           = tutor_utils()->get_quiz_answers_by_attempt_id( $attempt->attempt_id );
					?>
					<tr>
						<td>
							<h3 class="course-title">
								<a href="<?php echo get_the_permalink( $attempt->course_id ); ?>"
								   target="_blank"><?php echo esc_html( get_the_title( $attempt->course_id ) ); ?></a>
							</h3>
							<div class="dashboard-quiz-attempt-metas">
								<div class="meta-item quiz-attempt-date">
									<?php echo wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $attempt->attempt_ended_at ) ); ?>
								</div>
								<div class="meta-item quiz-attempt-question-count">
									<span class="meta-name"><?php esc_html_e( 'Question: ', 'edumall' ); ?></span>
									<span class="meta-value"><?php echo count( $answers ); ?></span>
								</div>
								<div class="meta-item quiz-attempt-total-marks">
									<span class="meta-name"><?php esc_html_e( 'Total Marks: ', 'edumall' ); ?></span>
									<span class="meta-value"><?php echo esc_html( $attempt->total_marks ); ?></span>
								</div>
							</div>
						</td>
						<?php
						$correct   = 0;
						$incorrect = 0;
						if ( is_array( $answers ) && count( $answers ) > 0 ) {
							foreach ( $answers as $answer ) {
								if ( (bool) isset( $answer->is_correct ) ? $answer->is_correct : '' ) {
									$correct++;
								} else {
									if ( $answer->question_type === 'open_ended' || $answer->question_type === 'short_answer' ) {
									} else {
										$incorrect++;
									}
								}
							}
						}
						?>
						<td>
							<div
								class="heading col-heading-mobile"><?php esc_html_e( 'Correct Answer', 'edumall' ); ?></div>
							<?php echo esc_html( $correct ); ?>
						</td>
						<td>
							<div
								class="heading col-heading-mobile"><?php esc_html_e( 'Incorrect Answer:', 'edumall' ); ?></div>
							<?php echo esc_html( $incorrect ); ?>
						</td>
						<td>
							<div
								class="heading col-heading-mobile"><?php esc_html_e( 'Earned Marks:', 'edumall' ); ?></div>
							<?php echo esc_html( $attempt->earned_marks . ' (' . $passing_grade . '%)' ); ?>
						</td>
						<td>
							<div class="heading col-heading-mobile"><?php esc_html_e( 'Result:', 'edumall' ); ?></div>
							<?php
							if ( $attempt->attempt_status === 'review_required' ) {
								echo '<span class="attempt-result attempt-result-review-required">' . esc_html__( 'Under Review', 'edumall' ) . '</span>';
							} else {
								if ( $earned_percentage >= $passing_grade ) {
									echo '<span class="attempt-result attempt-result-pass">' . esc_html__( 'Pass', 'edumall' ) . '</span>';
								} else {
									echo '<span class="attempt-result attempt-result-fail">' . esc_html__( 'Fail', 'edumall' ) . '</span>';
								}
							}
							?>
						</td>
						<td>
							<a href="<?php echo esc_url( $attempt_action ); ?>"
							   class="link-transition-01 quiz-attempt-detail-link"><?php esc_html_e( 'Details', 'edumall' ); ?></a>
						</td>
						<?php do_action( 'tutor_quiz/my_attempts/table/tbody/col' ); ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

<?php else : ?>
	<div class="dashboard-no-content-found">
		<?php esc_html_e( 'You have not attempted any quiz yet.', 'edumall' ); ?>
	</div>
<?php endif;
