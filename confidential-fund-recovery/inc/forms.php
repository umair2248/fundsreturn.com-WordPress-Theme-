<?php
/** Email-only enquiry handling. No uploads and no case records in the database. */
defined( 'ABSPATH' ) || exit;

function cfr_case_form() {
    $status = isset( $_GET['assessment'] ) && is_scalar( $_GET['assessment'] ) ? sanitize_key( wp_unslash( $_GET['assessment'] ) ) : '';
    $messages = array();
    foreach ( array( 'sent', 'invalid', 'expired', 'limited', 'failed' ) as $key ) { $messages[ $key ] = cfr_t( $key ); }
    ob_start(); ?>
    <section id="case-form" class="assessment-panel" aria-labelledby="assessment-heading">
        <div class="assessment-intro"><p class="eyebrow"><?php echo esc_html( cfr_t( 'review' ) ); ?></p>
        <h2 id="assessment-heading"><?php echo esc_html( cfr_t( 'form_title' ) ); ?></h2>
        <p><?php echo esc_html( cfr_t( 'form_help' ) ); ?></p>
        <p class="muted"><?php echo esc_html( cfr_t( 'form_notice' ) ); ?></p></div>
        <div>
        <?php if ( isset( $messages[ $status ] ) ) : ?><div tabindex="-1" class="form-message <?php echo 'sent' === $status ? 'success' : 'error'; ?>" role="<?php echo 'sent' === $status ? 'status' : 'alert'; ?>"><?php echo esc_html( $messages[ $status ] ); ?><?php if ( 'sent' !== $status ) : ?><p><?php echo esc_html( cfr_t( 'retry' ) ); ?></p><?php endif; ?></div><?php endif; ?>
        <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="case-form">
            <input type="hidden" name="action" value="cfr_assessment"><input type="hidden" name="case_language" value="<?php echo esc_attr( cfr_current_language() ); ?>">
            <input type="hidden" name="return_page" value="<?php echo esc_attr( get_queried_object_id() ); ?>">
            <?php wp_nonce_field( 'cfr_assessment', 'cfr_nonce' ); ?>
            <div class="form-trap" aria-hidden="true"><label for="cfr-website"><?php echo esc_html( cfr_t( 'empty' ) ); ?></label><input id="cfr-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
            <label for="cfr-name"><?php echo esc_html( cfr_t( 'name' ) ); ?> <span aria-hidden="true">*</span></label>
            <input id="cfr-name" name="case_name" type="text" autocomplete="name" maxlength="100" required>
            <label for="cfr-email"><?php echo esc_html( cfr_t( 'email' ) ); ?> <span aria-hidden="true">*</span></label>
            <input id="cfr-email" name="case_email" type="email" autocomplete="email" maxlength="254" required>
            <label for="cfr-phone"><?php echo esc_html( cfr_t( 'phone' ) ); ?></label>
            <input id="cfr-phone" name="case_phone" type="tel" autocomplete="tel" maxlength="50">
            <label for="cfr-summary"><?php echo esc_html( cfr_t( 'summary' ) ); ?> <span aria-hidden="true">*</span></label>
            <textarea id="cfr-summary" name="case_summary" rows="6" minlength="20" maxlength="4000" aria-describedby="summary-help" required></textarea>
            <p id="summary-help" class="field-help"><?php echo esc_html( cfr_t( 'summary_help' ) ); ?></p>
            <label class="checkbox-label"><input type="checkbox" name="acknowledge" value="1" required><span><?php echo esc_html( cfr_t( 'acknowledge' ) ); ?></span></label>
            <p class="field-help"><a href="<?php echo esc_url( cfr_page_url( 'privacy-policy' ) ); ?>"><?php echo esc_html( cfr_t( 'privacy_link' ) ); ?></a></p>
            <button type="submit" class="button"><?php echo esc_html( cfr_t( 'assess' ) ); ?> <?php echo cfr_arrow(); ?></button>
        </form></div>
    </section>
    <?php return ob_get_clean();
}
add_shortcode( 'cfr_case_form', 'cfr_case_form' );

function cfr_form_value( $key, $textarea = false ) {
    if ( ! isset( $_POST[ $key ] ) || ! is_string( $_POST[ $key ] ) ) { return ''; }
    return $textarea ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
}

function cfr_handle_assessment() {
    $page_id = absint( cfr_form_value( 'return_page' ) );
    $return_url = $page_id && 'page' === get_post_type( $page_id ) && 'publish' === get_post_status( $page_id ) ? get_permalink( $page_id ) : cfr_page_url( 'contact' );
    $language = cfr_form_value( 'case_language' );
    if ( ! isset( cfr_languages()[ $language ] ) ) { $language = 'fr'; }
    $return_url = add_query_arg( 'lang', $language, $return_url );
    $redirect = function( $status ) use ( $return_url ) {
        wp_safe_redirect( add_query_arg( 'assessment', $status, $return_url ) . '#case-form', 303 );
        exit;
    };
    if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! wp_verify_nonce( cfr_form_value( 'cfr_nonce' ), 'cfr_assessment' ) ) { $redirect( 'expired' ); }
    if ( cfr_form_value( 'website' ) ) { $redirect( 'invalid' ); }
    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
    $rate_key = 'cfr_' . hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) );
    if ( get_transient( $rate_key ) ) { $redirect( 'limited' ); }
    $name = cfr_form_value( 'case_name' );
    $raw_email = cfr_form_value( 'case_email' );
    $email = sanitize_email( $raw_email );
    $phone = cfr_form_value( 'case_phone' );
    $summary = cfr_form_value( 'case_summary', true );
    $length = function( $value ) { return function_exists( 'mb_strlen' ) ? mb_strlen( $value, 'UTF-8' ) : strlen( $value ); };
    if ( ! $name || $length( $name ) > 100 || ! is_email( $email ) || $raw_email !== $email || strlen( $email ) > 254 || preg_match( '/[\r\n]/', $email ) || $length( $phone ) > 50 || $length( $summary ) < 20 || $length( $summary ) > 4000 || '1' !== cfr_form_value( 'acknowledge' ) ) { $redirect( 'invalid' ); }
    set_transient( $rate_key, 1, 3 * MINUTE_IN_SECONDS );
    $recipient = sanitize_email( get_theme_mod( 'cfr_recipient', '' ) ?: get_option( 'admin_email' ) );
    $body = "New case assessment request\n\nName: {$name}\nEmail: {$email}\nTelephone: {$phone}\n\nCase summary:\n{$summary}\n\nThe visitor requested contact and acknowledged that recovery cannot be guaranteed.\n";
    $sent = is_email( $recipient ) && wp_mail( $recipient, __( 'New confidential case assessment request', 'confidential-fund-recovery' ), $body, array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $email ) );
    $redirect( $sent ? 'sent' : 'failed' );
}
add_action( 'admin_post_cfr_assessment', 'cfr_handle_assessment' );
add_action( 'admin_post_nopriv_cfr_assessment', 'cfr_handle_assessment' );
