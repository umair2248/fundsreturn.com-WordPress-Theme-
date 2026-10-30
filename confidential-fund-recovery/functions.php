<?php
/** Theme bootstrap. */
defined( 'ABSPATH' ) || exit;
define( 'CFR_VERSION', '2.6.17' );
require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/forms.php';
require_once get_template_directory() . '/inc/languages.php';
require_once get_template_directory() . '/inc/public-copy.php';
require_once get_template_directory() . '/inc/content.php';
require_once get_template_directory() . '/inc/branding.php';
require_once get_template_directory() . '/inc/images.php';
require_once get_template_directory() . '/inc/section-design.php';

function cfr_theme_setup() {
    load_theme_textdomain( 'confidential-fund-recovery', get_template_directory() . '/languages' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo', array( 'height' => 64, 'width' => 240, 'flex-width' => true, 'flex-height' => true ) );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'editor-styles' );
    add_editor_style( 'assets/css/main.css' );
    register_nav_menus( array( 'primary' => __( 'Primary navigation', 'confidential-fund-recovery' ), 'footer' => __( 'Legal and compliance', 'confidential-fund-recovery' ) ) );
}
add_action( 'after_setup_theme', 'cfr_theme_setup' );

function cfr_assets() {
    wp_enqueue_style( 'cfr-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@500;600;700&display=swap', array(), null );
    wp_enqueue_style( 'cfr-style', get_stylesheet_uri(), array(), CFR_VERSION );
    wp_enqueue_style( 'cfr-main', get_template_directory_uri() . '/assets/css/main.css', array( 'cfr-style' ), CFR_VERSION );
    wp_enqueue_style( 'cfr-sections', get_template_directory_uri() . '/assets/css/sections.css', array( 'cfr-main' ), CFR_VERSION );
    wp_enqueue_style( 'cfr-responsive', get_template_directory_uri() . '/assets/css/responsive.css', array( 'cfr-sections' ), CFR_VERSION . '.' . filemtime( get_template_directory() . '/assets/css/responsive.css' ) );
    wp_enqueue_script( 'cfr-navigation', get_template_directory_uri() . '/assets/js/main.js', array(), CFR_VERSION, true );
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) { wp_enqueue_script( 'comment-reply' ); }
}
add_action( 'wp_enqueue_scripts', 'cfr_assets' );

function cfr_firm_name() {
    $name = get_theme_mod( 'cfr_firm_name', 'FUNDS RETURN' );
    return cfr_clean_public_copy( $name ? $name : ( get_bloginfo( 'name' ) && ! in_array( get_bloginfo( 'name' ), array( 'Just another WordPress site', 'WordPress', 'My Blog' ), true ) ? get_bloginfo( 'name' ) : 'FUNDS RETURN' ), 'en' );
}

function cfr_page_url( $slug ) {
    $aliases = array( 'case-assessment' => array( 'contact', 'case-form' ), 'services' => array( 'home', 'services' ), 'recovery-process' => array( 'home', 'recovery-process' ), 'case-experience' => array( 'home', 'case-experience' ), 'regulatory-information' => array( 'about', 'professional-standards' ) );
    if ( isset( $aliases[ $slug ] ) ) { return cfr_page_url( $aliases[ $slug ][0] ) . '#' . $aliases[ $slug ][1]; }
    $page = get_page_by_path( $slug );
    return add_query_arg( 'lang', cfr_current_language(), $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' ) );
}

function cfr_notice() {
    $text = get_theme_mod( 'cfr_disclaimer_v23_' . cfr_current_language(), '' ) ?: cfr_disclaimer_default( cfr_current_language() );
    return str_replace( array( '[FIRM NAME]', '[ ]', '{{firm_name}}' ), cfr_firm_name(), $text );
}

function cfr_fallback_menu() {
    echo '<ul id="primary-menu" class="primary-menu">';
    foreach ( array( 'about' => cfr_t( 'about' ), 'faq' => cfr_t( 'faq' ), 'contact' => cfr_t( 'contact' ) ) as $slug => $title ) {
        printf( '<li><a href="%s">%s</a></li>', esc_url( cfr_page_url( $slug ) ), esc_html( $title ) );
    }
    echo '</ul>';
}

function cfr_legal_fallback_menu() {
    echo '<ul class="footer-legal">';
    foreach ( array( 'privacy-policy' => cfr_t( 'privacy' ), 'terms-of-use' => cfr_t( 'terms' ) ) as $slug => $label ) {
        printf( '<li><a href="%s">%s</a></li>', esc_url( cfr_page_url( $slug ) ), esc_html( $label ) );
    }
    echo '</ul>';
}
