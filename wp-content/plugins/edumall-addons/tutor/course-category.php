<?php

namespace Edumall_Addons\Tutor;

defined( 'ABSPATH' ) || exit;

class Course_Category {

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function initialize() {
		/**
		 * Add icon field html template.
		 */
		add_action( 'course-category_add_form_fields', [ $this, 'add_term_fields' ] );
		add_action( 'course-category_edit_form_fields', [ $this, 'edit_term_fields' ] );

		/**
		 * Save icon field.
		 */
		add_action( 'created_term', [ $this, 'save_term_fields' ], 10, 3 );
		add_action( 'edit_term', [ $this, 'save_term_fields' ], 10, 3 );

		/**
		 * Add icon to admin table columns
		 */
		add_filter( 'manage_edit-course-category_columns', [ $this, 'term_columns' ] );
		add_filter( 'manage_course-category_custom_column', [ $this, 'term_column' ], 10, 3 );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function add_term_fields() {
		?>
		<div class="form-field term-icon-wrap">
			<label><?php esc_html_e( 'Icon', 'edumall-addons' ); ?></label>

			<div class="edumall-addons-media-wrap">
				<div style="float: left; margin-right: 10px;" class="edumall-addons-media-image">
					<img src="<?php echo esc_url( edumall_addons_placeholder_img_src() ); ?>" width="60px" height="60px"
					     data-src-placeholder="<?php echo esc_attr( edumall_addons_placeholder_img_src() ); ?>"
					/></div>
				<div style="line-height: 60px;">
					<input type="hidden" class="edumall-addons-media-input" name="course_category_icon_id"/>
					<button type="button"
					        class="edumall-addons-media-upload button"><?php esc_html_e( 'Upload/Add image', 'edumall-addons' ); ?></button>
					<button type="button"
					        class="edumall-addons-media-remove button"><?php esc_html_e( 'Remove image', 'edumall-addons' ); ?></button>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}

	public function edit_term_fields( $term ) {
		$icon_id = absint( get_term_meta( $term->term_id, 'icon_id', true ) );

		if ( $icon_id ) {
			$icon = wp_get_attachment_thumb_url( $icon_id );
		} else {
			$icon = edumall_addons_placeholder_img_src();
		}
		?>
		<tr class="form-field term-thumbnail-wrap">
			<th scope="row" valign="top"><label><?php esc_html_e( 'Icon', 'edumall-addons' ); ?></label></th>
			<td>
				<div class="edumall-addons-media-wrap">
					<div style="float: left; margin-right: 10px;" class="edumall-addons-media-image">
						<img src="<?php echo esc_url( $icon ); ?>" width="60px" height="60px"
						     data-src-placeholder="<?php echo esc_attr( edumall_addons_placeholder_img_src() ); ?>"/>
					</div>
					<div style="line-height: 60px;">
						<input type="hidden"
						       class="edumall-addons-media-input"
						       name="course_category_icon_id"
						       value="<?php echo esc_attr( $icon_id ); ?>"/>
						<button type="button" class="edumall-addons-media-upload button">
							<?php esc_html_e( 'Upload/Add image', 'edumall-addons' ); ?>
						</button>
						<button type="button" class="edumall-addons-media-remove button">
							<?php esc_html_e( 'Remove image', 'edumall-addons' ); ?>
						</button>
					</div>
					<div class="clear"></div>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param        $term_id
	 * @param string $tt_id
	 * @param string $taxonomy
	 *
	 * Save Course Category Icon
	 */
	public function save_term_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
		if ( 'course-category' !== $taxonomy ) {
			return;
		}

		// Fix Tutor save 0 value.
		if ( empty( $_POST['course_category_thumbnail_id'] ) ) {
			delete_term_meta( $term_id, 'thumbnail_id' );
		}

		if ( ! empty( $_POST['course_category_icon_id'] ) ) {
			update_term_meta( $term_id, 'icon_id', absint( $_POST['course_category_icon_id'] ) );
		} else {
			delete_term_meta( $term_id, 'icon_id' );
		}
	}

	public function term_columns( $columns ) {
		$columns['icon'] = __( 'Icon', 'edumall-addons' );

		return $columns;
	}

	public function term_column( $columns, $column, $id ) {
		if ( 'icon' === $column ) {
			$icon_id = get_term_meta( $id, 'icon_id', true );

			if ( $icon_id ) {
				$image = wp_get_attachment_thumb_url( $icon_id );
			} else {
				$image = edumall_addons_placeholder_img_src();
			}

			// Prevent esc_url from breaking spaces in urls for image embeds. Ref: https://core.trac.wordpress.org/ticket/23605 .
			$image   = str_replace( ' ', '%20', $image );
			$columns .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr__( 'Icon', 'edumall-addons' ) . '" class="wp-post-image" height="48" width="48" />';
		}

		return $columns;
	}

	public function enqueue_scripts() {
		$screen = get_current_screen();

		if ( $screen->id === 'edit-course-category' ) {
			wp_enqueue_media();
			wp_enqueue_script( 'edumall-addons-media', EDUMALL_ADDONS_ASSETS_URI . '/admin/js/media-upload.js', [ 'jquery' ], null, true );
		}
	}
}

Course_Category::instance()->initialize();
