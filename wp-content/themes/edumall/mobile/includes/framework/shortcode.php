<?php

namespace edumallmobile\framework;

use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Tutor_Shortcode extends \Edumall_Tutor {

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_featured_courses_by_current_tax_mobile( $term_id ) {
		$current_tax = get_term( $term_id, $this->get_tax_category() );

		$query_args = [
			'post_type'      => $this->get_course_type(),
			'posts_per_page' => 8,
			'post_status'    => 'publish',
			'no_found_rows'  => true,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'tax_query'      => [
				'relation' => 'AND',
				array(
					'taxonomy' => $current_tax->taxonomy,
					'terms'    => $current_tax->term_id,
				),
				array(
					'taxonomy' => 'course-visibility',
					'field'    => 'slug',
					'terms'    => [ 'featured' ],
				),
			],
		];

		$query = new \WP_Query( $query_args );

		$data = array();
		if ( $query->have_posts() ) {
			global $post;
			global $edumall_course;
			$edumall_course_clone = $edumall_course;

			foreach ( $query->posts as $post ) :
				setup_postdata( $post );
				$edumall_course = new \Edumall_Course();
				/**
				 * Setup course object.
				 */
				$object               = new \stdClass();
				$object->idCourse     = $post->ID;
				$object->permalink    = get_permalink( $post->ID );
				$object->courseName   = get_the_title( $post->ID );
				$category             = \Edumall_Tutor::instance()->get_the_category();
				$link                 = get_term_link( $category );
				$object->idCategory   = $category->term_id;
				$object->categoryName = esc_html( $category->name );
				$object->categoryLink = esc_url( $link );
				$object->isBestseller = $edumall_course->is_featured();
				$object->isDiscount   = false;
				$object->discount     = '';
				if ( ! empty( $edumall_course->on_sale_text() ) ) {
					$object->isDiscount = true;
					$object->discount   = $edumall_course->on_sale_text();
				}
				$object->level      = Edumall_Mobile_Utils::get_level_label( $post->ID );
				$object->authorName = '';
				$instructors        = $edumall_course->get_instructors();

				if ( ! empty( $instructors ) ) {
					$first_instructor   = $instructors[0];
					$object->authorName = esc_html( $first_instructor->display_name );
				}
				$object->fixedPrice = Edumall_Mobile_Utils::getPriceOfCourses( $post->ID, 0 );
				$object->isFree     = true;
				if ( $object->fixedPrice > 0 ) {
					$object->isFree = false;
				}
				$object->salePrice = 0;
				if ( Edumall_Mobile_Utils::is_course_on_sale( $post->ID ) ) {
					$object->salePrice = Edumall_Mobile_Utils::getPriceOfCourses( $post->ID, 1 );
				}
				$object->urlThumnails = \Edumall_Image::get_the_post_thumbnail_url( array( 'size' => '226x150' ) );
				$object->rating       = '0.00';
				$object->totalRating  = 0;
				$course_rating        = $edumall_course->get_rating();
				$rating_count         = intval( $course_rating->rating_count );
				if ( $rating_count > 0 ) {
					$object->rating      = $course_rating->rating_avg;
					$object->totalRating = intval( $course_rating->rating_count );
				}
				$data[] = $object;

				?>
			<?php endforeach; ?>
			<?php
			wp_reset_postdata();
			$edumall_course = $edumall_course_clone;
		}

		return $data;
	}

	public function get_popular_topics_by_current_tax_mobile( $term_id ) {

		$current_tax = get_term( $term_id, $this->get_tax_category() );
		$args        = [
			'post_type'      => $this->get_course_type(),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'fields'         => 'ids',
			'tax_query'      => array(
				array(
					'taxonomy' => $current_tax->taxonomy,
					'field'    => 'term_id',
					'terms'    => [ $current_tax->term_id ],
				),
			),
		];

		$ids            = get_posts( $args );
		$popular_topics = [];

		if ( $ids ) {
			$popular_topics = get_terms( [
				'taxonomy'   => $this->get_tax_tag(),
				'object_ids' => $ids,
				'orderby'    => 'views',
				'order'      => 'DESC',
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key'     => 'views',
						'value'   => '0',
						'compare' => '>',
						'type'    => 'NUMERIC',
					),
					array(
						'key'     => 'views',
						'compare' => 'NOT EXISTS',
						'value'   => 'null',
					),
				),
			] );

			if ( is_wp_error( $popular_topics ) ) {
				$popular_topics = [];
			}
		}

		return $popular_topics;
	}

	public function get_sub_category_by_current_tax_mobile( $term_id ) {
		$current_tax = get_term( $term_id, $this->get_tax_category() );
		$terms_list  = get_terms( $current_tax->taxonomy, array( 'parent'     => $current_tax->term_id,
		                                                         'orderby'    => 'slug',
		                                                         'hide_empty' => false,
		) );
		$data        = array();
		foreach ( $terms_list as $category ) {
			$object       = new \stdClass();
			$object->id   = $category->term_id;
			$object->name = esc_html( $category->name );
			$data[]       = $object;
		}

		return $data;
	}

	public function get_popular_instructors_by_current_tax_mobile( $term_id ) {
		$current_tax = get_term( $term_id, $this->get_tax_category() );
		$args        = [
			'post_type'      => $this->get_course_type(),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'fields'         => 'ids',
			'tax_query'      => array(
				array(
					'taxonomy' => $current_tax->taxonomy,
					'field'    => 'term_id',
					'terms'    => [ $current_tax->term_id ],
				),
			),
		];

		$ids  = get_posts( $args );
		$data = array();
		if ( $ids ) {
			$popular_instructors = $this->get_popular_instructors_by_course_ids( $ids );

			if( is_array($popular_instructors)) {
				foreach ( $popular_instructors as $instructor ) {
					$object                   = new \stdClass();
					$object->id               = $instructor->ID;
					$object->display_name     = $instructor->display_name;
					$object->taught_course_id = $instructor->taught_course_id;
					$object->total_students   = (int) $instructor->tutor_profile_total_students;
					$object->total_courses    = $this->get_total_courses_by_instructor( $instructor->ID );
					$object->avatar           = $this->get_avatar_mobile( $instructor->ID );
					$object->profile_url      = esc_url( tutor_utils()->profile_url( $instructor->ID ) );
					$instructor_rating        = tutor_utils()->get_instructor_ratings( $instructor->ID );
					$object->rating_count     = 0;
					$object->rating_avg       = 0;
					if ( $instructor_rating->rating_count > 0 ) {
						$object->rating_count = $instructor_rating->rating_count;
						$object->rating_avg   = $instructor_rating->rating_avg;
					}
					$object->tutor_profile_job_title = '';
					if ( ! empty( $instructor->tutor_profile_job_title ) ) {
						$object->tutor_profile_job_title = $instructor->tutor_profile_job_title;
					}
					$data[] = $object;
				}
			}

			return $data;
		}

		return $data;
	}

	public function get_avatar_mobile( $user_id = null, $size = 'thumbnail' ) {
		if ( ! $user_id ) {
			return '';
		}

		$user = tutor_utils()->get_tutor_user( $user_id );
		if ( $user->tutor_profile_photo ) {
			return \Edumall_Image::get_attachment_by_id( [
				'id'        => $user->tutor_profile_photo,
				'size'      => $size,
				'img_attrs' => [
					'class' => 'tutor-image-avatar',
				],
			] );
		}

		return '';
	}

	public static function get_the_post_thumbnail_mb( $args = array() ) {
		if ( ! empty( $args['post_id'] ) ) {
			$args['id'] = get_post_thumbnail_id( $args['post_id'] );
		} else {
			$args['id'] = get_post_thumbnail_id( $args['user_id']);
		}

		$attachment = self::get_attachment_by_id_mb( $args );

		return $attachment;
	}
	public static function get_attachment_by_id_mb( $args = array() ) {
		$defaults = array(
			'id'        => '',
			'size'      => 'full',
			'width'     => '',
			'height'    => '',
			'crop'      => true,
			'img_attrs' => [],
		);

		$args = wp_parse_args( $args, $defaults );

		$image_full = \Edumall_Image::get_attachment_info( $args['id'] );

		if ( $image_full === false ) {
			return false;
		}

		$url           = $image_full['src'];
		$cropped_image = \Edumall_Image::get_image_cropped_url( $url, $args );

		if ( $cropped_image[0] === '' ) {
			return '';
		}
		return $cropped_image[0];


	}

	public function get_all_courses_by_current_tax_mobile( $term_id, $current_page = 0 ) {
		$current_tax = get_term( $term_id, $this->get_tax_category() );
		$allcourse   = $this->get_courses( 'by_category', 8, 'grid', $current_tax->term_id, $current_page * 8 );

		return $allcourse;
	}

	public function get_courses( $source = 'latest', $number = 8, $layout = 'grid', $term_id = 0, $star_number = 0 ) {
		global $wpdb;

		$course_post_type = $this->get_course_type();

		$sql_select   = "SELECT {$wpdb->posts}.* FROM {$wpdb->posts}";
		$sql_join     = '';
		$sql_group_by = '';
		$sql_orderby  = " ORDER BY {$wpdb->posts}.post_date";
		$sql_where    = " WHERE {$wpdb->posts}.post_type = '{$course_post_type}' AND {$wpdb->posts}.post_status = 'publish' ";
		$sql_limit    = " LIMIT {$star_number}, {$number}";

		switch ( $source ) {
			case 'trending':
				$sql_join     = " INNER JOIN {$wpdb->postmeta} ON ( {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id )";
				$sql_where    .= " AND ( {$wpdb->postmeta}.meta_key = 'views')";
				$sql_orderby  = " ORDER BY {$wpdb->postmeta}.meta_value+0 DESC";
				$sql_group_by = " GROUP BY {$wpdb->posts}.ID";
				break;
			case 'popular':
				$sql_join     = " INNER JOIN {$wpdb->postmeta} ON ( {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id )";
				$sql_where    .= " AND ( {$wpdb->postmeta}.meta_key = '_course_total_enrolls')";
				$sql_orderby  = " ORDER BY {$wpdb->postmeta}.meta_value+0 DESC";
				$sql_group_by = " GROUP BY {$wpdb->posts}.ID";
				break;
			case 'featured':
				$tax_query     = new \WP_Tax_Query( [
					'relation' => 'AND',
					array(
						'taxonomy' => 'course-visibility',
						'field'    => 'slug',
						'terms'    => [ 'featured' ],
					),
				] );
				$tax_query_sql = $tax_query->get_sql( $wpdb->posts, 'ID' );
				$sql_join      = $tax_query_sql['join'];
				$sql_where     .= $tax_query_sql['where'];
				$sql_group_by  = " GROUP BY {$wpdb->posts}.ID";
				break;
			case 'by_category':
				$tax_query     = new \WP_Tax_Query( [
					'tax_query' => [
						'relation' => 'AND',
						array(
							'taxonomy' => $this->get_tax_category(),
							'terms'    => $term_id,
						),
					],
				] );
				$tax_query_sql = $tax_query->get_sql( $wpdb->posts, 'ID' );

				$sql_join     = $tax_query_sql['join'];
				$sql_where    .= $tax_query_sql['where'];
				$sql_group_by = " GROUP BY {$wpdb->posts}.ID";
				break;

			case 'popular_by_catergory':
				$tax_query     = new \WP_Tax_Query( [
					'tax_query' => [
						'relation' => 'AND',
						array(
							'taxonomy' => $this->get_tax_category(),
							'terms'    => $term_id,
						),
					],
				] );
				$tax_query_sql = $tax_query->get_sql( $wpdb->posts, 'ID' );
				$sql_join      = " INNER JOIN {$wpdb->postmeta} ON ( {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id )";
				$sql_where     .= " AND ( {$wpdb->postmeta}.meta_key = '_course_total_enrolls')";
				$sql_join      .= $tax_query_sql['join'];
				$sql_where     .= $tax_query_sql['where'];
				$sql_group_by  = " GROUP BY {$wpdb->posts}.ID";
				$sql_orderby   = " ORDER BY {$wpdb->postmeta}.meta_value+0 DESC";
				break;
			case 'by_search':
				$sql_join     = " INNER JOIN {$wpdb->postmeta} ON ( {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id )";
				$sql_where    .= " AND ( {$wpdb->postmeta}.meta_key = 'views')";
				$sql_orderby  = " ORDER BY {$wpdb->postmeta}.meta_value+0 DESC";
				$sql_group_by = " GROUP BY {$wpdb->posts}.ID";
				break;
		}

		$sql           = "{$sql_select} {$sql_join} {$sql_where} {$sql_group_by} {$sql_orderby} {$sql_limit}";
		$query_results = $wpdb->get_results( $sql, OBJECT );

		$data = array();
		if ( is_array( $query_results ) && count( $query_results ) ) :
			global $post;
			global $edumall_course;
			$edumall_course_clone = $edumall_course;

			foreach ( $query_results as $post ) :
				setup_postdata( $post );
				$edumall_course = new \Edumall_Course();
				/**
				 * Setup course object.
				 */
				$object               = new \stdClass();
				$object->idCourse     = $post->ID;
				$object->permalink    = get_permalink( $post->ID );
				$object->courseName   = get_the_title( $post->ID );
				$category             = \Edumall_Tutor::instance()->get_the_category();
				$link                 = get_term_link( $category );
				$object->idCategory   = $category->term_id;
				$object->categoryName = esc_html( $category->name );
				$object->categoryLink = esc_url( $link );
				$object->isBestseller = $edumall_course->is_featured();
				$object->isDiscount   = false;
				$object->discount     = '';
				if ( ! empty( $edumall_course->on_sale_text() ) ) {
					$object->isDiscount = true;
					$object->discount   = $edumall_course->on_sale_text();
				}
				$object->level      = Edumall_Mobile_Utils::get_level_label( $post->ID );
				$object->authorName = '';
				$instructors        = $edumall_course->get_instructors();

				if ( ! empty( $instructors ) ) {
					$first_instructor   = $instructors[0];
					$object->authorName = esc_html( $first_instructor->display_name );
				}
				$object->fixedPrice = Edumall_Mobile_Utils::getPriceOfCourses( $post->ID, 0 );
				$object->isFree     = true;
				if ( $object->fixedPrice > 0 ) {
					$object->isFree = false;
				}
				$object->salePrice = 0;
				if ( Edumall_Mobile_Utils::is_course_on_sale( $post->ID ) ) {
					$object->salePrice = Edumall_Mobile_Utils::getPriceOfCourses( $post->ID, 1 );
				}
				$object->urlThumnails = \Edumall_Image::get_the_post_thumbnail_url( array( 'size' => '226x150' ) );
				$object->rating       = '0.00';
				$object->totalRating  = 0;
				$course_rating        = $edumall_course->get_rating();
				$rating_count         = intval( $course_rating->rating_count );
				if ( $rating_count > 0 ) {
					$object->rating      = $course_rating->rating_avg;
					$object->totalRating = intval( $course_rating->rating_count );
				}
				$data[] = $object;

				?>
			<?php endforeach; ?>
			<?php
			wp_reset_postdata();
			$edumall_course = $edumall_course_clone;
		endif;

		return $data;
	}

	public function get_tax_name( $term_id ) {
		$current_tax  = get_term( $term_id, $this->get_tax_category() );
		$data         = array();
		$object       = new \stdClass();
		$object->id   = $current_tax->term_id;
		$object->name = $current_tax->name;
		$data[]       = $object;

		return $data;
	}

	public function get_search_courses( $search, $number = 8, $star_number = 0 ) {
		global $wpdb;
		$course_post_type = $this->get_course_type();
		$sql_select       = "SELECT {$wpdb->posts}.post_title,{$wpdb->posts}.ID FROM {$wpdb->posts}";
		$sql_join         = '';
		$sql_group_by     = " GROUP BY {$wpdb->posts}.ID";
		$sql_orderby      = " ORDER BY {$wpdb->posts}.post_date";
		$sql_where        = " WHERE {$wpdb->posts}.post_type = '{$course_post_type}' AND {$wpdb->posts}.post_status = 'publish' ";

		$sql           = "{$sql_select} {$sql_join} {$sql_where} {$sql_group_by} {$sql_orderby}";
		$query_results = $wpdb->get_results( $sql, OBJECT );
		$data          = array();
		$temp          = array();
		if ( is_array( $query_results ) && count( $query_results ) ) {
			$search = preg_replace( '/\s+/', '', sanitize_text_field( $search ) );
			global $post;
			global $edumall_course;
			$edumall_course_clone = $edumall_course;

			foreach ( $query_results as $post ) :

				$display_name = '';
				global $wpdb;
				$instructors = $wpdb->get_results( "SELECT  ID,display_name
                FROM {$wpdb->users}
                INNER JOIN {$wpdb->usermeta} get_course ON {$wpdb->users}.ID = get_course.user_id AND get_course.meta_key = '_tutor_instructor_course_id' AND get_course.meta_value =  $post->ID  
                GROUP BY {$wpdb->users}.ID
                " );


				if ( is_array( $instructors ) && count( $instructors ) ) {

					foreach ( $instructors as $instructor ) {
						$display_name .= $instructor->display_name;
					}
				}

				$search_holder = $post->post_title . $display_name;
				$search_holder = preg_replace( '/\s+/', '', $search_holder );

				setup_postdata( $post );
				$edumall_course = new \Edumall_Course();
				if ( stristr( $search_holder, $search ) ) {
					/**
					 * Setup course object.
					 */
					$object               = new \stdClass();
					$object->idCourse     = $post->ID;
					$object->permalink    = get_permalink( $post->ID );
					$object->courseName   = get_the_title( $post->ID );
					$category             = \Edumall_Tutor::instance()->get_the_category();
					$link                 = get_term_link( $category );
					$object->idCategory   = $category->term_id;
					$object->categoryName = esc_html( $category->name );
					$object->categoryLink = esc_url( $link );
					$object->isBestseller = $edumall_course->is_featured();
					$object->isDiscount   = false;
					$object->discount     = '';
					if ( ! empty( $edumall_course->on_sale_text() ) ) {
						$object->isDiscount = true;
						$object->discount   = $edumall_course->on_sale_text();
					}
					$object->level      = Edumall_Mobile_Utils::get_level_label( $post->ID );
					$object->authorName = '';
					$instructors        = $edumall_course->get_instructors();

					if ( ! empty( $instructors ) ) {
						$first_instructor   = $instructors[0];
						$object->authorName = esc_html( $first_instructor->display_name );
					}
					$object->fixedPrice = Edumall_Mobile_Utils::getPriceOfCourses( $post->ID, 0 );
					$object->isFree     = true;
					if ( $object->fixedPrice > 0 ) {
						$object->isFree = false;
					}
					$object->salePrice = 0;
					if ( Edumall_Mobile_Utils::is_course_on_sale( $post->ID ) ) {
						$object->salePrice = Edumall_Mobile_Utils::getPriceOfCourses( $post->ID, 1 );
					}
					$object->urlThumnails = \Edumall_Image::get_the_post_thumbnail_url( array( 'size' => '226x150' ) );
					$object->rating       = '0.00';
					$object->totalRating  = 0;
					$course_rating        = $edumall_course->get_rating();
					$rating_count         = intval( $course_rating->rating_count );
					if ( $rating_count > 0 ) {
						$object->rating      = $course_rating->rating_avg;
						$object->totalRating = intval( $course_rating->rating_count );
					}


					$temp[] = $object;

				}

				?>
			<?php endforeach; ?>
			<?php
			wp_reset_postdata();
			$edumall_course = $edumall_course_clone;
		}

		$i = 0;
		foreach ( $temp as $tp ) {
			if ( $i >= $star_number * $number && $i < $star_number + $number ) {
				$data[] = $tp;
			}
			$i++;
		}

		return $data;
	}

}
