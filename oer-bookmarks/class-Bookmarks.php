<?php
 
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

require_once( 'class-Plugin.php' );

/**
 * 
 * 
 * @package OERB
 * @author Simon Wheatley
 **/
class OER_Bookmarks extends OERB_Plugin {

	/**
	 * The current version, used to cache bust for JS and CSS,
	 * and to know when to flush rewrite rules, update DB, etc.
	 *
	 * @var int
	 **/
	protected $version;

	/**
	 * Initiate!
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function __construct() {
		$this->setup( 'oerb', 'plugin' );
		if ( is_admin() ) {
			$this->add_action( 'admin_init' );
			$this->add_action( 'manage_bookmark_posts_custom_column', null, 0, 2 );
			$this->add_filter( 'manage_bookmark_posts_columns' );
		}
		$this->add_action( 'init', 'init_early', 0 );
		$this->add_meta_box( 'oerb_url', 'Bookmarked URL', 'url_metabox', 'bookmark', 'normal', 'core' );
		$this->add_action( 'save_post', null, null, 2 );
		$this->add_filter( 'the_content' );
		$this->add_shortcode( 'learningpaths', 'shortcode_learning_paths' );
		$this->saving = false;
		$this->version = 2;
	}
	
	// HOOKS AND ALL THAT
	// ==================

	/**
	 * Hooks the WP admin_init action to:
	 * * Potentially upgrade stuff
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function admin_init() {
		$this->maybe_upgrade();
		wp_enqueue_style( 'oerb-admin', $this->url( '/css/admin.css' ), array(), $this->version );
	}

	/**
	 * Hooks the WP init action to add a new custom post type.
	 *
	 * @return void
	 **/
	public function init_early() {
		$labels = array(
			'name' => __( 'Bookmarks', 'oerb' ),
			'singular_name' => __( 'Bookmark', 'oerb' ),
			'add_new' => __( 'Add New', 'oerb' ),
			'add_new_item' => __( 'Add New Bookmark', 'oerb' ),
			'edit_item' => __( 'Edit Bookmark', 'oerb' ),
			'new_item' => __( 'New Bookmark', 'oerb' ),
			'view_item' => __( 'View Bookmark', 'oerb' ),
			'search_items' => __( 'Search Bookmarks', 'oerb' ),
			'not_found' => __( 'No bookmarks found.', 'oerb' ),
			'not_found_in_trash' => __( 'No bookmarks found in Trash.', 'oerb' ),
			'parent_item_colon' => __( 'Parent Bookmark:', 'oerb' ),
		);
		$args = array( 
			'can_export' => true, 
			'description' => __( 'Bookmarks are a links to other web pages, with a description.', 'oerb' ), 
			'has_archive' => false, 
			'hierarchical' => false,
			'labels' => $labels, 
			'public' => true,
			'publicly_queryable' => true,
			'supports' => array( 'title', 'editor', 'comments', 'page-attributes' ), 
		 );
		register_post_type( 'bookmark', $args );
		
		$labels = array(
			'name' => __( 'Learning Paths', 'oerb' ),
			'singular_name' => __( 'Learning Path', 'oerb' ),
			'search_items' => __( 'Search Learning Paths', 'oerb' ),
			'popular_items' => __( 'Popular Learning Paths', 'oerb' ),
			'all_items' => __( 'All Paths', 'oerb' ),
			'edit_item' => __( 'Edit Path', 'oerb' ),
			'update_item' => __( 'Update Path', 'oerb' ),
			'add_new_item' => __( 'Add New Learning Path', 'oerb' ),
			'new_item_name' => __( 'New Path Name', 'oerb' ),
			'separate_items_with_commas' => __( 'Separate learning paths with commas', 'oerb' ),
			'add_or_remove_items' => __( 'Add or remove learning paths', 'oerb' ),
			'choose_from_most_used' => __( 'Choose from the most used learning paths', 'oerb' ),
		);
		// Note the new custom capability, we add this to roles in maybe_upgrade below
		$capabilities = array(
			'manage_terms'	=> 'manage_paths',
			'edit_terms'	=> 'manage_paths',
			'delete_terms'	=> 'manage_paths',
			'assign_terms'	=> 'edit_posts',
		);
		$args = array(
			'capabilities' => $capabilities,
			'hierarchical' => true,
			'labels' => $labels,
		);
		register_taxonomy( 'learning-path', 'bookmark', $args );
		register_taxonomy_for_object_type( 'category', 'bookmark' );
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
		if ( $url )
			update_post_meta( $post_id, '_bookmark_url', esc_url( $url ) );
	}

	/**
	 * Hooks the WP the_content filter to add a thumbnail of the site.
	 *
	 * @param string $content The content  
	 * @return string The content
	 * @author Simon Wheatley
	 **/
	public function the_content( $content ) {
		$post = get_post( get_the_ID() );
		if ( 'bookmark' != $post->post_type )
			return $content;

		$bookmark_url = get_post_meta( $post->ID, '_bookmark_url', true );
		if ( ! $bookmark_url )
			return $content;

		$img = '';
		$bookmark = '';

		// Thumbnail…
		// @TODO: Cache the thumbnail locally, trickier than you might think…
		$bookmark_url_enc = urlencode( $bookmark_url );
		$src = "http://s.wordpress.com/mshots/v1/{$bookmark_url_enc}?w=250";
		$img = "<a href='" . esc_attr( $bookmark_url ) . "' class='oerb-bookmark-thumb-link'><img src='$src' width='250' alt='Thumbnail of this website' class='oerb-bookmark-thumb' /></a>";

		// Visible URL…
		$bookmark = "<p class='oerb-bookmark-url'><span>Original URL:</span> <a href='" . esc_attr( $bookmark_url ) . "' class='oerb-bookmark-url'>" . esc_html( $bookmark_url ) . "</a></p>";

		return $img . '<div class="oerb-content">' . $content . $bookmark . '</div>';
	}

	/**
	 * Add in a Learning Path column.
	 *
	 * @param array $cols An array of column information 
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function manage_bookmark_posts_columns( $cols ) {
		// Add Learning Path in before Author
		$new_cols = array();
		foreach ( $cols as $name => $label ) {
			$new_cols[ $name ] = $label;
			if ( $name == 'title' ) {
				$new_cols[ 'learning-path' ] = 'Learning Paths';
			}
		}
		return $new_cols;
	}

		/**
		 * Content for the different columns.
		 *
		 * @param string $col_name The name of the column (dur) 
		 * @param int $post_id The ID of the post for this cell
		 * @return void
		 * @author Simon Wheatley
		 **/
		public function manage_bookmark_posts_custom_column( $col_name, $post_id ) {
			global $gad_admin_pages_posts;
			if ( 'learning-path' == $col_name ) {
				$terms = get_the_terms( $post_id, 'learning-path' );
				if ( ! empty( $terms ) ) {
					$post = get_post( $post_id );
					$out = array();
					foreach ( $terms as $c ) {
						$out[] = sprintf( '<a href="%s">%s</a>',
							esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'learning-path' => $c->slug ), 'edit.php' ) ),
							esc_html( sanitize_term_field( 'name', $c->name, $c->term_id, 'terms', 'display' ) )
						);
					}
					echo join( ', ', $out );
				} else {
					echo __( 'No learning path', 'oerb' );
				}
			}
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

	/**
	 * Callback function from the [learningpaths] shortcode, which prints
	 * all the learning paths.
	 *
	 * @param array $attr Attributes attributed to the shortcode.
	 * @param string $content Optional. Shortcode content.
	 * @return string
	 * @author Simon Wheatley
	 **/
	public function shortcode_learning_paths( $attr, $content = null ) {
		$vars = array();
		$vars[ 'paths' ] = get_terms( 'learning-path' );
		return $this->capture( 'shortcode-learning-paths.php', $vars );
	}

	// UTILITIES
	// =========


	/**
	 * Checks the DB structure is up to date, whether rewrite rules 
	 * need flushing, etc.
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function maybe_upgrade() {
		global $wpdb;
		$version = get_option( 'bfob_version', 0 );
		
		if ( $version == $this->version )
			return;

		error_log( "BFOB: Current version: v$version" );
		
		if ( $version < 1 ) {
			flush_rewrite_rules();
			error_log( "BFOB: Flushed rewrite rules" );
		}
		
		if ( $version < 2 ) {
			$wp_roles = new WP_Roles();
			// var_dump( $wp_roles );
			// exit;
			// Iterate all roles and add the manage_paths cap where that role
			// can manage_categories
			foreach ( $wp_roles->role_names as $role_name => $role_display_name ) {
				$some_role = $wp_roles->get_role( $role_name );
				if ( $some_role->has_cap( 'manage_categories' ) )
					$some_role->add_cap( 'manage_paths' );
			}
			// Ensure Authors can definitely manage_paths
			$author = $wp_roles->get_role( 'author' );
			$author->add_cap( 'manage_paths' );
			error_log( "BFOB: Added permissions for new capability" );
		}

		// N.B. Remember to increment the version property above when you add a new IF, 
		// as otherwise that upgrade will run every time!

		error_log( "BFOB: Done upgrade" );
		update_option( 'bfob_version', $this->version );
	}

} // END OER_Bookmarks class 

$oer_bookmarks = new OER_Bookmarks();

?>