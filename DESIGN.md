---
name: Professional Network Alpha
colors:
  surface: '#f5faff'
  surface-dim: '#caddeb'
  surface-bright: '#f5faff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eaf5ff'
  surface-container: '#def0ff'
  surface-container-high: '#d8ebfa'
  surface-container-highest: '#d2e5f4'
  on-surface: '#0b1d28'
  on-surface-variant: '#404850'
  inverse-surface: '#21323e'
  inverse-on-surface: '#e4f3ff'
  outline: '#707881'
  outline-variant: '#bfc7d1'
  surface-tint: '#006398'
  primary: '#005d8f'
  on-primary: '#ffffff'
  primary-container: '#0077b5'
  on-primary-container: '#f3f7ff'
  inverse-primary: '#93ccff'
  secondary: '#7c5800'
  on-secondary: '#ffffff'
  secondary-container: '#feb700'
  on-secondary-container: '#6b4b00'
  tertiary: '#005f85'
  on-tertiary: '#ffffff'
  tertiary-container: '#0079a8'
  on-tertiary-container: '#f0f8ff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#cde5ff'
  primary-fixed-dim: '#93ccff'
  on-primary-fixed: '#001d32'
  on-primary-fixed-variant: '#004b74'
  secondary-fixed: '#ffdea8'
  secondary-fixed-dim: '#ffba20'
  on-secondary-fixed: '#271900'
  on-secondary-fixed-variant: '#5e4200'
  tertiary-fixed: '#c6e7ff'
  tertiary-fixed-dim: '#82cfff'
  on-tertiary-fixed: '#001e2d'
  on-tertiary-fixed-variant: '#004c6b'
  background: '#f5faff'
  on-background: '#0b1d28'
  surface-variant: '#d2e5f4'
typography:
  display-lg:
    fontFamily: IBM Plex Sans
    fontSize: 48px
    fontWeight: '600'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: IBM Plex Sans
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
  headline-lg-mobile:
    fontFamily: IBM Plex Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  title-md:
    fontFamily: IBM Plex Sans
    fontSize: 18px
    fontWeight: '500'
    lineHeight: 24px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-lg:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.05em
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 14px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  gutter: 16px
  margin-mobile: 16px
  margin-desktop: 24px
  max-width: 1200px
---

## Brand & Style
The design system is engineered for a high-stakes professional environment where efficiency and clarity are paramount. It targets a workforce that values reliability, networking, and career progression. 

The aesthetic is **Corporate Modern** with a focus on high-density information and clear hierarchical pathways. By combining the authority of LinkedIn’s blue with a high-contrast accent and cold neutrals, the UI evokes a sense of "digital workspace"—clean, organized, and focused. The emotional response is one of confidence and utility, avoiding unnecessary ornamentation in favor of precise, functional layouts that feel both established and forward-thinking.

## Colors
The palette is rooted in a cold-neutral spectrum to maintain a professional, technological feel. 
- **Primary:** The signature blue is used for key actions, navigation, and branding.
- **Secondary (Accent):** The bright yellow is reserved for high-impact call-outs, notifications, and "premium" feature highlights. Use it sparingly to maintain its functional weight.
- **Neutrals:** Surfaces utilize bluish-grays (Cold Neutrals) to reduce eye strain while maintaining a crisp, cool atmosphere. Surface-Dim provides subtle contrast for sidebars and background containers.
- **Hierarchy:** High contrast between text (on-surface) and backgrounds ensures maximum readability for data-heavy views.

## Typography
The system uses a pairing of **IBM Plex Sans** for headings and **Inter** for body content. This combination balances corporate structure with modern UI utility.
- **Headlines:** IBM Plex Sans provides a technical, engineered feel that communicates authority.
- **Body:** Inter is used for its exceptional legibility at small sizes, crucial for feeds, profiles, and data tables.
- **Labels:** Uppercase labels with slight tracking are used for metadata and category tags to differentiate them from interactive body text.

## Layout & Spacing
The design system employs a **Fixed Grid** model for desktop to ensure content remains scannable and professional, centering the main feed.
- **Grid:** A 12-column system for desktop, 8-column for tablet, and 4-column for mobile.
- **Rhythm:** An 8px linear scale is the foundation for all spacing. 16px is the standard "gutter" between components.
- **Density:** Information density is medium-to-high. Use 16px (md) padding for cards and 8px (sm) for nested elements to keep related items grouped tightly.

## Elevation & Depth
Depth is achieved through **Tonal Layering** and **Low-Contrast Outlines** rather than heavy shadows.
- **Surfaces:** The primary background uses the cold neutral (#F0F2F5). Content cards and containers use pure White (#FFFFFF).
- **Outlines:** Use 1px borders in `Surface-Dim` (#E1E4E8) to define cards and input fields. This creates a "flat-plus" look that feels architectural.
- **Shadows:** If needed for modals or floating action buttons, use a very soft, diffused shadow: `0px 4px 12px rgba(0, 119, 181, 0.08)`. Note the subtle blue tint in the shadow to harmonize with the primary brand color.

## Shapes
A **Soft** approach to roundedness is used to maintain a professional and efficient tone.
- **Standard UI:** 4px (0.25rem) radius for buttons, inputs, and small components.
- **Cards:** 8px (0.5rem) radius for primary content containers to provide a gentle visual distinction from the background.
- **Avatar:** Circles are used for user profiles to provide a human contrast to the otherwise geometric and structured layout.

## Components
- **Buttons:** Primary buttons use the Primary Blue with white text. Secondary buttons use a Primary Blue outline with a transparent background. Special "Join" or "CTA" buttons may use the Accent Yellow with dark text to break the visual rhythm.
- **Input Fields:** Use a white background with a 1px `Surface-Dim` border. On focus, the border transitions to Primary Blue.
- **Chips:** Small, 4px rounded tags with a light cold-neutral background and dark gray text. Active states use a Primary Blue tint.
- **Cards:** White surfaces with a 1px border. Avoid heavy shadows; rely on the `Surface-Dim` background to make the white cards pop.
- **Lists:** Clean, separated by 1px dividers. Use ample horizontal padding (16px) to maintain a professional, airy feel within dense data sets.