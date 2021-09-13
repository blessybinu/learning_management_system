<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Template for displaying Announcements
 *
 * @since   v.1.7.9
 *
 * @author  Themeum
 * @url https://themeum.com
 *
 * @package TutorLMS/Templates
 * @version 1.7.9
 */
$per_page = 10;
$paged    = isset( $_GET['current_page'] ) ? $_GET['current_page'] : 1;

$order_filter  = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
$search_filter = isset( $_GET['search'] ) ? $_GET['search'] : '';
//announcement's parent
$course_id   = isset( $_GET['course-id'] ) ? $_GET['course-id'] : '';
$date_filter = isset( $_GET['date'] ) ? $_GET['date'] : '';

$year  = date( 'Y', strtotime( $date_filter ) );
$month = date( 'm', strtotime( $date_filter ) );
$day   = date( 'd', strtotime( $date_filter ) );

$args = array(
	'post_type'      => 'tutor_announcements',
	'post_status'    => 'publish',
	's'              => sanitize_text_field( $search_filter ),
	'post_parent'    => sanitize_text_field( $course_id ),
	'posts_per_page' => sanitize_text_field( $per_page ),
	'paged'          => sanitize_text_field( $paged ),
	'orderBy'        => 'ID',
	'order'          => sanitize_text_field( $order_filter ),

);
if ( ! empty( $date_filter ) ) {
	$args['date_query'] = array(
		array(
			'year'  => $year,
			'month' => $month,
			'day'   => $day,
		),
	);
}
if ( ! current_user_can( 'administrator' ) ) {
	$args['author'] = get_current_user_id();
}
$the_query = new WP_Query( $args );

//get courses.
$courses        = ( current_user_can( 'administrator' ) ) ? tutils()->get_courses() : tutils()->get_courses_by_instructor();
$image_base     = tutor()->url . '/assets/images/';
$notify_checked = tutils()->get_option( 'email_to_students.new_announcement_posted' );
?>
<h3><?php esc_html_e( 'Announcement', 'edumall' ); ?></h3>
<div class="tutor-dashboard-content-inner tutor-frontend-dashboard-withdrawal dashboard-content-box">
	<div class="withdraw-page-current-balance new-announcement-wrap">
		<div class="balance-info new-announcement-content">
			<div class="tutor-announcement-big-icon">
				<span class="far fa-bell"></span>
			</div>
			<div>
				<small><?php esc_html_e( 'Create Announcement', 'edumall' ); ?></small>
				<p>
					<strong>
						<?php esc_html_e( 'Notify all students of your course', 'edumall' ); ?>
					</strong>
				</p>
			</div>
		</div>
		<div class="new-announcement-button">
			<button type="button" class="tutor-btn tutor-announcement-add-new">
				<?php esc_html_e( 'Add New Announcement', 'edumall' ); ?>
			</button>
		</div>
	</div>
</div>
<!--sorting-->
<div class="tutor-dashboard-announcement-sorting dashboard-content-box">
	<div class="tutor-form-group">
		<label for="">
			<?php esc_html_e( 'Courses', 'edumall' ); ?>
		</label>
		<select class="tutor-report-category tutor-announcement-course-sorting ignore-nice-select">

			<option value=""><?php esc_html_e( 'All', 'edumall' ); ?></option>

			<?php if ( $courses ) : ?>
				<?php foreach ( $courses as $course ) : ?>
					<option
						value="<?php echo esc_attr( $course->ID ) ?>" <?php selected( $course_id, $course->ID, 'selected' ) ?>>
						<?php echo $course->post_title; ?>
					</option>
				<?php endforeach; ?>
			<?php else : ?>
				<option value=""><?php esc_html_e( 'No course found', 'edumall' ); ?></option>
			<?php endif; ?>
		</select>
	</div>

	<div class="tutor-form-group">
		<label><?php esc_html_e( 'Sort By', 'edumall' ); ?></label>
		<select class="tutor-announcement-order-sorting ignore-nice-select">
			<option <?php selected( $order_filter, 'ASC' ); ?>><?php esc_html_e( 'ASC', 'edumall' ); ?></option>
			<option <?php selected( $order_filter, 'DESC' ); ?>><?php esc_html_e( 'DESC', 'edumall' ); ?></option>
		</select>
	</div>

	<div class="tutor-form-group tutor-announcement-datepicker">
		<label><?php esc_html_e( 'Date', 'edumall' ); ?></label>
		<div class="input-group">
			<input type="text" class="tutor-announcement-date-sorting" id="tutor-announcement-datepicker"
			       value="<?php echo $date_filter; ?>" autocomplete="off"/>
			<i class="far fa-calendar"></i>
		</div>
	</div>
</div>
<!--sorting end-->

<div class="tutor-announcement-table-wrap dashboard-table-wrapper dashboard-table-responsive">
	<div class="dashboard-table-container">
		<table class="dashboard-table">
			<thead>
			<tr>
				<th style="width:24%"><?php esc_html_e( 'Date', 'edumall' ); ?></th>
				<th style="text-align:left"><?php esc_html_e( 'Announcements', 'edumall' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( $the_query->have_posts() ) : ?>
				<?php foreach ( $the_query->posts as $post ) : ?>
					<?php
					$course      = get_post( $post->post_parent );
					$dateObj     = date_create( $post->post_date );
					$date_format = date_format( $dateObj, 'F j, Y, g:i a' );
					?>
					<tr id="tutor-announcement-tr-<?php echo $post->ID; ?>">
						<td class="tutor-announcement-date"><?php echo esc_html( $date_format ); ?></td>
						<td class="tutor-announcement-content-wrap">
							<div class="tutor-announcement-content">
                                <span>
                                    <?php echo esc_html( $post->post_title ); ?>
                                </span>
								<p>
									<?php echo $course ? $course->post_title : ''; ?>
								</p>
							</div>
							<div class="tutor-announcement-buttons">
								<li>
									<a type="button" course-name="<?php echo esc_attr( $course->post_title ) ?>"
									   announcement-date="<?php echo esc_attr( $date_format ) ?>"
									   announcement-title="<?php echo esc_attr( $post->post_title ); ?>"
									   announcement-summary="<?php echo esc_attr( $post->post_content ); ?>"
									   course-id="<?php echo esc_attr( $post->post_parent ); ?>"
									   announcement-id="<?php echo esc_attr( $post->ID ); ?>"
									   class="tutor-btn bordered-btn tutor-announcement-details">
										<?php esc_html_e( 'Details', 'edumall' ); ?>
									</a>
								</li>
								<li class="tutor-dropdown ">
									<i class="tutor-icon-action"></i>
									<ul class="tutor-dropdown-menu">
										<li announcement-title="<?php echo $post->post_title; ?>"
										    course-id="<?php echo $post->post_parent; ?>"
										    announcement-id="<?php echo $post->ID; ?>" class="tutor-announcement-edit">
											<i class="tutor-icon-pencil"></i>
											<?php esc_html_e( 'Edit', 'edumall' ); ?>
										</li>
										<li class="tutor-announcement-delete"
										    announcement-id="<?php echo $post->ID; ?>">
											<i class="tutor-icon-garbage"></i>
											<?php esc_html_e( 'Delete', 'edumall' ); ?>
										</li>
									</ul>
								</li>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="2">
						<?php esc_html_e( 'Announcements not found', 'edumall' ); ?>
					</td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<!--pagination-->
<div class="tutor-pagination">
	<?php
	$big = 999999999; // need an unlikely integer

	echo paginate_links( array(

		'format'  => '?current_page=%#%',
		'current' => $paged,
		'total'   => $the_query->max_num_pages,
	) );

	?>
</div>
<!--pagination end-->
<?php /*tutor_load_template( 'dashboard.announcements.create', [
	'courses'        => $courses,
	'notify_checked' => $notify_checked,
] ); */?><!--
--><?php /*tutor_load_template( 'dashboard.announcements.update', [
	'courses'        => $courses,
	'notify_checked' => $notify_checked,
] ); */?>

<?php
include 'announcements/create.php';
include 'announcements/update.php';
include 'announcements/details.php';
?>
