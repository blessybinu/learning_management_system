<?php
/**
 * Template for displaying Assignments
 *
 * @since   v.1.3.4
 *
 * @author  Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;

$per_page     = 20;
$current_page = max( 1, tutor_utils()->avalue_dot( 'current_page', $_GET ) );
$offset       = ( $current_page - 1 ) * $per_page;

$current_user = get_current_user_id();
$assignments  = tutor_utils()->get_assignments_by_instructor( null, compact( 'per_page', 'offset' ) );
?>
	<h3><?php esc_html_e( 'Assignments', 'edumall' ); ?></h3>

<?php if ( $assignments->count ) : ?>
	<div class="table-assignments dashboard-table-wrapper dashboard-table-responsive">
		<div class="dashboard-table-container">
			<table class="dashboard-table">
				<thead>
				<tr>
					<th class="col-course-info"><?php esc_html_e( 'Course Name', 'edumall' ) ?></th>
					<th class="col-total-mark"><?php esc_html_e( 'Total Mark', 'edumall' ) ?></th>
					<th class="col-total-submit"><?php esc_html_e( 'Total Submit', 'edumall' ) ?></th>
					<th class="col-action">&nbsp;</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ( $assignments->results as $item ) : ?>
					<?php
					$max_mark      = tutor_utils()->get_assignment_option( $item->ID, 'total_mark' );
					$course_id     = tutor_utils()->get_course_id_by_assignment( $item->ID );
					$course_url    = tutor_utils()->get_tutor_dashboard_page_permalink( 'assignments/course' );
					$submitted_url = tutor_utils()->get_tutor_dashboard_page_permalink( 'assignments/submitted' );
					$comment_count = $wpdb->get_var( "SELECT COUNT(comment_ID) FROM {$wpdb->comments} WHERE comment_type = 'tutor_assignment' AND comment_post_ID = $item->ID" );
					// @TODO: assign post_meta is empty if user don't click on update button (http://prntscr.com/oax4t8) but post status is publish
					?>
					<tr>
						<td>
							<h5 class="assignment-title"><?php echo esc_html( $item->post_title ); ?></h5>
							<h5 class="course-title">
								<a href='<?php echo esc_url( $course_url . '?course_id=' . $course_id ) ?>'><?php echo esc_html__( 'Course: ', 'edumall' ) . get_the_title( $course_id ); ?> </a>
							</h5>
						</td>
						<td>
							<div
								class="heading col-heading-mobile"><?php esc_html_e( 'Total Mark:', 'edumall' ); ?></div>
							<?php echo esc_html( $max_mark ); ?>
						</td>
						<td>
							<div
								class="heading col-heading-mobile"><?php esc_html_e( 'Total Submit:', 'edumall' ); ?></div>
							<?php echo esc_html( number_format_i18n( $comment_count ) ); ?>
						</td>
						<td>
							<?php
							$view_detail_btn_class = 'review-assignment-link hint--bounce ';
							$view_detail_btn_class .= !EDUMALL_IS_RTL ? 'hint--top-left' : 'hint--top-right';

							?>
							<a class="<?php echo esc_attr( $view_detail_btn_class ); ?>"
							   aria-label="<?php esc_attr_e( 'View Details', 'edumall' ); ?>"
							   href="<?php echo esc_url( $submitted_url . '?assignment=' . $item->ID ); ?>">
								<i class="far fa-angle-right"></i>
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
			'total'   => ceil( $assignments->count / $per_page ),
		] )
		?>
	</div>
<?php else : ?>
	<div class="dashboard-no-content-found">
		<?php esc_html_e( 'No assignment available.', 'edumall' ); ?>
	</div>
<?php endif;
