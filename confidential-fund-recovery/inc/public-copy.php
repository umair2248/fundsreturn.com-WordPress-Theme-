<?php
/** Apply revised terminology to bundled copy and previously saved theme text. */
defined( 'ABSPATH' ) || exit;

function cfr_clean_public_copy( $html, $language = null ) {
    static $maps = null;
    if ( null === $maps ) { $maps = json_decode( file_get_contents( get_template_directory() . '/inc/neutral-copy.json' ), true ); }
    $language = $language ?: cfr_current_language();
    // Apply the hero revision only inside Home's existing hero, including saved layouts.
    $hero_pairs = $maps['_hero'][ $language ] ?? array();
    if ( $hero_pairs ) {
        $html = preg_replace_callback( '~<section\b(?=[^>]*\bid=["\x27]hero["\x27])[^>]*>[\s\S]*?</section>~i', function( $match ) use ( $hero_pairs ) {
            $parts = preg_split( '/(<[^>]*>)/', $match[0], -1, PREG_SPLIT_DELIM_CAPTURE );
            foreach ( $parts as &$part ) {
                if ( '' === $part || '<' === $part[0] ) { continue; }
                foreach ( $hero_pairs as $pair ) {
                    $part = str_replace( htmlspecialchars( $pair[0], ENT_NOQUOTES, 'UTF-8' ), htmlspecialchars( $pair[1], ENT_NOQUOTES, 'UTF-8' ), $part );
                    $part = str_replace( $pair[0], $pair[1], $part );
                }
            }
            unset( $part );
            return implode( '', $parts );
        }, $html );
    }
    $pairs = $maps[ $language ] ?? $maps['en'];
    $from = array_column( $pairs, 0 );
    $to = array_column( $pairs, 1 );
    foreach ( $pairs as $pair ) {
        $encoded = htmlspecialchars( $pair[0], ENT_NOQUOTES, 'UTF-8' );
        if ( $encoded !== $pair[0] ) { $from[] = $encoded; $to[] = htmlspecialchars( $pair[1], ENT_NOQUOTES, 'UTF-8' ); }
    }
    $ordered = array_combine( $from, $to );
    uksort( $ordered, function( $a, $b ) { return strlen( $b ) <=> strlen( $a ); } );
    $from = array_keys( $ordered );
    $to = array_values( $ordered );
    // Only visible text is revised; IDs, anchors, image paths and URLs stay stable.
    $parts = preg_split( '/(<[^>]*>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
    foreach ( $parts as &$part ) {
        if ( '' !== $part && '<' !== $part[0] ) { $part = str_ireplace( $from, $to, $part ); }
    }
    unset( $part );
    return preg_replace( '~<p\b[^>]*>\s*</p>~u', '', implode( '', $parts ) );
}

// Also cover the optional native WordPress page-content mode without changing stored posts.
add_filter( 'the_content', function( $content ) {
    return is_admin() ? $content : cfr_clean_public_copy( $content );
}, 9 );
