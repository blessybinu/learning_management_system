<?php
/**
 * Question and answer
 *
 * @author        themeum
 * @url https://themeum.com
 * @package       TutorLMS/Templates
 * @since         1.0.0
 * @version       1.5.2
 *
 * @theme-since   1.0.0
 * @theme-version 2.6.0
 */

$enable_q_and_a_on_course = tutor_utils()->get_option( 'enable_q_and_a_on_course' );
if ( ! $enable_q_and_a_on_course ) {
	tutor_load_template( 'single.course.q_and_a_turned_off' );

	return;
}

global $post;
$currentPost = $post;

$course_id = tutils()->get_course_id_by_content( $post );

?>
<?php do_action( 'tutor_course/question_and_answer/before' ); ?>

<div class="tutor-queston-and-answer-wrap">
	<div class="tutor_question_answer_wrap">
		<?php
		$current_user    = tutor_utils()->get_user_id();
		$all_instructors = tutor_utils()->get_instructors_by_course( $course_id );
		$all_instructors = wp_list_pluck( (array) $all_instructors, 'ID' );

		if ( in_array( $current_user, $all_instructors ) ) {
			$questions = tutor_utils()->get_top_question( $course_id, $current_user, 0, 20, true );
		} else {
			$questions = tutor_utils()->get_top_question( $course_id );
		}

		if ( is_array( $questions ) && count( $questions ) ) {
			foreach ( $questions as $question ) {
				$profile_url = tutor_utils()->profile_url( $question->user_id );
				?>
				<div class="tutor_original_question">
					<div class="tutor-question-wrap">
						<div class="tutor-question-avatar">
							<a href="<?php echo esc_url( $profile_url ); ?>"><?php echo edumall_get_avatar( $question->user_id, 52 ); ?></a>
						</div>
						<div class="tutor-question-info">
							<div class="tutor-question-meta">
								<h4 class="question-user-name">
									<a href="<?php echo esc_url( $profile_url ); ?>">
										<?php echo esc_html( $question->display_name ); ?>
									</a>
								</h4>
								<span
									class="question-post-date tutor-text-mute"><?php echo esc_html( sprintf( __( '%s ago', 'edumall' ), human_time_diff( strtotime
									( $question->comment_date ) ) ) ); ?></span>
							</div>

							<div class="tutor-question-content">
								<h5 class="question-title"><?php echo esc_html( $question->question_title ); ?></h5>
								<div
									class="question-description"><?php echo wpautop( stripslashes( $question->comment_content ) ); ?></div>
							</div>
						</div>
					</div>
				</div>

				<?php
				$answers = tutor_utils()->get_qa_answer_by_question( $question->comment_ID );
				?>
				<?php if ( is_array( $answers ) && count( $answers ) ) : ?>
					<div class="tutor_admin_answers_list_wrap">
						<?php
						foreach ( $answers as $answer ) :
							$responder_profile_url = tutor_utils()->profile_url( $answer->user_id );

							$wrapper_class = 'tutor_individual_answer';

							if ( $question->user_id == $answer->user_id ) {
								$wrapper_class .= ' tutor-bg-white';
							} else {
								$wrapper_class .= ' tutor-bg-light';
							}
							?>
							<div class="<?php echo esc_attr( $wrapper_class ); ?>">
								<div class="tutor-question-wrap">
									<div class="tutor-question-avatar">
										<a href="<?php echo esc_url( $responder_profile_url ); ?>"><?php echo edumall_get_avatar( $answer->user_id, 52 ); ?></a>
									</div>
									<div class="tutor-question-info">
										<div class="tutor-question-meta">
											<h4 class="question-user-name">
												<a href="<?php echo esc_url( $responder_profile_url ); ?>"><?php echo esc_html( $answer->display_name ); ?></a>
											</h4>
											<span
												class="question-post-date tutor-text-mute"><?php echo sprintf( __( '%s ago', 'edumall' ), human_time_diff( strtotime
												( $answer->comment_date ) ) ); ?></span>
										</div>
										<div class="tutor-question-content">
											<div
												class="question-description"><?php echo wpautop( stripslashes( $answer->comment_content ) ); ?></div>
										</div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="tutor_add_answer_row">
					<div class="tutor_add_answer_wrap"
					     data-question-id="<?php echo esc_attr( $question->comment_ID ); ?>">

						<?php Edumall_Templates::render_button( [
							'link'          => [
								'url' => 'javascript:void(0);',
							],
							'text'          => esc_html__( 'Add an answer', 'edumall' ),
							'size'          => 'xs',
							'wrapper_class' => 'tutor_wp_editor_show_btn_wrap',
							'extra_class'   => 'tutor_wp_editor_show_btn button-secondary-lighten',
						] ); ?>

						<div class="tutor_wp_editor_wrap" style="display: none;">
							<form method="post" class="tutor-add-answer-form">
								<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
								<input type="hidden" value="tutor_add_answer" name="tutor_action"/>
								<input type="hidden" value="<?php echo esc_attr( $question->comment_ID ); ?>"
								       name="question_id"/>

								<div class="tutor-form-group">
										<textarea id="tutor_answer_<?php echo esc_attr( $question->comment_ID ); ?>"
										          name="answer"
										          class="tutor_add_answer_textarea"
										          placeholder="<?php esc_attr_e( 'Write your answer here...', 'edumall' ); ?>"></textarea>
								</div>

								<div class="tutor-form-group">
									<?php Edumall_Templates::render_button( [
										'link'          => [
											'url' => 'javascript:void(0);',
										],
										'text'          => esc_html__( 'Cancel', 'edumall' ),
										'size'          => 'xs',
										'wrapper_class' => 'tutor_cancel_wp_editor-wrap tm-button-inline',
										'extra_class'   => 'tutor_cancel_wp_editor button-danger',
									] ); ?>
									<button type="submit" class="tutor-button tutor_add_answer_btn tutor-success"
									        name="tutor_answer_search_btn">
										<?php esc_html_e( 'Add Answer', 'edumall' ); ?>
									</button>
								</div>
							</form>
						</div>
					</div>
				</div>

				<?php
			}
		} else {
			?>

			<div class="tutor-lesson-sidebar-emptyqa-wrap">
				<?php echo Edumall_Helper::get_file_contents( EDUMALL_THEME_SVG_DIR . '/lesson-question-answer.svg' ); ?>
				<h3><?php esc_html_e( 'No questions yet', 'edumall' ); ?></h3>
				<p><?php esc_html_e( 'Be the first to ask your question! You’ll be able to add details in the next step.', 'edumall' ); ?></p>
			</div>

			<?php
		}
		?>
	</div>

	<div class="tutor-add-question-wrap">

		<h3><?php esc_html_e( 'Ask a new question', 'edumall' ); ?></h3>

		<form method="post" id="tutor-ask-question-form">
			<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
			<input type="hidden" value="add_question" name="tutor_action"/>
			<input type="hidden" value="<?php echo esc_attr( $course_id ); ?>" name="tutor_course_id"/>

			<div class="tutor-form-group">
				<input type="text" name="question_title" value=""
				       placeholder="<?php esc_attr_e( 'Question Title', 'edumall' ); ?>">
			</div>

			<div class="tutor-form-group">
				<?php
				$editor_settings = array(
					'teeny'         => true,
					'media_buttons' => false,
					'quicktags'     => false,
					'editor_height' => 100,
				);
				wp_editor( null, 'question', $editor_settings );
				?>
			</div>

			<div class="tutor-form-group">
				<button type="submit" class="tutor_ask_question_btn tutor-button tutor-success"
				        name="tutor_question_search_btn"><?php esc_html_e( 'Submit my question', 'edumall' ); ?></button>
			</div>
		</form>
	</div>

</div>
<?php do_action( 'tutor_course/question_and_answer/after' ); ?>
