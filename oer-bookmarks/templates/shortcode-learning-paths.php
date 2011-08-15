<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<div class="oerb-shortcode-learning-paths">

<?php if ( $paths ) : ?>

	<ul class="oerb-paths">

	<?php foreach ( $paths as $path ) : ?>
	
		<li><a href="<?php echo esc_attr( get_term_link( $path, 'learning-path' ) ); ?>"><?php echo esc_html( $path->name ); ?></a></li>
	
	<?php endforeach; ?>

	</ul>

<?php else : ?>
	
	<p class="oerb-no-paths"><?php _e( 'No learning paths found.', 'oerb' ); ?></p>
	
<?php endif; ?>

</div>
