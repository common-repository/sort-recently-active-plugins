<?php
/*
Plugin Name: Sort Recently Active Plugins
Plugin URI: 
Description: Sorts plugins in Recently Active list by deactivation date.
Version: 1.0
Author: Sergey Biryukov
Author URI: http://profiles.wordpress.org/sergeybiryukov/
*/

class Sort_Recently_Active_Plugins {

	public function __construct() {
		add_action( 'admin_head-plugins.php', array( $this, 'sort_plugins_by_deactivation_date' ) );
		add_action( 'deactivated_plugin',     array( $this, 'store_deactivation_time' ) );
		add_action( 'deleted_plugin',         array( $this, 'clear_deactivation_time' ) );

		register_uninstall_hook( __FILE__, array( 'Sort_Recently_Active_Plugins', 'uninstall' ) );
	}

	public function sort_plugins_by_deactivation_date() {
		global $wp_list_table, $status;

		if ( 'recently_activated' === $status ) {
			uksort( $wp_list_table->items, array( $this, '_order_callback' ) );
		}
	}

	private function _order_callback( $a, $b ) {
		global $wp_list_table;

		$times = get_option( 'sort_recently_active_plugins', array() );

		$a_time = isset( $times[ $a ] ) ? $times[ $a ] : '';
		$b_time = isset( $times[ $b ] ) ? $times[ $b ] : '';

		if ( $a_time && $b_time ) {
			return strcmp( $b_time, $a_time );
		} elseif ( $a_time && ! $b_time ) {
			return -1;
		} elseif ( ! $a_time && $b_time ) {
			return 1;
		} else {
			return strcasecmp( $wp_list_table->items[ $a ]['Name'], $wp_list_table->items[ $b ]['Name'] );
		}
	}

	public function store_deactivation_time( $plugin ) {
		$times = get_option( 'sort_recently_active_plugins', array() );

		$times[ $plugin ] = current_time( 'mysql' );

		update_option( 'sort_recently_active_plugins', $times );
	}

	public function clear_deactivation_time( $plugin ) {
		$times = get_option( 'sort_recently_active_plugins', array() );

		unset( $times[ $plugin ] );

		update_option( 'sort_recently_active_plugins', $times );
	}

	public static function uninstall() {
		delete_option( 'sort_recently_active_plugins' );
	}

}

new Sort_Recently_Active_Plugins;
