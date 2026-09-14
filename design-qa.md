# Design QA — Guia de Atendimento 2 banner

- Source visual truth: `public/images/contact-guide/sax-banner-1600x300.png`
- Desktop implementation: `/tmp/sax-guide-browser.LiPMUC/hero-1280.png`
- Mobile implementation: `/tmp/sax-guide-browser.LiPMUC/hero-390.png`
- Combined comparison: `/tmp/sax-guide-browser.LiPMUC/banner-comparison.png`
- Viewports: 1280 × 900 and 390 × 900 CSS px, device scale factor 1
- Source dimensions: 1600 × 300 px
- Desktop hero capture: 1248 × 234 px, normalized to 1600 × 300 in the combined comparison
- Mobile hero capture: 358 × 200 px; intentional `cover` crop with the building aligned right
- State: SAX Department Store, Ciudad del Este; default guide state at Piso 2

## Findings

No actionable P0, P1, or P2 differences remain.

- Fonts and typography: the existing ecommerce font is retained. The title and subtitle are real selectable HTML, with sufficient weight and contrast. Mobile wraps the title to two lines without clipping.
- Spacing and layout rhythm: desktop preserves the 1600:300 ratio (1248 × 234 in the rendered container). Mobile uses 200 px at 390 px and 180 px at 320 px. The breadcrumb and controls remain outside the banner.
- Colors and visual tokens: the original dark photographic palette is preserved. No gold, promotional buttons, heavy shadow, or global token changes were introduced.
- Image quality and asset fidelity: the building keeps its proportions on desktop. Mobile uses a responsive crop rather than scaling the full banner into a narrow strip; the facade remains recognizable at the right.
- Copy and content: Portuguese reads “Guia de Atendimento” and “Explore nossos pisos, setores e marcas”; Spanish and English equivalents use the project database-backed `messages.*` translation pattern.
- Focused comparison: the desktop source/implementation composite confirms identical frame ratio, crop, and facade placement; the mobile capture confirms legibility and intentional crop. A separate focused region was not needed beyond these hero-only captures.

## Comparison history

- Initial mobile browser check found the content expanding the hero to 219.42 px at 390 px (P2 responsive height drift).
- Fix: set the mobile hero to `clamp(180px, 52vw, 200px)`, reduced mobile copy padding, and restored automatic proportional height from 768 px upward.
- Post-fix evidence: `/tmp/sax-guide-browser.LiPMUC/hero-390.png` measures 358 × 200 px; the 320 px viewport measures 180 px. The title is fully visible and there is no horizontal overflow.

## Interaction verification

- Banner request completed without failed asset requests.
- Four real locations switch correctly; only SAX Department Store in Ciudad del Este shows the facade, while the other locations show the neutral header.
- Search, location selector, floor anchor navigation, sticky index, automatic active-floor highlighting, last-floor handling, mobile floor selector, and reduced-motion behavior passed.
- Tested at 320, 390, 768, and 1280 px with no page JavaScript errors or horizontal overflow.
- The original `/guia-de-atendimento` still returned successfully and retained its original guide markup.

final result: passed
