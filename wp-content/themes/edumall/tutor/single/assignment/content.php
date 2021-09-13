<?php
/**
 * @package       TutorLMS/Templates
 * @version       1.4.3
 *
 * @author        ThemeMove
 * @theme-since   1.3.2
 * @theme-version 2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wpdb;
$is_submitted  = false;
$is_submitting = tutor_utils()->is_assignment_submitting( get_the_ID() );
//get the comment
$post_id            = get_the_ID();
$user_id            = get_current_user_id();
$assignment_comment = tutor_utils()->get_single_comment_user_post_id( $post_id, $user_id );

if ( $assignment_comment != false ) {
	$submitted = $assignment_comment->comment_approved;
	$submitted == 'submitted' ? $is_submitted = true : '';
}
?>

<?php do_action( 'tutor_assignment/single/before/content' ); ?>
<div class="container">
	<div class="tutor-single-page-top-bar">
		<div class="tutor-topbar-item tutor-top-bar-course-link">
			<?php $course_id = Edumall_Tutor::instance()->get_course_id_by_lessons_id( get_the_ID() ); ?>
			<a href="<?php echo esc_url( get_the_permalink( $course_id ) ); ?>" class="tutor-topbar-home-btn">
				<i class="far fa-home"></i><?php esc_html_e( 'Go to course home', 'edumall' ); ?>
			</a>
		</div>
		<div class="tutor-topbar-item tutor-topbar-content-title-wrap">
					<span class="lesson-type-icon">
						<i class="far fa-file-alt"></i>
					</span>
			<?php the_title(); ?>
		</div>
	</div>

	<div class="tutor-lesson-content-area">
		<div class="tutor-assignment-information">
			<?php
			$time_duration = tutor_utils()->get_assignment_option( get_the_ID(), 'time_duration' );

			$total_mark = tutor_utils()->get_assignment_option( get_the_ID(), 'total_mark' );
			$pass_mark  = tutor_utils()->get_assignment_option( get_the_ID(), 'pass_mark' );

			global $post;
			$assignment_created_time = strtotime( $post->post_date_gmt );
			$time_duration_in_sec    = 0;
			if ( isset( $time_duration['value'] ) and isset( $time_duration['time'] ) ) {
				switch ( $time_duration['time'] ) {
					case 'hours':
						$time_duration_in_sec = 3600;
						break;
					case 'days':
						$time_duration_in_sec = 86400;
						break;
					case 'weeks':
						$time_duration_in_sec = 7 * 86400;
						break;
					default:
						$time_duration_in_sec = 0;
						break;
				}
			}
			$time_duration_in_sec = $time_duration_in_sec * $time_duration['value'];
			$remaining_time       = $assignment_created_time + $time_duration_in_sec;
			$now                  = time();
			?>
			<ul>
				<li>
					<?php esc_html_e( 'Time Duration : ', 'edumall' ); ?>
					<strong><?php echo $time_duration['value'] ? $time_duration['value'] . ' ' . $time_duration['time'] : esc_html__( 'No limit', 'edumall' ); ?></strong>
				</li>
				<?php
				/*
					*time_duration[value]==0 means no limit
					*if have unlimited time then no msg should
					*appear
					*/
				if ( $time_duration['value'] != 0 ) :
					if ( $now > $remaining_time and $is_submitted == false ) : ?>
						<li>
							<?php esc_html_e( 'Deadline : ', 'edumall' ) ?>
							<strong><?php esc_html_e( 'Expired', 'edumall' ); ?></strong>
						</li>
					<?php
					endif;
				endif;
				?>
				<!--<li>
                    <?php /*_e('Time Remaining : ') */ ?>
                    <strong><?php /*echo "7 Days, 12 Hour"; */ ?></strong>
                </li>-->

				<li>
					<?php esc_html_e( 'Total Points : ', 'edumall' ) ?>
					<strong><?php echo $total_mark; ?></strong>
				</li>
				<li>
					<?php esc_html_e( 'Minimum Pass Points : ', 'edumall' ) ?>
					<strong><?php echo $pass_mark; ?></strong>
				</li>
			</ul>
		</div>

		<hr/>
		<?php
		/*
		*time_duration[value]==0 means no limit
		*if have unlimited time then no msg should
		*appear
		*/
		if ( $time_duration['value'] != 0 ) :
			if ( $now > $remaining_time and $is_submitted == false ) : ?>
				<div class="tutor-asignment-expire tutor-alert-danger tutor-alert" style="margin:36px 0 46px">
					<?php esc_html_e( 'You have missed the submission deadline. Please contact the instructor for more information.', 'edumall' ); ?>
				</div>
			<?php
			endif;
		endif;
		?>
		<div class="tutor-assignment-content">
			<h2><?php esc_html_e( 'Description', 'edumall' ); ?></h2>
			<?php the_content(); ?>
		</div>

		<?php
		$assignment_attachments = maybe_unserialize( get_post_meta( get_the_ID(), '_tutor_assignment_attachments', true ) );
		if ( tutor_utils()->count( $assignment_attachments ) ) {
			?>
			<div class="tutor-assignment-attachments">
				<h2><?php esc_html_e( 'Attachments', 'edumall' ); ?></h2>
				<?php foreach ( $assignment_attachments as $attachment_id ) : ?>
					<?php if ( $attachment_id ) : ?>
						<?php
						$attachment_name = get_post_meta( $attachment_id, '_wp_attached_file', true );
						$attachment_name = substr( $attachment_name, strrpos( $attachment_name, '/' ) + 1 );
						?>
						<p class="attachment-file-name">
							<a href="<?php echo wp_get_attachment_url( $attachment_id ); ?>" target="_blank">
								<i class="tutor-icon-attach"></i> <?php echo $attachment_name; ?>
							</a>
						</p>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<?php
		}

		if ( $is_submitting and ( $remaining_time > $now or $time_duration['value'] == 0 ) ) { ?>
			<div class="tutor-assignment-submit-form-wrap">
				<h2><?php esc_html_e( 'Assignment answer form', 'edumall' ); ?></h2>

				<form action="" method="post" id="tutor_assignment_submit_form" enctype="multipart/form-data">
					<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
					<input type="hidden" value="tutor_assignment_submit" name="tutor_action"/>
					<input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">

					<?php $allowd_upload_files = (int) tutor_utils()->get_assignment_option( get_the_ID(), 'upload_files_limit' ); ?>

					<div class="tutor-form-group">
						<p><?php esc_html_e( 'Write your answer briefly', 'edumall' ); ?></p>
						<textarea name="assignment_answer"></textarea>
					</div>

					<div id="form_validation_response"></div>

					<?php if ( $allowd_upload_files ) : ?>
						<p><?php esc_html_e( 'Attach assignment files', 'edumall' ); ?></p>
						<div class="tutor-assignment-attachment-upload-wrap">
							<?php for ( $item = 1; $item <= $allowd_upload_files; $item++ ) : ?>
								<div class="tutor-form-group">
									<label for="tutor-assignment-input-<?php echo $item; ?>">
										<i class="tutor-icon-upload-file"></i><span><?php esc_html_e( 'Upload file', 'edumall' ); ?></span>
									</label>
									<input class="tutor-assignment-file-upload"
									       id="tutor-assignment-input-<?php echo $item; ?>" type="file"
									       name="attached_assignment_files[]">
								</div>
							<?php endfor; ?>
						</div>
					<?php endif; ?>
					<div class="tutor-assignment-submit-btn-wrap">
						<button type="submit" class="tutor-button tutor-success"
						        id="tutor_assignment_submit_btn"><?php esc_html_e( 'Submit Assignment', 'edumall' ); ?></button>
					</div>
				</form>
			</div>
		<?php } else { ?>
			<?php
			$submitted_assignment = tutor_utils()->is_assignment_submitted( get_the_ID() );
			if ( $submitted_assignment ) {
				$is_reviewed_by_instructor = get_comment_meta( $submitted_assignment->comment_ID, 'evaluate_time', true );

				if ( $is_reviewed_by_instructor ) {
					$assignment_id = $submitted_assignment->comment_post_ID;
					$submit_id     = $submitted_assignment->comment_ID;

					$max_mark   = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'total_mark' );
					$pass_mark  = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'pass_mark' );
					$given_mark = get_comment_meta( $submitted_assignment->comment_ID, 'assignment_mark', true );
					?>

					<?php ob_start(); ?>

					<div class="assignment-result-wrap">
						<h4><?php echo sprintf( __( 'You received %s points out of %s', 'edumall' ), "<span class='received-marks'>{$given_mark}</span>", "<span class='out-of-marks'>{$max_mark}</span>" ) ?></h4>
						<h4 class="submitted-assignment-grade">
							<?php esc_html_e( 'Your Grade is ', 'edumall' ); ?>
							<?php if ( $given_mark >= $pass_mark ) : ?>
								<span class="submitted-assignment-grade-pass">
											<?php esc_html_e( 'Passed', 'edumall' ); ?>
										</span>
							<?php else : ?>
								<span class="submitted-assignment-grade-failed">
											<?php esc_html_e( 'Failed', 'edumall' ); ?>
										</span>
							<?php endif; ?>
						</h4>
					</div>

					<?php echo apply_filters( 'tutor_assignment/single/results/after', ob_get_clean(), $submit_id, $assignment_id ); ?>

				<?php } ?>

				<div class="tutor-assignments-submitted-answers-wrap">
					<h2><?php esc_html_e( 'Your Answers', 'edumall' ); ?></h2>

					<?php echo nl2br( stripslashes( $submitted_assignment->comment_content ) );

					$attached_files = get_comment_meta( $submitted_assignment->comment_ID, 'uploaded_attachments', true );
					if ( $attached_files ) {
						$attached_files = json_decode( $attached_files, true );

						if ( tutor_utils()->count( $attached_files ) ) {

							?>
							<h2><?php esc_html_e( 'Your uploaded file(s)', 'edumall' ); ?></h2>

							<?php
							$upload_dir     = wp_get_upload_dir();
							$upload_baseurl = trailingslashit( tutor_utils()->array_get( 'baseurl', $upload_dir ) );
							foreach ( $attached_files as $attached_file ) {
								?>
								<div class="uploaded-files">
									<a href="<?php echo $upload_baseurl . tutor_utils()->array_get( 'uploaded_path', $attached_file ); ?>"
									   target="_blank"><?php echo tutor_utils()->array_get( 'name', $attached_file ); ?>
									</a>
								</div>
								<?php
							}
						}
					}
					?>

					<?php if ( $is_reviewed_by_instructor ) : ?>
						<div class="instructor-note-wrap">
							<h2><?php esc_html_e( 'Instructor Note', 'edumall' ); ?></h2>
							<p><?php echo nl2br( get_comment_meta( $submitted_assignment->comment_ID, 'instructor_note', true ) ) ?></p>
						</div>
					<?php endif; ?>
				</div>

				<?php
			} else { ?>
				<div class="tutor-assignment-start-btn-wrap">
					<form action="" method="post" id="tutor_assignment_start_form">
						<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
						<input type="hidden" value="tutor_assignment_start_submit" name="tutor_action"/>
						<input type="hidden" name="assignment_id" value="<?php echo get_the_ID(); ?>">
						<button type="submit" class="tutor-button"
						        id="tutor_assignment_start_btn" <?php if ( $time_duration['value'] != 0 ) {
							if ( $now > $remaining_time ) {
								echo "disabled";
							}
						} ?>>
							<?php esc_html_e( 'Submit assignment', 'edumall' ); ?>
						</button>
					</form>
				</div>
				<?php
			}
		}
		?>

		<?php tutor_next_previous_pagination(); ?>

	</div>
</div>

<?php do_action( 'tutor_assignment/single/after/content' ); ?>
