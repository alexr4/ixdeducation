<?php get_header(); ?>

		<div id="container">
			<div id="content" role="main">

<?php if ( have_posts() ) the_post(); ?>

				<h1 class="page-title author"><?php printf( __( 'Articles écrits par : %s', 'imbalance2' ), "<span class='vcard'>" . get_the_author() . "</span>" ); ?></h1>

<div class="content-author-left">
	<?php if ( get_the_author_meta( 'description' ) ) : // If a user has filled out their description, show a bio on their entries  ?>
    	<div id="author-avatar">
            <?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'imbalance2_author_bio_avatar_size', 160 ) ); ?>
        </div><!-- #author-avatar -->
        <div id="entry-author-info">
            <div id="author-description">
                <h2><?php printf( esc_attr__( 'À propos de %s', 'imbalance2' ), get_the_author() ); ?></h2>
                <?php the_author_meta( 'description' ); ?>
            </div><!-- #author-description --> 
         </div><!-- #entry-author-info -->
    <?php endif; ?>
</div>

<?php
	rewind_posts();
	get_template_part( 'loop', 'author' );
?>
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_footer(); ?>
