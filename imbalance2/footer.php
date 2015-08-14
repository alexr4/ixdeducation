	</div><!-- #main -->

	<div id="footer">
		<div id="site-info">
			© 2015 <a href="/"><?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?></a><br />
			Design original : <a href="http://wpshower.com/themes/imbalance-2/" target="_blank">Imbalance2</a> par <a href="http://wpshower.com" target="_blank">Wpshower</a><br />
			Redesign <a href="http://arivaux.com" target="_blank">arivaux.com</a>
			<span> — </span>
			fièrement propulsé par <a href="http://www.wordpress.org" target="_blank">WordPress</a>
		</div><!-- #site-info -->
		<div id="footer-right"><?php wp_nav_menu( array( 'container_class' => 'menu', 'theme_location' => 'footer-right', 'walker' => new Imbalance2_Walker_Nav_Menu(), 'depth' => 1 ) ); ?></div>
		<div id="footer-left"><?php wp_nav_menu( array( 'container_class' => 'menu', 'theme_location' => 'footer-left', 'walker' => new Imbalance2_Walker_Nav_Menu(), 'depth' => 1 ) ); ?></div>
		<div class="clear"></div>
	</div><!-- #footer -->

</div><!-- .wrapper -->

<?php wp_footer(); ?>

<?php echo imbalance2google() ?>

</body>
</html>
