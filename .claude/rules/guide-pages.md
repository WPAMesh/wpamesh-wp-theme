# Guide Page Workflow

Guide/documentation pages use a two-stage workflow:

## Stage 1: Preview

Location: `pages/previews/`

Standalone HTML files with inline CSS for browser preview. Use generic class names:
- `.intro-box`
- `.alert`
- `.info-card`

## Stage 2: WordPress

Location: `pages/wordpress/`

Content fragments using `wpa-*` prefixed classes from `assets/css/theme.css`:
- `.wpa-intro`
- `.wpa-alert`
- `.wpa-card`
- `.wpa-role`

Guide page styles start at approximately line 1524 in theme.css.

## Slash Commands

- `/new-page <brief>` - Create a new preview page based on content description
- `/wp-convert` - Auto-detect and migrate preview pages to WordPress format
