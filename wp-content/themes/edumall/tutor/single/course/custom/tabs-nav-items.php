<?php
/**
 * Display tabs nav
 *
 * @since   v.1.0.0
 * @author  thememove
 * @url https://thememove.com
 *
 * @package Edumall/TutorLMS/Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * @var Edumall_Course $edumall_course
 */
global $edumall_course;

/**
 * @var WP_Query $topics
 */
$topics = $edumall_course->get_topics();

$course_nav_items = tutor_utils()->course_sub_pages();
$course_nav_items = apply_filters( 'tutor_course/single/enrolled/nav_items', $course_nav_items );
?>
<li class="active">
	<a href="#tutor-course-tab-overview"><?php esc_html_e( 'Overview', 'edumall' ); ?></a>
</li>
<?php if ( $topics->have_posts() ): ?>
	<li>
		<a href="#tutor-course-tab-curriculum"><?php esc_html_e( 'Curriculum', 'edumall' ); ?></a>
	</li>
<?php endif; ?>
<?php if ( $edumall_course->is_enrolled() && ! empty( $edumall_course->get_attachments() ) ): ?>
	<li>
		<a href="#tutor-course-tab-resources"><?php esc_html_e( 'Resources', 'edumall' ); ?></a>
	</li>
<?php endif; ?>
<?php if ( $edumall_course->is_enrolled() ): ?>
	<li>
		<a href="#tutor-course-tab-question-and-answer"><?php esc_html_e( 'Question & Answer', 'edumall' ); ?></a>
	</li>
<?php endif; ?>
<li>
	<a href="#tutor-course-tab-instructors"><?php esc_html_e( 'Instructors', 'edumall' ); ?></a>
</li>
<?php if ( $edumall_course->is_enrolled() ): ?>
	<li>
		<a href="#tutor-course-tab-announcements"><?php esc_html_e( 'Announcements', 'edumall' ); ?></a>
	</li>
<?php endif; ?>
<?php if ( $edumall_course->is_enrolled() && isset( $course_nav_items['google-classroom-stream'] ) ): ?>
	<li>
		<a href="#tutor-course-tab-google-classroom-stream"><?php echo esc_html( $course_nav_items['google-classroom-stream'] ); ?></a>
	</li>
<?php endif; ?>
<?php if ( $edumall_course->is_enrolled() && isset( $course_nav_items['gradebook'] ) ): ?>
	<li>
		<a href="#tutor-course-tab-gradebook"><?php echo esc_html( $course_nav_items['gradebook'] ); ?></a>
	</li>
<?php endif; ?>
<?php if ( $edumall_course->get_reviews() ) : ?>
	<li>
		<a href="#tutor-course-tab-reviews"><?php esc_html_e( 'Reviews', 'edumall' ); ?></a>
	</li>
<?php endif; ?>
<!--
<li>
	<a href="#tutor-course-tab-frequently"><?php /*esc_html_e( 'Frequently', 'edumall' ); */ ?></a>
</li>
-->
<!-- Future support -->
<!--
<li>
	<a href="#tutor-course-tab-more-courses"><?php /*esc_html_e( 'More Courses', 'edumall' ); */ ?></a>
</li>
-->
<!-- Future support -->
