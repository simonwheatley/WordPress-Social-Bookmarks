<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<div class="tool-box">
	<h3 class="title"><?php _e('Bookmark This') ?></h3>
	<p><?php _e('Bookmark This is a bookmarklet: a little app that runs in your browser and lets you grab bits of the web and publish links to them here as collectons of bookmarks for others to browse.', 'oerb');?></p>

	<p class="description"><?php _e('Drag-and-drop the following link to your bookmarks bar or right click it and add it to your favorites for a posting shortcut.', 'oerb') ?></p>
	<p class="pressthis bookmarkthis"><a onclick="return false;" oncontextmenu="if(window.navigator.userAgent.indexOf('WebKit')!=-1)jQuery('.pressthis-code').show().find('textarea').focus().select();return false;" href="<?php echo htmlspecialchars( $bookmarklet_link ); ?>"><span><?php _e('Bookmark This', 'oerb') ?></span></a></p>
	<div class="pressthis-code" style="display:none;">
	<p class="description"><?php _e('If your bookmarks toolbar is hidden: copy the code below, open your Bookmarks manager, create new bookmark, type Press This into the name field and paste the code into the URL field.', 'oerb') ?></p>
	<p><textarea rows="5" cols="120" readonly="readonly"><?php echo htmlspecialchars( $bookmarklet_link ); ?></textarea></p>
	</div>
</div>
