<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<title><?php _e('Bookmark This', 'oerb') ?></title>

<?php
	wp_enqueue_style( 'press-this' );
	wp_enqueue_style( 'press-this-ie');
	wp_enqueue_style( 'colors' );
	wp_enqueue_script( 'post' );
?>
<script type="text/javascript">
//<![CDATA[
addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
var userSettings = {'url':'<?php echo SITECOOKIEPATH; ?>','uid':'<?php if ( ! isset($current_user) ) $current_user = wp_get_current_user(); echo $current_user->ID; ?>','time':'<?php echo time() ?>'};
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>', pagenow = 'press-this', isRtl = <?php echo (int) is_rtl(); ?>;
var photostorage = false;
//]]>
</script>

<?php
	do_action('admin_print_styles');
	do_action('admin_print_scripts');
	do_action('admin_head');
?>
	<script type="text/javascript">
	function insert_plain_editor(text) {
		edCanvas = document.getElementById('content');
		edInsertContent(edCanvas, text);
	}
	function set_editor(text) {
		if ( '' == text || '<p></p>' == text ) text = '<p><br /></p>';
		if ( tinyMCE.activeEditor ) tinyMCE.execCommand('mceSetContent', false, text);
	}
	function insert_editor(text) {
		if ( '' != text && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden()) {
			tinyMCE.execCommand('mceInsertContent', false, '<p>' + decodeURI(tinymce.DOM.decode(text)) + '</p>', {format : 'raw'});
		} else {
			insert_plain_editor(decodeURI(text));
		}
	}
	function append_editor(text) {
		if ( '' != text && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden()) {
			tinyMCE.execCommand('mceSetContent', false, tinyMCE.activeEditor.getContent({format : 'raw'}) + '<p>' + text + '</p>');
		} else {
			insert_plain_editor(text);
		}
	}

	jQuery(document).ready(function($) {
		//resize screen
		window.resizeTo(720,570);
		jQuery('#title').unbind();
		jQuery('#publish, #save').click(function() { jQuery('#saving').css('display', 'inline'); });

		$('#learning-pathdiv').children('h3, .handlediv').click(function(){
			$(this).siblings('.inside').toggle();
		});
	});
</script>
<style type="text/css" media="screen">
	#oerb_url { margin-top: 10px; }
</style>
</head>
<body class="press-this wp-admin">
<?php
if ( user_can_richedit() ) {
	wp_tiny_mce( true, array( 'height' => '300' ) );
}
?>
<form action="<?php echo esc_attr( admin_url( 'admin-ajax.php?action=bfob_post' ) ); ?>" method="post">
<div id="poststuff" class="metabox-holder">
	<div id="side-info-column">
		<div class="sleeve">
			<?php wp_nonce_field('bookmark-this') ?>
			<input type="hidden" name="post_type" id="post_type" value="bookmark"/>
			<input type="hidden" name="autosave" id="autosave" />
			<input type="hidden" id="original_post_status" name="original_post_status" value="draft" />
			<input type="hidden" id="prev_status" name="prev_status" value="draft" />

			<div id="submitdiv" class="postbox">
				<div class="handlediv" title="<?php _e( 'Click to toggle' ); ?>"><br /></div>
				<h3 class="hndle"><?php _e('Bookmark This', 'oerb') ?></h3>
				<div class="inside">
					<p id="publishing-actions">
					<?php
						submit_button( __( 'Save Draft' ), 'button', 'draft', false, array( 'id' => 'save' ) );
						if ( current_user_can('publish_posts') ) {
							submit_button( __( 'Publish' ), 'primary', 'publish', false );
						} else {
							echo '<br /><br />';
							submit_button( __( 'Submit for Review' ), 'primary', 'review', false );
						} ?>
						<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" id="saving" style="display:none;" />
					</p>
				</div>
			</div>

			<?php $tax = get_taxonomy( 'category' ); ?>
			<div id="categorydiv" class="postbox">
				<div class="handlediv" title="<?php _e( 'Click to toggle' ); ?>"><br /></div>
				<h3 class="hndle"><?php echo $tax->labels->name ?></h3>
				<div class="inside">
				<?php post_categories_meta_box( null, array(
					'id' => 'categorydiv',
					'title' => $tax->labels->name,
					'callback' => 'post_categories_meta_box',
					'args' => array( 'taxonomy' => 'category' ),
				) ); ?>
				</div>
			</div>

			<?php $tax = get_taxonomy( 'learning-path' ); ?>
			<div id="learning-pathdiv" class="postbox">
				<div class="handlediv" title="<?php _e( 'Click to toggle' ); ?>"><br /></div>
				<h3 class="hndle"><?php echo $tax->labels->name ?></h3>
				<div class="inside">
				<?php post_categories_meta_box( null, array(
					'id' => 'learning-pathdiv',
					'title' => $tax->labels->name,
					'callback' => 'post_categories_meta_box',
					'args' => array( 'taxonomy' => 'learning-path' ),
				) ); ?>
				</div>
			</div>

		</div>
	</div>
	<div class="posting">

		<div id="wphead">
			<img id="header-logo" src="<?php echo esc_url( includes_url( 'images/blank.gif' ) ); ?>" alt="" width="16" height="16" />
			<h1 id="site-heading">
				<a href="<?php echo get_option('home'); ?>/" target="_blank">
					<span id="site-title"><?php bloginfo('name'); ?></span>
				</a>
			</h1>
		</div>

		<?php if ( isset($posted) && intval($posted) ) { $post_id = intval($posted); ?>
		<div id="message" class="updated"><p><strong><?php _e('Your post has been saved.'); ?></strong> <a onclick="window.opener.location.replace(this.href); window.close();" href="<?php echo get_permalink( $post_id); ?>"><?php _e('View post'); ?></a> | <a href="<?php echo get_edit_post_link( $post_id ); ?>" onclick="window.opener.location.replace(this.href); window.close();"><?php _e('Edit Post'); ?></a> | <a href="#" onclick="window.close();"><?php _e('Close Window'); ?></a></p></div>
		<?php } ?>

		<div id="titlediv">
			<div class="titlewrap">
				<input name="title" id="title" class="text" value="<?php echo esc_attr($title);?>"/>
			</div>
		</div>

		<div id="extra-fields" style="display: none"></div>

		<div class="postdivrich">
			<div id="editor-toolbar">
				<?php if ( user_can_richedit() ) :
					wp_print_scripts( 'quicktags' );
					add_filter('the_editor_content', 'wp_richedit_pre'); ?>
					<a id="edButtonHTML" onclick="switchEditors.go('content', 'html');"><?php _e('HTML'); ?></a>
					<a id="edButtonPreview" class="active" onclick="switchEditors.go('content', 'tinymce');"><?php _e('Visual'); ?></a>
					<div class="zerosize"><input accesskey="e" type="button" onclick="switchEditors.go('content')" /></div>
				<?php endif; ?>

			</div>
			<div id="quicktags"></div>
			<div class="editor-container">
				<textarea name="content" id="content" style="width:100%;" class="theEditor" rows="15"><?php
					if ( $selection ) :
						echo wp_richedit_pre($selection);
					endif;
				?></textarea>
			</div>
		</div>

		<div id="oerb_url" class="postbox ">
			<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Bookmarked URL</span></h3>
				<div class="inside">
					<p>
						<label for="oerb_url">Enter the URL for this bookmark:</label>
						<input type="text" name="oerb_url" id="oerb_url" class="code" tabindex="7" value="<?php echo esc_attr( esc_url( $url ) ); ?>" style="width: 99%;">
					</p>
				</div>
		</div>
	</div>
</div>
</form>
<?php do_action('admin_print_footer_scripts'); ?>
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
</body>
</html>
