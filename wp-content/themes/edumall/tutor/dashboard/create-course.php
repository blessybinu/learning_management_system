<?php
/**
 * @package       TutorLMS/Templates
 * @version       1.4.3
 *
 * @theme-since   1.0.0
 * @theme-version 2.8.9
 */

defined( 'ABSPATH' ) || exit;

get_tutor_header( true );
do_action( 'tutor_load_template_before', 'dashboard.create-course', null );
global $post;

$course_id          = get_the_ID();
$can_publish_course = (bool) tutor_utils()->get_option( 'instructor_can_publish_course' ) || current_user_can( 'administrator' );

if ( ! tutor_utils()->can_user_edit_course( get_current_user_id(), $course_id ) ) {
	tutor_load_template( 'permission-denied' );

	return;
}
?>

<?php do_action( 'tutor/dashboard_course_builder_before' ); ?>
	<form action="" id="tutor-frontend-course-builder" class="tutor-frontend-course-builder" method="post"
	      enctype="multipart/form-data">

		<?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>

		<header id="page-header" class="page-header header-dark header-sticky-dark-logo">
			<div class="page-header-place-holder"></div>
			<div id="page-header-inner" class="page-header-inner" data-sticky="1">
				<div class="container">
					<div class="header-wrap">
						<div class="header-left">
							<?php
							$branding_args = [
								'reverse_scheme' => true,
							];
							?>
							<?php edumall_load_template( 'branding', null, $branding_args ); ?>

							<button type="submit"
							        class="tutor-dashboard-builder-draft-btn tutor-btn bordered-btn"
							        name="course_submit_btn"
							        value="save_course_as_draft">
								<i class="fal fa-save"></i>
								<span><?php esc_html_e( 'Save', 'edumall' ); ?></span>
							</button>
						</div>

						<div class="header-right">
							<div class="header-content-inner">
								<div id="header-right-inner" class="header-right-inner">
									<div class="header-right-inner-content">
										<?php Edumall_Templates::render_button( [
											'link'        => [
												'url'         => get_the_permalink( $course_id ),
												'is_external' => true,
											],
											'text'        => esc_html__( 'Preview', 'edumall' ),
											'icon'        => 'fal fa-glasses',
											'extra_class' => 'button-grey',
										] ); ?>

										<div class="form-submit">
											<?php if ( $can_publish_course ) : ?>
												<button class="tutor-button" type="submit"
												        name="course_submit_btn"
												        value="publish_course"><?php esc_html_e( 'Publish Course', 'edumall' ); ?></button>
											<?php else: ?>
												<button class="tutor-button" type="submit"
												        name="course_submit_btn"
												        value="submit_for_review"><?php esc_html_e( 'Submit for Review', 'edumall' ); ?></button>
											<?php endif; ?>
										</div>

										<div class="return-dashboard-link">
											<a href="<?php echo tutor_utils()->tutor_dashboard_url(); ?>"> <?php esc_html_e( 'Exit', 'edumall' ); ?></a>
										</div>
									</div>
								</div>

								<?php Edumall_Header::instance()->print_more_tools_button(); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</header>

		<div class="tutor-frontend-course-builder-section tm-sticky-parent">
			<div class="container">
				<div class="row">
					<div class="col-lg-8">
						<input type="hidden" value="tutor_add_course_builder" name="tutor_action"/>
						<input type="hidden" name="course_ID" id="course_ID" value="<?php echo get_the_ID(); ?>">
						<input type="hidden" name="post_ID" id="post_ID" value="<?php echo get_the_ID(); ?>">
						<div class="tutor-dashboard-course-builder-wrap tm-sticky-column">

							<!--since 1.8.0 alert message -->
							<?php
							$instructor_can_publish = tutils()->get_option( 'instructor_can_publish_course' );
							?>
							<?php if ( current_user_can( 'tutor_instructor' ) && ! current_user_can( 'administrator' ) ) : ?>
								<?php if ( isset( $_COOKIE['course_submit_for_review'] ) && ! $instructor_can_publish ) : ?>
									<div class="tutor-alert tutor-alert-info">
										<?php esc_html_e( 'Your course has been submitted to the admin. It will be published once it has been reviewed by the admins.', 'edumall' ); ?>
									</div>
								<?php endif; ?>
							<?php endif; ?>
							<!--alert message end -->

							<?php do_action( 'tutor/dashboard_course_builder_form_field_before' ); ?>

							<div class="tutor-course-builder-section tutor-course-builder-info">
								<div class="tutor-course-builder-section-title">
									<h3>
										<i class="tutor-icon-down"></i><span><?php esc_html_e( 'Course Info', 'edumall' ); ?></span>
									</h3>
								</div> <!--.tutor-course-builder-section-title-->
								<div class="tutor-course-builder-section-content">
									<div class="tutor-frontend-builder-item-scope">
										<div class="tutor-form-group">
											<label><?php esc_html_e( 'Course Title', 'edumall' ); ?></label>
											<input type="text" name="title"
											       value="<?php echo esc_attr( get_the_title() ); ?>"
											       placeholder="<?php esc_attr_e( 'ex. Learn photoshop CS6 from scratch', 'edumall' ); ?>">
										</div>
									</div> <!--.tutor-frontend-builder-item-scope-->

									<div class="tutor-frontend-builder-item-scope">
										<div class="tutor-form-group">
											<label> <?php esc_html_e( 'Description', 'edumall' ); ?></label>
											<?php
											$editor_settings = array(
												'media_buttons' => false,
												'quicktags'     => false,
												'editor_height' => 150,
												'textarea_name' => 'content',
											);
											wp_editor( $post->post_content, 'course_description', $editor_settings );
											?>
										</div>
									</div>  <!--.tutor-frontend-builder-item-scope-->

									<?php do_action( 'tutor/frontend_course_edit/after/description', $post ); ?>

									<div class="tutor-frontend-builder-item-scope">
										<div class="tutor-form-group">
											<label>
												<?php esc_html_e( 'Choose a category', 'edumall' ); ?>
											</label>
											<div class="tutor-form-field-course-categories">
												<?php //echo tutor_course_categories_checkbox($course_id);
												echo tutor_course_categories_dropdown( $course_id, array( 'classes' => 'tutor_select2' ) );
												?>
											</div>
										</div>
									</div>

									<div class="tutor-frontend-builder-item-scope">
										<div class="tutor-form-group">
											<label>
												<?php esc_html_e( 'Choose a tag', 'edumall' ); ?>
											</label>
											<div class="tutor-form-field-course-tags">
												<?php //echo tutor_course_tags_checkbox($course_id);
												echo tutor_course_tags_dropdown( $course_id, array( 'classes' => 'tutor_select2' ) );
												?>
											</div>
										</div>
									</div>

									<?php
									$monetize_by = tutils()->get_option( 'monetize_by' );
									if ( $monetize_by === 'wc' || $monetize_by === 'edd' ) {
										$course_price    = tutor_utils()->get_raw_course_price( get_the_ID() );
										$currency_symbol = tutor_utils()->currency_symbol();

										$_tutor_course_price_type = tutils()->price_type();
										?>
										<div
											class="tutor-frontend-builder-item-scope tutor-frontend-builder-course-price">
											<div class="tutor-form-group">
												<label><?php esc_html_e( 'Course Price', 'edumall' ); ?></label>
												<div class="tutor-row tutor-align-items-center">
													<div class="tutor-col-auto">
														<label for="tutor_course_price_type_pro"
														       class="tutor-styled-radio">
															<input id="tutor_course_price_type_pro" type="radio"
															       name="tutor_course_price_type"
															       value="paid" <?php $_tutor_course_price_type ? checked( $_tutor_course_price_type, 'paid' ) : checked( 'true', 'true' ); ?> >
															<span></span>
															<div class="tutor-form-group">
															<span
																class="tutor-input-prepand"><?php echo esc_html( $currency_symbol ); ?></span>
																<input type="text" name="course_price"
																       value="<?php echo esc_attr( $course_price->regular_price ); ?>"
																       placeholder="<?php esc_attr_e( 'Set course price', 'edumall' ); ?>">
															</div>
														</label>
													</div>
													<div class="tutor-col-auto">
														<label class="tutor-styled-radio">
															<input type="radio" name="tutor_course_price_type"
															       value="free" <?php checked( $_tutor_course_price_type, 'free' ); ?> >
															<span><?php esc_html_e( 'Free', 'edumall' ); ?></span>
														</label>
													</div>
												</div>
											</div>
										</div> <!--.tutor-frontend-builder-item-scope-->
									<?php } ?>

									<div class="tutor-frontend-builder-item-scope">
										<div class="tutor-form-group">
											<label>
												<?php esc_html_e( 'Course Thumbnail', 'edumall' ); ?>
											</label>
											<div
												class="tutor-form-field tutor-form-field-course-thumbnail tutor-thumbnail-wrap">
												<div class="tutor-row tutor-align-items-center">
													<div class="tutor-col-5">
														<div class="builder-course-thumbnail-img-src">
															<?php
															$builder_course_img_src = tutor()->url . 'assets/images/placeholder-course.jpg';
															$_thumbnail_url         = get_the_post_thumbnail_url( $course_id );
															$post_thumbnail_id      = get_post_thumbnail_id( $course_id );

															if ( ! $_thumbnail_url ) {
																$_thumbnail_url = $builder_course_img_src;
															}
															?>
															<img src="<?php echo esc_url( $_thumbnail_url ); ?>"
															     class="thumbnail-img"
															     data-placeholder-src="<?php echo esc_url( $builder_course_img_src ); ?>">
															<a href="javascript:;"
															   class="tutor-course-thumbnail-delete-btn"
															   style="<?php echo esc_attr( sprintf( 'display: %s;', $post_thumbnail_id ? 'block' : 'none' ) ); ?>">
																<i class="tutor-icon-line-cross"></i>
															</a>
														</div>
													</div>

													<div class="tutor-col-7">
														<div class="builder-course-thumbnail-upload-wrap">
															<p class="builder-course-thumbnail-guide-line">
																<?php echo sprintf( __( "Important Guideline: %1\$s 700x430 pixels %2\$s %3\$s File Support: %1\$s .jpg, .jpeg, .gif, or .png %2\$s no text on the image.", 'edumall' ), "<strong>", "</strong>", "<br>" ); ?>
															</p>
															<input type="hidden" id="tutor_course_thumbnail_id"
															       name="tutor_course_thumbnail_id"
															       value="<?php echo esc_attr( $post_thumbnail_id ); ?>">
															<a href="javascript:void(0);"
															   class="tutor-course-thumbnail-upload-btn tutor-button bordered-button"><?php esc_html_e( 'Upload Image', 'edumall' ); ?></a>
														</div>
													</div>
												</div>

											</div>
										</div>
									</div>
								</div>
							</div>

							<?php do_action( 'tutor/dashboard_course_builder_form_field_after', $post ); ?>

							<div class="tutor-form-row tutor-form-submit">
								<div class="tutor-form-col-12">
									<div class="tutor-form-group">
										<div class="tutor-form-field tutor-course-builder-btn-group">
											<button type="submit" class="tutor-button btn-save-as-draft"
											        name="course_submit_btn"
											        value="save_course_as_draft"><?php esc_html_e( 'Save course as draft', 'edumall' ); ?></button>
											<?php if ( $can_publish_course ) : ?>
												<button class="tutor-button tutor-success" type="submit"
												        name="course_submit_btn"
												        value="publish_course"><?php esc_html_e( 'Publish Course', 'edumall' ); ?></button>
											<?php else : ?>
												<button class="tutor-button tutor-success" type="submit"
												        name="course_submit_btn"
												        value="submit_for_review"><?php esc_html_e( 'Submit for Review', 'edumall' ); ?></button>
											<?php endif; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="tutor-course-builder-upload-tips tm-sticky-column">
							<h3 class="tutor-course-builder-tips-title">
								<i class="far fa-lightbulb-on"></i><?php esc_html_e( 'Course Upload Tips', 'edumall' ); ?>
							</h3>
							<ul>
								<li><?php esc_html_e( 'Set the Course Price option or make it free.', 'edumall' ); ?></li>
								<li><?php esc_html_e( 'Standard size for course thumbnail is 700x430.', 'edumall' ); ?></li>
								<li><?php esc_html_e( 'Video section controls the course overview video.', 'edumall' ); ?></li>
								<li><?php esc_html_e( 'Course Builder is where you create & organize a course.', 'edumall' ); ?></li>
								<li><?php esc_html_e( 'Add Topics in the Course Builder section to create lessons, quizzes, and assignments.', 'edumall' ); ?></li>
								<li><?php esc_html_e( 'Prerequisites refers to the fundamental courses to complete before taking this particular course.', 'edumall' ); ?></li>
								<li><?php esc_html_e( 'Information from the Additional Data section shows up on the course single page.', 'edumall' ); ?></li>
								<li><?php esc_html_e( 'Make Announcements to notify any important notes to all enrolled students at once.', 'edumall' ); ?></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>

<?php do_action( 'tutor/dashboard_course_builder_after' ); ?>

<?php do_action( 'tutor_load_template_after', 'dashboard.create-course', null ); ?>

<?php
get_tutor_footer( true );
