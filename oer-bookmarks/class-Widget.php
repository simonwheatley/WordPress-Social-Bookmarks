<?php 

/*  
	Copyright 2009 Simon Wheatley

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


/**
 * Extends the WP_Widget base class in ways I find handy.
 */
class OERB_Widget extends WP_Widget {

	// FORM TEMPLATE FUNCTIONS
	
	// Fields for the admin form

	function input_text( $label, $var, $value, $note = false, $class = 'widefat' )
	{
		$value = esc_attr( $value );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( $var ); ?>"><?php echo $label; ?> 
				<input class="widefat" id="<?php echo $this->get_field_id( $var ); ?>" name="<?php echo $this->get_field_name( $var ); ?>" type="text" value="<?php echo $value; ?>" />
			</label>
			<?php if ( $note ) { ?>
				<br /><small><?php echo $note; ?></small>
			<?php } ?>
		</p>
		<?php
	}
	
	function input_conversational_mini_text( $label, $var, $value, $note = false, $class = 'widefat' )
	{
		$value = esc_attr( $value );
		?>
		<p><label for="<?php echo $this->get_field_id( $var ); ?>"><?php echo $label; ?></label>
			<input size="3" id="<?php echo $this->get_field_id( $var ); ?>" name="<?php echo $this->get_field_name( $var ); ?>" type="text" value="<?php echo $value; ?>" /><br/>
			<?php if ( $note ) { ?>
				<small><?php echo $note; ?></small>
			<?php } ?>
		</p>
		<?php
	}

	function input_select( $label, $var, $options, $selected, $note = false  )
	{
		?>
		<p>
			<label for="<?php echo $this->get_field_id( $var ); ?>"><?php echo $label; ?> <br />
				
			<select name="<?php echo $this->get_field_name( $var ); ?>" id="<?php echo $this->get_field_id( $var ); ?>">
				<option value="">-- <?php _e( 'please select a module', 'wmlib' ); ?> --</option>
				<?php foreach ( $options as $i => $option ) { ?>
					<option value="<?php echo esc_attr( $i ); ?>" <?php if ( $i == $selected ) { ?>selected="selected"<?php } ?>><?php echo $option; ?></option>
				<?php } ?>
				
			</label>
			<?php if ( $note ) { ?>
				<br /><small><?php echo $note; ?></small>
			<?php } ?>
		</p>
		<?php
	}

	function input_checkbox( $label, $var, $value, $note = false, $class = 'widefat' )
	{
		$checked = ( $value ) ? ' checked="checked" ' : '';
		?>
		<p><input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( $var ); ?>" name="<?php echo $this->get_field_name( $var ); ?>" <?php echo $checked; ?> />
			<label for="<?php echo $this->get_field_id( $var ); ?>"><?php echo $label; ?></label>
			<?php if ( $note ) { ?>
				<br /><small><?php echo $note; ?></small>
			<?php } ?>
		</p>
		<?php
	}

	protected function truncate( $str, $num_words )
	{
		$str = strip_tags( $str );
		$words = explode(' ', $str );
		if ( count( $words ) > $num_words) {
			$k = $num_words;
			$use_dotdotdot = 1;
		} else {
			$k = count( $words );
			$use_dotdotdot = 0;
		}
		$words  = array_slice( $words, 0, $k );
		$excerpt = trim( join( ' ', $words ) );
		$excerpt .= ($use_dotdotdot) ? '…' : '';
		return $excerpt;
	}
	
	protected function add_classes( $before_widget, $additional_classes )
	{
		$main_widget_class = $this->widget_options[ 'classname' ];
		$classes = "$main_widget_class $additional_classes ";
		$before_widget = str_replace( $main_widget_class, $classes, $before_widget );
		return $before_widget;
	}
	

}

?>