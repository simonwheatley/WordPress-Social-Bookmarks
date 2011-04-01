<?php

/*
Plugin Name: OER Bookmarks
Plugin URI: http://simonwheatley.co.uk/wordpress/oerb
Description: OER bookmarks and bookmarklet for bookmarking
Version: 0.4
Author: Simon Wheatley
Author URI: http://simonwheatley.co.uk//wordpress/
*/
 
/*  Copyright 2011 Simon Wheatley

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

require_once( 'plugin.php' );

/**
 * 
 * 
 * @package OERB
 * @author Simon Wheatley
 **/
class OERB extends OERB_Plugin {
	
	/**
	 * A flag to avoid infinite loops when saving the GUID.
	 *
	 * @var boolean
	 **/
	protected $saving;

	/**
	 * Initiate!
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function __construct() {
		$this->setup( 'oerb' );
		$this->add_action( 'init' );
		$this->add_meta_box( 'oerb_url', 'Bookmarked URL', 'url_metabox', 'bookmark', 'normal', 'core' );
		$this->add_action( 'save_post', null, null, 2 );
		$this->add_filter( 'the_content' );
		$this->add_filter( 'the_excerpt', 'the_content' );
		$this->saving = false;
	}
	
	// HOOKS AND ALL THAT
	// ==================

	/**
	 * Hooks the WP init action to add a new custom post type.
	 *
	 * @return void
	 **/
	public function init() {
		$labels = array(
			'name' => 'Bookmarks',
			'singular_name' => 'Bookmark',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Bookmark',
			'edit_item' => 'Edit Bookmark',
			'new_item' => 'New Bookmark',
			'view_item' => 'View Bookmark',
			'search_items' => 'Search Bookmarks',
			'not_found' => 'No bookmarks found.',
			'not_found_in_trash' => 'No bookmarks found in Trash.',
			'parent_item_colon' => 'Parent Bookmark:',
		);
		$args = array( 
			'can_export' => true, 
			// 'capabilities' => array(), 
			'capability_type' => 'post', 
			'description' => 'OER Bookmarks', 
			'exclude_from_search' => false,
			'has_archive' => true, 
			'hierarchical' => false,
			'labels' => $labels, 
			'public' => true, 
			'publicly_queryable' => true, 
			'query_var' => true,
			'rewrite' => true, 
			'show_in_menu' => true,
			'show_in_nav_menus' => true, 
			'show_ui' => true, 
			'supports' => array( 'title', 'editor', 'comments' ), 
			// 'taxonomies' => array(), 
		 );
		register_post_type( 'bookmark', $args );
	}
	
	/**
	 * Hooks the WP save_post action to save our metabox GUID field.
	 *
	 * @param int $post_id The post ID
	 * @param object $post The WP Post object 
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function save_post( $post_id, $post ) {
		$url = @ $_POST[ 'oerb_url' ];
		if ( $url && ! $this->saving ) {
			$this->saving = true;
			update_post_meta( $post_id, '_bookmark_url', $url );
			$this->saving = false;
		}
	}

	/**
	 * Hooks the WP the_content filter to add a thumbnail of the site.
	 *
	 * @param string $content The content  
	 * @return string The content
	 * @author Simon Wheatley
	 **/
	public function the_content( $content ) {
		global $post;
		$img = '';
		$bookmark = '';
		$bookmark_url = get_post_meta( $post->ID, '_bookmark_url', true );
		if ( $post->post_type == 'bookmark' && $bookmark_url ) {
			// Thumbnail…
			// @TODO: Cache the thumbnail locally
			$bookmark_url_enc = urlencode( $bookmark_url );
			$src = "http://s.wordpress.com/mshots/v1/{$bookmark_url_enc}?w=250";
			$img = "<img src='$src' width='250' alt='Thumbnail of this website' class='oerb-bookmark-thumb' />";
			// Visible URL…
			$bookmark = "<p class='bookmark-url'><span>Original URL:</span> <a href='" . esc_attr( $bookmark_url ) . "'>" . esc_html( $bookmark_url ) . "</a></p>";
		}
		return $img . $bookmark . $content;
	}

	// CALLBACKS
	// =========
	
	/**
	 * Callback function, provides HTML for the GUID metabox UI.
	 *
	 * @param object $post The current WP Post object 
	 * @param object $metabox The metabox object, title, etc
	 * @return string Some HTML
	 * @author Simon Wheatley
	 **/
	public function url_metabox( $post, $metabox ) {
		$vars = array();
		$vars[ 'url' ] = get_post_meta( $post->ID, '_bookmark_url', true );
		$this->render_admin( 'url-metabox.php', $vars );
	}	

	// UTILITIES
	// =========


} // END OERB class 

$oerb = new OERB();




?>