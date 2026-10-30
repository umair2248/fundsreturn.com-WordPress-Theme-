<?php
defined( 'ABSPATH' ) || exit;

function cfr_arrow() {
    return '<svg class="arrow-icon" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6 18 18 6M6 6h12v12"/></svg>';
}
add_shortcode( 'cfr_arrow', 'cfr_arrow' );

function cfr_page_definition( $slug, $language = null ) {
    foreach ( cfr_translation_data( $language )['pages'] as $page ) { if ( $page['slug'] === $slug ) { return $page; } }
    return null;
}

function cfr_effective_page_content( $slug, $language, $stored ) {
    $page = cfr_page_definition( $slug, $language );
    if ( ! is_string( $stored ) ) { return $page ? $page['content'] : ''; }
    if ( in_array( $slug, array( 'privacy-policy', 'terms-of-use' ), true ) ) {
        foreach ( array( '[LEGAL CONTENT PLACEHOLDER]', '[ESPACE RÉSERVÉ AU CONTENU JURIDIQUE]', '[PLAATSHOUDER JURIDISCHE INHOUD]', '[PLATZHALTER FÜR RECHTSTEXTE]' ) as $marker ) {
            if ( false !== strpos( $stored, $marker ) ) { return $page['content']; }
        }
    }
    return $stored;
}

function cfr_render_page_content( $slug ) {
    static $rendering = array();
    $render_key = cfr_current_language() . ':' . $slug;
    if ( isset( $rendering[ $render_key ] ) ) { return ''; }
    $page = cfr_page_definition( $slug );
    if ( ! $page ) { return ''; }
    $content = get_option( 'cfr_content_' . cfr_current_language() . '_' . $slug, null );
    $content = cfr_effective_page_content( $slug, cfr_current_language(), $content );
    $content = cfr_add_editorial_images( $content, $slug );
    $content = cfr_section_design( $content, $slug );
    $content = cfr_clean_public_copy( $content );
    $content = preg_replace_callback( '/\{\{([a-z0-9-]+)\}\}/', function( $matches ) { return esc_url( cfr_page_url( $matches[1] ) ); }, $content );
    $rendering[ $render_key ] = true;
    try { return do_shortcode( $content ); }
    finally { unset( $rendering[ $render_key ] ); }
}
add_shortcode( 'cfr_page_content', function( $attributes ) {
    $attributes = shortcode_atts( array( 'page' => 'home' ), $attributes );
    return cfr_render_page_content( sanitize_key( $attributes['page'] ) );
} );

function cfr_content_admin_menu() {
    add_theme_page( 'Theme Content', 'Theme Content', 'edit_theme_options', 'cfr-content', 'cfr_content_admin_screen' );
}
add_action( 'admin_menu', 'cfr_content_admin_menu' );

function cfr_content_admin_screen() {
    if ( ! current_user_can( 'edit_theme_options' ) ) { return; }
    $language = isset( $_GET['content_language'] ) && is_string( $_GET['content_language'] ) && isset( cfr_languages()[ $_GET['content_language'] ] ) ? $_GET['content_language'] : 'fr';
    $slug = isset( $_GET['content_page'] ) && is_string( $_GET['content_page'] ) ? sanitize_key( $_GET['content_page'] ) : 'home';
    if ( in_array( $slug, array( 'header', 'footer' ), true ) ) { cfr_branding_admin_screen( $slug, $language ); return; }
    $page = cfr_page_definition( $slug, $language );
    if ( ! $page ) { $slug = 'home'; $page = cfr_page_definition( $slug, $language ); }
    $key = 'cfr_content_' . $language . '_' . $slug;
    if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && isset( $_POST['save_images'] ) ) { cfr_save_images(); }
    elseif ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
        check_admin_referer( 'cfr_edit_content_' . $key );
        if ( isset( $_POST['reset_content'] ) ) { delete_option( $key ); }
        elseif ( isset( $_POST['cfr_content'] ) && is_string( $_POST['cfr_content'] ) ) { update_option( $key, wp_kses_post( wp_unslash( $_POST['cfr_content'] ) ), false ); }
        echo '<div class="notice notice-success"><p>Theme content saved. Existing WordPress pages were not changed.</p></div>';
    }
    $content = get_option( $key, $page['content'] );
    $content = cfr_effective_page_content( $slug, $language, $content );
    $content = cfr_clean_public_copy( $content, $language );
    echo '<div class="wrap"><h1>Theme Content</h1><p>Edit the supplied layouts in each language. Changes are stored in theme content options and never overwrite existing page records. Keep section IDs, classes, link tokens such as {{contact}}, and shortcodes. Use the Text tab for structural HTML edits.</p><form method="get"><input type="hidden" name="page" value="cfr-content"><select name="content_language" aria-label="Content language">';
    foreach ( cfr_languages() as $code => $name ) { printf( '<option value="%s" %s>%s</option>', esc_attr( $code ), selected( $language, $code, false ), esc_html( $name ) ); }
    echo '</select> <select name="content_page" aria-label="Content page">';
    foreach ( cfr_translation_data( 'en' )['pages'] as $entry ) { printf( '<option value="%s" %s>%s</option>', esc_attr( $entry['slug'] ), selected( $slug, $entry['slug'], false ), esc_html( $entry['title'] ) ); }
    foreach ( array( 'header' => 'Header', 'footer' => 'Footer' ) as $area => $label ) { printf( '<option value="%s">%s</option>', esc_attr( $area ), esc_html( $label ) ); }
    echo '</select> <button class="button">Open editor</button></form><form method="post">';
    wp_nonce_field( 'cfr_edit_content_' . $key );
    if ( current_user_can( 'upload_files' ) ) { wp_enqueue_media(); }
    wp_editor( $content, 'cfr_content', array( 'textarea_name' => 'cfr_content', 'textarea_rows' => 28, 'media_buttons' => current_user_can( 'upload_files' ), 'tinymce' => array( 'content_css' => get_template_directory_uri() . '/assets/css/main.css?ver=' . CFR_VERSION ) ) );
    submit_button( 'Save translated content' );
    echo '<button class="button" type="submit" name="reset_content" value="1">Restore supplied content for this page and language</button></form>';
    if ( 'home' === $slug ) { cfr_images_admin_controls(); }
    echo '</div>';
}

add_filter( 'pre_get_document_title', function( $title ) {
    if ( is_front_page() ) { return cfr_t( 'site_title' ); }
    if ( ! is_singular( 'page' ) ) { return $title; }
    $page = cfr_page_definition( get_post_field( 'post_name', get_queried_object_id() ) );
    return $page ? $page['title'] . ' | ' . cfr_firm_name() : $title;
} );
