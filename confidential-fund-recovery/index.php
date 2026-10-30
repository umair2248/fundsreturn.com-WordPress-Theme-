<?php get_header(); ?>
<main id="main-content" class="container content-section" tabindex="-1"><h1><?php if ( is_home() && get_option( 'page_for_posts' ) ) { echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) ); } else { esc_html_e( 'Insights', 'confidential-fund-recovery' ); } ?></h1><?php get_template_part( 'template-parts/post-list' ); ?></main>
<?php get_footer(); ?>
