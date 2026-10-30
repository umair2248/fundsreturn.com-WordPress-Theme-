# FUNDS RETURN — WordPress theme 2.4.0

Custom PHP, CSS and vanilla JavaScript theme. No page builder, React, paid plugin or translation service is required.

## Install or update

1. Open WordPress **Appearance → Themes → Add New Theme → Upload Theme**.
2. Upload **confidential-fund-recovery-theme.zip**. If this theme is installed, choose **Replace current with uploaded**; otherwise install and activate.
3. Visit Dashboard once. The one-time branding update sets the platform/Site Title to **FUNDS RETURN**, with the French AI-investigation tagline. Existing page records remain untouched.
4. Clear website/CDN/browser caches and reopen the content editor if old wording or styles remain.
5. Fill official contact details, complete the marked legal-draft fields and test email receipt on your host before publication.

The archive has one top-level confidential-fund-recovery folder, with style.css directly inside it. Do not ZIP the entire workspace. Requires WordPress 6.4+ and PHP 7.4+; use host-supported versions.

## Brand, title and language

Platform name: **FUNDS RETURN**.

English Home browser title: **FUNDS RETURN | AI-Assisted Financial Investigation**. The French default uses **FUNDS RETURN | Investigation financière assistée par l’IA**. Dutch and German titles are translated too. Change the Home browser title in **Theme Content → Header** for each language.

French is the initial language. Explicit EN/FR/NL/DE URL selections take priority, then a valid saved choice, then French. The language cookie lasts 30 days. Existing visitors with an explicit choice retain it; use a private window to check the fresh-visitor default.

The static English/French/Dutch sentence highlighted in the supplied screenshot has been removed from page content, including matching older saved theme content. The working globe/language switchers remain.

Neutral financial-investigation wording is retained in ordinary copy. The new supplied DISCLAIMER includes its original advice-category wording as specifically requested; it is not passed through the older terminology replacement that would alter it.

## Pages and content

Exactly six starter page records: **Home, About, FAQ, Contact, Privacy Policy, Terms of Use**. Header navigation shows About, FAQ and Contact; the logo/name links Home. No separate service, process or case-experience page is created. Home retains its hero and nineteen supplied content sections.

Open **Appearance → Theme Content**. Choose a language and one of the six pages, **Header**, or **Footer**. Header/Footer are editing areas, not extra pages.

- Header: platform name, Home browser title, header line, subtitle, CTA label/destination and navigation labels.
- Footer: description, headings, Home-section link labels, disclaimer, contact information, hours, social links, copyright wording and assessment recipient.
- Pages: independent English/French/Dutch/German content editors. Keep structural classes, anchor IDs, link tokens and shortcodes when editing HTML.
- Home → Site images: choose Media Library replacements or restore bundled photographs. Add Media is enabled for page content. Custom-image alt text comes from the Media Library.

Content changes are saved in theme options. Setup creates only missing pages and missing menu items; it does not overwrite existing page/post records. The supplied layouts render on the six routes. An optional Customizer setting allows native WordPress content for English; those changes are not automatically translated.

The one-time FUNDS RETURN branding update changes only the requested firm/Site Title/tagline, not case records or unrelated settings. User Header/Footer edits remain available afterwards.

## Images and design

Photos are stored locally in assets/images and included in the ZIP. Home has a prominent document-review hero photo and review image. Contact now visibly pairs its introduction with a photo; About also uses imagery. Existing saved starter layouts receive photos at known image slots during rendering, so an old saved layout does not silently hide them.

These are illustrative stock photographs, not actual staff, office or case evidence. Source URLs and the license reference are in **assets/images/CREDITS.md**. No remote image request is needed during ordinary rendering.

Managed-image shortcodes: [cfr_image name="hero"] and [cfr_image name="evidence"]. Form shortcode: [cfr_case_form], once per page. CTA icon shortcode: [cfr_arrow]. [cfr_contact_details] renders official public details.

Graphite, copper, espresso, sand and cream remain the global palette, with white text on copper actions. The current editor CSS is versioned to refresh caches. Responsive styles cover desktop, tablet and mobile, keyboard navigation, visible focus and reduced motion. **DESIGN.md** explains the visual approach.

## New DISCLAIMER

The footer heading is **DISCLAIMER**. The supplied statement is included in full, with both firm placeholders replaced by FUNDS RETURN (or the subsequently edited firm name):

> FUNDS RETURN provides financial investigation, forensic analysis, asset tracing, and case-support services. No recovery of funds or assets, legal outcome, or specific result is guaranteed. Information provided on this website is for general informational purposes only and does not constitute legal, financial, investment, tax, or regulatory advice. No representative of our legal or investigative team will request your passwords, private keys, seed phrases, authentication codes, bank or cryptocurrency account access, or direct payment to a personal account. Never share confidential credentials or transfer funds to anyone claiming to represent FUNDS RETURN without first verifying the request through our official contact channels.

French, Dutch and German versions are included. Edit them under **Theme Content → Footer**. This release uses new disclaimer settings so older saved defaults do not suppress the requested replacement. The general no-guarantee statements within page content remain as well.

## Legal drafts — owner completion required

Privacy Policy and Terms of Use now contain structured, editable drafts in all four languages, rather than empty placeholder pages. Each clearly states its draft status.

Complete the marked fields for legal entity, country/address, official contact, purposes/legal bases, actual service providers, data retention, processing locations/transfers, complaint authority/process, fees/cancellation/refunds and governing law/forum. No unprovided company registration, jurisdiction, legal status, fee, refund promise or case-retention period has been invented.

The privacy draft describes the implemented form, email handoff, language cookie, HMAC rate limiter and optional Google Fonts requests. It leaves host/plugin and later case-handling details for the owner to confirm. Terms preserve the limits of the service and separate formal engagement.

The owner explicitly requested drafts with marked fields. They are not approved legal policies. Complete and review them for the actual operation before public launch. Existing approved custom legal copy is preserved; matching old placeholder-only theme copy renders the new draft instead.

Editorial checklist reference: [European Commission — Information for individuals](https://commission.europa.eu/law/law-topic/data-protection/information-individuals_en). This reference informed topics to check, not a claim that EU law applies to this unidentified legal entity.

## Contact details and form

Use **Theme Content → Footer** or **Customize → Firm details** to replace the official email, phone, office address, business hours and social-link placeholders. The pasted chat phone number was not treated as a verified public business contact.

The form collects name, email, optional telephone, short case summary and acknowledgement. No uploads or credentials are requested. It sends through wp_mail to the configured assessment recipient, falling back to WordPress Administration Email. The theme does not create a case database.

Security includes a nonce, honeypot, type/length/email validation, safe redirects and a three-minute HMAC-based connection limit. Success means accepted for email delivery, not confirmed inbox receipt or engagement. Test delivery with your host; an optional free SMTP plugin may be needed.

Use HTTPS. Exclude Contact from full-page caching so nonces remain fresh, and make caches respect the lang query parameter and preference cookie. Host/email/plugin logs must be reflected in the completed privacy policy.

## Validation and development

Source files and earlier supplied content remain in the workspace. build-theme.cjs and build-legal-drafts.cjs regenerate the bundled layouts/drafts; no build step is needed to install the ZIP. Existing WordPress option overrides are not overwritten by rebuilding files.

Run **node tests/verify.cjs** for syntax, source coverage, all four languages, six routes, nineteen Home sections, image inclusion, Header/Footer saving, French defaults, terminology cleanup, legal drafts and form scenarios. Set PHP_BINARY if needed. API tests use isolated WordPress doubles.

Local Chromium checks cover Home/Contact in all four languages at 1440, 768, 390 and 320 pixels, actual image loading, language navigation/cookies, keyboard Escape, graphite colors, DISCLAIMER, Contact photography, draft structure and browser title. They do not replace live WordPress activation or actual inbox-delivery tests.

Packaging validates the single-root archive layout and every file's SHA256 against source. Preview adapters and screenshots remain outside the theme ZIP. Back up before updating and use a child theme for ongoing code customization.


Version 2.4.0 uses six owner-supplied WebP illustrations: forensic analysis in the hero, network mapping in blockchain tracing, records audit in account investigation, payment trail analysis in payments, ledger review in corporate/evidence sections and About, and confidential consultation in case review and Contact. All six image slots remain replaceable in Theme Content > Home > Site images. The former two stock JPG assets have been removed.


Version 2.5.0: each Home section has an individual composition in assets/css/sections.css. Section counters sit above headings; decorative inline SVG symbols are inserted by inc/section-design.php. Three additional owner-supplied images illustrate payment pattern analysis, human-guided consultation, and asset tracing. All nine assets have editable Media Library slots. Selected images/cards/process steps reveal once on scroll, respect reduced motion, and remain visible without JavaScript. Copy, anchors, six pages, French default and language behavior are retained.


Version 2.6.0 supersedes the earlier section-icon treatment: eyebrow icons are removed throughout Home, and section 01 no longer shows its payment-analysis photo. Sections 01 and 02 use centered introductions and 2-by-2 cards on desktop, stacking on phones. Home descriptions share the same 18px Inter font and 1.8 line height through global tokens. Approved compositions 04, 08 and 09 are retained, while 05, 07, 11, 13 and 15-17 have revised reading layouts. Sections 03, 06, 12 and 19 have centered title blocks. Saved theme layouts receive the same decoration cleanup without overwriting their copy.


Version 2.6.11 adds site-wide entrance animation in assets/js/main.js using IntersectionObserver and the Web Animations API. Headings and text rise 30-42px over 820ms; images reveal over 1050ms with a small scale change; cards stagger by 90ms. Parent cards/forms animate once as a unit. Offscreen content is never pre-hidden. Reduced-motion preferences, keyboard focus, printing and page departure cancel applicable animation. All six pages and the footer are covered without layout or copy changes.


Version 2.6.16 adds assets/css/responsive.css as the final stylesheet. Mobile descriptions scale to 16-17px, headings use fluid sizes, long Dutch/German words wrap safely, and controls retain readable input text and touch targets. The header uses a bounded logo/menu grid on phones, cards and contact/legal content fit narrow screens, and the corporate checklist stacks on tablets. Desktop layouts are preserved. Browser verification covers six pages, four languages and seven viewport widths from 320px through 1440px.


Version 2.6.17 makes the utility language bar and primary navigation sticky together in .site-header-stack. After scrolling, .is-compact reduces logo dimensions, navigation/button size and vertical padding. Phone mode retains language choices, brand and menu in a short stack, hiding only the brand tagline/subtitle while compact. ResizeObserver keeps anchor offsets synchronized with the complete stack; WordPress toolbar offsets are preserved. Returning to the top restores the original header dimensions.
