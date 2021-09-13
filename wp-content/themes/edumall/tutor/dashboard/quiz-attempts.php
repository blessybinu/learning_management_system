<?php
/**
 * Students Quiz Attempts Frontend
 *
 * @since   v.1.4.0
 *
 * @author  Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.6.4
 */

defined( 'ABSPATH' ) || exit;

$per_page     = 20;
$current_page = max( 1, tutils()->array_get( 'current_page', $_GET ) );
$offset       = ( $current_page - 1 ) * $per_page;

$course_id           = tutor_utils()->get_assigned_courses_ids_by_instructors();
$quiz_attempts       = tutor_utils()->get_quiz_attempts_by_course_ids( $offset, $per_page, $course_id );
$quiz_attempts_count = tutor_utils()->get_total_quiz_attempts_by_course_ids( $course_id );
?>

	<h3><?php esc_html_e( 'Quiz Attempts', 'edumall' ); ?></h3>

<?php if ( $quiz_attempts_count ) : ?>
	<div class="dashboard-quiz-attempt-history dashboard-table-wrapper dashboard-table-responsive">
		<div class="dashboard-table-container">
			<table class="dashboard-table">
				<thead>
				<tr>
					<th class="col-course-info"><?php esc_html_e( 'Course Info', 'edumall' ); ?></th>
					<th class="col-student"><?php esc_html_e( 'Student', 'edumall' ); ?></th>
					<th class="col-correct-answer"><?php esc_html_e( 'Correct Answer', 'edumall' ); ?></th>
					<th class="col-incorrect-answer"><?php esc_html_e( 'Incorrect Answer', 'edumall' ); ?></th>
					<th class="col-earned-marks"><?php esc_html_e( 'Earned Marks', 'edumall' ); ?></th>
					<th class="col-result"><?php esc_html_e( 'Result', 'edumall' ); ?></th>
					<th></th>
					<?php do_action( 'tutor_quiz/my_attempts/table/thead/col' ); ?>
				</tr>
				</thead>
				<tbody>
				<?php foreach ( $quiz_attempts as $attempt ) : ?>
					<?php
					$attempt_action    = tutor_utils()->get_tutor_dashboard_page_permalink( 'quiz-attempts/quiz-reviews/?attempt_id=' . $attempt->attempt_id );
					$earned_percentage = $attempt->earned_marks > 0 ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;
					$passing_grade     = tutor_utils()->get_quiz_option( $attempt->quiz_id, 'passing_grade', 0 );
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
						<td>
							<div class="heading col-heading-mobile"><?php esc_html_e( 'Student:', 'edumall' ); ?></div>
							<div class="student">
								<span><?php echo esc_html( $attempt->display_name ); ?></span>
							</div>
							<div class="student-meta">
								<span><?php echo esc_html( $attempt->user_email ); ?></span>
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
								class="heading col-heading-mobile"><?php esc_html_e( 'Correct Answer:', 'edumall' ); ?></div>
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
	<div class="tutor-pagination">
		<?php
		Edumall_Templates::render_paginate_links( [
			'format'  => '?current_page=%#%',
			'current' => $current_page,
			'total'   => ceil( $quiz_attempts_count / $per_page ),
		] );
		?>
	</div>
<?php else : ?>
	<div class="dashboard-no-content-found">
		<?php esc_html_e( 'You have not attempted for any quiz yet.', 'edumall' ); ?>
	</div>
<?php endif;
