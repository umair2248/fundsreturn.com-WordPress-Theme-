<?php
/** Creates missing pages and menu items only. Existing post content is never written. */
defined( 'ABSPATH' ) || exit;

function cfr_create_starter_content() {
    if ( get_option( 'cfr_setup_v2' ) ) { return; }
    $pages = json_decode( file_get_contents( get_template_directory() . '/inc/starter-content.json' ), true );
    if ( ! is_array( $pages ) ) { return; }
    $ids = array();
    $complete = true;
    foreach ( $pages as $page ) {
        $existing = get_page_by_path( $page['slug'] );
        if ( $existing ) { $ids[ $page['slug'] ] = $existing->ID; continue; }
        $id = wp_insert_post( wp_slash( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $page['title'], 'post_name' => $page['slug'], 'post_content' => $page['content'] ) ), true );
        if ( is_wp_error( $id ) || ! $id ) { $complete = false; continue; }
        $ids[ $page['slug'] ] = $id;
        update_post_meta( $id, '_cfr_starter_page', 1 );
    }
    if ( ! get_option( 'page_on_front' ) && isset( $ids['home'] ) ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $ids['home'] );
    }
    $locations = get_theme_mod( 'nav_menu_locations', array() );
    $definitions = array( 'primary' => array( 'about' => 'About', 'faq' => 'FAQ', 'contact' => 'Contact' ), 'footer' => array( 'privacy-policy' => 'Privacy Policy', 'terms-of-use' => 'Terms of Use' ) );
    foreach ( $definitions as $location => $links ) {
        $menu_id = $locations[ $location ] ?? 0;
        if ( ! $menu_id ) {
            $menu = wp_get_nav_menu_object( 'FUNDS RETURN ' . $location );
            $menu_id = $menu ? $menu->term_id : wp_create_nav_menu( 'FUNDS RETURN ' . $location );
        }
        if ( is_wp_error( $menu_id ) ) { $complete = false; continue; }
        $existing = wp_get_nav_menu_items( $menu_id ) ?: array();
        $present = array_map( function( $item ) { return (int) $item->object_id; }, $existing );
        foreach ( $links as $slug => $label ) {
            if ( empty( $ids[ $slug ] ) || in_array( (int) $ids[ $slug ], $present, true ) ) { continue; }
            $result = wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $label, 'menu-item-object' => 'page', 'menu-item-object-id' => $ids[ $slug ], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
            if ( is_wp_error( $result ) ) { $complete = false; }
        }
        $locations[ $location ] = $menu_id;
    }
    set_theme_mod( 'nav_menu_locations', $locations );
    if ( $complete ) { update_option( 'cfr_setup_v2', CFR_VERSION ); }
}
add_action( 'after_switch_theme', 'cfr_create_starter_content' );
add_action( 'admin_init', function() { if ( current_user_can( 'edit_theme_options' ) ) { cfr_create_starter_content(); } } );

add_action( 'admin_notices', function() {
    if ( ! current_user_can( 'edit_theme_options' ) ) { return; }
    echo '<div class="notice notice-info"><p>' . esc_html__( 'FUNDS RETURN: edit all six pages in four languages under Appearance / Theme Content. Existing WordPress page records are preserved. Set footer details in Customize / Firm details, replace legal placeholders and test email delivery before launch.', 'confidential-fund-recovery' ) . '</p></div>';
} );
