<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

global $post;
$currentPost = $post;

$course            = tutor_utils()->get_course_by_quiz( get_the_ID() );
$previous_attempts = tutor_utils()->quiz_attempts();
$attempted_count   = is_array( $previous_attempts ) ? count( $previous_attempts ) : 0;

$attempts_allowed = tutor_utils()->get_quiz_option( get_the_ID(), 'attempts_allowed', 0 );
$passing_grade    = tutor_utils()->get_quiz_option( get_the_ID(), 'passing_grade', 0 );

$attempt_remaining = $attempts_allowed - $attempted_count;

do_action( 'tutor_quiz/single/before/top' );
?>

	<div class="tutor-quiz-header">
		<h2 class="entry-quiz-title"><?php echo get_the_title(); ?></h2>
		<h5 class="entry-quiz-course-title">
			<?php esc_html_e( 'Course', 'edumall' ); ?>:
			<a href="<?php echo get_the_permalink( $course->ID ); ?>"><?php echo get_the_title( $course->ID ); ?></a>
		</h5>
		<ul class="tutor-quiz-meta">
			<?php
			$total_questions = tutor_utils()->total_questions_for_student_by_quiz( get_the_ID() );
			$time_limit      = tutor_utils()->get_quiz_option( get_the_ID(), 'time_limit.time_value' );
			?>

			<?php if ( $total_questions ) : ?>
				<li>
					<span class="meta-label"><?php esc_html_e( 'Questions', 'edumall' ); ?></span>
					<span class="meta-value"><?php echo esc_html( $total_questions ); ?></span>
				</li>
			<?php endif; ?>

			<?php if ( $time_limit ) : ?>
				<?php
				$time_type = tutor_utils()->get_quiz_option( get_the_ID(), 'time_limit.time_type' );
				?>
				<li>
					<span class="meta-label"><?php esc_html_e( 'Time', 'edumall' ); ?></span>
					<span class="meta-value"><?php echo esc_html( $time_limit . ' ' . $time_type ); ?></span>
				</li>
			<?php endif; ?>

			<li>
				<span class="meta-label"><?php esc_html_e( 'Attempts Allowed', 'edumall' ); ?></span>
				<span class="meta-value">
				<?php echo 0 === $attempts_allowed ? esc_html__( 'No limit', 'edumall' ) : $attempts_allowed; ?>
			</span>
			</li>

			<?php if ( $attempted_count ) : ?>
				<li>
					<span class="meta-label"><?php esc_html_e( 'Attempted', 'edumall' ); ?></span>
					<span class="meta-value"><?php echo esc_html( $attempted_count ); ?></span>
				</li>
			<?php endif; ?>

			<li>
				<span class="meta-label"><?php esc_html_e( 'Attempts Remaining', 'edumall' ); ?></span>
				<span class="meta-value">
				<?php echo 0 === $attempt_remaining ? esc_html__( 'No limit', 'edumall' ) : $attempt_remaining; ?>
			</span>
			</li>

			<?php if ( $passing_grade ): ?>
				<li>
					<span class="meta-label"><?php esc_html_e( 'Passing Grade', 'edumall' ); ?></span>
					<span class="meta-value"><?php echo esc_html( $passing_grade . '%' ); ?></span>
				</li>
			<?php endif; ?>
		</ul>
	</div>

<?php do_action( 'tutor_quiz/single/after/top' );
