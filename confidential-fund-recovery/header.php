<?php defined( 'ABSPATH' ) || exit; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class( 'cfr-language-' . cfr_current_language() ); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main-content"><?php echo esc_html( cfr_t( 'skip' ) ); ?></a>
<div class="site-header-stack">
<div class="utility-bar"><div class="container"><span><?php echo esc_html( cfr_t( 'brand_line' ) ); ?></span><?php cfr_language_switcher( 'header' ); ?></div></div>
<header class="site-header"><div class="container header-inner">
<div class="site-branding"><?php if ( has_custom_logo() ) : ?><a class="custom-logo-link" href="<?php echo esc_url( cfr_page_url( 'home' ) ); ?>"><?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'custom-logo', 'alt' => cfr_firm_name() ) ); ?></a><?php else : ?><a class="brand" href="<?php echo esc_url( cfr_page_url( 'home' ) ); ?>" rel="home"><svg class="brand-symbol" viewBox="0 0 48 48" width="46" height="46" fill="none" aria-hidden="true"><path d="M8 35V13h14M19 35V23h14V13h7v22H19Z" stroke="currentColor" stroke-width="2"/><path d="M8 40h32" stroke="var(--orange)" stroke-width="2"/></svg><span><span class="brand-name"><?php echo esc_html( cfr_firm_name() ); ?></span><span class="brand-subtitle"><?php echo esc_html( cfr_t( 'description' ) ); ?></span></span></a><?php endif; ?></div>
<button class="menu-toggle" type="button" aria-controls="site-navigation" aria-expanded="false"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg><?php echo esc_html( cfr_t( 'menu' ) ); ?></button>
<nav id="site-navigation" class="site-navigation" aria-label="<?php echo esc_attr( cfr_t( 'nav' ) ); ?>"><?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_id' => 'primary-menu', 'menu_class' => 'primary-menu', 'depth' => 1, 'fallback_cb' => 'cfr_fallback_menu' ) ); ?></nav>
<a class="button header-cta" href="<?php echo esc_url( cfr_assessment_url() ); ?>"><?php echo esc_html( cfr_t( 'assess' ) ); ?><?php echo cfr_arrow(); ?></a>
</div></header>
</div>
