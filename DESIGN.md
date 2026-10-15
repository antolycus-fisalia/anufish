# Anufish Design System

## Home and exploration separation (15 October 2026)

The supplied home/exploration screenshots supersede the combined dashboard below.
`/` is Beranda: page title and supporting text, a pale split hero, three shortcut
panels, then Bacaan untukmu. `/fish` is Jelajahi Ikan: its own heading, search form,
results and pagination. Legacy `/?q=...` redirects to `/fish` preserving q/page.
Sidebar active state and header breadcrumb follow the route, including empty search.
Home performs no external fish request. Keep iNaturalist copy rather than the old
GBIF reference. Do not invent articles, habitat metadata or implemented scan routes.
Unavailable scan/history/article shortcuts show Segera; article area has an honest
empty state. Habitat filter chips are omitted until trustworthy habitat data exists.

Reuse the existing 256px sidebar, 64px header, 1280px content maximum, 32px desktop
and 16px mobile gutters, Instrument Sans, six brand colors, 16px panel radius,
24px section gaps and 44px targets. Hero uses 32px desktop / 24px mobile padding,
48/56 desktop / 36/44 mobile title and a 416x264 contained media column on wide
screens; stack at compact widths. Shortcut panels use 24px padding, 20px icons,
18/26 headings and 16/24 body; three columns on desktop and one on phone.
Explore results add a 16:10 photo frame, contained imagery, pale empty/error state,
and visible source/photographer/license. Keep genuine taxonomy and alias controls.
Use one main vertical scroll owner, with no page-level document scroll. ID/EN,
keyboard focus, empty/error/loading and image-failure states are required. CSS/JS
remain external. Responsive captures extrapolate from the desktop references;
the app remains light themed under either system preference.

## Figma redesign (October 2026)

### Fish detail (FSH-10)

#### Species photo gallery

Keep the existing Anufish palette and authenticated shell. The iNaturalist screenshot
is a functional reference for a main image with thumbnails, not a new brand or chart layout.
The reusable `x-fish.photo-gallery` uses a white 16px-radius panel, 24px desktop / 16px
mobile padding, a contained 4:3 main photo, 80x64 thumbnails in a horizontal rail with
8px gaps, and 44px previous/next controls. Active thumbnails have a pale teal wash,
a visible check glyph, and `aria-pressed`; focus retains the teal ring. Credit and
license change with the selected photo. Show the current position and loaded photo count.
Curated licensed photos render immediately; additional observation photos load on demand
through “Muat foto lainnya” using cursor pagination, preserving the main page's sole
vertical scroll. The thumbnail rail scrolls only horizontally; selecting a photo keeps
its thumbnail visible without growing the panel as more photos arrive.
Thumbnail images are lazy-loaded; the main photo is eager. No autoplay,
no animation requirement, no observation locations. Arrow keys on the gallery select
photos; buttons remain usable by touch and keyboard. Loading, retry, exhausted and
broken-image states must preserve existing photos and taxonomy. Indonesian and English
labels are required. Include only photos with the existing CC0 / CC BY / CC BY-SA policy.

#### Enlarged photo viewer

Clicking the main photo opens a native modal dialog (`x-fish.photo-viewer`) in the
top layer. The photo is a real button with a labelled expand glyph, a visible focus
ring, and a 44px minimum target. The dialog uses Anufish white/pale surfaces, navy
text, 16px panel radius, 24px desktop / 16px phone padding and 16px gaps; its backdrop
is navy at 70% opacity. Width is at most 1152px and viewport minus 32px. Height is at
most viewport minus 32px, using a contained image in a flexible central row and a
scrollable panel only when text or a short viewport requires it. Header and footer
keep close and previous/next controls reachable, with 44px targets. Show species,
photo position, credit, source and license. Fetch only the selected large image on
open/navigation; no original-size downloads or autoplay. Fall back to the existing
medium image if large fails; show a localized error/retry if both fail. Loading
overlays hide the previous photo while a new one loads. Left/right keys and buttons
select the same shared gallery index, including photos already loaded with “Muat
foto lainnya”. Disable navigation at boundaries. Escape, close button and backdrop
close the dialog; focus returns to the main-photo button and page scrolling is
restored. Native dialog provides focus containment; the main page is inert and
cannot scroll underneath. No opening motion is required. ID/EN labels are mandatory.

Provider migration: use iNaturalist taxon IDs under `/fish/taxa/{taxonId}`.
Previous `/fish/{id}` links return 404 instead of interpreting an unrelated provider ID.
Do not show observation locations or fetch observations for location information.
The interface supports Indonesian and English through a persistent ID/EN toggle.
Missing Indonesian source descriptions fall back to English with an explicit note.
Search and detail keep
distinct 404/429/502 states, a visible retry action, and the existing brand tokens.

The requested detail route extends the existing authenticated shell. Search cards gain
a 44px primary “Lihat detail ikan” link preserving query and page. Detail uses the same
brand tokens, white bordered 16px panels, 24px gaps and 32/40 title. A contained 4:3
photograph with photographer, source and license sits beside species identity and
taxonomy on desktop; stack on mobile. Source descriptions and Indonesian/English
names follow below. Optional photos and English fallback descriptions load asynchronously.
External descriptions are plain text, never executable
HTML. Missing data and unavailable photographs have localized ID/EN states. A failed
optional provider leaves core detail readable; core not-found and outage use distinct
404/429/502 states. Main remains the sole page scroll owner. No fabricated habitat or images.

### Multilingual fish search

Existing search accepts 1–150 Unicode characters, including a single CJK character;
names use bidirectional isolation (`bdi`) and automatic input direction. Compact white
cards retain the brand palette, 18/26 headings and 14/22 metadata. A 40px icon replaces
large empty photo placeholders. Taxonomy is a two-column definition list. Native details
exposes available Indonesian and English common names with language labels, a visible open/closed indicator,
and a bounded 256px scroll region specifically for the alias list. Source links and
summary controls retain 44px hit areas. Actual iNaturalist data determines available languages;
no names are invented or automatically translated. Match the typed name before preferring
Indonesian or English. Scientific and available common names are searched through the taxa endpoint.

The approved Figma redesign supersedes the older visual recipes below where they conflict.
Keep all six brand colors and Instrument Sans (400, 500, 600, 700). Only existing login, registration, homepage/search,
and profile views are implemented; routes and application behavior stay unchanged.

- Primary buttons: solid `anufish-teal`, white semibold labels, navy hover/pressed,
  44px minimum height, radius 8px, visible teal focus ring. No gradient or decorative shadow.
- Typography: page title 32/40 bold; section 24/32 semibold; card 18/26 semibold;
  body 16/24; labels 14/20; supporting text 14/22; caption 12/18.
  Hero: 48/56 desktop, 36/44 mobile, semibold.
- Auth: white desktop canvas, navy editorial rail (568px at 1440px), 64px rail padding,
  440x320 fish photograph; form card 448px including 40px padding. At mobile sizes,
  pale canvas, white bordered card with 24px padding and original cropped logo; hide rail.
- Logo viewport: width 160px sidebar, 200px mobile auth, 184px editorial. Original
  transparent bitmap stays intact; viewport aspect ratio 100/37, image width 141.791%,
  left offset -20.894%, top offset -157.315% of viewport height.
- Authenticated shell: 256px white sidebar, 64px white header, gray-50 canvas,
  content padding 32px desktop / 16px mobile, 24px section gaps, existing scroll ownership.
- Hero: pale bordered surface, radius 16px, padding 32px desktop / 24px mobile;
  two columns at wide desktop, stacked on compact widths; photo 416x264 / 310x190.
- Profile: summary 336px and flexible form in desktop columns with 24px gap;
  stacked on compact screens. White bordered panels, radius 16px, padding 24px;
  80px teal rounded-square avatar, file input remains attached to the existing form.
- Search: white bordered panel with 16px padding, input and button min-height 44px;
  results grid 3 desktop / 2 tablet / 1 mobile columns. No invented photos or detail routes.
- Navigation groups: Eksplorasi and Ruang saya; no duplicate Dashboard destination.
  Unavailable features retain the existing “Segera” treatment.
- Photo asset: Nick Hobgood, Amphiprion ocellaris, CC BY-SA 3.0, Wikimedia Commons.


## Purpose

This document describes the visual language already established by Anufish, a fish identification and information application. It is a reference for extending the interface without replacing its current Laravel Blade structure, Tailwind conventions, forms, navigation shell, or responsive behavior.

The system should feel clear, trustworthy, and approachable. Brand expression comes from the combination of a calm pale background, aquatic blue-green color, confident navy typography, and practical form controls. Avoid introducing a separate visual identity for individual pages.

## Design Principles

- **Useful before decorative.** Information, labels, validation, and primary actions should be immediately understandable.
- **Aquatic but restrained.** Use the blue-green palette as a recognizable accent, not as a full-screen ornament.
- **Consistent surfaces.** White cards and panels sit on pale or neutral page backgrounds; depth is subtle and functional.
- **Clear hierarchy.** Titles, descriptions, field labels, helper text, and errors should remain distinct at a glance.
- **Progressive responsiveness.** Preserve the existing single-column mobile layouts and max-width containers rather than introducing page-specific breakpoints.
- **Accessible states.** Focus, error, disabled, and interactive states must remain visible without relying on color alone.

## Current Application Structure

The interface is organized around Laravel Blade layouts and components:

- `layouts.app` provides the document shell, Vite assets, neutral body background, and default text color.
- `layouts.guest` centers guest content in a full-viewport pale canvas with a `max-w-md` content column.
- `layouts.authenticated` provides the signed-in header, brand link, reserved navigation region, and `max-w-7xl` content container.
- `pages.auth.login` uses the reusable `x-auth.card`, `x-auth.form-field`, `x-auth.password-field`, and `x-auth.submit-button` components.
- `pages.index` is the authenticated home/dashboard entry point and currently uses a centered welcome treatment.

New screens should extend these layouts and components. Do not create a competing page shell or duplicate authentication markup when an existing component can express the need.

## Color System

The source of truth for brand color tokens is `resources/css/app.css`. Use the named Tailwind colors rather than repeating hex values in templates.

### Brand Tokens

| Token | Value | Role |
| --- | --- | --- |
| `anufish-navy` | `#124F7C` | Primary headings, labels, important text, brand emphasis |
| `anufish-teal` | `#197E8F` | Interactive focus, active controls, secondary brand emphasis |
| `anufish-cyan` | `#2AB7CB` | Highlight, hover emphasis, gradient endpoint, positive visual energy |
| `anufish-pale` | `#EFF9FB` | Guest page canvas and low-contrast interactive state background |
| `anufish-muted` | `#66798B` | Supporting text, placeholders, descriptions, secondary metadata |
| `anufish-border` | `#B9DDE3` | Input and card borders, quiet separators |

### Neutral and Semantic Colors

- Use `gray-50` and `white` for the application shell and content surfaces already established by `layouts.app` and the auth card.
- Use `gray-900` for default body text only where the shared shell already does so; use `anufish-navy` for branded headings and labels.
- Use `rose` error styles for invalid fields and validation messages, matching the existing `rose-300`, `rose-500`, `rose-100`, and `rose-600` treatment.
- Reserve green, amber, and other semantic colors for states that genuinely need them. They should not compete with the blue-green brand palette.

### Color Usage Rules

- Prefer `anufish-pale` for calm page-level context and hover backgrounds.
- Prefer white for readable form and content surfaces.
- Use `anufish-navy` for high-emphasis text, not as a large decorative fill by default.
- Use `anufish-teal` for focus rings and active controls.
- Use `anufish-cyan` sparingly as an accent or gradient endpoint.
- Maintain readable contrast for all text and controls; do not use muted text for required information or errors.

## Typography

The Vite configuration provisions **Instrument Sans** in weights 400, 500, and 600. Keep the type system simple and use the existing sans-serif direction rather than adding a display face.

| Role | Tailwind direction | Use |
| --- | --- | --- |
| Page title | `text-3xl` to `text-4xl font-bold tracking-tight` | Page and welcome headings |
| Card title | `text-2xl font-bold tracking-tight sm:text-3xl` | Auth and focused content cards |
| Body | `text-base leading-6` | Explanatory and dashboard copy |
| Label | `text-sm font-semibold` | Form labels and compact headings |
| Supporting text | `text-sm leading-6 text-anufish-muted` | Descriptions, placeholders, and guidance |
| Action text | `text-sm font-bold` | Buttons and high-intent actions |
| Error text | `text-sm font-medium text-rose-600` | Inline validation feedback |

Typography should establish hierarchy through size, weight, and color. Avoid all-caps labels, excessive letter spacing, and long runs of bold text.

## Spacing and Layout

Use Tailwind spacing utilities and follow the spacing already visible in the layouts:

- Guest pages use `px-4 py-4`, increasing to `sm:px-6 sm:py-8`.
- Auth cards use `px-6` with larger `sm:px-10` horizontal padding and compact vertical rhythm.
- Forms use `space-y-4` between fields.
- The authenticated shell uses a fixed `h-16` header and `py-6` main content spacing.
- Authenticated content is constrained to `max-w-7xl`; guest authentication content is constrained to `max-w-md`.

Use centered, readable columns for focused tasks. Use the authenticated max-width container for application content. Do not make a form or dashboard wider merely to fill available space.

## Surfaces, Borders, and Depth

- Cards and panels use a white surface with `border-anufish-border`.
- The auth card uses `rounded-2xl` and a restrained navy-tinted shadow: `shadow-xl shadow-anufish-navy/10`.
- Inputs and controls use `rounded-lg`; the password visibility control uses `rounded-md`.
- The authenticated header is white with a simple bottom border and no heavy shadow.
- Use shadows to clarify a primary contained surface, not as a general page decoration.
- Do not introduce glassmorphism, large blur effects, ornamental gradients, or unrelated corner-radius scales.

## Components

### Brand and Logo

The Anufish logo is served from `public/images/anufish-logo.png`. The auth card centers it above the card heading, allowing the image to overlap the card rhythm with the existing negative vertical margin. Preserve its aspect ratio and provide meaningful `alt` text. Do not recreate the mark as text or replace it with a generic fish icon.

### Authentication Card

`x-auth.card` is the primary focused-task container:

- White background, `rounded-2xl`, brand border, and soft navy shadow.
- Centered logo above the title.
- Navy bold title with a muted supporting description.
- Responsive padding: compact on mobile, more generous from `sm` upward.
- Slot content follows the heading without adding a second nested card.

### Form Fields

`x-auth.form-field` and `x-auth.password-field` are the standard field patterns:

- Labels sit above controls with a 2-unit gap and use navy semibold text.
- Inputs are full width, white, bordered, padded, and lightly shadowed.
- Default interaction uses cyan hover border, teal focus border, and a pale teal focus ring.
- Invalid fields use the existing rose border, ring, and alert text treatment.
- Preserve native labels, autocomplete attributes, required state, `aria-invalid`, and `aria-describedby` behavior.
- Password visibility is an inline button inside the field; keep it keyboard accessible and retain its pressed and accessible-label states.

Do not replace these controls with unlabeled floating labels, icon-only required indicators, or custom widgets without a concrete accessibility benefit.

### Primary Submit Button

`x-auth.submit-button` is the high-intent action:

- Full width within the current form context.
- `rounded-lg`, bold small text, white label.
- Existing left-to-right gradient: navy, teal, then cyan.
- Hover shifts toward the brighter teal/cyan range.
- Focus uses a visible teal ring with offset.
- Active state becomes darker and more grounded.

Use one visually dominant submit/action button per focused form. Secondary actions should not use the same gradient treatment.

### Authenticated Header

`layouts.authenticated` is the current navigation shell:

- White `h-16` header with a bottom border.
- Brand link at the left, using the existing `text-xl font-semibold` treatment.
- Reserved navigation region at the right.
- Horizontal inner layout with `max-w-7xl` and responsive side padding.

When navigation is added, keep the existing shell and responsive padding. Use text links and clear active/focus states before adding extra visual chrome. Do not move navigation into a new sidebar without an application-level requirement.

### Inline Links and Status

Links should be identifiable through color, weight, underline, or a clear state change. The login prompt currently uses bold teal text for the registration affordance; preserve that compact pattern until registration becomes an actual link. Error messages remain inline below their field and use `role="alert"`.

## Interaction States

Every interactive component should account for:

- Default: quiet white or transparent surface with readable text.
- Hover: modest border, background, or text emphasis; avoid layout shifts.
- Focus-visible: visible teal ring with sufficient contrast and offset where needed.
- Active/pressed: darker or more compact visual treatment without changing the control’s dimensions.
- Disabled: reduced contrast and no misleading hover affordance, while remaining legible.
- Error: rose border and ring on the control plus an associated alert message.

Transitions should remain short and support perception of state change. Do not animate page structure or use motion as the primary way to communicate status.

## Responsive Behavior

Preserve the current mobile-first Tailwind approach:

- The guest layout remains vertically and horizontally centered with a full-height pale canvas.
- Guest content remains full width within `max-w-md`, with `px-4` on the smallest screens and `sm:px-6` above them.
- Auth cards retain readable horizontal padding on mobile and expand at `sm`.
- Form controls remain full width and touch-friendly at every width.
- The authenticated header keeps its 16px-height rhythm and switches only through additions to the existing flex layout when navigation requires it.
- Authenticated content retains the `max-w-7xl` container with `px-4 sm:px-6 lg:px-8`.
- Avoid horizontal scrolling, fixed-width forms, hover-only actions, and breakpoint-specific duplicate content.

Prefer stacking over squeezing. Any future multi-column content should collapse to one column before labels, controls, or fish imagery become crowded.

## Accessibility and Content

- Keep the document language and page titles meaningful for each Blade view.
- Use semantic landmarks already provided by `main`, `header`, `nav`, `article`, and form elements.
- Pair every input with a visible label; preserve the existing error association.
- Ensure keyboard focus is visible for links, buttons, password toggles, and future navigation.
- Use concise Indonesian UI copy consistently with the current login and welcome pages.
- Keep action labels specific: “Masuk ke Dashboard” communicates the destination better than a generic “Submit”.
- Use descriptive alternative text for the logo and future fish imagery; decorative imagery should be hidden from assistive technology.

## Do and Don't

### Do

- Reuse `layouts.app`, `layouts.guest`, and `layouts.authenticated`.
- Reuse the auth components and extend them with attributes when possible.
- Use the named `anufish-*` Tailwind tokens from `resources/css/app.css`.
- Keep content inside the established `max-w-md` or `max-w-7xl` bounds.
- Use white surfaces, pale aqua context, navy hierarchy, and teal/cyan interaction cues.
- Preserve clear inline validation and keyboard-accessible controls.

### Don't

- Do not copy the reference’s monospaced, terminal, ASCII, cream-and-black visual language.
- Do not introduce a second brand palette or a new font family without a product-level decision.
- Do not replace the existing gradient submit action, form semantics, or password toggle pattern casually.
- Do not add decorative dashboards, dense data visualization, or fish imagery before the information architecture needs them.
- Do not use color alone for errors, focus, active navigation, or success.
- Do not change responsive behavior by adding arbitrary custom breakpoints.

## Extension Checklist

## Application Sidebar (FSH-33)

The application-level navigation requirement supersedes the former header-only shell.
Follow the supplied Anufish screenshot: white left rail, original logo, grouped navigation,
pale active row with navy text, compact icons, and a bordered profile card at the bottom.
Use existing brand tokens and Instrument Sans; do not reproduce the screenshot's selection handles.

- Desktop rail: `w-64`, padding `p-4`, logo `w-40`, navigation rows `min-h-11`, `rounded-lg`.
- Shell: `h-dvh`, desktop `lg` rail, fixed `h-16` header; main owns content scroll.
- Sidebar middle navigation owns overflow when the viewport is short; profile and logout remain available.
- Mobile: native modal dialog with the same sidebar component, `w-72 max-w-full`, backdrop,
  close button, Escape dismissal, trapped focus and focus restored to the menu trigger.
- Navigation primitive: icon `size-5`, `gap-3`, `text-sm font-semibold`; active pale background
  and `aria-current`, cyan hover, teal focus-visible ring. Unbuilt destinations show “Segera”.
- Profile primitive: navy name, muted role, `rounded-lg` border, initials avatar `size-10`.
- Public group: Beranda, Jelajahi Ikan, Artikel. User group: Dashboard, Scan Ikan,
  Riwayat Scan, Artikel Saya. Admin group appears only for the admin role.
- Existing homepage contains fish exploration, so Beranda links to its top and Jelajahi Ikan
  to its search section. Dashboard is the same existing homepage; no new feature screens are implied.
- Accessibility: semantic nav/list, skip link, minimum 44px controls, visible focus,
  native POST logout with CSRF protection. Current project has a light theme only.


Before adding a UI surface, confirm:

1. Which existing Blade layout owns the page?
2. Can an existing component express the interaction without duplication?
3. Which named brand or semantic token supplies each color?
4. What are the default, focus, error, disabled, and mobile states?
5. Does the result preserve the current container widths, spacing rhythm, and navigation shell?
6. Are labels, validation, focus order, and Indonesian copy clear without relying on visual styling alone?
