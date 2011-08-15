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

require_once( 'class-Widget.php' );

/**
 * new WordPress Widget format, extended by SW Widget base class
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class OERB_Learning_Paths_Widget extends OERB_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 **/
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'oerb-learning-path', 
			'description' => __( 'Displays any related learning paths. This widget only shows on single post pages, otherwise it is invisible.', 'oerb' ), 
		);
		$this->WP_Widget( 'oerb-learning-path', 'Learning Paths', $widget_ops );
	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @param array  An array of standard parameters for widgets in this theme 
	 * @param array  An array of settings for this widget instance 
	 * @return void Echoes it's output
	 **/
	public function widget( $args, $instance ) {
		if ( ! is_singular( 'post' ) && ! is_category() )
			return;
		extract( $args );
		extract( $instance, EXTR_SKIP );

		$title = apply_filters( 'widget_title', $title );

		// Wow. Some ugly arsed SQL coming up round about now.
		// I'm attempting to find all the bookmarks withing the
		// categories for the current post, then find the bookmarks 
		// within those categories, then find the distinct learning 
		// paths those bookmarks belong to.
		global $wpdb;
		// Ensure nothing's been screwing around with the global ID thingy
		wp_reset_query();
		if ( is_singular( 'post' ) ) {
			$cats = wp_get_object_terms( get_the_ID(), 'category', array( 'fields' => 'ids' ) );
			// Irritatingly, the IDs come out as strings and we need integers… *sigh*
			$cats = array_map( 'absint', $cats );
		} elseif ( is_category() ) {
			$cat = get_queried_object();
			$cats = array( (int) $cat->term_id  );
		}
		$cat_ids = join( ',', $cats );
		// Create a sub-query to get the IDs of the bookmarks in the categories that
		// this post belongs to.
		$bookmark_ids_sql = " SELECT DISTINCT p.ID FROM $wpdb->posts as p, $wpdb->terms as t, $wpdb->term_relationships as tr, $wpdb->term_taxonomy as tt WHERE p.ID = tr.object_id AND tr.term_taxonomy_id = tt.term_taxonomy_id AND t.term_id = tt.term_id AND p.post_type = 'bookmark' AND p.post_status = 'publish' AND tt.taxonomy = 'category' AND t.term_id IN ( $cat_ids ) ";
		// Get the term_ids of the learning paths which have bookmarks from the above sub-query.
		$sql = " SELECT DISTINCT t.term_id FROM $wpdb->terms as t, $wpdb->term_relationships as tr, $wpdb->term_taxonomy as tt WHERE tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'learning-path' AND tt.term_id = t.term_id AND tr.object_id IN ( $bookmark_ids_sql ) ";
		$term_ids = $wpdb->get_col( $sql );
		// WOW. That query was MENTAL.
		?>
			<?php echo $before_widget; ?>
			<?php if ( $title ) : ?>
				<?php echo $before_title . $title . $after_title; ?>
			<?php endif; ?>

			<?php if ( $intro ) : ?>
				<p><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
			
			<?php if ( empty( $term_ids ) ) : ?>

				<p><em><?php _e( 'No learning paths available', 'oerb' ); ?></em></p>

			<?php else : ?>
				
				<ul>

				<?php foreach ( $term_ids as $term_id ) : $path = get_term( $term_id, 'learning-path' ); ?>
				
					<li><a href="<?php echo esc_attr( get_term_link( $path, 'learning-path' ) ); ?>"><?php echo esc_html( $path->name ); ?></a></li>
				
				<?php endforeach; ?>

				</ul>
				
			<?php endif; ?>

			<?php echo $after_widget; ?>
		<?php
	}

	/**
	 * Deals with the settings when they are saved by the admin. Here is
	 * where any validation should be dealt with.
	 *
	 * @param array  An array of new settings as submitted by the admin
	 * @param array  An array of the previous settings 
	 * @return array The validated and (if necessary) amended settings
	 **/
	public function update( $new_instance, $old_instance ) {
		// update logic goes here
		$updated_instance = $new_instance;
		return $updated_instance;
	}

	/**
	 * Displays the form for this widget on the Widgets page of the WP Admin area.
	 *
	 * @param array  An array of the current settings for this widget
	 * @return void Echoes it's output
	 **/
	public function form( $instance ) {
		extract( $instance, EXTR_SKIP );
		if ( empty( $title ) )
			$title = __( 'Related Learning Paths', 'oerb' );
		$this->input_text( __( 'Title', 'oerb' ), 'title', $title );
		$this->input_text( __( 'Intro', 'oerb' ), 'intro', $intro, __( 'Any intro text will be placed above the list of available learning paths.', 'oerb' ) );
	}
}
add_action( 'widgets_init', create_function( '', "register_widget('OERB_Learning_Paths_Widget');" ) );


?>