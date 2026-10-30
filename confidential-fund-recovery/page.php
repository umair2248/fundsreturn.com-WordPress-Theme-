<?php get_header(); ?>
<main id="main-content" tabindex="-1">
<?php while ( have_posts() ) : the_post(); ?>
<?php $cfr_slug = get_post_field( 'post_name', get_the_ID() ); $cfr_page = cfr_page_definition( $cfr_slug ); ?>
<header class="page-banner"><div class="container"><p class="eyebrow"><?php echo esc_html( cfr_firm_name() ); ?></p><h1><?php echo $cfr_page ? esc_html( $cfr_page['title'] ) : esc_html( get_the_title() ); ?></h1></div></header>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'container page-body entry-content' ); ?>><?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'large', array( 'class' => 'page-featured' ) ); endif; ?><?php if ( $cfr_page && ! ( get_theme_mod( 'cfr_use_wordpress_content', false ) && 'en' === cfr_current_language() ) ) { echo cfr_render_page_content( $cfr_slug ); } else { the_content(); wp_link_pages(); } ?></article>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
