<?php get_header(); ?>
<main id="main-content" class="container content-section" tabindex="-1"><header><?php the_archive_title( '<h1>', '</h1>' ); the_archive_description( '<div class="archive-description">', '</div>' ); ?></header><?php get_template_part( 'template-parts/post-list' ); ?></main>
<?php get_footer(); ?>
