<?php

namespace Edumall_Addons\Event;

defined( 'ABSPATH' ) || exit;

class Event_Speakers {

	protected static $instance = null;

	const    POST_TYPE        = 'tp_event';
	const    TAXONOMY_SPEAKER = 'event-speaker';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function initialize() {
		/**
		 * Priority 1 to make save_post action working properly.
		 */
		add_action( 'init', array( $this, 'register_tax_speaker' ), 1 );

		/**
		 * Add link to tabs.
		 */
		add_filter( 'tp_event_admin_event_tab_info', [ $this, 'add_event_speaker_to_tab' ] );
		add_filter( 'tp-event_admin_tabs_on_pages', [ $this, 'add_event_speaker_on_show_pages' ] );

		/**
		 * Add thumbnail field html template.
		 */
		add_action( 'event-speaker_add_form_fields', [ $this, 'add_term_fields' ] );
		add_action( 'event-speaker_edit_form_fields', [ $this, 'edit_term_fields' ] );

		/**
		 * Save thumbnail field.
		 */
		add_action( 'created_term', [ $this, 'save_term_fields' ], 10, 3 );
		add_action( 'edit_term', [ $this, 'save_term_fields' ], 10, 3 );

		/**
		 * Add thumbnail to admin table columns
		 */
		add_filter( 'manage_edit-event-speaker_columns', [ $this, 'term_columns' ] );
		add_filter( 'manage_event-speaker_custom_column', [ $this, 'term_column' ], 10, 3 );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function register_tax_speaker() {
		register_taxonomy( self::TAXONOMY_SPEAKER, self::POST_TYPE, [
			'hierarchical'      => false,
			'label'             => esc_html__( 'Speakers', 'edumall-addons' ),
			'labels'            => array(
				'name' => _x( 'Speakers', 'taxonomy general name', 'edumall-addons' ),
			),
			'public'            => true,
			'query_var'         => true,
			'show_ui'           => true,
			'show_in_admin_bar' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => apply_filters( 'edumall_event_speaker_slug', 'event-speaker' ) ),
			'show_admin_column' => true,
		] );
	}

	public function add_event_speaker_to_tab( $tabs ) {
		$tabs[40] = [
			'link' => 'edit-tags.php?taxonomy=event-speaker&post_type=tp_event',
			'name' => esc_html__( 'Speakers', 'edumall-addons' ),
			'id'   => 'edit-event-speaker',
		];

		return $tabs;
	}

	public function add_event_speaker_on_show_pages( $pages ) {
		// plugin missing.
		if ( ! in_array( $pages, [ 'edit-tp_event_tag' ], true ) ) {
			$pages[] = 'edit-tp_event_tag';
		}

		// custom by theme.
		if ( ! in_array( $pages, [ 'edit-event-speaker' ], true ) ) {
			$pages[] = 'edit-event-speaker';
		}

		return $pages;
	}

	public function add_term_fields() {
		?>
		<div class="form-field term-icon-wrap">
			<label><?php esc_html_e( 'Avatar', 'edumall-addons' ); ?></label>

			<div class="edumall-addons-media-wrap">
				<div style="float: left; margin-right: 10px;" class="edumall-addons-media-image">
					<img src="<?php echo esc_url( edumall_addons_placeholder_img_src() ); ?>" width="60px" height="60px"
					     data-src-placeholder="<?php echo esc_attr( edumall_addons_placeholder_img_src() ); ?>"
					/></div>
				<div style="line-height: 60px;">
					<input type="hidden" class="edumall-addons-media-input" name="event_speaker_thumbnail_id"/>
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
		$thumbnail_id = absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );

		if ( $thumbnail_id ) {
			$thumbnail_url = wp_get_attachment_thumb_url( $thumbnail_id );
		} else {
			$thumbnail_url = edumall_addons_placeholder_img_src();
		}
		?>
		<tr class="form-field term-thumbnail-wrap">
			<th scope="row" valign="top"><label><?php esc_html_e( 'Avatar', 'edumall-addons' ); ?></label></th>
			<td>
				<div class="edumall-addons-media-wrap">
					<div style="float: left; margin-right: 10px;" class="edumall-addons-media-image">
						<img src="<?php echo esc_url( $thumbnail_url ); ?>" width="60px" height="60px"
						     data-src-placeholder="<?php echo esc_attr( edumall_addons_placeholder_img_src() ); ?>"/>
					</div>
					<div style="line-height: 60px;">
						<input type="hidden"
						       class="edumall-addons-media-input"
						       name="event_speaker_thumbnail_id"
						       value="<?php echo esc_attr( $thumbnail_id ); ?>"/>
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
	 * Save Event Speaker Thumbnail.
	 */
	public function save_term_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
		if ( 'event-speaker' !== $taxonomy ) {
			return;
		}

		if ( ! empty( $_POST['event_speaker_thumbnail_id'] ) ) {
			update_term_meta( $term_id, 'thumbnail_id', absint( $_POST['event_speaker_thumbnail_id'] ) );
		} else {
			delete_term_meta( $term_id, 'thumbnail_id' );
		}
	}

	public function term_columns( $columns ) {
		$columns['thumbnail'] = __( 'Avatar', 'edumall-addons' );

		return $columns;
	}

	public function term_column( $columns, $column, $id ) {
		if ( 'thumbnail' === $column ) {
			$thumbnail_id = get_term_meta( $id, 'thumbnail_id', true );

			if ( $thumbnail_id ) {
				$image = wp_get_attachment_thumb_url( $thumbnail_id );
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

		if ( $screen->id === 'edit-event-speaker' ) {
			wp_enqueue_media();
			wp_enqueue_script( 'edumall-addons-media', EDUMALL_ADDONS_ASSETS_URI . '/admin/js/media-upload.js', [ 'jquery' ], null, true );
		}
	}
}

Event_Speakers::instance()->initialize();
