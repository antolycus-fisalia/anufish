# Anufish Design System

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

Before adding a UI surface, confirm:

1. Which existing Blade layout owns the page?
2. Can an existing component express the interaction without duplication?
3. Which named brand or semantic token supplies each color?
4. What are the default, focus, error, disabled, and mobile states?
5. Does the result preserve the current container widths, spacing rhythm, and navigation shell?
6. Are labels, validation, focus order, and Indonesian copy clear without relying on visual styling alone?
