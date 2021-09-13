<?php

namespace edumallmobile\framework;

use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Course_Query_Mb extends \Edumall_Course_Query {

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	public function get_course_filtering( $array_filter, $number, $star_number ) {
		$data  = array();
		$query = $this->course_filtering( $array_filter, $number, $star_number );

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


	public function course_filtering( $array_filter, $numbers = 8, $star_number = 0 ) {
		global $wpdb;
		$query_args = [
			'post_type'      => \Edumall_Tutor::instance()->get_course_type(),
			'posts_per_page' => $numbers,
			'offset'         => $star_number * $numbers,
			'post_status'    => 'publish',
			'no_found_rows'  => true,
		];


		// Query vars that affect posts shown.
		$meta_query = array();
		$tax_query  = array();;
		$query_args['meta_query'] = $this->get_meta_query_mb( $meta_query, $array_filter );
		$query_args['tax_query']  = $this->get_tax_query_mb( $tax_query, $array_filter );

		// Filter by instructor.
		if ( isset( $array_filter['instructor'] ) ) {
			$selected_instructors = array_map( 'absint', explode( ',', $array_filter['instructor'] ) );

			if ( ! empty( $selected_instructors ) ) {
				$query_args['author__in'] = $selected_instructors;
			}
		}

		// Order by.
		$orderby = isset( $array_filter['orderby'] ) ? \Edumall_Helper::data_clean( $array_filter['orderby'] ) : \Edumall_Tutor::instance()->get_course_default_sort_option();

		switch ( $orderby ) {
			case 'newest_first':
				$query_args['orderby'] = 'date';
				$query_args['order']   = 'desc';
				break;
			case 'oldest_first':
				$query_args['orderby'] = 'date';
				$query_args['order']   = 'asc';

				break;
			case 'course_title_az':
				$query_args['orderby'] = 'post_title';
				$query_args['order']   = 'asc';
				break;
			case 'course_title_za':
				$query_args['orderby'] = 'post_title';
				$query_args['order']   = 'desc';

				break;
			default:
				$query_args['orderby'] = 'date';
				$query_args['order']   = 'desc';

				break;
		}
		$query = new \WP_Query( $query_args );


		return $query;
	}

	/**
	 * Appends meta queries to an array.
	 *
	 * @param  array $meta_query   Meta query.
	 * @param  array $array_filter filters.
	 *
	 * @return array
	 */
	public function get_meta_query_mb( $meta_query = array(), $array_filter ) {
		if ( ! is_array( $meta_query ) ) {
			$meta_query = array();
		}

		// Filter by difficulty level.
		if ( isset( $array_filter['level'] ) ) {
			$selected_levels = explode( ',', \Edumall_Helper::data_clean( $array_filter['level'] ) );

			if ( ! empty( $selected_levels ) && ! in_array( 'all_levels', $selected_levels ) ) {
				$meta_query[] = array(
					'key'     => '_tutor_course_level',
					'value'   => $selected_levels,
					'compare' => 'IN',
				);
			}
		}

		// Filter by price type.
		if ( isset( $array_filter['price_type'] ) ) {
			$price_type = \Edumall_Helper::data_clean( $array_filter['price_type'] );

			$meta_query = self::set_meta_query_price( $meta_query, $price_type );
		}

		// Filter by duration.
		if ( isset( $array_filter['duration'] ) ) {
			$durations = explode( ',', \Edumall_Helper::data_clean( $array_filter['duration'] ) );

			$meta_query = self::set_meta_query_duration( $meta_query, $durations );
		}

		return $meta_query;

	}

	/**
	 * Appends tax queries to an array.
	 *
	 * @param  array $tax_query    Tax query.
	 * @param  array $array_filter Filters.
	 *
	 * @return array
	 */
	public function get_tax_query_mb( $tax_query = array(), $array_filter ) {
		if ( ! is_array( $tax_query ) ) {
			$tax_query = array(
				'relation' => 'AND',
			);
		}
		$category_taxonomy = \Edumall_Tutor::instance()->get_tax_category();


		if ( taxonomy_exists( $category_taxonomy ) && isset( $array_filter[ 'filter_' . $category_taxonomy ] ) ) {
			$selected_cats = explode( ',', \Edumall_Helper::data_clean( $array_filter[ 'filter_' . $category_taxonomy ] ) );

			$tax_query[] = array(
				'taxonomy' => $category_taxonomy,
				'field'    => 'term_id',
				'terms'    => $selected_cats,
			);
		}

		$language_taxonomy = \Edumall_Tutor::instance()->get_tax_language();
		if ( taxonomy_exists( $language_taxonomy ) && isset( $array_filter[ 'filter_' . $language_taxonomy ] ) ) {
			$selected_languages = explode( ',', \Edumall_Helper::data_clean( $array_filter[ 'filter_' . $language_taxonomy ] ) );

			$tax_query[] = array(
				'taxonomy' => $language_taxonomy,
				'field'    => 'term_id',
				'terms'    => $selected_languages,
			);
		}

		$visibility_taxonomy = \Edumall_Tutor::instance()->get_tax_visibility();
		if ( taxonomy_exists( $visibility_taxonomy ) && isset( $array_filter['rating_filter'] ) && '' !== $array_filter['rating_filter'] ) {
			$selected_ratings = explode( ',', \Edumall_Helper::data_clean( $array_filter['rating_filter'] ) );
			$selected_ratings = array_map( 'intval', $selected_ratings );

			$terms = \Edumall_Tutor::instance()->get_course_visibility_term_ids();

			$term_ids = [];

			foreach ( $selected_ratings as $selected_rating ) {
				$rating_key = 'rated-' . $selected_rating;

				if ( isset( $terms[ $rating_key ] ) ) {
					$term_ids[] = $terms[ $rating_key ];
				}
			}

			if ( ! empty( $term_ids ) ) {
				$tax_query[] = array(
					'taxonomy' => $visibility_taxonomy,
					'field'    => 'term_id',
					'terms'    => $term_ids,
				);
			}
		}

		return $tax_query;
	}

	public function get_course_widget_filtering( $array_filter ) {
		$data = array();
		//get list category filter

		$data['filter_category']    = $this->get_data_filter_for_category( $array_filter );
		$data['filter_level']       = $this->get_data_filter_for_levels( $array_filter );
		$data['filter_instructors'] = $this->get_data_filter_for_instructors( $array_filter );
		$data['filter_languages']   = $this->get_data_filter_for_languages( $array_filter );
		$data['filter_durations']   = $this->get_data_filter_for_durations( $array_filter );
		$data['filter_prices']      = $this->get_data_filter_for_prices( $array_filter );
		$data['filter_ratings']     = $this->get_data_filter_for_ratings( $array_filter );
		$data['filter_sorting']     = $this->get_data_filter_for_sorting();

		return $data;
	}

	protected function get_data_filter_for_category( $array_filter ) {
		$taxonomy = \Edumall_Tutor::instance()->get_tax_category();
		$data     = array();
		if ( taxonomy_exists( $taxonomy ) ) {
			// Get only parent terms. Methods will recursively retrieve children.
			$terms = get_terms( [
				'taxonomy'   => $taxonomy,
				'hide_empty' => '1',
				'parent'     => 0,
			] );

			foreach ( $terms as $term_key => $term ) {

				$child_ids = get_terms( [
					'taxonomy' => $taxonomy,
					'parent'   => $term->term_id,
					'fields'   => 'ids',
				] );

				$child_ids[] = $term->term_id;
				$count       = $this->get_filtered_term_counts_mb( $child_ids, $array_filter );
				if ( empty( $count ) ) {
					$count = 0;
				}

				$object        = new \stdClass();
				$object->id    = $term->term_id;
				$object->name  = $term->name;
				$object->count = $count;
				$data[]        = $object;

			}
		}

		return $data;

	}

	protected function get_filtered_term_counts_mb( $term_ids, $array_filter ) {
		global $wpdb;

		$term_ids = (array) $term_ids;

		$meta_query               = array();
		$tax_query                = array();
		$query_args               = array();
		$query_args['meta_query'] = $this->get_meta_query_mb( $meta_query, $array_filter );
		$query_args['tax_query']  = $this->get_tax_query_mb( $tax_query, $array_filter );

		$meta_query = new \WP_Meta_Query( $query_args['meta_query'] );
		$tax_query  = new \WP_Tax_Query( $query_args['tax_query'] );

		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );
		// Filter by instructor.
		if ( isset( $array_filter['instructor'] ) ) {

			$selected_instructors = array_map( 'absint', explode( ',', $array_filter['instructor'] ) );

			if ( ! empty( $selected_instructors ) ) {
				$query_args['author__in'] = ' AND ' . $wpdb->posts . '.post_author IN (' . implode( ',', $selected_instructors ) . ')';
			}
		}
		else{

			$query_args['author__in'] = '';
		}
		//$search_query_sql = Edumall_Course_Query::get_search_title_sql();

		$sql           = array();
		$sql['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID )";
		$sql['from']   = "FROM {$wpdb->posts}";
		$sql['join']   = "
			INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
			INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
			INNER JOIN {$wpdb->terms} AS terms USING( term_id )
			" . $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql['where']  = "
			WHERE {$wpdb->posts}.post_type IN ( 'courses' )
			AND {$wpdb->posts}.post_status = 'publish'
			" . $tax_query_sql['where'] . $meta_query_sql['where'] . $query_args['author__in'] . "
			AND terms.term_id IN (" . implode( ',', array_map( 'absint', $term_ids ) ) . ")
		";
		$sql           = implode( ' ', $sql );

		return absint( $wpdb->get_var( $sql ) ); // WPCS: unprepared SQL ok.

	}

	protected function get_data_filter_for_levels( $array_filter ) {
		$data          = array();
		$course_levels = tutor_utils()->course_levels();
		// List display.
		foreach ( $course_levels as $level_key => $level_name ) {
			// Skip all option for checkbox.
			if ( 'all_levels' === $level_key ) {
				continue;
			}

			$count = $this->get_filtered_course_count( $level_key, $array_filter );

			if ( empty( $count ) ) {
				$count = 0;
			}
			$object        = new \stdClass();
			$object->level = Edumall_Mobile_Utils::course_levels( $level_key );
			$object->count = $count;
			$data[]        = $object;
		}

		return $data;
	}

	protected function get_filtered_course_count( $current_level, $array_filter ) {
		global $wpdb;
		$meta_query               = array();
		$tax_query                = array();
		$query_args               = array();
		$query_args['meta_query'] = $this->get_meta_query_mb( $meta_query, $array_filter );
		$query_args['tax_query']  = $this->get_tax_query_mb( $tax_query, $array_filter );

		$current_level = (array) $current_level;


		// Set new level filter.
		$level_meta_query = array(
			array(
				'key'     => '_tutor_course_level',
				'value'   => $current_level,
				'compare' => 'IN',
			),
		);

		// Should use array merge instead of + operator.
		//$meta_query = array_merge( $meta_query, $level_meta_query );
		$query_args['meta_query'] = array_merge( $query_args['meta_query'], $level_meta_query );
		$meta_query               = new \WP_Meta_Query( $query_args['meta_query'] );
		$tax_query                = new \WP_Tax_Query( $query_args['tax_query'] );


//		$tax_query  = new \WP_Tax_Query( $tax_query );
//		$meta_query = new \WP_Meta_Query( $meta_query );

		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );
		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		// Filter by instructor.
		if ( isset( $array_filter['instructor'] ) ) {
			$selected_instructors = array_map( 'absint', explode( ',', $array_filter['instructor'] ) );

			if ( ! empty( $selected_instructors ) ) {
				$query_args['author__in'] = ' AND ' . $wpdb->posts . '.post_author IN (' . implode( ',', $selected_instructors ) . ')';
			}
		}
		else{
			$query_args['author__in'] = '';
		}


		$sql = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) FROM {$wpdb->posts} ";
		$sql .= $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= " WHERE {$wpdb->posts}.post_type = 'courses' AND {$wpdb->posts}.post_status = 'publish' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'] . $query_args['author__in'];

		return absint( $wpdb->get_var( $sql ) ); // WPCS: unprepared SQL ok.

	}

	protected function get_data_filter_for_instructors( $array_filter ) {
		$data        = array();
		$instructors = tutor_utils()->get_instructors( 0, 100, '', 'approved' );
		foreach ( $instructors as $instructor_key => $instructor ) {
			/**
			 * @var WP_User $instructor
			 */
			$instructor_id   = intval( $instructor->ID );
			$instructor_name = $instructor->display_name;
			$count           = $this->get_filtered_course_count_insructor( $instructor_id, $array_filter );

			if ( empty( $count ) ) {
				$count = 0;
			}
			$object        = new \stdClass();
			$object->id    = $instructor_id;
			$object->name  = $instructor_name;
			$object->count = $count;
			$data[]        = $object;


		}

		return $data;
	}

	protected function get_filtered_course_count_insructor( $current_user, $array_filter ) {
		global $wpdb;

		$meta_query               = array();
		$tax_query                = array();
		$query_args               = array();
		$query_args['meta_query'] = $this->get_meta_query_mb( $meta_query, $array_filter );
		$query_args['tax_query']  = $this->get_tax_query_mb( $tax_query, $array_filter );

		$author_query_sql = \Edumall_Course_Query::get_author_sql( [ $current_user ] );

		$meta_query = new \WP_Meta_Query( $query_args['meta_query'] );
		$tax_query  = new \WP_Tax_Query( $query_args['tax_query'] );

		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );
		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		//$search_query_sql = Edumall_Course_Query::get_search_title_sql();

		$sql = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) FROM {$wpdb->posts} ";
		$sql .= $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= " WHERE {$wpdb->posts}.post_type = 'courses' AND {$wpdb->posts}.post_status = 'publish' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'] . $author_query_sql['where'];

		return absint( $wpdb->get_var( $sql ) ); // WPCS: unprepared SQL ok.
	}

	protected function get_data_filter_for_languages( $array_filter ) {
		$data     = array();
		$taxonomy = \Edumall_Tutor::instance()->get_tax_language();

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		// Get only parent terms. Methods will recursively retrieve children.
		$terms = get_terms( [
			'taxonomy'   => $taxonomy,
			'hide_empty' => '1',
			'parent'     => 0,
		] );

		foreach ( $terms as $term_key => $term ) {

			$count = $this->get_filtered_term_counts_mb( $term->term_id, $array_filter );
			// Only show options with count > 0.
			if ( empty( $count ) ) {
				$count = 0;
			}
			$object        = new \stdClass();
			$object->id    = $term->term_id;
			$object->name  = $term->name;
			$object->count = $count;
			$data[]        = $object;


		}


		return $data;
	}

	protected function get_data_filter_for_durations( $array_filter ) {
		$data            = array();
		$duration_ranges = array(
			'short'     => esc_html__( 'Less than 2 hours', 'edumall' ),
			'medium'    => esc_html__( '3 - 6 hours', 'edumall' ),
			'long'      => esc_html__( '7 - 16 hours', 'edumall' ),
			'extraLong' => esc_html__( '17+ Hours', 'edumall' ),
		);

		$duration_ranges = apply_filters( 'edumall_widget_course_duration_filter_ranges', $duration_ranges );

		foreach ( $duration_ranges as $option_key => $option_name ) {
			$count = $this->get_filtered_course_count_durations( $option_key, $array_filter );

			// Only show options with count > 0.
			if ( empty( $count ) ) {
				$count = 0;
			}
			$object        = new \stdClass();
			$object->key   = $option_key;
			$object->name  = $option_name;
			$object->count = $count;
			$data[]        = $object;

		}

		return $data;
	}

	protected function get_filtered_course_count_durations( $duration, $array_filter ) {
		global $wpdb;

		$meta_query               = array();
		$tax_query                = array();
		$query_args               = array();
		$query_args['meta_query'] = $this->get_meta_query_mb( $meta_query, $array_filter );
		$query_args['tax_query']  = $this->get_tax_query_mb( $tax_query, $array_filter );


		// Set new duration filter.
		$query_args['meta_query'] = \Edumall_Course_Query::set_meta_query_duration( $query_args['meta_query'], (array) $duration );

		$meta_query = new \WP_Meta_Query( $query_args['meta_query'] );
		$tax_query  = new \WP_Tax_Query( $query_args['tax_query'] );


		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );
		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		// Filter by instructor.
		if ( isset( $array_filter['instructor'] ) ) {
			$selected_instructors = array_map( 'absint', explode( ',', $array_filter['instructor'] ) );

			if ( ! empty( $selected_instructors ) ) {
				$query_args['author__in'] = ' AND ' . $wpdb->posts . '.post_author IN (' . implode( ',', $selected_instructors ) . ')';
			}
		}
		else{
			$query_args['author__in'] = '';
		}


		//$search_query_sql = Edumall_Course_Query::get_search_title_sql();

		$sql = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) FROM {$wpdb->posts} ";
		$sql .= $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= " WHERE {$wpdb->posts}.post_type = 'courses' AND {$wpdb->posts}.post_status = 'publish' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'] . $query_args['author__in'];

		return absint( $wpdb->get_var( $sql ) ); // WPCS: unprepared SQL ok.
	}

	protected function get_data_filter_for_prices( $array_filter ) {
		$data          = array();
		$price_options = [
			''     => esc_html__( 'All', 'edumall' ),
			'free' => esc_html__( 'Free', 'edumall' ),
			'paid' => esc_html__( 'Paid', 'edumall' ),
		];

		foreach ( $price_options as $price_key => $price_name ) {
			$count = $this->get_filtered_course_count_prices( $price_key, $array_filter );

			// Only show options with count > 0.
			if ( empty( $count ) ) {
				$count = 0;
			}
			$object        = new \stdClass();
			$object->key   = $price_key;
			$object->name  = $price_name;
			$object->count = $count;
			$data[]        = $object;
		}

		return $data;
	}

	protected function get_filtered_course_count_prices( $price_type, $array_filter ) {
		global $wpdb;

		$meta_query               = array();
		$tax_query                = array();
		$query_args               = array();
		$query_args['meta_query'] = $this->get_meta_query_mb( $meta_query, $array_filter );
		$query_args['tax_query']  = $this->get_tax_query_mb( $tax_query, $array_filter );


		// Set new duration filter.
		$query_args['meta_query'] = \Edumall_Course_Query::set_meta_query_price( $query_args['meta_query'], $price_type );

		$meta_query = new \WP_Meta_Query( $query_args['meta_query'] );
		$tax_query  = new \WP_Tax_Query( $query_args['tax_query'] );


		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );
		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		// Filter by instructor.
		if ( isset( $array_filter['instructor'] ) ) {
			$selected_instructors = array_map( 'absint', explode( ',', $array_filter['instructor'] ) );

			if ( ! empty( $selected_instructors ) ) {
				$query_args['author__in'] = ' AND ' . $wpdb->posts . '.post_author IN (' . implode( ',', $selected_instructors ) . ')';
			}
		}
		else{
			$query_args['author__in'] = '';
		}


		//$search_query_sql = Edumall_Course_Query::get_search_title_sql();

		$sql = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) FROM {$wpdb->posts} ";
		$sql .= $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= " WHERE {$wpdb->posts}.post_type = 'courses' AND {$wpdb->posts}.post_status = 'publish' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'] . $query_args['author__in'];

		return absint( $wpdb->get_var( $sql ) ); // WPCS: unprepared SQL ok.
	}

	protected function get_data_filter_for_ratings( $array_filter ) {
		$data     = array();
		$taxonomy = \Edumall_Tutor::instance()->get_tax_visibility();

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return $data;
		}

		// Get only parent terms. Methods will recursively retrieve children.
		$terms = \Edumall_Tutor::instance()->get_course_visibility_term_ids();

		if ( empty( $terms ) ) {
			return $data;
		}

		for ( $rating = 5; $rating >= 1; $rating-- ) {


			$compare_slug = 'rated-' . $rating;
			$term_id      = $terms[ $compare_slug ];

			$count = 0;

			if ( $term_id !== 0 ) {
				$count = $this->get_filtered_term_counts_mb( $term_id, $array_filter );
			}
			$object        = new \stdClass();
			$object->key   = $rating;
			$object->count = $count;
			$data[]        = $object;
		}

		return $data;

	}

	protected function get_data_filter_for_sorting() {
		$data = array();
		$arr  = \Edumall_Tutor::instance()->get_course_sorting_options();
		//$object = json_decode(json_encode($arr), FALSE);
		foreach ( $arr as $key => $value ) {
			$object       = new \stdClass();
			$object->key  = $key;
			$object->name = $value;
			$data[]       = $object;
		}

		return $data;
	}

}

