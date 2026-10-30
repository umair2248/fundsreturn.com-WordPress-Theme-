<?php get_header(); ?>
<main id="main-content" class="container content-section narrow-content" tabindex="-1"><?php while ( have_posts() ) : the_post(); ?><article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-content' ); ?>><p class="eyebrow"><?php echo esc_html( get_the_date() ); ?></p><h1><?php the_title(); ?></h1><?php the_post_thumbnail( 'large' ); the_content(); wp_link_pages(); ?></article><?php the_post_navigation(); if ( comments_open() || get_comments_number() ) { comments_template(); } endwhile; ?></main>
<?php get_footer(); ?>
