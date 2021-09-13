<?php
/**
 * @package       TutorLMS/Templates
 * @version       1.4.3
 *
 * @theme-since   1.0.0
 * @theme-version 2.7.2
 */

defined( 'ABSPATH' ) || exit;
?>

<h3><?php esc_html_e( 'My Courses', 'edumall' ); ?></h3>

<div class="tutor-dashboard-content-inner edumall-animation-zoom-in">
	<?php
	$my_courses = tutor_utils()->get_courses_by_instructor( null, [ 'publish', 'draft', 'pending' ] );
	?>
	<?php if ( is_array( $my_courses ) && count( $my_courses ) ): ?>
		<?php
		global $post;
		?>
		<?php foreach ( $my_courses as $post ): ?>
			<?php
			setup_postdata( $post );

			$course_rating = tutor_utils()->get_course_rating();
			$avg_rating    = $course_rating->rating_avg;
			$rating_count  = $course_rating->rating_count;
			?>
			<div id="tutor-dashboard-course-<?php the_ID(); ?>"
			     class="edumall-box tutor-mycourse-wrap tutor-mycourse-<?php the_ID(); ?>">
				<div class="tutor-mycourse-thumbnail edumall-image">
					<a href="<?php the_permalink(); ?>">
						<?php Edumall_Image::the_post_thumbnail( [
							'size' => '480x295',
						] ); ?>
					</a>
				</div>
				<div class="tutor-mycourse-content">
					<div class="tutor-mycourse-rating">

						<?php Edumall_Templates::render_rating( $avg_rating, [
							'style' => '03',
						] ); ?>
						<span class="rating-count"><?php echo "({$rating_count})"; ?></span>
					</div>
					<h3 class="course-title"><a href="<?php the_permalink(); ?>"
					                            class="link-in-title"><?php the_title(); ?></a></h3>
					<div class="tutor-meta tutor-course-metadata">
						<?php
						$total_lessons     = tutor_utils()->get_lesson_count_by_course();
						$completed_lessons = tutor_utils()->get_completed_lesson_count_by_course();

						$course_duration = get_tutor_course_duration_context();
						$course_students = tutor_utils()->count_enrolled_users_by_course();
						?>
						<ul class="course-meta">
							<li class="course-meta-status">
								<?php
								$status = ucwords( $post->post_status );
								$status = ( $status == 'Publish' ) ? __( 'Published', 'edumall' ) : $status; // WPCS: XSS ok.
								?>
								<span class="meta-label"><?php esc_html_e( 'Status:', 'edumall' ); ?></span>
								<span class="meta-value"><?php echo esc_html( $status ); ?></span>
							</li>
							<?php if ( ! empty( $course_duration ) ) : ?>
								<li class="course-meta-duration">
									<span class="meta-label"><?php esc_html_e( 'Duration:', 'edumall' ); ?></span>
									<span class="meta-value"><?php echo esc_html( $course_duration ); ?></span>
								</li>
							<?php endif; ?>
							<li class="course-meta-total-enrolled">
								<span class="meta-label"><?php esc_html_e( 'Students:', 'edumall' ); ?></span>
								<span class="meta-value"><?php echo esc_html( $course_students ); ?></span>
							</li>
						</ul>
					</div>
					<div class="mycourse-footer">
						<div class="tutor-mycourses-stats">
							<?php echo tutor_utils()->tutor_price( tutor_utils()->get_course_price() ); ?>
							<div class="course-actions">
								<a href="<?php echo esc_url( tutor_utils()->course_edit_link( $post->ID ) ); ?>"
								   class="tutor-mycourse-edit edit">
									<i class="fal fa-pencil-alt"></i><?php esc_html_e( 'Edit', 'edumall' ); ?>
								</a>
								<a href="#tutor-course-delete"
								   class="tutor-dashboard-element-delete-btn tutor-mycourse-delete"
								   data-id="<?php echo esc_attr( $post->ID ); ?>">
									<i class="fal fa-trash-alt"></i><?php esc_html_e( 'Delete', 'edumall' ) ?>
								</a>
								<?php do_action( 'tutor_course_dashboard_actions_after', $post->ID ); ?>
							</div>
						</div>
					</div>
				</div>

			</div>
		<?php endforeach; ?>
		<?php wp_reset_postdata(); ?>
	<?php else : ?>
		<div class="dashboard-no-content-found">
			<?php esc_html_e( 'You do not have any courses yet.', 'edumall' ); ?>
		</div>
	<?php endif; ?>

	<div class="tutor-frontend-modal delete-course-confirm-modal" data-popup-rel="#tutor-course-delete"
	     style="display: none">
		<div class="tutor-frontend-modal-overlay"></div>
		<div class="tutor-frontend-modal-content">
			<button class="tm-close tutor-icon-line-cross"></button>
			<div class="tutor-modal-body tutor-course-delete-popup">
				<img src="<?php echo tutor()->url . 'assets/images/delete-icon.png'; ?>"
				     alt="<?php esc_attr_e( 'Delete course?', 'edumall' ); ?>">
				<h3 class="popup-title"><?php esc_html_e( 'Delete This Course?', 'edumall' ); ?></h3>
				<p class="popup-description"><?php esc_html_e( 'You are going to delete this course, it can\'t be undone', 'edumall' ); ?></p>
				<div class="tutor-modal-button-group">
					<form action="" id="tutor-dashboard-delete-element-form">
						<input type="hidden" name="action" value="edumall_delete_dashboard_course">
						<input type="hidden" name="course_id" id="tutor-dashboard-delete-element-id" value="">
						<button type="button"
						        class="tutor-modal-btn-cancel"><?php esc_html_e( 'Cancel', 'edumall' ) ?></button>
						<button type="submit"
						        class="tutor-danger tutor-modal-element-delete-btn"><?php esc_html_e( 'Yes, Delete Course', 'edumall' ) ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>

</div>
