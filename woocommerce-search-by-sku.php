<?php
/*
	Plugin Name:          WooCommerce Search By SKU
	Plugin URI:           https://github.com/logic-design/woocommerce-search-by-sku
	Description:          Enables product searches using SKU's for WooCommerce themes 
	Version:              0.1.0
	Author:               Logic Design and Consultancy Ltd 
	Author URI:           https://www.logicdesign.co.uk/
	License:              GNU General Public License v2 or later
	License URI:          http://www.gnu.org/licenses/gpl-2.0.html

	Copyright 2010-2011  Logic Design and Consultancy Ltd hello@logicdesign.co.uk

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License version 2 as published by
	the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function loki_woo_search_where( $where )
{
	global $pagenow, $wpdb, $wp;

	// Storage array to keep product id's
	$search_ids = array();

	// Break search phrase into words
	$terms	= explode(' ', $wp->query_vars['s'] );

	// Loop through terms and compare with _sku meta_value
	if ( ! empty($terms) ) {
		foreach ( $terms as $term ) {
			$sku_to_id = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_parent FROM {$wpdb->posts} LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id WHERE meta_key='_sku' AND meta_value LIKE %s;", '%' . $wpdb->esc_like( wc_clean( $term ) ) . '%' ) );

			$sku_to_id = array_merge( wp_list_pluck( $sku_to_id, 'ID' ), wp_list_pluck( $sku_to_id, 'post_parent' ) );

			if ( sizeof( $sku_to_id ) > 0 ) {
				$search_ids = array_merge( $search_ids, $sku_to_id );
			}
		}
	}

	// If product ids have been found modify search query with id's
	if ( ! empty($search_ids) ) {
		$where = str_replace( 'AND (((', "AND ( ({$wpdb->posts}.ID IN (" . implode( ',', $search_ids ) . ")) OR ((", $where );
	}

	return $where;
}
add_filter( 'posts_where', 'loki_woo_search_where');

?>