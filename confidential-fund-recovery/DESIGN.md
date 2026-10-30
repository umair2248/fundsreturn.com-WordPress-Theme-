# FUNDS RETURN — evidence-led editorial design

The site uses an investigative editorial style: clear numbered sections, full explanatory copy and visible limits on outcomes. It avoids invented results, authority badges and aggressive financial imagery.

Graphite frames the hero and footer; espresso distinguishes deep reading sections. Copper is reserved for important actions, with white button text. Orange is a small accent, while sand and cream provide calmer reading surfaces. Manrope headings and Inter body text keep the hierarchy consistent.

Locally bundled document-review and writing photographs make the investigation theme tangible. They are illustrative stock photographs, not representations of the firm's employees, premises or real cases. Home uses a stronger image-led hero; Contact pairs its introductory explanation with a prominent photo; About reuses the review image.

The site preserves all six page routes and the 19 Home sections. Editorial splits, restrained evidence panels, a process timeline and open reading layouts provide variety without turning every section into cards. The expanded DISCLAIMER has its own copper separator and a readable dark panel.

French is the default. The four-language switcher navigates to equivalent pages and preserves the visitor's selection. The separate static language-name sentence was removed because it duplicated the real controls.

Header, footer, copy, images and legal drafts remain editable through Appearance → Theme Content. The marked legal fields are deliberately visible until the owner completes them; the design does not imply that missing legal information has been verified.


Version 2.4.0 uses six owner-supplied WebP illustrations: forensic analysis in the hero, network mapping in blockchain tracing, records audit in account investigation, payment trail analysis in payments, ledger review in corporate/evidence sections and About, and confidential consultation in case review and Contact. All six image slots remain replaceable in Theme Content > Home > Site images. The former two stock JPG assets have been removed.


Version 2.5.0: each Home section has an individual composition in assets/css/sections.css. Section counters sit above headings; decorative inline SVG symbols are inserted by inc/section-design.php. Three additional owner-supplied images illustrate payment pattern analysis, human-guided consultation, and asset tracing. All nine assets have editable Media Library slots. Selected images/cards/process steps reveal once on scroll, respect reduced motion, and remain visible without JavaScript. Copy, anchors, six pages, French default and language behavior are retained.


Version 2.6.0 supersedes the earlier section-icon treatment: eyebrow icons are removed throughout Home, and section 01 no longer shows its payment-analysis photo. Sections 01 and 02 use centered introductions and 2-by-2 cards on desktop, stacking on phones. Home descriptions share the same 18px Inter font and 1.8 line height through global tokens. Approved compositions 04, 08 and 09 are retained, while 05, 07, 11, 13 and 15-17 have revised reading layouts. Sections 03, 06, 12 and 19 have centered title blocks. Saved theme layouts receive the same decoration cleanup without overwriting their copy.


Version 2.6.11 adds site-wide entrance animation in assets/js/main.js using IntersectionObserver and the Web Animations API. Headings and text rise 30-42px over 820ms; images reveal over 1050ms with a small scale change; cards stagger by 90ms. Parent cards/forms animate once as a unit. Offscreen content is never pre-hidden. Reduced-motion preferences, keyboard focus, printing and page departure cancel applicable animation. All six pages and the footer are covered without layout or copy changes.


Version 2.6.16 adds assets/css/responsive.css as the final stylesheet. Mobile descriptions scale to 16-17px, headings use fluid sizes, long Dutch/German words wrap safely, and controls retain readable input text and touch targets. The header uses a bounded logo/menu grid on phones, cards and contact/legal content fit narrow screens, and the corporate checklist stacks on tablets. Desktop layouts are preserved. Browser verification covers six pages, four languages and seven viewport widths from 320px through 1440px.


Version 2.6.17 makes the utility language bar and primary navigation sticky together in .site-header-stack. After scrolling, .is-compact reduces logo dimensions, navigation/button size and vertical padding. Phone mode retains language choices, brand and menu in a short stack, hiding only the brand tagline/subtitle while compact. ResizeObserver keeps anchor offsets synchronized with the complete stack; WordPress toolbar offsets are preserved. Returning to the top restores the original header dimensions.
