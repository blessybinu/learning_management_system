<?php
/**
 * Display attachments
 *
 * @since         v.1.0.0
 * @author        themeum
 * @url https://themeum.com
 * @package       TutorLMS/Templates
 * @version       1.4.3
 *
 * @theme-since   1.0.0
 * @theme-version 2.6.1
 */

defined( 'ABSPATH' ) || exit;

global $edumall_course;
if ( $edumall_course instanceof Edumall_Course ) {
	$attachments = $edumall_course->get_attachments();
} else {
	$attachments = tutor_utils()->get_attachments();
}

$open_mode_view = apply_filters( 'tutor_pro_attachment_open_mode', 'view' ) == 'view' ? ' target="_blank" ' : null;

do_action( 'tutor_global/before/attachments' );
?>

<?php if ( ! empty( $attachments ) ) : ?>
	<div class="tutor-single-course-segment tutor-attachments-wrap">
		<h4 class="tutor-segment-title"><?php esc_html_e( 'Attachments', 'edumall' ); ?></h4>

		<div class="attachments-list">
			<?php foreach ( $attachments as $attachment ) { ?>
				<div class="tutor-individual-attachment">
					<a href="<?php echo esc_url($attachment->url); ?>" class="tutor-lesson-attachment clearfix"
					   <?php if( $open_mode_view ) : ?>
						   download="<?php echo esc_attr($attachment->name); ?>"
					   <?php endif; ?>
					>
						<div class="tutor-attachment-icon">
							<i class="tutor-icon-<?php Edumall_Helper::e( $attachment->icon ); ?>"></i>
						</div>
						<div class="tutor-attachment-info">
							<span class="attachment-file-name"><?php Edumall_Helper::e( $attachment->name ); ?></span>
							<span class="attachment-file-size"><?php Edumall_Helper::e( $attachment->size ); ?></span>
						</div>
					</a>
				</div>
			<?php } ?>
		</div>
	</div>
<?php endif; ?>

<?php
do_action( 'tutor_global/after/attachments' );
