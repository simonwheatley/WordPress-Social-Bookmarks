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
class OERB_All_Paths_Widget extends OERB_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 **/
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'oerb-all-paths', 
			'description' => __( 'Displays links to all Learning Paths.', 'oerb' ), 
		);
		$this->WP_Widget( 'oerb-learning-path', 'All Learning Paths', $widget_ops );
	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @param array  An array of standard parameters for widgets in this theme 
	 * @param array  An array of settings for this widget instance 
	 * @return void Echoes it's output
	 **/
	public function widget( $args, $instance ) {
		extract( $args );
		extract( $instance, EXTR_SKIP );

		$title = apply_filters( 'widget_title', $title );

		?>
			<?php echo $before_widget; ?>
			<?php if ( $title ) : ?>
				<?php echo $before_title . $title . $after_title; ?>
			<?php endif; ?>

			<?php if ( $intro ) : ?>
				<p><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
			
			<?php if ( $paths = get_terms( 'learning-path' ) ) : ?>

				<ul>

				<?php foreach ( $paths as $path ) : ?>
				
					<li><a href="<?php echo esc_attr( get_term_link( $path, 'learning-path' ) ); ?>"><?php echo esc_html( $path->name ); ?></a></li>
				
				<?php endforeach; ?>

				</ul>

			<?php else : ?>
				
				<p><?php _e( 'No learning paths found.', 'oerb' ); ?></p>
				
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
			$title = __( 'All Learning Paths', 'oerb' );
		$this->input_text( __( 'Title', 'oerb' ), 'title', $title );
		$this->input_text( __( 'Intro', 'oerb' ), 'intro', $intro, __( 'Any intro text will be placed above the list of available learning paths.', 'oerb' ) );
	}
}
add_action( 'widgets_init', create_function( '', "register_widget('OERB_All_Paths_Widget');" ) );


?>