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
| `wp-content/themes/abex/assets/abex-logo.css` | Header and footer logo as embedded PNG data URIs | — |
| `customizer/additional-css.css` | Iteration layer of fixes (desktop, tablet, mobile) | Appearance → Customize → Additional CSS (theme `abex`) |
| `elementor/page-27-home.json` | Home page — full Elementor tree with settings | Page 27, `_elementor_data` |
| `elementor/hf-37-header.json` | Header template | `elementor-hf` post 37 |
| `elementor/hf-38-footer.json` | Footer template | `elementor-hf` post 38 |
| `site/settings.json` | Site title, front page, search visibility, site icon, versions | Settings, Reading |
| `site/menu-abex-primary.json` | Primary navigation | Appearance → Menus |
| `site/media-manifest.json` | Every upload with its ID, URL, alt text and size (no binaries) | Media Library |

The Elementor files are the round-trippable export (`elements` array) that
Elementor itself stores, pretty-printed so diffs are readable.

## Stack

- WordPress, theme **Hello Elementor 3.5.2** with the **`abex`** child theme
- **Elementor 4.3.6** (free — no Pro) and **Ultimate Addons for Elementor 2.9.6**
  for the header and footer templates
- Fonts: Archivo (display) and Public Sans (body), from Google Fonts

## Before launch

- The enquiry form sends to a **test address** on purpose. Change
  `abex_enquiry_recipient()` in `functions.php` (or filter
  `abex_enquiry_recipient`) to the client's inbox.
- `search_engine_visible` is `false` (staging, `noindex`). Turn it on at launch.
- No SMTP plugin is installed, so `wp_mail()` falls back to PHP `mail()`.
- About Us, Services, Clients and Contact Us are published but still empty.

## Sync history

See `SYNC-*.md` for what each sync from the live site picked up.
