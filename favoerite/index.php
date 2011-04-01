<?php
/*
Plugin Name: favoerite
Plugin URI: http://www.g-loaded.eu/2006/01/14/creative-commons-configurator-wordpress-plugin/
Description: Adds a Creative Commons license to your blog pages and feeds. Also, provides some <em>Template Tags</em> for use in your theme templates. Please visit the plugin's <a href="options-general.php?page=cc-configurator.php">configuration panel</a>.
Version: 1.2
Author: George Notaras
Author URI: http://www.g-loaded.eu/
*/

function bookmarkletdisplay_function(){

	echo "<div class='updated fade'>";
    	
    echo "<p style=\"text-decoration:underline; font-weight:bold\">Share you favourites via wordpress.</p>";
    	
    echo "<p>Drag <a href=\"javascript:window.open('" . WP_PLUGIN_URL . "/favoerite/bookmark.php?author=" . get_current_user_id() . "&title=' + document.title + '&url=' + window.location)\">Favoerite</a> to your menu bar or right click on <a href=\"javascript:window.open('" . WP_PLUGIN_URL . "/favoerite/bookmark.php?author=" . get_current_user_id() . "&title=' + document.title + '&url=' + window.location)\">Favoerite</a></p>";
    
    echo "</div>";

}



add_action('admin_notices', 'bookmarkletdisplay_function');

?>