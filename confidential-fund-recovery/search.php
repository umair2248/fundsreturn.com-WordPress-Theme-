<?php get_header(); ?>
<main id="main-content" class="container content-section" tabindex="-1"><h1><?php printf( esc_html__( 'Search results for: %s', 'confidential-fund-recovery' ), esc_html( get_search_query() ) ); ?></h1><?php get_search_form(); get_template_part( 'template-parts/post-list' ); ?></main>
<?php get_footer(); ?>
