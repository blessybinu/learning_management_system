<?php
/**
 * The template for displaying all single posts.
 *
 * @link    https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Edumall
 * @since   1.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="page-content" class="page-content">
	<div class="container">
		<div class="row">

			<?php Edumall_Sidebar::instance()->render( 'left' ); ?>

			<div class="page-main-content">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php edumall_load_template( 'global/content-rich-snippet' ); ?>

					<?php edumall_load_template( 'blog/single/style', 'standard' ); ?>
				<?php endwhile; ?>
			</div>

			<?php Edumall_Sidebar::instance()->render( 'right' ); ?>

		</div>
	</div>
</div>
