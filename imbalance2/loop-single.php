<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
<!-- NAVIGATION -->
<div id="nav-above" class="navigation">
  <div id="nav-articles"><span id="nav-info">Article :</span><br />
    <div class="nav-previous">
      <?php if (get_previous_post(false) != null): ?>
      <?php previous_post_link( '%link', '« Précédent' ); ?>
      <?php else: ?>
      « Précédent
      <?php endif ?>
    </div>
    <span> / </span>
    <div class="nav-next">
      <?php if (get_next_post(false) != null): ?>
      <?php next_post_link( '%link', 'Suivant »' ); ?>
      <?php else: ?>
      Suivant »
      <?php endif ?>
    </div>
  </div>
  <div id="social">
    <div id="postDate"><?php imbalance2_posted_on() ?></div><br />
	<div id="share">
        <?php echo do_shortcode( '[ess_post]' ); ?>
    </div>
  </div>
</div><!-- #nav-above -->
				
<!-- POST -->
<div class="content-article">
	  <div class="content-post">
        <div id="author"><?php imbalance2_posted_by() ?></div>
        <h1><?php the_title(); ?></h1>
        <div id="tag"><?php imbalance2_posted_in(); ?><span class="main_separator"> — </span><?php imbalance2_tags() ?></div>
        <div id="postThumbnail"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('post-thumb', array('alt' => '', 'title' => '')) ?></a></div>
        <div id="postContent">
            <table id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <tr>
                        <td class="entry-content-right">
                            <?php the_content(); ?>
                            <!--<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'imbalance2' ), 'after' => '</div>' ) ); ?>-->
  
      
                        </td>
                    </tr>
                </table><!-- #post-## -->
        </div>
      </div>
      <div class="content-author">
        <?php if ( get_the_author_meta( 'description' ) ) : // If a user has filled out their description, show a bio on their entries  ?>
        <div id="entry-author-info">
        	<div id="author-description">
            	<h2><?php printf( esc_attr__( 'À propos de %s', 'imbalance2' ), get_the_author() ); ?></h2>
                <?php the_author_meta( 'description' ); ?>
                <div id="author-link">
                	<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>">
                    <?php printf( __( 'Voir tous les articles écrits par %s <span class="meta-nav">&rarr;</span>', 'imbalance2' ), get_the_author() ); ?>
                    </a>
                </div><!-- #author-link	-->
             </div><!-- #author-description --> 
        </div><!-- #entry-author-info -->
        <div id="author-avatar">
        	<?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'imbalance2_author_bio_avatar_size', 160 ) ); ?>
        </div><!-- #author-avatar -->
        <?php endif; ?>
      </div>
</div>			
<?php endwhile; ?>

<script>
$(function() {

    var $sidebar   = $("#nav-above"), 
        $window    = $(window),
        offset     = $sidebar.offset(),
        topPadding = 0;

    $window.scroll(function() {
        if ($window.scrollTop() > offset.top) {
            $sidebar.stop().animate({
                marginTop: $window.scrollTop() - offset.top + topPadding
            });
        } else {
            $sidebar.stop().animate({
                marginTop: 0
            });
        }
    });
    
});
</script>
