---
name: wp-convert
description: Migrate preview HTML pages to WordPress format by converting CSS classes and stripping wrappers. Use when the user wants to convert a preview page or prepare content for WordPress.
allowed-tools: Read, Write, Glob, Edit
---

# /wp-convert - Migrate Preview Pages to WordPress

Auto-detect preview pages without WordPress versions and convert them.

## Workflow

1. **Scan directories**
   - List files in `pages/previews/`
   - List files in `pages/wordpress/`
   - Identify previews without corresponding `wp-*.html` versions

2. **For each new preview file:**
   - Extract content body (strip HTML wrapper, head, scripts)
   - Convert CSS classes to `wpa-*` prefix
   - Add WordPress instruction header comment
   - Generate TOC without emojis in link text
   - Save to `pages/wordpress/wp-{name}.html`

3. **Check for missing theme styles**
   - If preview uses classes not yet in theme CSS, add them to `assets/css/theme.css`
   - Follow existing pattern in "Guide Page Styles" section (line ~1417)

4. **Report results**
   - List converted files
   - List any new CSS added to theme
   - Remind user to test in WordPress

## Class Mapping (Preview → WordPress)

| Preview Class | WordPress Class |
|--------------|-----------------|
| `.page-layout` | `.wpa-page-layout` |
| `.main-content` | `.wpa-main-content` |
| `.toc-container` | `.wpa-toc-container` |
| `.toc` | `.wpa-toc` |
| `.toc-title` | `.wpa-toc-title` |
| `.toc-list` | `.wpa-toc-list` |
| `.toc-sub` | `.wpa-toc-sub` |
| `.article-content` | `.wpa-guide` |
| `.intro-box` | `.wpa-intro` |
| `.quick-answer` | `.wpa-quick-answer` |
| `.quick-answer-title` | `.wpa-quick-answer-title` |
| `.alert` | `.wpa-alert` |
| `.alert-danger` | `.wpa-alert-danger` |
| `.alert-warning` | `.wpa-alert-warning` |
| `.alert-info` | `.wpa-alert-info` |
| `.alert-success` | `.wpa-alert-success` |
| `.alert-title` | `.wpa-alert-title` |
| `.info-card` | `.wpa-card` |
| `.info-card.highlight` | `.wpa-card.wpa-card-highlight` |
| `.info-card-header` | (remove - not used in WP) |
| `.info-card-title` | `.wpa-card-title` |
| `.info-card-emoji` | (inline in title) |
| `.settings-table` | `.wpa-table` |
| `.steps` | `.wpa-steps` |
| `.steps-title` | `.wpa-steps-title` |
| `.summary-box` | `.wpa-summary` |
| `.cta-btn` | `.wpa-cta` |
| `blockquote` | `.wpa-note` |

## WordPress Output Template

```html
<!--
================================================================================
{Page Title}
================================================================================
WordPress Ready - Styles are in the WPAMesh theme (assets/css/theme.css)

INSTRUCTIONS:
1. Create a new Page in WordPress
2. Switch to the Code Editor (⋮ menu → Code Editor) or use a Custom HTML block
3. Paste this entire content (no inline styles needed - theme provides .wpa-* classes)
4. Update/Publish

NOTE: TOC scroll tracking requires the theme's guide-toc.js script to be enqueued.
================================================================================
-->

<div class="wpa-page-layout">
<div class="wpa-main-content">
<div class="wpa-guide">

{converted content}

</div>
</div>

<!-- Table of Contents - Right Side -->
<aside class="wpa-toc-container">
<nav class="wpa-toc">
<div class="wpa-toc-title">On This Page</div>
<ul class="wpa-toc-list">
{toc items - NO emojis in link text}
</ul>
</nav>
</aside>
</div>
```

## Content Transformations

### Remove from preview:
- `<!DOCTYPE html>`, `<html>`, `<head>`, `<body>` tags
- All `<style>` blocks
- All `<script>` blocks
- `.content-header` section (WordPress provides page title)
- `.content-footer` section
- Google Fonts links

### Transform:
- Convert class names per mapping table
- `<blockquote>` → `<div class="wpa-note">`
- `.info-card-header` structure → simpler `.wpa-card-title` with emoji inline
- Internal links: `href="#section"` stays, external links stay
- TOC link text: strip emojis (e.g., "Firmware 🔢" → "Firmware")

### Preserve:
- All `id` attributes on headings
- Table structure
- List structure
- Code blocks
- External links with `target="_blank"`

## Adding New Theme Styles

If a preview uses components not in `assets/css/theme.css`:

1. Read the inline styles from the preview file
2. Convert to `wpa-*` naming
3. Add to theme.css in the "Guide Page Styles" section (after line 1417)
4. Use existing CSS custom properties: `--wpamesh-gold`, `--wpamesh-green`, etc.

## Filename Convention

- Preview: `antenna-guide.html` or `wpamesh-getting-started.html`
- WordPress: `wp-antenna-guide.html` or `wp-getting-started.html`
- Strip `wpamesh-` prefix when adding `wp-` prefix
