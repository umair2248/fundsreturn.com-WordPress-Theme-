<?php
/** Header and footer controls in the same Theme Content screen as page copy. */
defined( 'ABSPATH' ) || exit;

function cfr_disclaimer_default( $language = 'fr' ) {
    $copy = json_decode( file_get_contents( get_template_directory() . '/inc/disclaimers.json' ), true );
    return $copy[ $language ] ?? $copy['fr'];
}

function cfr_editor_selector( $selected_page, $language ) {
    echo '<form method="get"><input type="hidden" name="page" value="cfr-content"><select name="content_language" aria-label="Content language">';
    foreach ( cfr_languages() as $code => $label ) { printf( '<option value="%s" %s>%s</option>', esc_attr( $code ), selected( $language, $code, false ), esc_html( $label ) ); }
    echo '</select> <select name="content_page" aria-label="Content area">';
    foreach ( cfr_translation_data( 'en' )['pages'] as $page ) { printf( '<option value="%s" %s>%s</option>', esc_attr( $page['slug'] ), selected( $selected_page, $page['slug'], false ), esc_html( $page['title'] ) ); }
    foreach ( array( 'header' => 'Header', 'footer' => 'Footer' ) as $key => $label ) { printf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $selected_page, $key, false ), esc_html( $label ) ); }
    echo '</select> <button class="button">Open editor</button></form>';
}

function cfr_branding_fields( $area ) {
    return 'header' === $area
        ? array( 'site_title' => 'Home browser / website title', 'brand_line' => 'Top header line', 'description' => 'Brand subtitle', 'assess' => 'Assessment button label', 'about' => 'About navigation label', 'faq' => 'FAQ navigation label', 'contact' => 'Contact navigation label', 'menu' => 'Mobile menu label' )
        : array( 'investigation' => 'Footer eyebrow', 'footer_nav' => 'Navigation heading', 'about' => 'About link label (shared with header)', 'faq' => 'FAQ link label (shared with header)', 'contact' => 'Contact link label (shared with header)', 'assess' => 'Assessment button label (shared with header)', 'services' => 'Service links heading', 'details' => 'Contact details heading', 'hours' => 'Business hours heading', 'social' => 'Social links heading', 'notice' => 'Disclaimer heading', 'rights' => 'Copyright text', 'privacy' => 'Privacy Policy link label', 'terms' => 'Terms of Use link label' );
}

function cfr_branding_shared_fields( $area ) {
    return 'header' === $area
        ? array( 'firm_name' => array( 'Firm / site name', 'text' ), 'assessment_url' => array( 'Assessment button destination (blank uses Contact form)', 'url' ) )
        : array( 'firm_name' => array( 'Firm / site name', 'text' ), 'email' => array( 'Official email', 'email' ), 'phone' => array( 'Phone', 'text' ), 'address' => array( 'Office address', 'textarea' ), 'hours' => array( 'Business hours', 'textarea' ), 'social_linkedin' => array( 'LinkedIn URL', 'url' ), 'social_facebook' => array( 'Facebook URL', 'url' ), 'social_instagram' => array( 'Instagram URL', 'url' ), 'footer_text' => array( 'Additional footer text', 'textarea' ), 'recipient' => array( 'Assessment email recipient (blank uses WordPress administration email)', 'email' ) );
}

function cfr_save_branding( $area, $language, $input ) {
    $ui = get_option( 'cfr_ui_' . $language, array() );
    if ( ! is_array( $ui ) ) { $ui = array(); }
    foreach ( cfr_branding_fields( $area ) as $key => $label ) {
        if ( isset( $input['ui_' . $key] ) && is_string( $input['ui_' . $key] ) ) { $ui[ $key ] = sanitize_text_field( wp_unslash( $input['ui_' . $key] ) ); }
    }
    update_option( 'cfr_ui_' . $language, $ui, false );
    foreach ( cfr_branding_shared_fields( $area ) as $key => $field ) {
        if ( ! isset( $input['shared_' . $key] ) || ! is_string( $input['shared_' . $key] ) ) { continue; }
        $raw = wp_unslash( $input['shared_' . $key] );
        $value = 'url' === $field[1] ? esc_url_raw( $raw ) : ( 'email' === $field[1] ? sanitize_email( $raw ) : sanitize_textarea_field( $raw ) );
        set_theme_mod( 'cfr_' . $key, $value );
    }
    if ( 'footer' === $area ) {
        foreach ( array( 'description', 'disclaimer' ) as $key ) {
            if ( isset( $input[ $key ] ) && is_string( $input[ $key ] ) ) { set_theme_mod( 'cfr_' . ( 'disclaimer' === $key ? 'disclaimer_v23' : $key ) . '_' . $language, sanitize_textarea_field( wp_unslash( $input[ $key ] ) ) ); }
        }
        $service_labels = array();
        foreach ( array( 4, 5, 6, 7, 8, 9, 11 ) as $index ) {
            if ( isset( $input[ 'service_' . $index ] ) && is_string( $input[ 'service_' . $index ] ) ) { $service_labels[ $index ] = sanitize_text_field( wp_unslash( $input[ 'service_' . $index ] ) ); }
        }
        update_option( 'cfr_footer_service_labels_' . $language, $service_labels, false );
    }
}

function cfr_branding_input( $name, $label, $value, $type = 'text' ) {
    $value = 'disclaimer' === $name ? $value : cfr_clean_public_copy( $value, isset( $_GET['content_language'] ) && is_string( $_GET['content_language'] ) ? $_GET['content_language'] : 'fr' );
    echo '<tr><th scope="row"><label for="' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label></th><td>';
    if ( 'textarea' === $type ) { echo '<textarea class="large-text" rows="4" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '">' . esc_html( $value ) . '</textarea>'; }
    else { echo '<input class="regular-text" type="' . esc_attr( $type ) . '" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">'; }
    echo '</td></tr>';
}

function cfr_branding_admin_screen( $area, $language ) {
    if ( ! current_user_can( 'edit_theme_options' ) ) { return; }
    if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
        check_admin_referer( 'cfr_branding_' . $area . '_' . $language );
        cfr_save_branding( $area, $language, $_POST );
        echo '<div class="notice notice-success"><p>' . esc_html( ucfirst( $area ) ) . ' settings saved.</p></div>';
    }
    echo '<div class="wrap"><h1>Theme Content — ' . esc_html( ucfirst( $area ) ) . '</h1><p>Choose Header or Footer here to edit site-wide content. Text labels apply to the selected language; firm and contact details are shared across languages. Navigation links, language switching and theme structure are kept intact.</p>';
    cfr_editor_selector( $area, $language );
    $data = cfr_translation_data( $language );
    $overrides = get_option( 'cfr_ui_' . $language, array() );
    echo '<form method="post">'; wp_nonce_field( 'cfr_branding_' . $area . '_' . $language );
    echo '<h2>Translated labels</h2><table class="form-table">';
    foreach ( cfr_branding_fields( $area ) as $key => $label ) { cfr_branding_input( 'ui_' . $key, $label, $overrides[ $key ] ?? $data['ui'][ $key ], 'text' ); }
    if ( 'footer' === $area ) {
        cfr_branding_input( 'description', 'Footer description', get_theme_mod( 'cfr_description_' . $language, '' ) ?: $data['ui']['description'], 'textarea' );
        cfr_branding_input( 'disclaimer', 'Footer disclaimer — [FIRM NAME] is replaced automatically', get_theme_mod( 'cfr_disclaimer_v23_' . $language, '' ) ?: cfr_disclaimer_default( $language ), 'textarea' );
        $service_labels = get_option( 'cfr_footer_service_labels_' . $language, array() );
        foreach ( array( 4, 5, 6, 7, 8, 9, 11 ) as $index ) { cfr_branding_input( 'service_' . $index, 'Home section link: ' . cfr_translation_data( 'en' )['headings'][ $index ], $service_labels[ $index ] ?? $data['headings'][ $index ] ); }
    }
    echo '</table><h2>Shared firm details</h2><table class="form-table">';
    foreach ( cfr_branding_shared_fields( $area ) as $key => $field ) { cfr_branding_input( 'shared_' . $key, $field[0], get_theme_mod( 'cfr_' . $key, 'firm_name' === $key ? 'FUNDS RETURN' : ( 'address' === $key ? "170 Bishopsgate\nLondon EC2M 4LX, UK" : ( 'email' === $key ? 'support@fundsreturn.com' : '' ) ) ), $field[1] ); }
    echo '</table><p>For the site logo use Appearance → Customize → Site Identity.</p>'; submit_button( 'Save ' . ucfirst( $area ) ); echo '</form></div>';
}

function cfr_footer_service_label( $index ) {
    $overrides = get_option( 'cfr_footer_service_labels_' . cfr_current_language(), array() );
    return cfr_clean_public_copy( isset( $overrides[ $index ] ) && is_string( $overrides[ $index ] ) && '' !== $overrides[ $index ] ? $overrides[ $index ] : cfr_translation_data()['headings'][ $index ] );
}

function cfr_apply_brand_release() {
    if ( ! current_user_can( 'edit_theme_options' ) || get_option( 'cfr_funds_return_release_230' ) ) { return; }
    set_theme_mod( 'cfr_firm_name', 'FUNDS RETURN' );
    update_option( 'blogname', 'FUNDS RETURN' );
    update_option( 'blogdescription', 'Investigation financière assistée par l’IA' );
    update_option( 'cfr_funds_return_release_230', 1 );
}
add_action( 'admin_init', 'cfr_apply_brand_release' );
