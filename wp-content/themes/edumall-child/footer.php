<?php
/**
 * The template for displaying the footer.
 *
 * @link    https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Edumall
 * @since   1.0
 */

?>
</div><!-- /.content-wrapper -->

<?php Edumall_THA::instance()->footer_before(); ?>

<?php edumall_load_template( 'footer/entry' ); ?>

<?php Edumall_THA::instance()->footer_after(); ?>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</div><!-- /.site -->

<?php wp_footer(); ?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('.js-example-placeholder-multiple').select2({
		  placeholder: {
		    id: '-1', // the value of the option
		    text: 'Select an option'
		  }
		});
	});
</script>
</body>
</html>
