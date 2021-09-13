<?php
/**
 * Template for displaying course reviews
 *
 * @author        Themeum
 * @url https://themeum.com
 * @package       TutorLMS/Templates
 * @since         1.0.0
 * @version       1.4.5
 *
 * @theme-since   1.0.0
 * @theme-version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'tutor_course/single/enrolled/before/reviews' );

global $edumall_course;

$reviews = $edumall_course->get_reviews();
?>

<div class="tutor-single-course-segment tutor-course-reviews-wrap">

	<?php if ( ! empty( $reviews ) ) : ?>

		<div class="tutor-course-reviews-average-wrap">
			<h4 class="tutor-segment-title"><?php esc_html_e( 'Student Feedback', 'edumall' ); ?></h4>

			<div class="tutor-course-reviews-average">
				<div class="course-ratings-average-wrap">
					<div class="course-avg-rating primary-color">
						<?php
						$rating = $edumall_course->get_rating();
						echo number_format( $rating->rating_avg, 1 );
						?>
					</div>
					<?php Edumall_Templates::render_rating( $rating->rating_avg, [ 'wrapper_class' => 'course-avg-rating-html' ] ); ?>
					<div
						class="course-avg-rating-total"><?php echo sprintf( esc_html( _n( '%s Rating', '%s Ratings', $rating->rating_count, 'edumall' ) ), '<span>' . $rating->rating_count . '</span>' ); ?></div>
				</div>

				<div class="course-ratings-count-meter-wrap">
					<?php
					foreach ( $rating->count_by_value as $rating_point => $rating_numbers ) {
						$rating_percent = Edumall_Helper::calculate_percentage( $rating_numbers, $rating->rating_count );
						?>
						<div class="course-rating-meter">
							<div class="rating-meter-col">
								<?php Edumall_Templates::render_rating( $rating_point ); ?>
							</div>
							<div class="rating-meter-col rating-meter-bar-wrap">
								<div class="rating-meter-bar">
									<div class="rating-meter-fill-bar"
									     style="<?php echo "width: {$rating_percent}%;"; ?>"></div>
								</div>
							</div>
							<div class="rating-meter-col rating-text-col">
								<?php echo "{$rating_percent}%"; ?>
							</div>
						</div>
					<?php } ?>
				</div>

			</div>
		</div>

		<div class="tutor-course-reviews-list-wrap">
			<?php $reviews_count = count( $reviews ); ?>

			<h4 class="tutor-segment-title"><?php echo sprintf( esc_html__( 'Reviews %s', 'edumall' ), '<span>(' . $reviews_count . ')</span>' ); ?></h4>

			<div class="tutor-course-reviews-list">
				<?php
				foreach ( $reviews as $review ) {
					$profile_url   = tutor_utils()->profile_url( $review->user_id );
					$wrapper_class = 'tutor-review-individual-item tutor-review-' . $review->comment_ID;
					?>
					<div class="<?php echo esc_attr( $wrapper_class ); ?>">
						<div class="review-header">
							<div class="review-avatar">
								<a href="<?php echo esc_url( $profile_url ); ?>">
									<?php echo edumall_get_avatar( $review->user_id, 52 ); ?>
								</a>
							</div>
							<div class="tutor-review-user-info">
								<h4 class="review-name">
									<a href="<?php echo esc_url( $profile_url ); ?>"><?php echo esc_html( $review->display_name ); ?> </a>
								</h4>
								<p class="review-date">
									<?php echo sprintf( esc_html__( '%s ago', 'edumall' ), human_time_diff( strtotime( $review->comment_date ) ) ); ?>
								</p>
							</div>
						</div>
						<div class="review-body">
							<?php Edumall_Templates::render_rating( $review->rating, [ 'wrapper_class' => 'review-rating' ] ) ?>
							<div class="review-content">
								<?php echo wpautop( stripslashes( $review->comment_content ) ); ?>
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>

	<?php endif; ?>

	<?php tutor_course_target_review_form_html(); ?>
</div>

<?php do_action( 'tutor_course/single/enrolled/after/reviews' ); ?>
