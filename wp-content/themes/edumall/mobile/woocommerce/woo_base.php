<?php

namespace edumallmobile\woocommerce;


class Edumall_Mobile_Woo {
	protected static $instance = null;
	public $prefix = 'woo_edumall';


	public function __construct() {
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}



	public function initialize() {

		$this->order_route();

		add_filter( 'query_vars', array($this,'order_query_vars'));
	}


	public function order_route(){


		add_rewrite_rule(
			'^'.$this->prefix.'/woo_order/?$',
			'index.php?pagename=woo_order',
			'top' );
		add_rewrite_rule(
			'^'.$this->prefix.'/woo_order/(\d+)/?$',
			'index.php?pagename=woo_order&order_id=$matches[1]',
			'top' );
		add_rewrite_rule(
			'^'.$this->prefix.'/create_order/(.+?)$',
			'index.php?pagename=create_order&order_common=$matches[1]',
			'top' );
		flush_rewrite_rules();

	}


	public function order_query_vars( $query_vars ){

		$query_vars[] = 'pagename';
		$query_vars[] = 'order_id';
		$query_vars[] = 'order_common';
		return $query_vars;
	}



}

