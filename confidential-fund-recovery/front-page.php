<?php get_header(); ?>
<main id="main-content" class="front-content" tabindex="-1">
<?php if ( is_home() ) : ?>
<div class="container content-section"><h1><?php esc_html_e( 'Latest insights', 'confidential-fund-recovery' ); ?></h1><?php get_template_part( 'template-parts/post-list' ); ?></div>
<?php else : while ( have_posts() ) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-content' ); ?>><?php if ( get_theme_mod( 'cfr_use_wordpress_content', false ) && 'en' === cfr_current_language() ) { the_content(); } else { echo cfr_render_page_content( 'home' ); } ?></article>
<?php endwhile; endif; ?>
</main>
<?php get_footer(); ?>
