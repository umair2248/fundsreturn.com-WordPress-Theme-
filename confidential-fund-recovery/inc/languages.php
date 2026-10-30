<?php
/** Built-in, server-rendered translations: no external translation service. */
defined( 'ABSPATH' ) || exit;

function cfr_languages() { return array( 'en' => 'English', 'fr' => 'Français', 'nl' => 'Nederlands', 'de' => 'Deutsch' ); }

function cfr_current_language() {
    $value = $_GET['lang'] ?? null;
    if ( is_string( $value ) && isset( cfr_languages()[ $value ] ) ) { return $value; }
    return 'fr';
}

function cfr_translation_data( $language = null ) {
    static $cache = array();
    $language = $language ?: cfr_current_language();
    if ( ! isset( cfr_languages()[ $language ] ) ) { $language = 'fr'; }
    if ( ! isset( $cache[ $language ] ) ) {
        $cache[ $language ] = json_decode( file_get_contents( get_template_directory() . '/inc/translations/' . $language . '.json' ), true );
    }
    return $cache[ $language ];
}

function cfr_t( $key ) {
    $data = cfr_translation_data();
    $overrides = get_option( 'cfr_ui_' . cfr_current_language(), array() );
    if ( is_array( $overrides ) && isset( $overrides[ $key ] ) && is_string( $overrides[ $key ] ) && '' !== $overrides[ $key ] ) { return cfr_clean_public_copy( $overrides[ $key ] ); }
    return cfr_clean_public_copy( $data['ui'][ $key ] ?? $key );
}

function cfr_persist_language() {
    if ( is_admin() || ! isset( $_GET['lang'] ) || ! is_string( $_GET['lang'] ) || ! isset( cfr_languages()[ $_GET['lang'] ] ) || headers_sent() ) { return; }
    $path = wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ?: '/';
    setcookie( 'cfr_language', $_GET['lang'], array( 'expires' => time() + 30 * DAY_IN_SECONDS, 'path' => $path, 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax' ) );
}
add_action( 'init', 'cfr_persist_language', 1 );

function cfr_language_url( $language ) {
    if ( ! isset( cfr_languages()[ $language ] ) ) { $language = 'fr'; }
    $url = is_singular() ? get_permalink( get_queried_object_id() ) : home_url( '/' );
    return add_query_arg( 'lang', $language, $url );
}

function cfr_language_switcher( $location = 'footer' ) {
    echo '<nav class="language-switcher" aria-label="' . esc_attr( cfr_t( 'languages' ) ) . '"><svg class="language-globe" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="4" ry="9"/><path d="M3 12h18"/></svg><ul>';
    foreach ( cfr_languages() as $code => $label ) {
        echo '<li><a href="' . esc_url( cfr_language_url( $code ) ) . '" hreflang="' . esc_attr( $code ) . '" lang="' . esc_attr( $code ) . '" aria-label="' . esc_attr( $label ) . '"' . ( cfr_current_language() === $code ? ' aria-current="true"' : '' ) . '>' . esc_html( strtoupper( $code ) ) . '</a></li>';
    }
    echo '</ul></nav>';
}

function cfr_html_language( $attributes ) {
    $locales = array( 'en' => 'en-GB', 'fr' => 'fr-FR', 'nl' => 'nl-NL', 'de' => 'de-DE' );
    return 'lang="' . esc_attr( $locales[ cfr_current_language() ] ) . '"';
}
add_filter( 'language_attributes', 'cfr_html_language' );

function cfr_language_head() {
    if ( ! is_singular() && ! is_front_page() ) { return; }
    foreach ( cfr_languages() as $code => $name ) {
        printf( '<link rel="alternate" hreflang="%s" href="%s">' . "\n", esc_attr( $code ), esc_url( cfr_language_url( $code ) ) );
    }
    printf( '<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url( cfr_language_url( 'fr' ) ) );
}
add_action( 'wp_head', 'cfr_language_head' );
add_filter( 'get_canonical_url', function( $url ) { return add_query_arg( 'lang', cfr_current_language(), $url ); } );

function cfr_language_menu_items( $items, $args ) {
    if ( ! in_array( $args->theme_location ?? '', array( 'primary', 'footer' ), true ) ) { return $items; }
    $allowed = 'primary' === $args->theme_location ? array( 'about' => 'about', 'faq' => 'faq', 'contact' => 'contact' ) : array( 'privacy-policy' => 'privacy', 'terms-of-use' => 'terms' );
    $result = array();
    foreach ( $items as $item ) {
        $slug = get_post_field( 'post_name', $item->object_id );
        if ( ! isset( $allowed[ $slug ] ) ) { continue; }
        $copy = clone $item;
        $copy->title = cfr_t( $allowed[ $slug ] );
        $copy->url = add_query_arg( 'lang', cfr_current_language(), $copy->url );
        $copy->menu_item_parent = 0;
        $result[] = $copy;
    }
    return $result;
}
add_filter( 'wp_nav_menu_objects', 'cfr_language_menu_items', 10, 2 );

// Bare URLs always start in French; navigation preserves explicit language URLs.
