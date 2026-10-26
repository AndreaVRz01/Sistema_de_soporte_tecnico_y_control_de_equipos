---
name: Tactile Enterprise Asset Control
colors:
  surface: '#fff8f7'
  surface-dim: '#ffcfcb'
  surface-bright: '#fff8f7'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#fff0ef'
  surface-container: '#ffe9e7'
  surface-container-high: '#ffe1df'
  surface-container-highest: '#ffdad7'
  on-surface: '#410004'
  on-surface-variant: '#42474d'
  inverse-surface: '#5f1415'
  inverse-on-surface: '#ffedeb'
  outline: '#72787e'
  outline-variant: '#c2c7cd'
  surface-tint: '#3d627d'
  primary: '#001a2b'
  on-primary: '#ffffff'
  primary-container: '#003049'
  on-primary-container: '#7398b6'
  inverse-primary: '#a5cbea'
  secondary: '#835500'
  on-secondary: '#ffffff'
  secondary-container: '#feb956'
  on-secondary-container: '#734a00'
  tertiary: '#675f2b'
  on-tertiary: '#ffffff'
  tertiary-container: '#b6ac6f'
  on-tertiary-container: '#47400e'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#cae6ff'
  primary-fixed-dim: '#a5cbea'
  on-primary-fixed: '#001e2f'
  on-primary-fixed-variant: '#244a64'
  secondary-fixed: '#ffddb4'
  secondary-fixed-dim: '#feb956'
  on-secondary-fixed: '#291800'
  on-secondary-fixed-variant: '#633f00'
  tertiary-fixed: '#efe4a2'
  tertiary-fixed-dim: '#d2c888'
  on-tertiary-fixed: '#201c00'
  on-tertiary-fixed-variant: '#4e4715'
  background: '#fff8f7'
  on-background: '#410004'
  surface-variant: '#ffdad7'
typography:
  headline-xl:
    fontFamily: Space Grotesk
    fontSize: 3rem
    fontWeight: '700'
    lineHeight: '1.15'
    letterSpacing: -0.03em
  headline-xl-mobile:
    fontFamily: Space Grotesk
    fontSize: 2rem
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Space Grotesk
    fontSize: 2.25rem
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Space Grotesk
    fontSize: 1.625rem
    fontWeight: '600'
    lineHeight: '1.25'
    letterSpacing: -0.015em
  headline-md:
    fontFamily: Space Grotesk
    fontSize: 1.5rem
    fontWeight: '600'
    lineHeight: '1.3'
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Space Grotesk
    fontSize: 1.25rem
    fontWeight: '600'
    lineHeight: '1.4'
    letterSpacing: '0'
  body-lg:
    fontFamily: Hanken Grotesk
    fontSize: 1.125rem
    fontWeight: '400'
    lineHeight: '1.55'
    letterSpacing: -0.01em
  body-md:
    fontFamily: Hanken Grotesk
    fontSize: 0.9375rem
    fontWeight: '400'
    lineHeight: '1.5'
    letterSpacing: '0'
  body-sm:
    fontFamily: Hanken Grotesk
    fontSize: 0.8125rem
    fontWeight: '400'
    lineHeight: '1.45'
    letterSpacing: 0.005em
  label-lg:
    fontFamily: JetBrains Mono
    fontSize: 0.875rem
    fontWeight: '500'
    lineHeight: '1.3'
    letterSpacing: 0.02em
  label-md:
    fontFamily: JetBrains Mono
    fontSize: 0.75rem
    fontWeight: '500'
    lineHeight: '1.3'
    letterSpacing: 0.04em
  label-sm:
    fontFamily: JetBrains Mono
    fontSize: 0.6875rem
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: 0.06em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.5rem
  gutter-mobile: 0.75rem
  margin: 2rem
  margin-mobile: 1rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2.5rem
---

## Brand & Style

This design system establishes an authoritative, tactile, and highly operational visual language tailored for institutional asset tracking, hardware lending logs, and urgent maintenance ticketing workflows. 

The aesthetic merges **Modern Functional Neumorphism** with **High-Density Enterprise Clarity**. Rather than relying on low-contrast, purely decorative soft-plastic trends, this system uses dual-tone extruded planes, sculpted debossed surfaces for interactive states, and razor-sharp typographic hierarchies to guarantee WCAG AAA legibility. The interface evokes physical hardware consoles: reliable, calibrated, and reassuringly solid.

### Core Visual Principles
- **Tactile Materiality:** Surfaces mimic physical anodized plates and precision-molded panels. Cards and containers float via subtle, balanced dual-tone lighting models; actionable inputs appear delicately milled or recessed into the surface.
- **Instrument Precision:** Operations, ticket queues, and loan checkout tables prioritize clean data geometry, strict horizontal alignments, and clear scannability under high operational stress.
- **Calibrated Feedback:** Statuses are unmistakable, using semantic state indicators engineered to differentiate active inventory, field loans, maintenance locks, and write-offs.

## Colors

The palette leverages a dignified chromatic foundation anchored by naval depth and warm amber-custard utility tones, reinforced by crimson alerts.

### Light Mode Architecture
- **Base Canvas (`surface-ground`):** `#F0F4F8` — A crisp, calibrated light slate-blue surface providing the required midtone foundation for dual-tone light/shadow extrusion.
- **Primary Surface (`surface-panel`):** `#F8FAFC` — Slightly raised container plane.
- **Recessed Surface (`surface-inset`):** `#E4EBF2` — Milled foundation for search fields, inputs, and toggles.
- **Brand Primary (`#003049`):** Command blue for primary actions, critical metrics, and definitive typography.
- **Accent Warmth (`#E09F3E` & `#FFF3B0`):** Honey Bronze acts as interactive highlights, focused tabs, and active warnings; Vanilla Custard serves as tinted tag fills and gentle badge backdrops.
- **Critical Action & Risk (`#9E2A2B` & `#540B0E`):** Brown Red governs severe maintenance alerts, damages, and deletions; Night Bordeaux anchors structural dark borders and destructive emphasis.

### Dark Mode Architecture
- **Base Canvas (`surface-ground-dark`):** `#001A29` — A profound midnight-navy canvas.
- **Elevated Surface (`surface-panel-dark`):** `#00253A` — Elevated instrument tiers receiving soft ambient light.
- **Recessed Surface (`surface-inset-dark`):** `#00131E` — Deep debossed fields.
- **Accent Illumination:** `#E09F3E` steps forward as the primary interactive glow and cursor anchor, with `#FFF3B0` supplying high-contrast text on dark buttons and pill highlights.

### Operational Status Mapping
- **Activo (Available):** Fill `#E6F4EA`, Text `#137333`, Border `#34A853` (Dark mode: Fill `#0D2818`, Text `#81C995`).
- **En Préstamo (On Loan):** Fill `#FFF8E1`, Text `#B06000`, Border `#E09F3E` (Dark mode: Fill `#2D2006`, Text `#FDD663`).
- **En Reparación (In Maintenance):** Fill `#FCE8E6`, Text `#9E2A2B`, Border `#EA4335` (Dark mode: Fill `#311012`, Text `#F28B82`).
- **Baja Lógica (Decommissioned):** Fill `#EAECEF`, Text `#4A5568`, Border `#9AA0A6` (Dark mode: Fill `#1A232E`, Text `#9AA0A6`).

## Typography

The typographic hierarchy balances industrial engineering precision with fluid readability. 

- **Display & Headings (Space Grotesk):** Gives module headers, asset counts, and operational KPIs a sharp, technological authority.
- **Body & Data Tables (Hanken Grotesk):** Provides clean proportions and deep clarity for ticket logs, technician descriptions, and equipment manifests.
- **Technical Labels & Metadata (JetBrains Mono):** Formatted for asset serial codes (`#AST-9823-X`), timestamp logs, loan expiry countdowns, and maintenance priority badges to eliminate scanning ambiguity.

## Layout & Spacing

The layout is built upon an 8-point base rhythm engineered for dense institutional information architecture.

### Grid & Breakpoints
- **Desktop (>= 1280px):** 12-column layout with 24px (`1.5rem`) gutters and 32px (`2rem`) margins. Supports persistent compact sidebar navigation expandable via the hamburger toggle into a full drawer panel with asset tree structures.
- **Tablet (768px – 1279px):** 8-column layout with 16px gutters and 24px margins. Sidebar collapses to an icon bar; data tables convert to responsive horizontal scroll containers with frozen primary asset keys.
- **Mobile (< 768px):** 4-column layout with 12px (`0.75rem`) gutters and 16px (`1rem`) margins. Navigation shifts into a high-depth off-canvas drawer accessed via a tactile hamburger trigger.

### Spatial Discipline
Cards, tables, and asset grids maintain strict separation using `space-lg` margins. Dense internal controls (e.g., ticket filter ribbons, serial badge arrays) rely on `space-xs` and `space-sm` for immediate spatial grouping.

## Elevation & Depth

Visual depth is achieved through **Dual-Tone Structural Neumorphism**, engineered to ensure accessibility through visible boundary definition. Pure flat borders are avoided in favor of light-source extrusion.

### Light Mode Dual-Light Model (Light from Top-Left at 315°)
- **Surface Extrusion (Default Raised Card):** 
  - Highlights: `-6px -6px 14px rgba(255, 255, 255, 0.95)`
  - Shadows: `6px 6px 16px rgba(0, 48, 73, 0.08)`
  - Micro-border: `1px solid rgba(255, 255, 255, 0.6)`
- **Surface Inset (Recessed Fields, Active Buttons, Search):** 
  - Highlights: `inset 3px 3px 6px rgba(0, 48, 73, 0.08)`
  - Shadows: `inset -3px -3px 6px rgba(255, 255, 255, 0.9)`
  - Micro-border: `1px solid rgba(0, 48, 73, 0.06)`
- **Floating Overlays (Hamburger Drawer, Predictive Search Dropdown):**
  - Ambient Shadow: `0 20px 35px -10px rgba(0, 48, 73, 0.18), 0 1px 3px rgba(0, 48, 73, 0.05)`

### Dark Mode Dual-Light Model
- **Surface Extrusion (Default Raised Card):**
  - Highlights: `-4px -4px 12px rgba(255, 243, 176, 0.04)`
  - Shadows: `6px 6px 18px rgba(0, 10, 18, 0.65)`
  - Micro-border: `1px solid rgba(224, 159, 62, 0.12)`
- **Surface Inset (Recessed Fields):**
  - Shadows: `inset 3px 3px 6px rgba(0, 10, 18, 0.8), inset -2px -2px 5px rgba(255, 243, 176, 0.03)`
  - Micro-border: `1px solid rgba(0, 0, 0, 0.5)`
- **Floating Overlays:**
  - Ambient Shadow: `0 24px 48px -12px rgba(0, 0, 0, 0.85)`
  - Edge Ring: `1px solid rgba(224, 159, 62, 0.2)`

## Shapes

The design system standardizes on generous curvature that softens enterprise austerity while maintaining clean internal grid discipline.

- **Primary Cards and Panels:** `rounded-2xl` (`1.5rem` / `24px`) provides a smooth visual perimeter that captures the dual-light shadows evenly without sharp corner artifacts.
- **Buttons, Field Inputs & Search Bars:** `rounded-xl` (`1rem` / `16px`) ensuring structural consistency with card outer bounds.
- **Status Badges, Chips & Meta Pills:** `rounded-full` (`9999px`) creating an immediate visual distinction between actionable structural containers and informational status tags.

## Components

### Buttons
- **Primary:** Deep Space Blue (`#003049`) fill with crisp text (`#FFFFFF` or `#FFF3B0`). Extruded light-source dual shadow. On `:active`, transitions to an inset shadow with a `scale(0.98)` translation.
- **Secondary (Tactile Neutral):** Surface-colored button with raised dual-tone neumorphic shadows and `#003049` (or `#FFF3B0` in dark mode) text.
- **Warning / Maintenance Action:** Honey Bronze (`#E09F3E`) fill with Deep Space Blue text for checkout authorizations; Brown Red (`#9E2A2B`) fill with white text for destructive or decommissioning events.

### Micro-Interactive Predictive Search Bar
- **Surface:** Recessed debossed plane (`surface-inset`) with subtle inner shadows.
- **Iconography:** Leading search glyph in Honey Bronze (`#E09F3E`).
- **Predictive Dropdown:** Elevates as a floating card (`rounded-2xl`) directly below the input upon keydown. Contains auto-categorized result sections (*Activos*, *Préstamos*, *Técnicos*) highlighted with high-contrast JetBrains Mono serial badges.

### Status Badges (Pills)
- Rendered in `rounded-full` with a solid 1px border matching the status tint.
- **Activo:** Emerald-toned badge displaying a solid 6px pulse dot.
- **En Préstamo:** Honey Bronze tint accompanied by loan return countdown indicators.
- **En Reparación:** Brown Red tint indicating ticket urgency level.
- **Baja Lógica:** Muted slate surface with a strike-through serial number reference.

### Cards & Data Lists
- **Asset / Ticket Cards:** `rounded-2xl` elevated containers with internal padding `space-lg`. Hovering triggers a smooth elevation boost (`translateY(-2px)` with expanded shadow diffusion).
- **Data Tables:** Outer wrapper enclosed in `rounded-2xl` with subtle extrusion. Headers use JetBrains Mono `label-md` with sticky placement. Rows alternate subtle contrast shifts with a tactile debossed highlight on row selection.

### Form Inputs, Checkboxes & Radios
- **Inputs:** Inset debossed surfaces. Focus states introduce a 1.5px outer glow in Honey Bronze (`#E09F3E`).
- **Checkboxes & Radios:** Sculpted inset wells. Selected state pops upward with a vibrant `#003049` (light) or `#E09F3E` (dark) extruded indicator icon.

### Navigation Drawer (Hamburger Trigger)
- **Hamburger Trigger:** Tactile square button (`rounded-xl`) with physical press feedback.
- **Drawer Panel:** Floats from the left canvas edge with backdrop blur (`blur(12px)`) and a deep ambient drop-shadow. Contains profile telemetry, rapid loan check-in buttons, and expandable hierarchical category trees.

### Empty State Illustrations
- Built with architectural, isometric line-work rendered in Deep Space Blue and Honey Bronze line weights.
- Complemented by debossed CTA buttons encouraging the user to *“Registrar Nuevo Activo”* or *“Crear Ticket de Mantenimiento”*.