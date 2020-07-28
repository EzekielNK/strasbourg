<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Sydney
 */
?>
			</div>
		</div>
	</div><!-- #content -->

	<?php do_action('sydney_before_footer'); ?>

	<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
		<?php get_sidebar('footer'); ?>
	<?php endif; ?>

    <a class="go-top"><i class="fa fa-angle-up"></i></a>
		
	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="site-info container">
			<a href="<?php echo esc_url( __( 'https://www.virtualdev.fr/', 'sydney' ) ); ?>"><?php printf( __( 'Copyright © 2019 %s', 'VirtualDev' ), 'VirtualDev' ); ?></a>
			<span class="sep"> | </span>
			<?php printf( __( 'Theme: %2$s. %1$s.', 'sydney' ), 'Design par VirtualDev', '<https://www.virtualdev.fr/" rel="designer">Sydney</a>' ); ?>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->

	<?php do_action('sydney_after_footer'); ?>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
