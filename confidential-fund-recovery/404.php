<?php get_header(); ?>
<main id="main-content" class="container page-body" tabindex="-1"><p class="eyebrow">404</p><h1><?php echo esc_html( cfr_t( 'not_found' ) ); ?></h1><a class="button" href="<?php echo esc_url( cfr_page_url( 'home' ) ); ?>"><?php echo esc_html( cfr_t( 'back_home' ) ); ?><?php echo cfr_arrow(); ?></a></main>
<?php get_footer(); ?>
