<?php

namespace Edumall_Addons;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'FAQs' ) ) {
	class FAQs {

		function __construct() {
			add_action( 'init', [ $this, 'register_post_types' ], 1 );
		}

		function register_post_types() {
			$slug = apply_filters( 'edumall_addons_faq_slug', 'faq' );

			$labels = array(
				'name'               => _x( 'FAQs', 'Post Type General Name', 'edumall-addons' ),
				'singular_name'      => _x( 'FAQ', 'Post Type Singular Name', 'edumall-addons' ),
				'menu_name'          => __( 'FAQs', 'edumall-addons' ),
				'name_admin_bar'     => __( 'FAQ', 'edumall-addons' ),
				'parent_item_colon'  => __( 'Parent FAQ:', 'edumall-addons' ),
				'all_items'          => __( 'FAQs', 'edumall-addons' ),
				'add_new_item'       => __( 'Add New FAQ', 'edumall-addons' ),
				'add_new'            => __( 'Add New', 'edumall-addons' ),
				'new_item'           => __( 'New FAQ', 'edumall-addons' ),
				'edit_item'          => __( 'Edit FAQ', 'edumall-addons' ),
				'update_item'        => __( 'Update FAQ', 'edumall-addons' ),
				'view_item'          => __( 'View FAQ', 'edumall-addons' ),
				'search_items'       => __( 'Search FAQ', 'edumall-addons' ),
				'not_found'          => __( 'Not found', 'edumall-addons' ),
				'not_found_in_trash' => __( 'Not found in Trash', 'edumall-addons' ),
			);

			$args = array(
				'label'               => __( 'faq', 'edumall-addons' ),
				'description'         => __( 'Frequently Asked Questions', 'edumall-addons' ),
				'labels'              => apply_filters( 'edumall_addons_faq_labels', $labels ),
				'supports'            => apply_filters( 'edumall_addons_faq_supports', array( 'title', 'editor' ) ),
				'hierarchical'        => false,
				'public'              => true,
				'exclude_from_search' => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_position'       => 10,
				'menu_icon'           => 'dashicons-format-chat',
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'can_export'          => true,
				'has_archive'         => true,
				'publicly_queryable'  => true,
				'show_in_rest'        => true,
				'rest_base'           => apply_filters( 'edumall_addons_faq_rest_base', __( 'faqs', 'edumall-addons' ) ),
				'capability_type'     => 'post',
				'capabilities'        => array(
					'edit_post'          => 'edit_faq',
					'edit_posts'         => 'edit_faqs',
					'edit_others_posts'  => 'edit_other_faqs',
					'publish_posts'      => 'publish_faqs',
					'read_post'          => 'read_faq',
					'read_private_posts' => 'read_private_faqs',
					'delete_post'        => 'delete_faq',
				),
				'map_meta_cap'        => true,
			);

			register_post_type( 'faq', apply_filters( 'edumall_addons_register_faq_arguments', $args ) );

			$labels = array(
				'name'                       => _x( 'FAQ Groups', 'Taxonomy General Name', 'edumall-addons' ),
				'singular_name'              => _x( 'FAQ Group', 'Taxonomy Singular Name', 'edumall-addons' ),
				'menu_name'                  => __( 'Groups', 'edumall-addons' ),
				'all_items'                  => __( 'All FAQ Groups', 'edumall-addons' ),
				'parent_item'                => __( 'Parent FAQ Group', 'edumall-addons' ),
				'parent_item_colon'          => __( 'Parent FAQ Group:', 'edumall-addons' ),
				'new_item_name'              => __( 'New FAQ Group Name', 'edumall-addons' ),
				'add_new_item'               => __( 'Add New FAQ Group', 'edumall-addons' ),
				'edit_item'                  => __( 'Edit FAQ Group', 'edumall-addons' ),
				'update_item'                => __( 'Update FAQ Group', 'edumall-addons' ),
				'view_item'                  => __( 'View FAQ Group', 'edumall-addons' ),
				'separate_items_with_commas' => __( 'Separate FAQ Groups with commas', 'edumall-addons' ),
				'add_or_remove_items'        => __( 'Add or remove FAQ Groups', 'edumall-addons' ),
				'choose_from_most_used'      => __( 'Choose from the most used', 'edumall-addons' ),
				'popular_items'              => __( 'Popular FAQ Groups', 'edumall-addons' ),
				'search_items'               => __( 'Search FAQ Groups', 'edumall-addons' ),
				'not_found'                  => __( 'Not Found', 'edumall-addons' ),
			);

			$tax_args = array(
				'labels'              => apply_filters( 'edumall_addons_faq_group_labels', $labels ),
				'hierarchical'        => true,
				'public'              => true,
				'exclude_from_search' => false,
				'rewrite'             => array( 'slug' => 'faq-group' ),
				'show_ui'             => true,
				'show_in_menu'        => 'edit.php?post_type=faq',
				'show_admin_column'   => true,
				'show_in_nav_menus'   => true,
				'show_tagcloud'       => false,
				'show_in_rest'        => true,
				'rest_base'           => apply_filters( 'edumall_addons_faq_group_rest_base', __( 'faq_groups', 'edumall-addons' ) ),
			);

			register_taxonomy( 'faq-group', array( 'faq' ), apply_filters( 'edumall_addons_register_faq_group_arguments', $tax_args ) );
		}
	}

	new FAQs;
}
