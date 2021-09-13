<?php
/**
 * Template for displaying left navigation for public profile page.
 *
 * @author  ThemeMove
 * @package EduMall
 * @since   1.3.1
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

$user_name = sanitize_text_field( get_query_var( 'tutor_student_username' ) );
$sub_page  = sanitize_text_field( get_query_var( 'profile_sub_page' ) );
$get_user  = tutor_utils()->get_user_by_login( $user_name );

if ( empty( $get_user ) ) {
	return;
}

$user_id = $get_user->ID;

global $wp_query;

$profile_sub_page = '';
if ( isset( $wp_query->query_vars['profile_sub_page'] ) && $wp_query->query_vars['profile_sub_page'] ) {
	$profile_sub_page = $wp_query->query_vars['profile_sub_page'];
}
?>
<div id="tutor-dashboard-left-menu" class="tutor-dashboard-left-menu">
	<div id="dashboard-nav-wrapper" class="dashboard-nav-wrapper">
		<div class="dashboard-nav-header">
			<div class="dashboard-header-toggle-menu dashboard-header-close-menu">
				<span class="fal fa-times"></span>
			</div>

			<?php
			$branding_args = [
				'reverse_scheme' => true,
			];
			?>
			<?php edumall_load_template( 'branding', null, $branding_args ); ?>
		</div>
		<div class="dashboard-nav-content">
			<div class="dashboard-nav-content-inner">
				<?php
				$permalinks          = tutor_utils()->user_profile_permalinks();
				$student_profile_url = tutor_utils()->profile_url( $user_id );
				?>
				<ul class="tutor-dashboard-permalinks">
					<li class="tutor-dashboard-menu-bio <?php echo '' === $profile_sub_page ? 'active' : ''; // WPCS: XSS OK. ?>">
						<a href="<?php echo tutor_utils()->profile_url( $user_id ); ?>"><?php esc_html_e( 'My Profile', 'edumall' ); ?></a>
					</li>
					<?php
					if ( is_array( $permalinks ) && count( $permalinks ) ) {
						foreach ( $permalinks as $permalink_key => $permalink ) {
							$li_class     = "tutor-dashboard-menu-{$permalink_key}";
							$active_class = $profile_sub_page == $permalink_key ? "active" : "";
							echo '<li class="' . $active_class . ' ' . $li_class . '"><a href="' . trailingslashit( $student_profile_url ) . $permalink_key . '"> ' . $permalink . ' </a> </li>';
						}
					}
					?>
				</ul>
			</div>
		</div>
	</div>
</div>
