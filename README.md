# ABEX Management Services — website source

Source snapshot of the ABEX Management Services WordPress site on staging
(`https://abex-management.wsdfy.com`). WordPress keeps most of this site in the
database rather than in files, so this repo holds an export of every part that
was built for the project, laid out so changes show up as ordinary diffs.

The live site is the source of truth. This repo is synced **from** it; nothing
here is deployed automatically.

## Layout

| Path | What it is | Where it lives in WordPress |
|---|---|---|
| `wp-content/themes/abex/` | The `abex` child theme, verbatim | Theme files on disk |
| `wp-content/themes/abex/functions.php` | Enqueues, header/footer rendering, the `[abex_enquiry_form]` shortcode and its handler | — |
| `wp-content/themes/abex/assets/abex.css` | Design system: tokens, components, base responsive rules | — |
| `wp-content/themes/abex/assets/abex.js` | Scroll reveal + the "How We Work" panel-stack animation | — |
| `wp-content/themes/abex/assets/abex-logo.css` | Logo as embedded PNG data URIs. No longer used by the header or footer since 2026-11-16 (they use Image blocks with media 275 and 278), still imported by Additional CSS | — |
| `customizer/additional-css.css` | Iteration layer of fixes (desktop, tablet, mobile) | Appearance → Customize → Additional CSS (theme `abex`) |
| `elementor/page-27-home.json` | Home page — full Elementor tree with settings | Page 27, `_elementor_data` |
| `elementor/page-28-about.json` | About Us | Page 28, `_elementor_data` |
| `elementor/page-29-services.json` | Services | Page 29, `_elementor_data` |
| `elementor/page-30-clients.json` | Clients (`/clientele/`) | Page 30, `_elementor_data` |
| `elementor/page-31-contact.json` | Contact Us (`/contact/`) | Page 31, `_elementor_data` |
| `elementor/hf-37-header.json` | Header template | `elementor-hf` post 37 |
| `elementor/hf-38-footer.json` | Footer template | `elementor-hf` post 38 |
| `site/settings.json` | Site title, front page, search visibility, site icon, versions | Settings, Reading |
| `site/menu-abex-primary.json` | Primary navigation | Appearance → Menus |
| `site/cf7-form-239.json` | The Contact page enquiry form (fields, mail settings) | Contact → Contact Forms, form 239 |
| `site/media-manifest.json` | Every upload with its ID, URL, alt text and size (no binaries) | Media Library |

The Elementor files are the round-trippable export (`elements` array) that
Elementor itself stores, pretty-printed so diffs are readable.

## Stack

- WordPress, theme **Hello Elementor 3.5.2** with the **`abex`** child theme
- **Elementor 4.3.6** (free — no Pro) and **Ultimate Addons for Elementor 2.9.6**
  for the header and footer templates
- **Contact Form 7 6.2.5** for the Contact page form (Elementor's own Form
  widget needs Pro)
- Fonts: Archivo (display) and Public Sans (body), from Google Fonts

## Before launch

- Both enquiry forms send to a **test address** on purpose:
  - Home: change `abex_enquiry_recipient()` in `functions.php` (or filter
    `abex_enquiry_recipient`) to the client's inbox.
  - Contact page: change the recipient of Contact Form 7 form 239
    (Contact → Contact Forms → ABEX Contact Page Enquiry → Mail).
- `search_engine_visible` is `false` (staging, `noindex`). Turn it on at launch.
- No SMTP plugin is installed, so `wp_mail()` falls back to PHP `mail()`.

## Sync history

See `SYNC-*.md` for what each sync from the live site picked up.
