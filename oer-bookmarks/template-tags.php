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

/**
 * Print the bookmark URL.
 *
 * @param int $post_id A post ID, otherwise the current ID in The Loop is used
 * @return void
 * @author Simon Wheatley
 **/
function bookmark_url( $post_id = null )	{
	echo get_bookmark_url( $post_id );
}

/**
 * Return the bookmark URL.
 *
 * @param int $post_id A post ID, otherwise the current ID in The Loop is used
 * @return string A URL
 * @author Simon Wheatley
 **/
function get_bookmark_url( $post_id = null )	{
	if ( is_null( $post_id ) )
		$post_id = get_the_ID();
	return get_post_meta( $post_id, '_bookmark_url', true );
}

?>