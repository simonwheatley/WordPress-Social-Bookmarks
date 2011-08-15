<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- Ticket #11289, IE bug fix: always pad the error page with enough characters such that it is greater than 512 bytes, even after gzip compression abcdefghijklmnopqrstuvwxyz1234567890aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzz11223344556677889900abacbcbdcdcededfefegfgfhghgihihjijikjkjlklkmlmlnmnmononpopoqpqprqrqsrsrtstsubcbcdcdedefefgfabcadefbghicjkldmnoepqrfstugvwxhyz1i234j567k890laabmbccnddeoeffpgghqhiirjjksklltmmnunoovppqwqrrxsstytuuzvvw0wxx1yyz2z113223434455666777889890091abc2def3ghi4jkl5mno6pqr7stu8vwx9yz11aab2bcc3dd4ee5ff6gg7hh8ii9j0jk1kl2lmm3nnoo4p5pq6qrr7ss8tt9uuvv0wwx1x2yyzz13aba4cbcb5dcdc6dedfef8egf9gfh0ghg1ihi2hji3jik4jkj5lkl6kml7mln8mnm9ono -->
<html xmlns="http://www.w3.org/1999/xhtml" <?php if ( function_exists( 'language_attributes' ) && function_exists( 'is_rtl' ) ) language_attributes(); else echo "dir='$text_direction'"; ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $title; ?></title>
	<link rel="stylesheet" href="<?php echo $admin_dir; ?>css/install.css" type="text/css" />

<?php if ( 'rtl' == $text_direction ) : ?>
	<link rel="stylesheet" href="<?php echo $admin_dir; ?>css/install-rtl.css" type="text/css" />
<?php endif; ?>

	<style type="text/css" media="screen">
		body { width: 554px; }
	</style>

</head>
<body>

	<h1><?php echo $header; ?></h1>
	
	<p>
		<?php echo $message; ?>
		<a onclick="window.opener.location.replace(this.href); window.close();" href="<?php echo get_permalink( $post_id); ?>"><?php printf( __('View %s', 'oerb'), $post_type_name ); ?></a> | 
		<a href="<?php echo get_edit_post_link( $post_id ); ?>" onclick="window.opener.location.replace(this.href); window.close();"><?php printf( __('Edit %s', 'oerb'), $post_type_name ); ?></a> | 
		<a href="#" onclick="window.close();"><?php _e('Close Window'); ?></a>
	</p>
	
</body>
</html>