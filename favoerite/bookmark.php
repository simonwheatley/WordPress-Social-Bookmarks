<?php
/**
 * Press This Display and Handler.
 *
 * @package WordPress
 * @subpackage Press_This
 */

define('IFRAME_REQUEST' , true);

/** WordPress Administration Bootstrap */
require_once('../../../wp-admin/admin.php');

header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

if ( ! current_user_can('edit_posts') )
	wp_die( __( 'Cheatin&#8217; uh?' ) );

/**
 * Press It form handler.
 *
 * @package WordPress
 * @subpackage Press_This
 * @since 2.6.0
 *
 * @return int Post ID
 */
function press_it() {
	// define some basic variables
	$quick = array();
	$quick['post_status'] = 'publish'; // Publish and be damned!
	$quick['post_title'] = ( trim($_POST['title']) != '' ) ? $_POST['title'] : '  ';
	$quick['post_content'] = isset($_POST['post_content']) ? $_POST['post_content'] : '';
	$quick['post_type'] = 'bookmark';

	// insert the post with nothing in it, to get an ID
	$post_ID = wp_insert_post($quick, true);
	if ( is_wp_error($post_ID) )
		wp_die($post_ID);

	$content = isset($_POST['content']) ? $_POST['content'] : '';
	
	// set the post_content and status
	$quick['post_status'] = isset($_POST['publish']) ? 'publish' : 'draft';
	$quick['post_content'] = $content;
	// error handling for media_sideload
	if ( is_wp_error($upload) ) {
		wp_delete_post($post_ID);
		wp_die($upload);
	} else {
		// Post formats
		if ( current_theme_supports( 'post-formats' ) && isset( $_POST['post_format'] ) ) {
			$post_formats = get_theme_support( 'post-formats' );
			if ( is_array( $post_formats ) ) {
				$post_formats = $post_formats[0];
				if ( in_array( $_POST['post_format'], $post_formats ) )
					set_post_format( $post_ID, $_POST['post_format'] );
				elseif ( '0' == $_POST['post_format'] )
					set_post_format( $post_ID, false );
			}
		}

		$quick['ID'] = $post_ID;
		wp_update_post($quick);
	}
	wp_publish_post( $post_ID );
	return $post_ID;
}


// For submitted posts.
if ( isset($_REQUEST['action']) && 'post' == $_REQUEST['action'] ) {

	print_r($_REQUEST);

	check_admin_referer('press-this');
	$post_ID = press_it();
	$posted =  $post_ID;
	add_post_meta($post_ID, "_bookmark_url", $_REQUEST['url']);
	
	$linkdata = array(
		"link_url"		=> $_REQUEST['url'], // varchar, the URL the link points to
		"link_name"		=> $_REQUEST['title'], 
		"link_owner"	=> $_REQUEST['author']
		);
		
	echo wp_insert_link($linkdata);
	
	echo "DONE";	
	
} else {
	$post_ID = 0;
}

// Set Variables
$title = $_GET['title']; 

$url = $_GET['url'];	

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<title>Add a favourite to Wordpress</title>

<?php
	add_thickbox();
	wp_enqueue_style( 'press-this' );
	wp_enqueue_style( 'press-this-ie');
	wp_enqueue_style( 'colors' );
	wp_enqueue_script( 'post' );
	wp_enqueue_script( 'editor' );
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

	if ( user_can_richedit() ) {
		wp_tiny_mce( true, array( 'height' => '370' ) );
		add_action( 'admin_print_footer_scripts', 'wp_tiny_mce_preload_dialogs', 30 );
	}
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
			tinyMCE.execCommand('mceCleanup');
		} else {
			insert_plain_editor(text);
		}
	}

</script>
</head>
<body class="wp-admin">
<div id="wphead"></div>
<form action="" method="post">
<div id="poststuff" class="metabox-holder">
	<div id="side-info-column">
		<div class="sleeve">
			<?php wp_nonce_field('press-this') ?>
			<input type="hidden" name="post_type" id="post_type" value="text"/>
			<input type="hidden" name="autosave" id="autosave" />
			<input type="hidden" id="original_post_status" name="original_post_status" value="draft" />
			<input type="hidden" id="prev_status" name="prev_status" value="draft" />
			<input type="hidden" name="_url_add" value="<?PHP echo $_GET['url']; ?>" />
			<input type="hidden" name="action" value="post" />
			<input type="hidden" name="_title_add" value="<?PHP echo $_GET['title']; ?>" />
			<input type="hidden" name="_author_add" value="<?PHP echo $_GET['author']; ?>" />
			<div id="submitdiv" class="stuffbox">
				<div class="inside">
					<p>
					<?php
						submit_button( __( 'Submit' ), 'primary', 'review', false );
				    ?>
					<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" id="saving" style="display:none;" />
					</p>
					<?php if ( current_theme_supports( 'post-formats' ) && post_type_supports( 'post', 'post-formats' ) ) :
							$post_formats = get_theme_support( 'post-formats' );
							if ( is_array( $post_formats[0] ) ) :
								$default_format = get_option( 'default_post_format', '0' );
						?>
					<p>
						<label for="post_format"><?php _e( 'Post Format:' ); ?>
						<select name="post_format" id="post_format">
							<option value="0"><?php _e( 'Standard' ); ?></option>
						<?php foreach ( $post_formats[0] as $format ): ?>
							<option<?php selected( $default_format, $format ); ?> value="<?php echo esc_attr( $format ); ?>"> <?php echo esc_html( get_post_format_string( $format ) ); ?></option>
						<?php endforeach; ?>
						</select></label>
					</p>
					<?php endif; endif; ?>
				</div>
			</div>
		</div>
	</div>
	<div class="posting">
		<?php if ( isset($posted) && intval($posted) ) { $post_ID = intval($posted); ?>
		<div id="message" class="updated"><p><strong><?php _e('Your post has been saved.'); ?></strong> <a onclick="window.opener.location.replace(this.href); window.close();" href="<?php echo get_permalink( $post_ID); ?>"><?php _e('View post'); ?></a> | <a href="<?php echo get_edit_post_link( $post_ID ); ?>" onclick="window.opener.location.replace(this.href); window.close();"><?php _e('Edit Post'); ?></a> | <a href="#" onclick="window.close();"><?php _e('Close Window'); ?></a></p></div>
		<?php } ?>

		<div id="titlediv">
			<div class="titlewrap">
				<input name="title" id="title" class="text" value="<?php echo urldecode($_GET['title']);?>"/>
			</div>
		</div>
		<div class="postdivrich">
			<div class="editor-container">
				<textarea name="content" id="content" style="width:100%;" class="theEditor" rows="15"><?php
				
					echo $url . "<br>"; 
										
				?></textarea>
			</div>
		</div>
	</div>
</div>
</form>
</body>
</html>
