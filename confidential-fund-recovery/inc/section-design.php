<?php
/** Normalize saved Home decoration without changing editable copy. */
defined( 'ABSPATH' ) || exit;
function cfr_section_design( $content, $slug ) {
    if ( 'home' !== $slug ) { return $content; }
    $content = preg_replace( '~<span\b[^>]*class=["\'][^"\']*\bsection-symbol\b[^"\']*["\'][^>]*>[\s\S]*?</span>~', '', $content );
    $content = cfr_platform_section_design( $content );
    $content = cfr_contract_section_design( $content );
    $content = cfr_closing_section_design( $content );
    $content = cfr_payment_service_icons( $content );
    return preg_replace_callback( '~<section\b[^>]*id=["\'](?:financial-investigation|professional-standards)["\'][^>]*>[\s\S]*?</section>~', function( $match ) {
        $section = preg_replace( '~\[cfr_image\s+name=["\']patterns["\']\s*\]~', '', $match[0] );
        $icons = array(
            '<circle cx="10" cy="10" r="6"/><path d="m15 15 6 6M7 10h6m-3-3v6"/>',
            '<circle cx="5" cy="5" r="2"/><circle cx="19" cy="6" r="2"/><circle cx="12" cy="19" r="2"/><path d="m7 5 10 1M6 7l5 10m7-9-5 9"/>',
            '<rect x="3" y="4" width="7" height="7" rx="1"/><rect x="14" y="13" width="7" height="7" rx="1"/><path d="M10 7h7v6M7 11v6h7"/>',
            '<circle cx="5" cy="5" r="2"/><circle cx="19" cy="19" r="2"/><path d="M7 5h8a4 4 0 0 1 0 8H9a4 4 0 0 0 0 6h8"/>',
        );
        if ( preg_match( '~\bid=["\']professional-standards["\']~', $match[0] ) ) {
            $icons = array(
                '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M7 16v-4m5 4V8m5 8v-6M7 7h2"/>',
                '<path d="M12 3v18M4 7h16M6 7l-4 7h8Zm12 0-4 7h8ZM8 21h8"/>',
                '<path d="m12 3 8 3v6c0 5-8 9-8 9S4 17 4 12V6Z"/><rect x="9" y="10" width="6" height="6" rx="1"/><path d="M10 10V8a2 2 0 0 1 4 0v2"/>',
                '<circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="4" ry="9"/><path d="M3 12h18M5 6h14M5 18h14"/>',
            );
        }
        return preg_replace_callback( '~(<article\b[^>]*>\s*)<span\b[^>]*>\s*0?([1-4])\s*</span>~', function( $card ) use ( $icons ) {
            return $card[1] . '<span class="analysis-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false">' . $icons[ (int) $card[2] - 1 ] . '</svg></span>';
        }, $section );
    }, $content );
}

/** Relevant decorative icons for the existing section 06 and 07 service cards. */
function cfr_payment_service_icons( $content ) {
    return preg_replace_callback( '~<section\b[^>]*id=["\'](?:investment|payment)["\'][^>]*>[\s\S]*?</section>~', function( $section ) {
        if ( false !== strpos( $section[0], 'case-service-icon' ) ) { return $section[0]; }
        $icons = array(
            '<circle cx="10" cy="10" r="6"/><path d="m15 15 6 6M7 10h6m-3-3v6"/>',
            '<circle cx="5" cy="5" r="2"/><circle cx="19" cy="19" r="2"/><path d="M7 5h8a4 4 0 0 1 0 8H9a4 4 0 0 0 0 6h8"/>',
            '<circle cx="5" cy="5" r="2"/><circle cx="19" cy="6" r="2"/><circle cx="12" cy="19" r="2"/><path d="m7 5 10 1M6 7l5 10m7-9-5 9"/>',
            '<path d="M3 12h4l3-7 4 14 3-7h4"/>',
            '<path d="M6 3h9l3 3v15H6ZM14 3v5h4m-9 4h6m-6 4h4"/>',
            '<path d="M12 3v18M4 7h16M6 7l-4 7h8Zm12 0-4 7h8ZM8 21h8"/>',
        );
        if ( preg_match( '~\bid=["\']payment["\']~', $section[0] ) ) {
            $icons = array(
                $icons[1], $icons[2],
                '<path d="m3 8 9-5 9 5ZM5 10v8m7-8v8m7-8v8M3 21h18"/>',
                $icons[4], $icons[3],
                '<path d="m12 3 8 3v6c0 5-8 9-8 9S4 17 4 12V6Z"/><path d="m8 12 3 3 5-6"/>',
            );
        }
        $index = 0;
        return preg_replace_callback( '~<li>([\s\S]*?)</li>~', function( $card ) use ( $icons, &$index ) {
            return '<li><span class="case-service-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false">' . $icons[ $index++ % count( $icons ) ] . '</svg></span><span class="case-service-label">' . $card[1] . '</span></li>';
        }, $section[0] );
    }, $content );
}

/** Decorative symbols inside the existing paragraphs; no additional claims. */
function cfr_closing_section_design( $content ) {
    return preg_replace_callback( '~<section\b[^>]*id=["\'](?:asset-tracing|transparent-assessment)["\'][^>]*>[\s\S]*?</section>~', function( $section ) {
        if ( false !== strpos( $section[0], 'closing-card-icon' ) ) { return $section[0]; }
        $symbols = false !== strpos( $section[0], 'id="asset-tracing"' ) ? array(
            '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h4"/>',
            '<circle cx="5" cy="5" r="2"/><circle cx="19" cy="19" r="2"/><path d="M7 5h8a4 4 0 0 1 0 8H9a4 4 0 0 0 0 6h8"/>',
        ) : array(
            '<circle cx="10" cy="10" r="6"/><path d="m15 15 6 6M7 10h6"/>',
            '<path d="M6 3h9l3 3v15H6ZM14 3v5h4m-9 4h6m-6 4h4"/>',
            '<path d="m12 3 8 3v6c0 5-8 9-8 9S4 17 4 12V6Z"/><path d="m8 12 3 3 5-6"/>',
        );
        $index = 0;
        return preg_replace_callback( '~<p>([\s\S]*?)</p>~', function( $paragraph ) use ( $symbols, &$index ) {
            return '<p><span class="closing-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false">' . $symbols[ $index++ % count( $symbols ) ] . '</svg></span>' . $paragraph[1] . '</p>';
        }, $section[0] );
    }, $content );
}

function cfr_contract_section_design( $content ) {
    return preg_replace_callback( '~<section\b[^>]*id=["\']smart-contract["\'][^>]*>[\s\S]*?</section>~', function( $section ) {
        if ( false !== strpos( $section[0], 'contract-service-icon' ) ) { return $section[0]; }
        $symbols = array(
            '<path d="M4 20V4m0 16h17M8 15l4-5 4 3 5-7"/>',
            '<path d="M6 3h9l3 3v15H6ZM14 3v5h4m-9 4h6m-6 4h4"/>',
            '<circle cx="5" cy="5" r="2"/><circle cx="19" cy="19" r="2"/><path d="M7 5h8a4 4 0 0 1 0 8H9a4 4 0 0 0 0 6h8"/>',
            '<circle cx="5" cy="5" r="2"/><circle cx="19" cy="6" r="2"/><circle cx="12" cy="19" r="2"/><path d="m7 5 10 1M6 7l5 10m7-9-5 9"/>',
            '<circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="4" ry="9"/><path d="M3 12h18"/>',
            '<circle cx="10" cy="10" r="6"/><path d="m15 15 6 6M6 10h2l2-3 2 6 2-3"/>',
        );
        $index = 0;
        return preg_replace_callback( '~<li>([\s\S]*?)</li>~', function( $card ) use ( $symbols, &$index ) {
            return '<li><span class="contract-service-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false">' . $symbols[ $index++ % count( $symbols ) ] . '</svg></span><span>' . $card[1] . '</span></li>';
        }, $section[0] );
    }, $content );
}

/** Give section 05 a separate visual overview and full-width service cards. */
function cfr_platform_section_design( $content ) {
    return preg_replace_callback( '~(<section\b[^>]*id=["\']brokers["\'][^>]*>\s*<div class="section-shell">)([\s\S]*?)(</div>\s*</section>)~', function( $section ) {
        if ( false !== strpos( $section[2], 'platform-overview' ) ) { return $section[0]; }
        if ( ! preg_match( '~^<div class="section-title">([\s\S]*?)</div>\s*<div class="service-copy">([\s\S]*)</div>\s*$~', $section[2], $parts ) ) { return $section[0]; }
        if ( ! preg_match( '~\[cfr_image\s+name=["\']platform["\']\s*\]~', $parts[1], $image ) ) { return $section[0]; }
        if ( ! preg_match( '~^([\s\S]*?)(<ul class="check-list">[\s\S]*?</ul>)([\s\S]*)$~', $parts[2], $copy ) ) { return $section[0]; }
        $symbols = array(
            '<path d="M4 20V4m0 16h17M8 15l4-5 4 3 5-7"/>',
            '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h4"/>',
            '<circle cx="5" cy="5" r="2"/><circle cx="19" cy="19" r="2"/><path d="M7 5h8a4 4 0 0 1 0 8H9a4 4 0 0 0 0 6h8"/>',
            '<path d="m3 8 9-5 9 5ZM5 10v8m7-8v8m7-8v8M3 21h18"/>',
            '<path d="M6 3h9l3 3v15H6ZM14 3v5h4m-9 4h6m-6 4h4"/>',
            '<circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="4" ry="9"/><path d="M3 12h18"/>',
        );
        $index = 0;
        $cards = preg_replace_callback( '~<li>([\s\S]*?)</li>~', function( $card ) use ( $symbols, &$index ) {
            $symbol = $symbols[ $index++ % count( $symbols ) ];
            return '<li><span class="platform-service-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false">' . $symbol . '</svg></span><span>' . $card[1] . '</span></li>';
        }, $copy[2] );
        return $section[1] . '<div class="section-title">' . str_replace( $image[0], '', $parts[1] ) . '</div><div class="platform-overview"><div class="platform-image">' . $image[0] . '</div><div class="platform-intro">' . $copy[1] . '</div></div><div class="platform-services">' . $cards . '<div class="platform-action">' . $copy[3] . '</div></div>' . $section[3];
    }, $content );
}
