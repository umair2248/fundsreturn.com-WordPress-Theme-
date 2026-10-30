<?php
/** Bundled editorial photos, replaceable through the WordPress Media Library. */
defined( 'ABSPATH' ) || exit;

function cfr_image_definition( $name, $language = null ) {
    $language = $language ?: cfr_current_language();
    $files = array('patterns'=>'AI-Assisted Payment Pattern Investigation.webp', 'guidance'=>'Human-guided financial evidence map.webp', 'flows'=>'Tracing Blockchain Transaction Flows.webp', 'hero' => 'Forensic analyst tracing transaction connections-1.webp', 'blockchain' => 'Blockchain transaction map under magnifying glass.webp', 'platform' => 'After-Hours Brokerage Records Audit-3.webp', 'payments' => 'Tracing the payment trail-4.webp', 'evidence' => 'Boardroom ledger review-5.webp', 'review' => 'Confidential Financial Case Review-6.webp');
    $alts = array('en' => array('patterns' => 'Illustrative AI-assisted payment pattern analysis.', 'guidance' => 'Illustrative human-guided financial evidence mapping.', 'flows' => 'Illustrative tracing of blockchain transaction flows.', 'hero' => 'Illustrative forensic document analysis.', 'blockchain' => 'Illustrative transaction network mapping.', 'platform' => 'Illustrative account review workspace.', 'payments' => 'Illustrative examination of a payment trail.', 'evidence' => 'Illustrative financial document review.', 'review' => 'Illustrative confidential consultation.'), 'fr' => array('patterns' => 'Illustration d’une analyse de paiements assistée par IA.', 'guidance' => 'Illustration d’une cartographie des preuves guidée par un analyste.', 'flows' => 'Illustration du suivi des flux de transactions blockchain.', 'hero' => 'Illustration d’une analyse documentaire.', 'blockchain' => 'Illustration des réseaux de transactions.', 'platform' => 'Espace illustratif d’examen des comptes.', 'payments' => 'Illustration du parcours des paiements.', 'evidence' => 'Illustration d’un examen de documents financiers.', 'review' => 'Illustration d’une consultation confidentielle.'), 'nl' => array('patterns' => 'Illustratieve AI-ondersteunde analyse van betalingspatronen.', 'guidance' => 'Illustratieve analyse van bewijs door een onderzoeker.', 'flows' => 'Illustratief volgen van blockchaintransacties.', 'hero' => 'Illustratief documentonderzoek.', 'blockchain' => 'Illustratieve transactienetwerken.', 'platform' => 'Illustratieve werkplek voor rekeningonderzoek.', 'payments' => 'Illustratief onderzoek van een betalingsspoor.', 'evidence' => 'Illustratieve bespreking van financiële documenten.', 'review' => 'Illustratief vertrouwelijk overleg.'), 'de' => array('patterns' => 'Illustrative KI-gestützte Analyse von Zahlungsmustern.', 'guidance' => 'Illustrative Beweisanalyse unter menschlicher Anleitung.', 'flows' => 'Illustrative Nachverfolgung von Blockchain-Transaktionen.', 'hero' => 'Illustrative Dokumentenanalyse.', 'blockchain' => 'Illustrative Transaktionsnetzwerke.', 'platform' => 'Illustrativer Arbeitsplatz zur Kontenprüfung.', 'payments' => 'Illustrative Untersuchung einer Zahlungsspur.', 'evidence' => 'Illustrative Prüfung von Finanzunterlagen.', 'review' => 'Illustrative vertrauliche Beratung.'));
    if ( ! isset( $files[ $name ] ) ) { return null; }
    $sizes = array( 'blockchain'=>array(1122,1402), 'patterns'=>array(1536,1024), 'guidance'=>array(1122,1402) );
    $size = $sizes[ $name ] ?? array(1672,941);
    return array( 'url' => get_template_directory_uri() . '/assets/images/' . rawurlencode( $files[ $name ] ), 'width' => $size[0], 'height' => $size[1], 'alt' => $alts[ $language ][ $name ] ?? $alts['en'][ $name ] );
}

function cfr_image( $attributes = array() ) {
    $attributes = shortcode_atts( array( 'name' => 'hero' ), $attributes );
    $name = sanitize_key( $attributes['name'] );
    $image = cfr_image_definition( $name );
    if ( ! $image ) { return ''; }
    $id = absint( get_theme_mod( 'cfr_image_' . $name, 0 ) );
    $image_attributes = array( 'class' => 'editorial-photo', 'loading' => 'hero' === $name ? 'eager' : 'lazy', 'decoding' => 'async' );
    if ( 'hero' === $name ) { $image_attributes['fetchpriority'] = 'high'; }
    $html = '';
    if ( $id && wp_attachment_is_image( $id ) ) {
        $alt = get_post_meta( $id, '_wp_attachment_image_alt', true );
        $image_attributes['alt'] = $alt ?: $image['alt'];
        $html = wp_get_attachment_image( $id, 'large', false, $image_attributes );
    }
    if ( ! $html ) {
        $html = '<img src="' . esc_url( $image['url'] ) . '" width="' . esc_attr( $image['width'] ) . '" height="' . esc_attr( $image['height'] ) . '" alt="' . esc_attr( $image['alt'] ) . '" class="editorial-photo" loading="' . esc_attr( $image_attributes['loading'] ) . '" decoding="async"' . ( 'hero' === $name ? ' fetchpriority="high"' : '' ) . '>';
    }
    return '<figure class="site-image site-image-' . esc_attr( $name ) . '">' . $html . '</figure>';
}
add_shortcode( 'cfr_image', 'cfr_image' );

function cfr_add_editorial_images( $content, $slug ) {
    if ( 'home' === $slug ) {
        if ( false === strpos( $content, '[cfr_image name="hero"]' ) ) {
            $content = preg_replace( '~<div\b[^>]*class=["\'][^"\']*\bevidence-orbit\b[^"\']*["\'][^>]*>[\s\S]*?</div>~', '[cfr_image name="hero"]', $content, 1 );
        }
        if ( false === strpos( $content, '[cfr_image name="evidence"]' ) ) {
            $content = preg_replace( '~(<section\b[^>]*id=["\']transaction-intelligence["\'][^>]*>[\s\S]*?</h2>\s*</div>)~', '$1[cfr_image name="evidence"]', $content, 1 );
        }
    } elseif ( 'about' === $slug && false === strpos( $content, '[cfr_image name="evidence"]' ) ) { $content = '[cfr_image name="evidence"]' . $content; }
    elseif ( 'contact' === $slug && false === strpos( $content, 'contact-intro-grid' ) && false !== strpos( $content, '[cfr_case_form]' ) ) {
        $parts = explode( '[cfr_case_form]', $content, 2 );
        $content = '<div class="contact-intro-grid"><div>' . $parts[0] . '</div><aside class="contact-photo">[cfr_image name="hero"]<p class="image-caption">' . esc_html( cfr_t( 'evidence' ) . ' · ' . cfr_t( 'review' ) ) . '</p></aside></div>[cfr_case_form]' . $parts[1];
    }
    if ( 'home' === $slug ) {
        foreach ( array( 'cryptocurrency'=>'blockchain', 'brokers'=>'platform', 'payment'=>'payments', 'corporate'=>'evidence', 'case-review'=>'review', 'asset-tracing'=>'flows', 'consultation'=>'guidance' ) as $section => $image ) {
            $pattern = '~<section\b[^>]*id=["\']' . $section . '["\'][^>]*>[\s\S]*?</section>~';
            $content = preg_replace_callback( $pattern, function( $match ) use ( $image ) {
                if ( false !== strpos( $match[0], '[cfr_image ' ) ) { return $match[0]; }
                return preg_replace( '~</h2>~', '</h2>[cfr_image name="' . $image . '"]', $match[0], 1 );
            }, $content, 1 );
        }
    }
    if ( 'contact' === $slug ) { $content = str_replace( '[cfr_image name="hero"]', '[cfr_image name="review"]', $content ); }
    return $content;
}

function cfr_images_admin_controls() {
    if ( ! current_user_can( 'upload_files' ) ) { return; }
    wp_enqueue_media();
    wp_enqueue_script( 'cfr-content-admin', get_template_directory_uri() . '/assets/js/content-admin.js', array( 'jquery', 'media-views' ), CFR_VERSION, true );
    echo '<hr><h2>Site images</h2><p>Replace the bundled editorial photos with images from your Media Library. Set alt text in the Media Library. These settings are shared across languages; bundled images have translated alt text.</p><form method="post">';
    wp_nonce_field( 'cfr_save_images' );
    foreach ( array( 'patterns'=>'AI-assisted pattern investigation', 'guidance'=>'Human-guided consultation', 'flows'=>'Asset / transaction flow tracing', 'hero'=>'Hero / forensic investigation', 'blockchain'=>'Blockchain / network tracing', 'platform'=>'Platform / account review', 'payments'=>'Payment tracing', 'evidence'=>'Corporate / evidence review (Home and About)', 'review'=>'Confidential case review / Contact' ) as $name => $label ) {
        echo '<h3>' . esc_html( $label ) . '</h3><div id="cfr-image-preview-' . esc_attr( $name ) . '" style="max-width:360px">' . cfr_image( array( 'name' => $name ) ) . '</div><input type="hidden" id="cfr-image-' . esc_attr( $name ) . '" name="image_' . esc_attr( $name ) . '" value="' . esc_attr( get_theme_mod( 'cfr_image_' . $name, 0 ) ) . '"><p><button type="button" class="button" data-cfr-select-image="' . esc_attr( $name ) . '">Choose image</button> <button type="button" class="button" data-cfr-reset-image="' . esc_attr( $name ) . '">Use bundled image</button></p>';
    }
    submit_button( 'Save site images', 'secondary', 'save_images' );
    echo '</form>';
}

function cfr_save_images() {
    if ( ! current_user_can( 'edit_theme_options' ) || ! current_user_can( 'upload_files' ) ) { return; }
    check_admin_referer( 'cfr_save_images' );
    foreach ( array( 'patterns', 'guidance', 'flows', 'hero', 'blockchain', 'platform', 'payments', 'evidence', 'review' ) as $name ) {
        $id = isset( $_POST[ 'image_' . $name ] ) && is_scalar( $_POST[ 'image_' . $name ] ) ? absint( $_POST[ 'image_' . $name ] ) : 0;
        if ( ! $id || wp_attachment_is_image( $id ) ) { set_theme_mod( 'cfr_image_' . $name, $id ); }
    }
    echo '<div class="notice notice-success"><p>Site images saved.</p></div>';
}
