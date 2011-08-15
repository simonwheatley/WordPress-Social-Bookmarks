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
 * Provide the HTML for the bookmarklet UI and handle
 * the form submissions from it.
 * 
 * @package BFOB
 * @author Simon Wheatley
 **/
class OER_Bookmarklet extends OERB_Plugin {
	
	/**
	 * Used for JS and CSS cache busting mostly.
	 *
	 * @var string
	 **/
	protected $version = 1;
	
	/**
	 * Initiate!
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function __construct() {
		$this->setup( 'bfob' );
		if ( is_admin() ) {
			$this->add_action( 'wp_ajax_nopriv_bfob', 'redirect_to_login' );
			$this->add_action( 'wp_ajax_nopriv_bfob_post', 'redirect_to_login' );
			$this->add_action( 'wp_ajax_bfob', 'bookmarklet' );
			$this->add_action( 'wp_ajax_bfob_post', 'handle_post' );
			$this->add_action( 'tool_box' );
		}
		$this->add_action( 'bfob_process_sideload', 'process_sideload_screenshot', null, 3 );
	}
	
	// HOOKS AND ALL THAT
	// ==================

	/**
	 * Called when an unprivileged user hits the AJAX gateway asking
	 * for the bookmarklet UI.
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function redirect_to_login() {
		wp_redirect( wp_login_url( $_SERVER['REQUEST_URI'] ) );
		exit;
	}

	/**
	 * Hooks the WP action tool_box to add a new "Press This" style
	 * bookmarklet for the user.
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function tool_box() {
		// if ( ! current_user_can( 'edit_posts' ) )
		// 	return;
		$vars = array();
		$vars[ 'bookmarklet_link' ] = $this->get_bookmarklet_link();
		$this->render_admin( 'tools-bookmarklet.php', $vars );
	}

	/**
	 * Hooks a dynamic AJAX action to provide the UI
	 * for someone to bookmark something.
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function bookmarklet() {
		require_once( ABSPATH . '/wp-admin/includes/meta-boxes.php' );
		// Set Variables
		$vars = array();
		$vars[ 'title' ] = isset( $_GET['t'] ) ? trim( strip_tags( html_entity_decode( stripslashes( $_GET['t'] ) , ENT_QUOTES) ) ) : '';

		$selection = '';
		if ( !empty( $_GET['s'] ) ) {
			$selection = str_replace('&apos;', "'", stripslashes($_GET['s']));
			$selection = trim( htmlspecialchars( html_entity_decode($selection, ENT_QUOTES) ) );
		}

		if ( ! empty( $selection ) ) {
			$selection = preg_replace('/(\r?\n|\r)/', '</p><p>', $selection);
			$selection = '<blockquote><p>' . str_replace('<p></p>', '', $selection) . '</p></blockquote>';
		}
		
		if ( ! empty( $_GET['p'] ) ) {
			$description = str_replace('&apos;', "'", stripslashes($_GET['p']));
			$description = trim( htmlspecialchars( html_entity_decode($description, ENT_QUOTES) ) );
			if ( ! empty( $description ) ) {
				$selection = '<p>' . str_replace('<p></p>', '', $description) . '</p>' . $selection;
				
			}
		}
		
		$vars[ 'selection' ] = $selection;

		$vars[ 'url' ] = isset($_GET['u']) ? esc_url($_GET['u']) : '';
		
		$vars[ 'null_post' ] = (object) array( 'ID' => 0 );
		
		$this->render_admin( 'bookmarklet-ui.php', $vars );
		exit;
	}

	/**
	 * Hooks a dynamic AJAX function and handles the posted data for a bookmark.
	 * 
	 * Cribbed from press_it, but a lot simpler
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	public function handle_post() {
		// define some basic variables
		$quick = array();
		if ( isset( $_POST[ 'publish' ] ) && current_user_can( 'publish_posts' ) )
			$quick[ 'post_status' ] = 'publish';
		elseif ( isset( $_POST[ 'review' ] ) )
			$quick[ 'post_status' ] = 'pending';
		else
			$quick[ 'post_status' ] = 'draft';
		$quick[ 'post_category' ] = isset($_POST[ 'post_category' ]) ? $_POST[ 'post_category' ] : null;
		$quick[ 'post_type' ] = ( post_type_exists( $_POST[ 'post_type' ] ) ) ? $_POST[ 'post_type' ] : 'post' ;
		$quick[ 'tax_input' ] = isset($_POST[ 'tax_input' ]) ? $_POST[ 'tax_input' ] : null;
		$quick[ 'post_title' ] = ( trim($_POST[ 'title' ]) != '' ) ? $_POST[ 'title' ] : '  ';
		$quick[ 'post_content' ] = isset($_POST[ 'content' ]) ? $_POST[ 'content' ] : '';

		// insert the post with nothing in it, to get an ID
		$post_id = wp_insert_post($quick, true);
		if ( is_wp_error($post_id) )
			wp_die($post_id);

		$status = $quick[ 'post_status' ];

		$post = get_post( $post_id );

		$message = ( 'publish' == $status ) ? __( 'Your %s has been published.', 'oerb' ) : __( 'Your %s has been saved.', 'oerb' ) ;
		$post_type_object = get_post_type_object( $post->post_type );
		$message = sprintf( $message, strtolower( $post_type_object->labels->singular_name ) );

		$vars = array();
		$vars[ 'post_type_name' ] = strtolower( $post_type_object->labels->singular_name );
		$vars[ 'post_id' ] = $post_id;
		$vars[ 'admin_dir' ] = admin_url();
		$vars[ 'text_direction' ] = ( is_rtl() ) ? 'rtl' : 'ltr';
		$vars[ 'header' ] = ( 'publish' == $status ) ? __( 'Published', 'oerb' ) : __( 'Saved', 'oerb' ) ;
		$vars[ 'title' ] = ( 'publish' == $status ) ? __( 'Published', 'oerb' ) : __( 'Saved', 'oerb' ) ;
		$vars[ 'message' ] = $message;
		$this->render_admin( 'publish-confirm.php', $vars );
		die();
	}

	// UTILITIES
	// =========

	/**
	 * Retrieve shortcut link.
	 *
	 * Use this in 'a' element 'href' attribute.
	 *
	 * @return string
	 */
	protected function get_bookmarklet_link() {
		// This JS is designed to:
		// * Get any selected text
		// * Get the document description from the meta description element
		// * Launch the Bookmark This window
		$link = "javascript:
				var d=document,
				w=window,
				dd=function(){
					var x,m=d.getElementsByTagName('meta');
				    if(!m){return;}
				    for(x=0,y=m.length; x<y; x++){if(m[x].name.toLowerCase()=='description'){return m[x].content;}}
				},
				n=w.getSelection,
				k=d.getSelection,
				x=d.selection,
				p=dd(),
				s=(n?n():(k)?k():(x?x.createRange().text:0)),
				f='" . admin_url( 'admin-ajax.php' ) . "',
				l=d.location,
				e=encodeURIComponent,
				u=f+'?action=bfob&u='+e(l.href)+'&t='+e(d.title)+'&s='+e(s)+'&v=1';
				if(p) u+='&p='+e(p);
				a=function(){if(!w.open(u,'t','toolbar=0,resizable=1,scrollbars=1,status=1,width=720,height=570'))l.href=u;};
				if (/Firefox/.test(navigator.userAgent)) setTimeout(a, 0); else a();
				void(0)";

		$link = str_replace(array("\r", "\n", "\t"),  '', $link);

		return $link;
	}

} // END BFOB_Bookmarklet class 

$oer_bookmarklet = new OER_Bookmarklet();

?>