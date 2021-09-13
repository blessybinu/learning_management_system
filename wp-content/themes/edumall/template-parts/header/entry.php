<?php
$type = Edumall_Global::instance()->get_header_type();

if ( 'none' === $type ) {
	return;
}

if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
	edumall_load_template( 'header/header', $type );
}
