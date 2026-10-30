<?php
defined( 'ABSPATH' ) || exit;

function cfr_customize_register( $customizer ) {
    $customizer->add_section( 'cfr_details', array( 'title' => __( 'Firm details', 'confidential-fund-recovery' ), 'priority' => 30 ) );
    $fields = array(
        'firm_name' => array( __( 'Firm name (blank uses Site Title)', 'confidential-fund-recovery' ), 'text', 'sanitize_text_field' ),
        'email' => array( __( 'Public contact email', 'confidential-fund-recovery' ), 'email', 'sanitize_email' ),
        'phone' => array( __( 'Public telephone', 'confidential-fund-recovery' ), 'text', 'sanitize_text_field' ),
        'address' => array( __( 'Public office address', 'confidential-fund-recovery' ), 'textarea', 'sanitize_textarea_field' ),
        'recipient' => array( __( 'Assessment recipient (blank uses WordPress administration email)', 'confidential-fund-recovery' ), 'email', 'sanitize_email' ),
        'footer_text' => array( __( 'Additional footer details', 'confidential-fund-recovery' ), 'textarea', 'sanitize_textarea_field' ),
        'assessment_url' => array( __( 'Assessment link override (optional)', 'confidential-fund-recovery' ), 'url', 'esc_url_raw' ),
        'hours' => array( __( 'Business hours', 'confidential-fund-recovery' ), 'textarea', 'sanitize_textarea_field' ),
        'social_linkedin' => array( 'LinkedIn URL', 'url', 'esc_url_raw' ),
        'social_facebook' => array( 'Facebook URL', 'url', 'esc_url_raw' ),
        'social_instagram' => array( 'Instagram URL', 'url', 'esc_url_raw' ),
    );
    foreach ( $fields as $key => $field ) {
        $customizer->add_setting( 'cfr_' . $key, array( 'default' => 'firm_name' === $key ? 'FUNDS RETURN' : ( 'address' === $key ? "170 Bishopsgate\nLondon EC2M 4LX, UK" : ( 'email' === $key ? 'support@fundsreturn.com' : '' ) ), 'sanitize_callback' => $field[2] ) );
        $customizer->add_control( 'cfr_' . $key, array( 'section' => 'cfr_details', 'label' => $field[0], 'type' => $field[1] ) );
    }
    $customizer->add_setting( 'cfr_use_wordpress_content', array( 'default' => false, 'sanitize_callback' => 'rest_sanitize_boolean' ) );
    $customizer->add_control( 'cfr_use_wordpress_content', array( 'section' => 'cfr_details', 'label' => 'Use existing WordPress page content for English (instead of supplied layouts)', 'type' => 'checkbox' ) );
    foreach ( cfr_languages() as $language => $name ) {
        foreach ( array( 'description' => 'Short footer description', 'disclaimer' => 'Legal notice / disclaimer (use [FIRM NAME] for the firm name)' ) as $key => $label ) {
            $setting = 'cfr_' . ( 'disclaimer' === $key ? 'disclaimer_v23' : $key ) . '_' . $language;
            $customizer->add_setting( $setting, array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field' ) );
            $customizer->add_control( $setting, array( 'section' => 'cfr_details', 'label' => $label . ' — ' . $name, 'type' => 'textarea' ) );
        }
    }
}
add_action( 'customize_register', 'cfr_customize_register' );

function cfr_assessment_url() {
    return get_theme_mod( 'cfr_assessment_url', '' ) ?: cfr_page_url( 'case-assessment' );
}

function cfr_contact_details() {
    $email = get_theme_mod( 'cfr_email', 'support@fundsreturn.com' );
    $address = get_theme_mod( 'cfr_address', "170 Bishopsgate\nLondon EC2M 4LX, UK" );
    $html = '<div class="contact-details">';
    if ( $email ) { $html .= '<p><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></p>'; }
    if ( $address ) { $html .= '<p>' . nl2br( esc_html( $address ) ) . '</p>'; }
    if ( ! $email ) { $html .= '<p class="placeholder">[ADD OFFICIAL EMAIL]</p>'; }
    if ( ! $address ) { $html .= '<p class="placeholder">[ADD OFFICE ADDRESS]</p>'; }
    return $html . '</div>';
}
add_shortcode( 'cfr_contact_details', 'cfr_contact_details' );

// Install the supplied contact address once; subsequent editor changes remain editable.
add_action( 'admin_init', function() {
    if ( ! current_user_can( 'edit_theme_options' ) || get_option( 'cfr_contact_address_20261027' ) ) { return; }
    set_theme_mod( 'cfr_address', "170 Bishopsgate\nLondon EC2M 4LX, UK" );
    update_option( 'cfr_contact_address_20261027', 1 );
} );

// Apply the confirmed public support address once, preserving future editor changes.
add_action( 'admin_init', function() {
    if ( ! current_user_can( 'edit_theme_options' ) || get_option( 'cfr_support_email_20261027' ) ) { return; }
    set_theme_mod( 'cfr_email', 'support@fundsreturn.com' );
    update_option( 'cfr_support_email_20261027', 1 );
} );

// Apply the supplied opening hours once; keep future edits available.
add_action( 'admin_init', function() {
    if ( ! current_user_can( 'edit_theme_options' ) || get_option( 'cfr_hours_20261027' ) ) { return; }
    set_theme_mod( 'cfr_hours', 'Lun-Ven : 9h00 - 18h00' );
    update_option( 'cfr_hours_20261027', 1 );
} );
